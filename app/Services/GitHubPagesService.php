<?php

namespace App\Services;

use App\Models\GitHubPages;
use App\Models\GitHubRepository;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubPagesService
{
    protected GitHubService $github;
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->github = new GitHubService($user);
    }

    /**
     * Get GitHub Pages status for repository
     */
    public function getPages(GitHubRepository $repository): ?array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $response = Http::withToken($token)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/pages");

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 404) {
                return null; // Pages not enabled
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Failed to get GitHub Pages: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync GitHub Pages status
     */
    public function syncPages(GitHubRepository $repository): ?GitHubPages
    {
        $pagesData = $this->getPages($repository);

        if (!$pagesData) {
            // Delete if exists
            GitHubPages::where('repository_id', $repository->id)->delete();
            return null;
        }

        return GitHubPages::updateOrCreate(
            ['repository_id' => $repository->id],
            [
                'enabled' => true,
                'status' => $pagesData['status'] ?? null,
                'branch' => $pagesData['source']['branch'] ?? 'gh-pages',
                'path' => $pagesData['source']['path'] ?? '/',
                'url' => $pagesData['html_url'] ?? null,
                'custom_domain' => $pagesData['cname'] ?? null,
                'https_enforced' => $pagesData['https_enforced'] ?? true,
                'build_error' => null,
                'last_build_at' => isset($pagesData['updated_at']) ? now() : null,
                'metadata' => $pagesData,
            ]
        );
    }

    /**
     * Enable GitHub Pages
     */
    public function enablePages(
        GitHubRepository $repository,
        string $branch = 'gh-pages',
        string $path = '/'
    ): ?GitHubPages {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            
            // Enable Pages
            $response = Http::withToken($token)
                ->post("https://api.github.com/repos/{$owner}/{$repo}/pages", [
                    'source' => [
                        'branch' => $branch,
                        'path' => $path,
                    ],
                ]);

            if ($response->successful()) {
                return $this->syncPages($repository);
            }

            Log::error("Failed to enable GitHub Pages: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Failed to enable GitHub Pages: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Disable GitHub Pages
     */
    public function disablePages(GitHubRepository $repository): bool
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            
            $response = Http::withToken($token)
                ->delete("https://api.github.com/repos/{$owner}/{$repo}/pages");

            if ($response->successful() || $response->status() === 404) {
                GitHubPages::where('repository_id', $repository->id)->delete();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Failed to disable GitHub Pages: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update GitHub Pages configuration
     */
    public function updatePages(
        GitHubRepository $repository,
        ?string $branch = null,
        ?string $path = null,
        ?string $customDomain = null,
        ?bool $httpsEnforced = null
    ): ?GitHubPages {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $data = [];

            if ($branch && $path) {
                $data['source'] = [
                    'branch' => $branch,
                    'path' => $path,
                ];
            }

            if ($customDomain !== null) {
                $data['cname'] = $customDomain;
            }

            if ($httpsEnforced !== null) {
                $data['https_enforced'] = $httpsEnforced;
            }

            $response = Http::withToken($token)
                ->put("https://api.github.com/repos/{$owner}/{$repo}/pages", $data);

            if ($response->successful()) {
                return $this->syncPages($repository);
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Failed to update GitHub Pages: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get GitHub Pages builds
     */
    public function getBuilds(GitHubRepository $repository): array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $response = Http::withToken($token)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/pages/builds");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Failed to get GitHub Pages builds: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get latest build
     */
    public function getLatestBuild(GitHubRepository $repository): ?array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $response = Http::withToken($token)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/pages/builds/latest");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Failed to get latest GitHub Pages build: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Request page build
     */
    public function requestBuild(GitHubRepository $repository): bool
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $response = Http::withToken($token)
                ->post("https://api.github.com/repos/{$owner}/{$repo}/pages/builds");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to request GitHub Pages build: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if custom domain is verified
     */
    public function verifyCustomDomain(GitHubRepository $repository, string $domain): array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $response = Http::withToken($token)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/pages/domains/{$domain}");

            if ($response->successful()) {
                return $response->json();
            }

            return ['verified' => false];
        } catch (\Exception $e) {
            Log::error("Failed to verify custom domain: " . $e->getMessage());
            return ['verified' => false];
        }
    }
}
