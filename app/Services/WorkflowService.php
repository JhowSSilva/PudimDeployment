<?php

namespace App\Services;

use App\Models\GitHubRepository;
use App\Models\GitHubWorkflow;
use App\Models\GitHubWorkflowRun;
use App\Models\User;
use Github\Exception\RuntimeException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    protected GitHubService $github;
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->github = new GitHubService($user);
    }

    /**
     * Sync workflows for a repository
     */
    public function syncWorkflows(GitHubRepository $repository): void
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $workflows = $this->github->getClient()
                ->api('repo')
                ->workflows()
                ->all($owner, $repo);

            if (isset($workflows['workflows'])) {
                foreach ($workflows['workflows'] as $workflowData) {
                    $this->syncWorkflow($repository, $workflowData);
                }
            }
        } catch (RuntimeException $e) {
            Log::error("Failed to sync workflows for {$repository->full_name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync a single workflow
     */
    protected function syncWorkflow(GitHubRepository $repository, array $workflowData): GitHubWorkflow
    {
        return GitHubWorkflow::updateOrCreate(
            [
                'repository_id' => $repository->id,
                'github_id' => $workflowData['id'],
            ],
            [
                'name' => $workflowData['name'],
                'path' => $workflowData['path'],
                'state' => $workflowData['state'],
                'github_created_at' => $workflowData['created_at'],
                'github_updated_at' => $workflowData['updated_at'],
                'metadata' => $workflowData,
            ]
        );
    }

    /**
     * Get workflow runs
     */
    public function getWorkflowRuns(GitHubRepository $repository, ?GitHubWorkflow $workflow = null, int $perPage = 30): array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $url = "https://api.github.com/repos/{$owner}/{$repo}/actions/runs";
            
            $params = ['per_page' => $perPage];
            
            if ($workflow) {
                $url = "https://api.github.com/repos/{$owner}/{$repo}/actions/workflows/{$workflow->github_id}/runs";
            }

            $response = Http::withToken($token)
                ->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Failed to get workflow runs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync workflow runs
     */
    public function syncWorkflowRuns(GitHubRepository $repository, ?GitHubWorkflow $workflow = null): void
    {
        $runsData = $this->getWorkflowRuns($repository, $workflow);

        if (isset($runsData['workflow_runs'])) {
            foreach ($runsData['workflow_runs'] as $runData) {
                $this->syncWorkflowRun($repository, $runData);
            }
        }
    }

    /**
     * Sync a single workflow run
     */
    public function syncWorkflowRun(GitHubRepository $repository, array $runData): GitHubWorkflowRun
    {
        $workflow = GitHubWorkflow::where('repository_id', $repository->id)
            ->where('github_id', $runData['workflow_id'])
            ->first();

        return GitHubWorkflowRun::updateOrCreate(
            [
                'repository_id' => $repository->id,
                'github_id' => $runData['id'],
            ],
            [
                'workflow_id' => $workflow?->id,
                'name' => $runData['name'],
                'head_branch' => $runData['head_branch'],
                'head_sha' => $runData['head_sha'],
                'status' => $runData['status'],
                'conclusion' => $runData['conclusion'],
                'event' => $runData['event'],
                'html_url' => $runData['html_url'],
                'github_created_at' => $runData['created_at'],
                'github_updated_at' => $runData['updated_at'],
                'started_at' => $runData['run_started_at'] ?? null,
                'completed_at' => $runData['updated_at'],
                'run_number' => $runData['run_number'],
                'metadata' => $runData,
            ]
        );
    }

    /**
     * Trigger workflow dispatch
     */
    public function dispatchWorkflow(
        GitHubRepository $repository,
        GitHubWorkflow $workflow,
        string $ref = 'main',
        array $inputs = []
    ): bool {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $workflowId = $workflow->github_id;
            
            $response = Http::withToken($token)
                ->post("https://api.github.com/repos/{$owner}/{$repo}/actions/workflows/{$workflowId}/dispatches", [
                    'ref' => $ref,
                    'inputs' => $inputs,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to dispatch workflow: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel workflow run
     */
    public function cancelWorkflowRun(GitHubRepository $repository, GitHubWorkflowRun $run): bool
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $runId = $run->github_id;
            
            $response = Http::withToken($token)
                ->post("https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}/cancel");

            if ($response->successful()) {
                $run->update(['status' => 'completed', 'conclusion' => 'cancelled']);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Failed to cancel workflow run: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rerun workflow
     */
    public function rerunWorkflow(GitHubRepository $repository, GitHubWorkflowRun $run): bool
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $runId = $run->github_id;
            
            $response = Http::withToken($token)
                ->post("https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}/rerun");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to rerun workflow: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get workflow logs
     */
    public function getWorkflowLogs(GitHubRepository $repository, GitHubWorkflowRun $run): ?string
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            $runId = $run->github_id;
            
            $response = Http::withToken($token)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}/logs");

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Failed to get workflow logs: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get repository secrets
     */
    public function getSecrets(GitHubRepository $repository): array
    {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            $token = $this->user->getGitHubToken();
            
            $response = Http::withToken($token)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/actions/secrets");

            if ($response->successful()) {
                return $response->json()['secrets'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Failed to get secrets: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create or update secret
     */
    public function createOrUpdateSecret(
        GitHubRepository $repository,
        string $secretName,
        string $secretValue
    ): bool {
        try {
            [$owner, $repo] = explode('/', $repository->full_name);
            
            // Get public key for encryption
            $token = $this->user->getGitHubToken();
            $publicKeyResponse = Http::withToken($token)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/actions/secrets/public-key");

            if (!$publicKeyResponse->successful()) {
                return false;
            }

            $publicKeyData = $publicKeyResponse->json();
            $encryptedValue = $this->encryptSecret($secretValue, $publicKeyData['key']);

            $response = Http::withToken($token)
                ->put("https://api.github.com/repos/{$owner}/{$repo}/actions/secrets/{$secretName}", [
                    'encrypted_value' => $encryptedValue,
                    'key_id' => $publicKeyData['key_id'],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to create/update secret: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Encrypt secret using libsodium
     */
    protected function encryptSecret(string $secretValue, string $publicKey): string
    {
        if (!function_exists('sodium_crypto_box_seal')) {
            throw new \RuntimeException('libsodium extension is required');
        }

        $publicKeyDecoded = base64_decode($publicKey);
        $encrypted = sodium_crypto_box_seal($secretValue, $publicKeyDecoded);
        
        return base64_encode($encrypted);
    }

    /**
     * Get workflow templates
     */
    public static function getTemplates(): array
    {
        return [
            'laravel' => [
                'name' => 'Laravel Deploy',
                'description' => 'Deploy Laravel application with tests',
                'content' => self::getLaravelTemplate(),
            ],
            'static' => [
                'name' => 'Static Site Deploy',
                'description' => 'Build and deploy static site to GitHub Pages',
                'content' => self::getStaticSiteTemplate(),
            ],
            'nodejs' => [
                'name' => 'Node.js Application',
                'description' => 'Build and test Node.js application',
                'content' => self::getNodeJsTemplate(),
            ],
            'docker' => [
                'name' => 'Docker Build & Push',
                'description' => 'Build Docker image and push to registry',
                'content' => self::getDockerTemplate(),
            ],
        ];
    }

    protected static function getLaravelTemplate(): string
    {
        return <<<'YAML'
name: Laravel Deploy

on:
  push:
    branches: [ main, staging ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_mysql
          
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
        
      - name: Run Tests
        run: php artisan test

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to Server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/html
            git pull origin main
            composer install --no-dev
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
YAML;
    }

    protected static function getStaticSiteTemplate(): string
    {
        return <<<'YAML'
name: Deploy to GitHub Pages

on:
  push:
    branches: [ main ]

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          
      - name: Install and Build
        run: |
          npm install
          npm run build
          
      - name: Deploy to GitHub Pages
        uses: peaceiris/actions-gh-pages@v3
        with:
          github_token: ${{ secrets.GITHUB_TOKEN }}
          publish_dir: ./dist
YAML;
    }

    protected static function getNodeJsTemplate(): string
    {
        return <<<'YAML'
name: Node.js CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  build:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        node-version: [16.x, 18.x]
    steps:
      - uses: actions/checkout@v3
      
      - name: Use Node.js ${{ matrix.node-version }}
        uses: actions/setup-node@v3
        with:
          node-version: ${{ matrix.node-version }}
          
      - run: npm ci
      - run: npm run build --if-present
      - run: npm test
YAML;
    }

    protected static function getDockerTemplate(): string
    {
        return <<<'YAML'
name: Docker Build and Push

on:
  push:
    branches: [ main ]
    tags: [ 'v*' ]

jobs:
  docker:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v2
        
      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKERHUB_USERNAME }}
          password: ${{ secrets.DOCKERHUB_TOKEN }}
          
      - name: Build and push
        uses: docker/build-push-action@v4
        with:
          context: .
          push: true
          tags: user/app:latest
YAML;
    }
}
