<?php

namespace App\Services;

use App\Models\User;
use Github\AuthMethod;
use Github\Client as GitHubClient;
use Github\Exception\RuntimeException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    protected ?GitHubClient $client = null;
    protected ?User $user = null;

    public function __construct(?User $user = null)
    {
        $this->user = $user;
        
        if ($user && $user->hasGitHubConnected()) {
            $this->authenticate($user->getGitHubToken());
        }
    }

    /**
     * Authenticate with GitHub using a token
     */
    public function authenticate(string $token): self
    {
        $this->client = new GitHubClient();
        $this->client->authenticate($token, null, AuthMethod::ACCESS_TOKEN);
        
        return $this;
    }

    /**
     * Get authenticated GitHub client
     */
    public function getClient(): ?GitHubClient
    {
        return $this->client;
    }

    /**
     * Test if the connection is valid
     */
    public function testConnection(): bool
    {
        try {
            $this->client->currentUser()->show();
            return true;
        } catch (RuntimeException $e) {
            Log::error('GitHub connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get authenticated user information
     */
    public function getAuthenticatedUser(): ?array
    {
        try {
            return $this->client->currentUser()->show();
        } catch (RuntimeException $e) {
            Log::error('Failed to get GitHub user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get rate limit information
     */
    public function getRateLimit(): array
    {
        try {
            return $this->client->rateLimit()->getRateLimits();
        } catch (RuntimeException $e) {
            Log::error('Failed to get rate limit: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if we're approaching rate limit
     */
    public function isApproachingRateLimit(): bool
    {
        $rateLimit = $this->getRateLimit();
        
        if (empty($rateLimit['rate'])) {
            return false;
        }

        $remaining = $rateLimit['rate']['remaining'] ?? 0;
        $limit = $rateLimit['rate']['limit'] ?? 5000;
        
        return $remaining < ($limit * 0.1); // Less than 10% remaining
    }

    /**
     * Get repository API
     */
    public function repositories()
    {
        return $this->client->repositories();
    }

    /**
     * Get workflow API
     */
    public function workflows()
    {
        return $this->client->repo()->workflows();
    }

    /**
     * Get webhook API
     */
    public function webhooks()
    {
        return $this->client->repo()->hooks();
    }

    /**
     * Verify webhook signature
     */
    public static function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Cache GitHub API response
     */
    public function cached(string $key, \Closure $callback, int $ttl = 300)
    {
        return Cache::remember(
            'github:' . ($this->user ? "user_{$this->user->id}:" : '') . $key,
            $ttl,
            $callback
        );
    }

    /**
     * Create a GitHub OAuth URL
     */
    public static function getOAuthUrl(string $clientId, string $redirectUri, array $scopes = []): string
    {
        $defaultScopes = ['repo', 'workflow', 'admin:repo_hook', 'read:user', 'user:email'];
        $scopes = empty($scopes) ? $defaultScopes : $scopes;
        
        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => bin2hex(random_bytes(16)),
        ]);
        
        return "https://github.com/login/oauth/authorize?{$params}";
    }

    /**
     * Exchange code for access token
     */
    public static function exchangeCodeForToken(string $clientId, string $clientSecret, string $code): ?array
    {
        try {
            $response = \Http::post('https://github.com/login/oauth/access_token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
            ], [
                'Accept' => 'application/json',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to exchange GitHub code for token: ' . $e->getMessage());
            return null;
        }
    }
}
