<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\View;

class ProvisionService
{
    public function __construct(
        private SSHService $ssh
    ) {}
    
    /**
     * Provision a server
     */
    public function provision(Server $server): void
    {
        \Log::info("Starting provision for server: {$server->name}");
        
        // 1. Test SSH connection
        $server->update(['status' => 'connecting']);
        
        if (!$this->ssh->testConnection($server)) {
            throw new \Exception('Cannot connect to server via SSH. Please ensure the SSH key is properly configured.');
        }
        
        \Log::info("SSH connection successful for server: {$server->name}");
        
        // 2. Collect system information
        try {
            $systemInfo = $this->ssh->getSystemInfo($server);
            $server->update([
                'architecture' => $systemInfo['architecture'],
                'kernel_version' => $systemInfo['kernel'],
                'cpu_cores' => $systemInfo['cpu_cores'],
                'ram_mb' => $systemInfo['ram_mb'],
                'disk_gb' => $systemInfo['disk_gb'],
                'hostname' => $systemInfo['hostname'] ?? null,
                'system_info' => $systemInfo
            ]);
            
            \Log::info("System info collected for server: {$server->name}", $systemInfo);
        } catch (\Exception $e) {
            \Log::warning("Failed to collect system info: " . $e->getMessage());
        }
        
        // 3. Generate provisioning script
        $script = $this->generateScript($server);
        
        $server->update([
            'provision_script' => $script,
            'provision_started_at' => now(),
            'status' => 'provisioning',
            'provision_status' => 'provisioning'
        ]);
        
        \Log::info("Executing provision script for server: {$server->name}");
        
        // 4. Execute provisioning script
        try {
            $result = $this->ssh->executeScript($server, $script);
            
            if ($result['success']) {
                $server->update([
                    'status' => 'ready',
                    'provision_status' => 'active',
                    'provision_completed_at' => now(),
                    'provisioned_at' => now(),
                    'provision_log' => [
                        'output' => $result['output'],
                        'exit_code' => $result['exit_code'],
                        'success' => true,
                        'completed_at' => now()->toIso8601String()
                    ]
                ]);
                
                \Log::info("Server provisioned successfully: {$server->name}");
            } else {
                throw new \Exception('Provisioning script failed with exit code: ' . $result['exit_code']);
            }
        } catch (\Exception $e) {
            \Log::error("Provisioning failed for server {$server->name}: " . $e->getMessage());
            
            $server->update([
                'status' => 'error',
                'provision_status' => 'failed',
                'provision_log' => [
                    'error' => $e->getMessage(),
                    'output' => $result['output'] ?? '',
                    'exit_code' => $result['exit_code'] ?? -1,
                    'success' => false,
                    'failed_at' => now()->toIso8601String()
                ]
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Generate provisioning script from template
     */
    private function generateScript(Server $server): string
    {
        return View::make('scripts.provision', [
            'server' => $server,
            'os' => $server->os,
            'architecture' => $server->architecture ?? 'x86_64',
            'type' => $server->type,
            'webserver' => $server->webserver,
            'php_versions' => $server->php_versions ?? [],
            'database_type' => $server->database_type,
            'database_version' => $server->database_version,
            'cache_service' => $server->cache_service,
            'nodejs_version' => $server->nodejs_version,
            'installed_software' => $server->installed_software ?? [],
            'deploy_user' => $server->deploy_user,
            'ssh_public_key' => $server->ssh_key_public,
        ])->render();
    }
    
    /**
     * Estimate provision time in minutes
     */
    public function estimateProvisionTime(Server $server): int
    {
        $baseTime = 3; // Base installation ~3 min
        
        // Add time based on components
        if ($server->webserver) $baseTime += 2;
        if ($server->php_versions) $baseTime += count($server->php_versions) * 2;
        if ($server->database_type) $baseTime += 3;
        if ($server->cache_service) $baseTime += 1;
        if ($server->nodejs_version) $baseTime += 2;
        if ($server->installed_software) $baseTime += count($server->installed_software) * 0.5;
        
        return (int) ceil($baseTime);
    }
}
