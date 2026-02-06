<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Deployment;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeploymentPipeline
{
    private SSHConnectionService $ssh;
    private ArtisanService $artisan;
    private DeploymentService $deployment;

    public function __construct(private Site $site)
    {
        $this->ssh = new SSHConnectionService($site->server);
        $this->artisan = new ArtisanService($site->server);
        $this->deployment = new DeploymentService();
    }

    /**
     * Execute full deployment pipeline
     */
    public function execute(?Deployment $deployment = null): array
    {
        $deployment = $deployment ?? $this->createDeployment();

        try {
            Log::info('Starting deployment pipeline', [
                'site' => $this->site->domain,
                'deployment_id' => $deployment->id
            ]);

            $this->updateDeploymentStatus($deployment, 'running');

            $steps = [];

            // Step 1: Pre-deployment health check
            $steps['health_check'] = $this->healthCheck();
            if (!$steps['health_check']['success']) {
                throw new \Exception('Health check failed: ' . $steps['health_check']['message']);
            }

            // Step 2: Backup before deploy
            $steps['backup'] = $this->backupBeforeDeploy();
            if (!$steps['backup']['success']) {
                Log::warning('Backup failed, continuing anyway', ['site' => $this->site->domain]);
            }

            // Step 3: Enable maintenance mode
            $steps['maintenance_mode'] = $this->artisan->enableMaintenanceMode($this->site);

            // Step 4: Pull latest code
            $steps['pull_code'] = $this->pullCode();
            if (!$steps['pull_code']['success']) {
                throw new \Exception('Failed to pull code: ' . $steps['pull_code']['message']);
            }

            // Step 5: Install dependencies
            $steps['install_dependencies'] = $this->installDependencies();
            if (!$steps['install_dependencies']['success']) {
                throw new \Exception('Failed to install dependencies');
            }

            // Step 6: Build assets
            $steps['build_assets'] = $this->buildAssets();
            if (!$steps['build_assets']['success']) {
                Log::warning('Asset building failed', ['site' => $this->site->domain]);
            }

            // Step 7: Run migrations
            if ($this->site->auto_migrate ?? true) {
                $steps['migrations'] = $this->artisan->runMigrations($this->site);
                if (!$steps['migrations']['success']) {
                    Log::warning('Migrations failed', ['site' => $this->site->domain]);
                }
            }

            // Step 8: Clear and optimize caches
            $steps['cache_clear'] = $this->artisan->clearCache($this->site, ['config', 'route', 'view', 'cache']);
            $steps['optimize'] = $this->artisan->optimize($this->site);

            // Step 9: Restart queue workers
            $steps['restart_queues'] = $this->artisan->restartQueueWorkers($this->site);

            // Step 10: Disable maintenance mode
            $steps['disable_maintenance'] = $this->artisan->disableMaintenanceMode($this->site);

            // Step 11: Post-deployment health check
            $steps['post_health_check'] = $this->healthCheck();
            if (!$steps['post_health_check']['success']) {
                // Rollback if health check fails
                $steps['rollback'] = $this->rollbackOnFailure($deployment);
                throw new \Exception('Post-deployment health check failed');
            }

            // Step 12: Warm up cache (optional)
            $steps['warm_cache'] = $this->warmCache();

            $this->updateDeploymentStatus($deployment, 'completed', $steps);

            Log::info('Deployment pipeline completed successfully', [
                'site' => $this->site->domain,
                'deployment_id' => $deployment->id
            ]);

            return [
                'success' => true,
                'deployment' => $deployment,
                'steps' => $steps,
                'message' => 'Deployment completed successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Deployment pipeline failed', [
                'site' => $this->site->domain,
                'deployment_id' => $deployment->id,
                'error' => $e->getMessage()
            ]);

            // Rollback on failure
            $rollbackResult = $this->rollbackOnFailure($deployment);

            $this->updateDeploymentStatus($deployment, 'failed', [
                'error' => $e->getMessage(),
                'rollback' => $rollbackResult
            ]);

            return [
                'success' => false,
                'deployment' => $deployment,
                'message' => $e->getMessage(),
                'rollback' => $rollbackResult
            ];
        }
    }

    /**
     * Create new deployment record
     */
    private function createDeployment(): Deployment
    {
        return Deployment::create([
            'site_id' => $this->site->id,
            'status' => 'pending',
            'commit_hash' => null,
            'branch' => $this->site->branch ?? 'main',
            'started_at' => now()
        ]);
    }

    /**
     * Update deployment status
     */
    private function updateDeploymentStatus(Deployment $deployment, string $status, array $output = []): void
    {
        $deployment->update([
            'status' => $status,
            'output' => array_merge($deployment->output ?? [], $output),
            'completed_at' => in_array($status, ['completed', 'failed']) ? now() : null
        ]);
    }

    /**
     * Health check before and after deployment
     */
    public function healthCheck(): array
    {
        try {
            Log::info('Running health check', ['site' => $this->site->domain]);

            // Check if site is accessible
            $curlResult = $this->ssh->execute("curl -I -s -o /dev/null -w '%{http_code}' https://{$this->site->domain}/ --max-time 10");
            $statusCode = (int) trim($curlResult['output']);

            if ($statusCode === 0) {
                return [
                    'success' => false,
                    'message' => 'Site is not accessible'
                ];
            }

            if ($statusCode >= 500) {
                return [
                    'success' => false,
                    'message' => "Site returned HTTP {$statusCode}"
                ];
            }

            // Check disk space
            $diskResult = $this->ssh->execute("df -h {$this->site->path} | tail -1 | awk '{print \$5}' | sed 's/%//'");
            $diskUsage = (int) trim($diskResult['output']);

            if ($diskUsage > 90) {
                return [
                    'success' => false,
                    'message' => "Disk usage is critically high: {$diskUsage}%"
                ];
            }

            // Check if database is accessible (for Laravel)
            if ($this->site->type === 'laravel') {
                $dbCheck = $this->ssh->execute("cd {$this->site->path} && php artisan tinker --execute='DB::connection()->getPdo(); echo \"OK\";' 2>&1");
                
                if (strpos($dbCheck['output'], 'OK') === false) {
                    return [
                        'success' => false,
                        'message' => 'Database connection failed'
                    ];
                }
            }

            return [
                'success' => true,
                'status_code' => $statusCode,
                'disk_usage' => $diskUsage,
                'message' => 'Health check passed'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup before deployment
     */
    public function backupBeforeDeploy(): array
    {
        try {
            Log::info('Creating backup before deployment', ['site' => $this->site->domain]);

            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupDir = "/var/backups/sites/{$this->site->domain}";
            
            // Create backup directory
            $this->ssh->execute("mkdir -p {$backupDir}");

            // Backup code
            $codeBackup = "{$backupDir}/code_{$timestamp}.tar.gz";
            $result = $this->ssh->execute("tar -czf {$codeBackup} -C {$this->site->path} .");

            if ($result['exit_code'] !== 0) {
                throw new \Exception('Failed to backup code');
            }

            // Backup database if enabled
            $dbBackup = null;
            if ($this->site->database) {
                $dbBackup = "{$backupDir}/database_{$timestamp}.sql";
                $dbResult = $this->ssh->execute("mysqldump -u {$this->site->database->username} -p'{$this->site->database->password}' {$this->site->database->name} > {$dbBackup}");
                
                if ($dbResult['exit_code'] !== 0) {
                    Log::warning('Database backup failed', ['site' => $this->site->domain]);
                }
            }

            // Keep only last 5 backups
            $this->ssh->execute("cd {$backupDir} && ls -t | tail -n +6 | xargs -r rm --");

            return [
                'success' => true,
                'code_backup' => $codeBackup,
                'db_backup' => $dbBackup,
                'message' => 'Backup created successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Backup failed', [
                'site' => $this->site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Pull latest code from repository
     */
    private function pullCode(): array
    {
        try {
            $branch = $this->site->branch ?? 'main';
            
            Log::info('Pulling code from repository', [
                'site' => $this->site->domain,
                'branch' => $branch
            ]);

            // Fetch and pull
            $commands = [
                "cd {$this->site->path}",
                "git fetch origin",
                "git reset --hard origin/{$branch}"
            ];

            $result = $this->ssh->execute(implode(' && ', $commands));

            // Get latest commit hash
            $commitResult = $this->ssh->execute("cd {$this->site->path} && git rev-parse HEAD");
            $commitHash = trim($commitResult['output']);

            return [
                'success' => $result['exit_code'] === 0,
                'commit_hash' => $commitHash,
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Install Composer dependencies
     */
    private function installDependencies(): array
    {
        try {
            Log::info('Installing Composer dependencies', ['site' => $this->site->domain]);

            $command = "cd {$this->site->path} && composer install --no-dev --optimize-autoloader --no-interaction";
            $result = $this->ssh->execute($command);

            return [
                'success' => $result['exit_code'] === 0,
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Build frontend assets
     */
    public function buildAssets(): array
    {
        try {
            Log::info('Building assets', ['site' => $this->site->domain]);

            // Check if package.json exists
            $packageCheck = $this->ssh->execute("test -f {$this->site->path}/package.json && echo 'exists'");
            
            if (trim($packageCheck['output']) !== 'exists') {
                return [
                    'success' => true,
                    'message' => 'No package.json found, skipping asset build'
                ];
            }

            // Install npm dependencies
            $this->ssh->execute("cd {$this->site->path} && npm ci");

            // Build assets
            $buildCommand = "cd {$this->site->path} && npm run build";
            $result = $this->ssh->execute($buildCommand);

            return [
                'success' => $result['exit_code'] === 0,
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Rollback on deployment failure
     */
    public function rollbackOnFailure(Deployment $deployment): array
    {
        try {
            Log::warning('Rolling back deployment', [
                'site' => $this->site->domain,
                'deployment_id' => $deployment->id
            ]);

            // Enable maintenance mode
            $this->artisan->enableMaintenanceMode($this->site);

            // Find latest successful backup
            $backupDir = "/var/backups/sites/{$this->site->domain}";
            $latestBackup = $this->ssh->execute("ls -t {$backupDir}/code_*.tar.gz | head -1");
            $backupFile = trim($latestBackup['output']);

            if (empty($backupFile)) {
                throw new \Exception('No backup found for rollback');
            }

            // Restore code from backup
            $this->ssh->execute("cd {$this->site->path} && rm -rf * .[!.]* ..?*");
            $this->ssh->execute("tar -xzf {$backupFile} -C {$this->site->path}");

            // Clear caches
            $this->artisan->clearCache($this->site);

            // Disable maintenance mode
            $this->artisan->disableMaintenanceMode($this->site);

            Log::info('Rollback completed', ['site' => $this->site->domain]);

            return [
                'success' => true,
                'message' => 'Deployment rolled back successfully',
                'backup_used' => $backupFile
            ];

        } catch (\Exception $e) {
            Log::error('Rollback failed', [
                'site' => $this->site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Warm up application cache
     */
    private function warmCache(): array
    {
        try {
            // Hit main URLs to warm up cache
            $urls = [
                "https://{$this->site->domain}/",
                "https://{$this->site->domain}/",
                "https://{$this->site->domain}/"
            ];

            foreach ($urls as $url) {
                $this->ssh->execute("curl -s -o /dev/null {$url}");
            }

            return [
                'success' => true,
                'message' => 'Cache warmed up'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Run tests before deployment
     */
    public function runTests(): array
    {
        try {
            Log::info('Running tests', ['site' => $this->site->domain]);

            // Check if phpunit.xml exists
            $phpunitCheck = $this->ssh->execute("test -f {$this->site->path}/phpunit.xml && echo 'exists'");
            
            if (trim($phpunitCheck['output']) !== 'exists') {
                return [
                    'success' => true,
                    'message' => 'No test configuration found, skipping tests'
                ];
            }

            // Run PHPUnit tests
            $command = "cd {$this->site->path} && php artisan test";
            $result = $this->ssh->execute($command);

            return [
                'success' => $result['exit_code'] === 0,
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
