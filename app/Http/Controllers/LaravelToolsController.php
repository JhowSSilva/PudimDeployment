<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SSHService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LaravelToolsController extends Controller
{
    public function __construct(
        private SSHService $sshService
    ) {}

    /**
     * Run Artisan command
     */
    public function artisan(Request $request, Site $site)
    {
        $validated = $request->validate([
            'command' => 'required|string',
        ]);

        try {
            $basePath = "/var/www/{$site->domain}";
            $command = "cd {$basePath} && php artisan {$validated['command']}";
            
            $output = $this->sshService->execute($site->server, $command);

            return response()->json([
                'success' => true,
                'command' => $validated['command'],
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            Log::error("Artisan command failed: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Command failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of available Artisan commands
     */
    public function listCommands(Site $site)
    {
        try {
            $basePath = "/var/www/{$site->domain}";
            $command = "cd {$basePath} && php artisan list --format=json";
            
            $output = $this->sshService->execute($site->server, $command);
            $commands = json_decode($output, true);

            return response()->json([
                'success' => true,
                'commands' => $commands['commands'] ?? [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list commands: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run database migrations
     */
    public function migrate(Request $request, Site $site)
    {
        $force = $request->boolean('force', false);
        $seed = $request->boolean('seed', false);

        try {
            $basePath = "/var/www/{$site->domain}";
            $flags = $force ? ' --force' : '';
            $flags .= $seed ? ' --seed' : '';
            
            $command = "cd {$basePath} && php artisan migrate{$flags}";
            $output = $this->sshService->execute($site->server, $command);

            return response()->json([
                'success' => true,
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(Site $site)
    {
        try {
            $basePath = "/var/www/{$site->domain}";
            $commands = [
                'config:clear',
                'cache:clear',
                'route:clear',
                'view:clear',
            ];

            $outputs = [];
            foreach ($commands as $cmd) {
                $command = "cd {$basePath} && php artisan {$cmd}";
                $outputs[$cmd] = $this->sshService->execute($site->server, $command);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'outputs' => $outputs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize application
     */
    public function optimize(Site $site)
    {
        try {
            $basePath = "/var/www/{$site->domain}";
            $commands = [
                'config:cache',
                'route:cache',
                'view:cache',
            ];

            $outputs = [];
            foreach ($commands as $cmd) {
                $command = "cd {$basePath} && php artisan {$cmd}";
                $outputs[$cmd] = $this->sshService->execute($site->server, $command);
            }

            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully',
                'outputs' => $outputs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get application logs
     */
    public function logs(Request $request, Site $site)
    {
        $lines = $request->input('lines', 100);
        $type = $request->input('type', 'laravel'); // laravel, nginx-access, nginx-error, php

        try {
            $logFile = match($type) {
                'laravel' => "/var/www/{$site->domain}/storage/logs/laravel.log",
                'nginx-access' => "/var/www/{$site->domain}/logs/access.log",
                'nginx-error' => "/var/www/{$site->domain}/logs/error.log",
                'php' => "/var/www/{$site->domain}/logs/php-error.log",
                default => "/var/www/{$site->domain}/storage/logs/laravel.log",
            };

            $command = "sudo tail -n {$lines} {$logFile} 2>/dev/null || echo 'Log file not found'";
            $output = $this->sshService->execute($site->server, $command);

            return response()->json([
                'success' => true,
                'logs' => $output,
                'type' => $type,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run composer commands
     */
    public function composer(Request $request, Site $site)
    {
        $validated = $request->validate([
            'command' => 'required|string', // install, update, require, remove
            'packages' => 'nullable|array',
            'dev' => 'nullable|boolean',
        ]);

        try {
            $basePath = "/var/www/{$site->domain}";
            $cmd = $validated['command'];
            $packages = isset($validated['packages']) ? implode(' ', $validated['packages']) : '';
            $dev = $validated['dev'] ?? false ? '--dev' : '';

            $command = "cd {$basePath} && composer {$cmd} {$packages} {$dev} --no-interaction";
            $output = $this->sshService->execute($site->server, $command);

            return response()->json([
                'success' => true,
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Composer command failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run queue worker status
     */
    public function queueStatus(Site $site)
    {
        try {
            $basePath = "/var/www/{$site->domain}";
            $command = "cd {$basePath} && php artisan queue:failed";
            
            $output = $this->sshService->execute($site->server, $command);

            return response()->json([
                'success' => true,
                'failed_jobs' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get queue status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get application environment
     */
    public function environment(Site $site)
    {
        try {
            $basePath = "/var/www/{$site->domain}";
            $command = "cd {$basePath} && php artisan env";
            
            $output = $this->sshService->execute($site->server, $command);

            return response()->json([
                'success' => true,
                'environment' => trim($output),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get environment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
