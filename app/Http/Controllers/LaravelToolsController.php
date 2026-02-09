<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SSHService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LaravelToolsController extends Controller
{
    /**
     * Allowed artisan commands to prevent destructive operations.
     */
    private const ALLOWED_ARTISAN_COMMANDS = [
        'list', 'env', 'about', 'inspire',
        'config:clear', 'config:cache', 'config:show',
        'cache:clear', 'cache:forget',
        'route:clear', 'route:cache', 'route:list',
        'view:clear', 'view:cache',
        'event:clear', 'event:cache',
        'optimize', 'optimize:clear',
        'queue:failed', 'queue:retry', 'queue:restart', 'queue:flush',
        'migrate', 'migrate:status',
        'schedule:list',
        'storage:link',
        'down', 'up',
    ];

    /**
     * Allowed composer commands.
     */
    private const ALLOWED_COMPOSER_COMMANDS = [
        'install', 'update', 'require', 'remove', 'dump-autoload',
    ];

    public function __construct(
        private SSHService $sshService
    ) {}

    /**
     * Validate artisan command against allowlist.
     */
    private function validateArtisanCommand(string $command): string
    {
        $baseCommand = explode(' ', trim($command))[0];
        if (!in_array($baseCommand, self::ALLOWED_ARTISAN_COMMANDS)) {
            abort(422, "Artisan command '{$baseCommand}' is not allowed.");
        }
        // Block shell metacharacters in the full command
        if (preg_match('/[;&|`$(){}\[\]!<>\\]/', $command)) {
            abort(422, 'Invalid characters in command');
        }
        return $command;
    }

    /**
     * Run Artisan command
     */
    public function artisan(Request $request, Site $site)
    {
        $this->authorize('update', $site);

        $validated = $request->validate([
            'command' => 'required|string|max:500',
        ]);

        $safeCommand = $this->validateArtisanCommand($validated['command']);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
            $command = "cd {$basePath} && php artisan {$safeCommand}";
            
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
        $this->authorize('view', $site);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
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
        $this->authorize('update', $site);

        $force = $request->boolean('force', false);
        $seed = $request->boolean('seed', false);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
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
        $this->authorize('update', $site);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
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
        $this->authorize('update', $site);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
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
        $this->authorize('view', $site);

        $validated = $request->validate([
            'lines' => 'nullable|integer|min:1|max:10000',
            'type' => 'nullable|string|in:laravel,nginx-access,nginx-error,php',
        ]);

        $lines = (int) ($validated['lines'] ?? 100);
        $type = $validated['type'] ?? 'laravel';

        try {
            $domain = escapeshellarg($site->domain);
            $logFile = match($type) {
                'laravel' => "/var/www/{$site->domain}/storage/logs/laravel.log",
                'nginx-access' => "/var/www/{$site->domain}/logs/access.log",
                'nginx-error' => "/var/www/{$site->domain}/logs/error.log",
                'php' => "/var/www/{$site->domain}/logs/php-error.log",
                default => "/var/www/{$site->domain}/storage/logs/laravel.log",
            };

            $command = 'sudo tail -n ' . escapeshellarg((string) $lines) . ' ' . escapeshellarg($logFile) . " 2>/dev/null || echo 'Log file not found'";
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
        $this->authorize('update', $site);

        $validated = $request->validate([
            'command' => 'required|string|in:' . implode(',', self::ALLOWED_COMPOSER_COMMANDS),
            'packages' => 'nullable|array',
            'packages.*' => 'string|regex:/^[a-zA-Z0-9\/_.-]+$/',
            'dev' => 'nullable|boolean',
        ]);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
            $cmd = escapeshellarg($validated['command']);
            $packages = isset($validated['packages'])
                ? implode(' ', array_map('escapeshellarg', $validated['packages']))
                : '';
            $dev = ($validated['dev'] ?? false) ? '--dev' : '';

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
        $this->authorize('view', $site);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
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
        $this->authorize('view', $site);

        try {
            $basePath = '/var/www/' . escapeshellarg($site->domain);
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
