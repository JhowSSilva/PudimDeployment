<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGitHubTokenValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasGitHubConnected()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'GitHub account not connected'], 403);
            }

            return redirect()->route('github.connect')
                ->with('info', 'Please connect your GitHub account to access this feature');
        }

        // Check if token has expired
        if ($user->github_token_expires_at && $user->github_token_expires_at->isPast()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'GitHub token expired, please reconnect'], 403);
            }

            return redirect()->route('github.settings')
                ->with('warning', 'Your GitHub token has expired. Please reconnect your account.');
        }

        return $next($request);
    }
}
