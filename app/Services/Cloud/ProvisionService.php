<?php

namespace App\Services\Cloud;

use App\Models\Server;
use App\Services\SSHConnectionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ProvisionService
{
    public function __construct(
        private SSHConnectionService $ssh
    ) {}

    /**
     * Generate provisioning script for server
     */
    public function generateScript(Server $server): string
    {
        $config = $server->stack_config ?? [];

        return View::make('scripts.provision', [
            'server' => $server,
            'config' => $config,
        ])->render();
    }

    /**
     * Execute provisioning script on server
     */
    public function executeProvisioning(Server $server): bool
    {
        try {
            Log::info('Starting provisioning', ['server_id' => $server->id]);

            // Wait for server to be ready (SSH available)
            if (!$this->waitForSSH($server)) {
                throw new \Exception('Server SSH not available after waiting');
            }

            // Generate script
            $script = $this->generateScript($server);

            // Upload and execute script
            $connection = $this->ssh->connect($server);
            
            if (!$connection) {
                throw new \Exception('Failed to connect via SSH');
            }

            // Upload script
            $scriptPath = '/tmp/provision-' . uniqid() . '.sh';
            $this->ssh->uploadContent($connection, $script, $scriptPath);

            // Make executable
            $this->ssh->execute($connection, "chmod +x {$scriptPath}");

            // Execute with output logging
            Log::info('Executing provisioning script', ['server_id' => $server->id]);
            
            $output = $this->ssh->execute($connection, "sudo bash {$scriptPath} 2>&1");

            // Store output in server log
            $server->update([
                'provision_log' => $output,
                'provision_status' => 'provisioning',
            ]);

            // Verify installation
            if ($this->verifyInstallation($server)) {
                $server->update([
                    'provision_status' => 'active',
                    'status' => 'online',
                    'provisioned_at' => now(),
                ]);

                Log::info('Provisioning completed successfully', ['server_id' => $server->id]);
                return true;
            } else {
                throw new \Exception('Installation verification failed');
            }

        } catch (\Exception $e) {
            Log::error('Provisioning failed: ' . $e->getMessage(), [
                'server_id' => $server->id,
            ]);

            $server->update([
                'provision_status' => 'failed',
                'status' => 'offline',
                'provision_log' => ($server->provision_log ?? '') . "\n\nERROR: " . $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Wait for SSH to become available
     */
    private function waitForSSH(Server $server, int $maxAttempts = 30): bool
    {
        Log::info('Waiting for SSH to become available', ['server_id' => $server->id]);

        for ($i = 0; $i < $maxAttempts; $i++) {
            try {
                $connection = $this->ssh->connect($server);
                
                if ($connection) {
                    Log::info('SSH connection successful', [
                        'server_id' => $server->id,
                        'attempt' => $i + 1,
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                // Ignore and retry
            }

            sleep(10); // Wait 10 seconds between attempts
        }

        return false;
    }

    /**
     * Verify that all required services are installed and running
     */
    public function verifyInstallation(Server $server): bool
    {
        try {
            $connection = $this->ssh->connect($server);
            
            if (!$connection) {
                return false;
            }

            $config = $server->stack_config ?? [];

            // Check PHP
            $phpVersion = $config['php_version'] ?? '8.3';
            $phpCheck = $this->ssh->execute($connection, "php --version | grep 'PHP {$phpVersion}'");
            if (empty($phpCheck)) {
                Log::error('PHP verification failed', ['server_id' => $server->id]);
                return false;
            }

            // Check Composer
            $composerCheck = $this->ssh->execute($connection, "composer --version");
            if (empty($composerCheck)) {
                Log::error('Composer verification failed', ['server_id' => $server->id]);
                return false;
            }

            // Check webserver
            $webserver = $config['webserver'] ?? 'nginx';
            $webserverCheck = $this->ssh->execute($connection, "systemctl is-active {$webserver}");
            if (trim($webserverCheck) !== 'active') {
                Log::error('Webserver verification failed', ['server_id' => $server->id]);
                return false;
            }

            // Check database if configured
            if (isset($config['database']) && $config['database'] !== 'none') {
                $dbService = $config['database'] === 'mysql' ? 'mysql' : 'postgresql';
                $dbCheck = $this->ssh->execute($connection, "systemctl is-active {$dbService}");
                if (trim($dbCheck) !== 'active') {
                    Log::error('Database verification failed', ['server_id' => $server->id]);
                    return false;
                }
            }

            // Check cache if configured
            if (isset($config['cache']) && $config['cache'] !== 'none') {
                $cacheService = $config['cache'] === 'redis' ? 'redis-server' : 'memcached';
                $cacheCheck = $this->ssh->execute($connection, "systemctl is-active {$cacheService}");
                if (trim($cacheCheck) !== 'active') {
                    Log::warning('Cache verification failed (non-critical)', ['server_id' => $server->id]);
                    // Don't fail for cache
                }
            }

            Log::info('Installation verification passed', ['server_id' => $server->id]);
            return true;

        } catch (\Exception $e) {
            Log::error('Verification error: ' . $e->getMessage(), ['server_id' => $server->id]);
            return false;
        }
    }

    /**
     * Get provisioning progress
     */
    public function getProgress(Server $server): array
    {
        $log = $server->provision_log ?? '';
        
        // Count completed steps (lines starting with "✅" or "[X/Y]")
        preg_match_all('/\[(\d+)\/(\d+)\]/', $log, $matches);
        
        if (!empty($matches[1])) {
            $current = max($matches[1]);
            $total = max($matches[2]);
            
            return [
                'current' => (int)$current,
                'total' => (int)$total,
                'percentage' => round(($current / $total) * 100),
                'status' => $server->provision_status,
            ];
        }

        return [
            'current' => 0,
            'total' => 15,
            'percentage' => 0,
            'status' => $server->provision_status,
        ];
    }
}
