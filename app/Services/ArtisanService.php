<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Log;

class ArtisanService
{
    private SSHConnectionService $ssh;

    public function __construct(private Server $server)
    {
        $this->ssh = new SSHConnectionService($server);
    }

    /**
     * Run artisan command
     */
    public function runCommand(Site $site, string $command, array $options = []): array
    {
        try {
            $artisanPath = $site->path . '/artisan';

            // Check if artisan exists
            $checkResult = $this->ssh->execute("test -f {$artisanPath} && echo 'exists' || echo 'not found'");
            
            if (trim($checkResult['output']) !== 'exists') {
                throw new \Exception('Artisan file not found. This may not be a Laravel application.');
            }

            // Build command
            $fullCommand = "cd {$site->path} && php artisan {$command}";

            // Add options
            foreach ($options as $key => $value) {
                if (is_bool($value)) {
                    $fullCommand .= " --{$key}";
                } else {
                    $fullCommand .= " --{$key}={$value}";
                }
            }

            Log::info('Running artisan command', [
                'site' => $site->domain,
                'command' => $command,
                'options' => $options
            ]);

            $result = $this->ssh->execute($fullCommand);

            return [
                'success' => $result['exit_code'] === 0,
                'output' => $result['output'],
                'exit_code' => $result['exit_code']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to run artisan command', [
                'site' => $site->domain,
                'command' => $command,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Run database migrations
     */
    public function runMigrations(Site $site, array $options = []): array
    {
        try {
            $defaultOptions = array_merge([
                'force' => true
            ], $options);

            Log::info('Running migrations', [
                'site' => $site->domain,
                'options' => $defaultOptions
            ]);

            $result = $this->runCommand($site, 'migrate', $defaultOptions);

            if ($result['success']) {
                Log::info('Migrations completed successfully', ['site' => $site->domain]);
            } else {
                Log::error('Migrations failed', [
                    'site' => $site->domain,
                    'output' => $result['output'] ?? 'No output'
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to run migrations', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Rollback migrations
     */
    public function rollbackMigrations(Site $site, ?int $step = null): array
    {
        try {
            $options = ['force' => true];
            
            if ($step !== null) {
                $options['step'] = $step;
            }

            Log::info('Rolling back migrations', [
                'site' => $site->domain,
                'step' => $step
            ]);

            return $this->runCommand($site, 'migrate:rollback', $options);

        } catch (\Exception $e) {
            Log::error('Failed to rollback migrations', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear various caches
     */
    public function clearCache(Site $site, array $types = ['cache']): array
    {
        try {
            $results = [];

            foreach ($types as $type) {
                Log::info('Clearing cache', [
                    'site' => $site->domain,
                    'type' => $type
                ]);

                $command = match($type) {
                    'config' => 'config:clear',
                    'route' => 'route:clear',
                    'view' => 'view:clear',
                    'cache' => 'cache:clear',
                    'event' => 'event:clear',
                    'compiled' => 'clear-compiled',
                    default => "cache:clear"
                };

                $results[$type] = $this->runCommand($site, $command);
            }

            // Optimize autoloader
            $this->ssh->execute("cd {$site->path} && composer dump-autoload -o");

            Log::info('Cache cleared successfully', [
                'site' => $site->domain,
                'types' => $types
            ]);

            return [
                'success' => true,
                'message' => 'Cache cleared successfully',
                'details' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Failed to clear cache', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Optimize application
     */
    public function optimize(Site $site): array
    {
        try {
            Log::info('Optimizing application', ['site' => $site->domain]);

            $results = [];

            // Cache config
            $results['config'] = $this->runCommand($site, 'config:cache');

            // Cache routes
            $results['route'] = $this->runCommand($site, 'route:cache');

            // Cache views
            $results['view'] = $this->runCommand($site, 'view:cache');

            // Optimize autoloader
            $composerResult = $this->ssh->execute("cd {$site->path} && composer dump-autoload -o");
            $results['composer'] = [
                'success' => $composerResult['exit_code'] === 0,
                'output' => $composerResult['output']
            ];

            // Optimize Laravel
            $results['optimize'] = $this->runCommand($site, 'optimize');

            Log::info('Application optimized successfully', ['site' => $site->domain]);

            return [
                'success' => true,
                'message' => 'Application optimized successfully',
                'details' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Failed to optimize application', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Seed database
     */
    public function seedDatabase(Site $site, ?string $class = null, bool $force = true): array
    {
        try {
            $options = ['force' => $force];
            
            if ($class) {
                $options['class'] = $class;
            }

            Log::info('Seeding database', [
                'site' => $site->domain,
                'class' => $class
            ]);

            return $this->runCommand($site, 'db:seed', $options);

        } catch (\Exception $e) {
            Log::error('Failed to seed database', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Schedule cron job
     */
    public function scheduleCronJob(Site $site): array
    {
        try {
            Log::info('Setting up Laravel scheduler', ['site' => $site->domain]);

            // Add Laravel scheduler to crontab
            $cronEntry = "* * * * * cd {$site->path} && php artisan schedule:run >> /dev/null 2>&1";

            // Check if cron entry already exists
            $checkCron = $this->ssh->execute('crontab -l 2>/dev/null');
            
            if (strpos($checkCron['output'], $cronEntry) === false) {
                // Add cron entry
                $addCron = "(crontab -l 2>/dev/null; echo '{$cronEntry}') | crontab -";
                $result = $this->ssh->execute($addCron);

                if ($result['exit_code'] === 0) {
                    Log::info('Laravel scheduler added to crontab', ['site' => $site->domain]);
                    
                    return [
                        'success' => true,
                        'message' => 'Laravel scheduler configured successfully'
                    ];
                } else {
                    throw new \Exception('Failed to update crontab: ' . $result['output']);
                }
            } else {
                return [
                    'success' => true,
                    'message' => 'Laravel scheduler already configured'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to schedule cron job', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Show scheduled tasks
     */
    public function listScheduledTasks(Site $site): array
    {
        try {
            $result = $this->runCommand($site, 'schedule:list');

            return [
                'success' => $result['success'],
                'tasks' => $result['output'] ?? ''
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Run queue worker
     */
    public function runQueueWorker(Site $site, string $queue = 'default', array $options = []): array
    {
        try {
            $defaultOptions = array_merge([
                'daemon' => true,
                'tries' => 3,
                'timeout' => 60
            ], $options);

            Log::info('Starting queue worker', [
                'site' => $site->domain,
                'queue' => $queue,
                'options' => $defaultOptions
            ]);

            return $this->runCommand($site, "queue:work --queue={$queue}", $defaultOptions);

        } catch (\Exception $e) {
            Log::error('Failed to run queue worker', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Restart queue workers
     */
    public function restartQueueWorkers(Site $site): array
    {
        try {
            Log::info('Restarting queue workers', ['site' => $site->domain]);

            return $this->runCommand($site, 'queue:restart');

        } catch (\Exception $e) {
            Log::error('Failed to restart queue workers', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Show failed jobs
     */
    public function showFailedJobs(Site $site): array
    {
        try {
            $result = $this->runCommand($site, 'queue:failed');

            return [
                'success' => true,
                'failed_jobs' => $result['output'] ?? ''
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Retry failed job
     */
    public function retryFailedJob(Site $site, ?string $id = null): array
    {
        try {
            $command = $id ? "queue:retry {$id}" : 'queue:retry all';

            Log::info('Retrying failed jobs', [
                'site' => $site->domain,
                'id' => $id ?? 'all'
            ]);

            return $this->runCommand($site, $command);

        } catch (\Exception $e) {
            Log::error('Failed to retry jobs', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Put application in maintenance mode
     */
    public function enableMaintenanceMode(Site $site, ?string $secret = null): array
    {
        try {
            $options = [];
            
            if ($secret) {
                $options['secret'] = $secret;
            }

            Log::info('Enabling maintenance mode', [
                'site' => $site->domain,
                'secret' => $secret ? 'Yes' : 'No'
            ]);

            $result = $this->runCommand($site, 'down', $options);

            if ($result['success']) {
                $site->update(['maintenance_mode' => true]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to enable maintenance mode', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenanceMode(Site $site): array
    {
        try {
            Log::info('Disabling maintenance mode', ['site' => $site->domain]);

            $result = $this->runCommand($site, 'up');

            if ($result['success']) {
                $site->update(['maintenance_mode' => false]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to disable maintenance mode', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate application key
     */
    public function generateKey(Site $site): array
    {
        try {
            Log::info('Generating application key', ['site' => $site->domain]);

            return $this->runCommand($site, 'key:generate', ['force' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to generate application key', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Run custom artisan command
     */
    public function runCustomCommand(Site $site, string $command): array
    {
        try {
            Log::info('Running custom artisan command', [
                'site' => $site->domain,
                'command' => $command
            ]);

            return $this->runCommand($site, $command);

        } catch (\Exception $e) {
            Log::error('Failed to run custom command', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
