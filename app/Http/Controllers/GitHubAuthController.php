<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubAuthController extends Controller
{
    /**
     * Redirect to GitHub OAuth
     */
    public function redirectToGitHub()
    {
        $clientId = config('services.github.client_id');
        $redirectUri = route('github.callback');
        
        $url = GitHubService::getOAuthUrl($clientId, $redirectUri);
        
        return redirect($url);
    }

    /**
     * Handle GitHub OAuth callback
     */
    public function handleGitHubCallback(Request $request)
    {
        $code = $request->get('code');
        
        if (!$code) {
            return redirect()->route('dashboard')
                ->with('error', 'GitHub authentication failed');
        }

        // Exchange code for token
        $tokenData = $this->exchangeCodeForToken($code);
        
        if (!$tokenData || !isset($tokenData['access_token'])) {
            return redirect()->route('dashboard')
                ->with('error', 'Failed to get access token from GitHub');
        }

        // Get user info from GitHub
        $githubUser = $this->getGitHubUser($tokenData['access_token']);
        
        if (!$githubUser) {
            return redirect()->route('dashboard')
                ->with('error', 'Failed to get user information from GitHub');
        }

        // Update current user with GitHub data
        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'github_id' => $githubUser['id'],
            'github_username' => $githubUser['login'],
        ]);
        
        $user->setGitHubToken($tokenData['access_token']);
        $user->save();

        return redirect()->route('github.repositories.index')
            ->with('success', 'Successfully connected to GitHub!');
    }

    /**
     * Disconnect GitHub
     */
    public function disconnect()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $user->update([
            'github_id' => null,
            'github_username' => null,
            'github_token' => null,
            'github_token_expires_at' => null,
            'github_refresh_token' => null,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'GitHub account disconnected');
    }

    /**
     * Save Personal Access Token
     */
    public function savePersonalToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');
        
        // Test the token
        $githubUser = $this->getGitHubUser($token);
        
        if (!$githubUser) {
            return back()->with('error', 'Invalid GitHub token');
        }

        // Update user
        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'github_id' => $githubUser['id'],
            'github_username' => $githubUser['login'],
        ]);
        
        $user->setGitHubToken($token);
        $user->save();

        return redirect()->route('github.repositories.index')
            ->with('success', 'GitHub Personal Access Token saved!');
    }

    /**
     * Exchange authorization code for access token
     */
    protected function exchangeCodeForToken(string $code): ?array
    {
        try {
            $response = Http::asForm()->post('https://github.com/login/oauth/access_token', [
                'client_id' => config('services.github.client_id'),
                'client_secret' => config('services.github.client_secret'),
                'code' => $code,
            ])->throw();

            $data = [];
            parse_str($response->body(), $data);
            
            return $data;
        } catch (\Exception $e) {
            Log::error('Failed to exchange GitHub code for token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get GitHub user information
     */
    protected function getGitHubUser(string $token): ?array
    {
        try {
            $response = Http::withToken($token)
                ->get('https://api.github.com/user')
                ->throw();

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to get GitHub user: ' . $e->getMessage());
            return null;
        }
    }
}
