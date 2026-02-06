<?php

namespace App\Services;

use App\Models\GitHubRepository;
use App\Models\User;
use Github\Exception\RuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RepositoryService
{
    protected GitHubService $github;
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->github = new GitHubService($user);
    }

    /**
     * Sync repositories from GitHub
     */
    public function syncRepositories(bool $forceRefresh = false): Collection
    {
        try {
            $repositories = $this->github->cached(
                'repositories',
                fn() => $this->fetchAllRepositories(),
                $forceRefresh ? 0 : 600
            );

            foreach ($repositories as $repo) {
                $this->syncRepository($repo);
            }

            // Mark last sync time
            $this->user->github_repositories()
                ->update(['last_synced_at' => now()]);

            return $this->user->githubRepositories()->get();
        } catch (RuntimeException $e) {
            Log::error('Failed to sync GitHub repositories: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch all repositories from GitHub
     */
    protected function fetchAllRepositories(): array
    {
        $allRepos = [];
        $page = 1;
        $perPage = 100;

        do {
            $repos = $this->github->getClient()
                ->currentUser()
                ->repositories([
                    'page' => $page,
                    'per_page' => $perPage,
                    'sort' => 'updated',
                    'direction' => 'desc',
                ]);

            $allRepos = array_merge($allRepos, $repos);
            $page++;
        } while (count($repos) === $perPage);

        return $allRepos;
    }

    /**
     * Sync a single repository
     */
    public function syncRepository(array $repoData): GitHubRepository
    {
        return GitHubRepository::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'github_id' => $repoData['id'],
            ],
            [
                'name' => $repoData['name'],
                'full_name' => $repoData['full_name'],
                'description' => $repoData['description'] ?? null,
                'private' => $repoData['private'],
                'language' => $repoData['language'] ?? null,
                'default_branch' => $repoData['default_branch'] ?? 'main',
                'clone_url' => $repoData['clone_url'],
                'ssh_url' => $repoData['ssh_url'],
                'html_url' => $repoData['html_url'],
                'github_created_at' => $repoData['created_at'],
                'github_updated_at' => $repoData['updated_at'],
                'last_synced_at' => now(),
                'metadata' => [
                    'stargazers_count' => $repoData['stargazers_count'] ?? 0,
                    'watchers_count' => $repoData['watchers_count'] ?? 0,
                    'forks_count' => $repoData['forks_count'] ?? 0,
                    'open_issues_count' => $repoData['open_issues_count'] ?? 0,
                    'size' => $repoData['size'] ?? 0,
                ],
            ]
        );
    }

    /**
     * Get repository from GitHub
     */
    public function getRepository(string $owner, string $repo): array
    {
        try {
            return $this->github->getClient()
                ->repositories()
                ->show($owner, $repo);
        } catch (RuntimeException $e) {
            Log::error("Failed to get repository {$owner}/{$repo}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create new repository on GitHub
     */
    public function createRepository(array $data): GitHubRepository
    {
        try {
            $repoData = $this->github->getClient()
                ->repositories()
                ->create(
                    $data['name'],
                    $data['description'] ?? '',
                    $data['homepage'] ?? '',
                    $data['private'] ?? false,
                    null,
                    $data['has_issues'] ?? true,
                    $data['has_wiki'] ?? true,
                    $data['has_downloads'] ?? true,
                    null,
                    $data['auto_init'] ?? false
                );

            return $this->syncRepository($repoData);
        } catch (RuntimeException $e) {
            Log::error('Failed to create GitHub repository: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Setup webhook for repository
     */
    public function setupWebhook(GitHubRepository $repository, string $webhookUrl, ?string $secret = null): array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);

            $config = [
                'url' => $webhookUrl,
                'content_type' => 'json',
                'insecure_ssl' => 0,
            ];

            if ($secret) {
                $config['secret'] = $secret;
            }

            return $this->github->getClient()
                ->repositories()
                ->hooks()
                ->create($owner, $repo, [
                    'name' => 'web',
                    'active' => true,
                    'events' => ['push', 'pull_request', 'workflow_run', 'deployment'],
                    'config' => $config,
                ]);
        } catch (RuntimeException $e) {
            Log::error("Failed to setup webhook for {$repository->full_name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * List branches
     */
    public function getBranches(GitHubRepository $repository): array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            return $this->github->getClient()
                ->repositories()
                ->branches($owner, $repo);
        } catch (RuntimeException $e) {
            Log::error("Failed to get branches for {$repository->full_name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get repository contents
     */
    public function getContents(GitHubRepository $repository, string $path = '', ?string $ref = null): array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $params = [];
            if ($ref) {
                $params['ref'] = $ref;
            }
            
            return $this->github->getClient()
                ->repositories()
                ->contents()
                ->show($owner, $repo, $path, $params);
        } catch (RuntimeException $e) {
            Log::error("Failed to get contents for {$repository->full_name}/{$path}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get file content
     */
    public function getFileContent(GitHubRepository $repository, string $path, ?string $ref = null): ?string
    {
        try {
            $content = $this->getContents($repository, $path, $ref);
            
            if (isset($content['content'])) {
                return base64_decode($content['content']);
            }
            
            return null;
        } catch (RuntimeException $e) {
            Log::error("Failed to get file content for {$repository->full_name}/{$path}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create or update file
     */
    public function createOrUpdateFile(
        GitHubRepository $repository,
        string $path,
        string $content,
        string $message,
        ?string $sha = null,
        ?string $branch = null
    ): array {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $params = [
                'message' => $message,
                'content' => base64_encode($content),
                'branch' => $branch ?? $repository->default_branch,
            ];

            if ($sha) {
                $params['sha'] = $sha;
            }

            return $this->github->getClient()
                ->repositories()
                ->contents()
                ->createOrUpdate($owner, $repo, $path, $params);
        } catch (RuntimeException $e) {
            Log::error("Failed to create/update file {$path} in {$repository->full_name}: " . $e->getMessage());
            throw $e;
        }
    }
}
