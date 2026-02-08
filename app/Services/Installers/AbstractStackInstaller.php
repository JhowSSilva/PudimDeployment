<?php

namespace App\Services\Installers;

use App\Contracts\StackInstallerInterface;
use App\Models\Server;
use App\Services\SSHService;
use Illuminate\Support\Facades\Log;

abstract class AbstractStackInstaller implements StackInstallerInterface
{
    protected SSHService $ssh;
    
    public function __construct(SSHService $ssh)
    {
        $this->ssh = $ssh;
    }
    
    /**
     * Execute SSH command with logging
     */
    protected function executeCommand(Server $server, string $command, string $step): array
    {
        Log::info("Executing: {$step}", [
            'server_id' => $server->id,
            'command' => $command
        ]);
        
        $result = $this->ssh->execute($server, $command);
        
        // Save command to history
        $server->sshCommands()->create([
            'user_id' => auth()->id() ?? 1, // Fallback for jobs
            'command' => $command,
            'output' => $result['output'] ?? '',
            'exit_code' => $result['exit_code'] ?? 0,
        ]);
        
        return $result;
    }
    
    /**
     * Log provisioning progress
     */
    protected function logProgress(Server $server, string $step, string $status, ?string $output = null, ?string $error = null): void
    {
        $server->provisionLogs()->create([
            'step' => $step,
            'status' => $status,
            'output' => $output,
            'error_message' => $error,
            'started_at' => now(),
            'completed_at' => in_array($status, ['completed', 'failed']) ? now() : null,
        ]);
    }
    
    /**
     * Check if command exists on server
     */
    protected function commandExists(Server $server, string $command): bool
    {
        $result = $this->executeCommand($server, "command -v {$command}", "Checking {$command}");
        return $result['exit_code'] === 0;
    }
    
    /**
     * Upload file via SSH
     */
    protected function uploadFile(Server $server, string $localContent, string $remotePath): bool
    {
        return $this->ssh->uploadFile($server, $localContent, $remotePath);
    }
    
    /**
     * Install packages via apt
     */
    protected function installPackages(Server $server, array $packages, string $step = null): bool
    {
        $step = $step ?: 'Installing packages';
        $packageList = implode(' ', $packages);
        
        $result = $this->executeCommand(
            $server,
            "DEBIAN_FRONTEND=noninteractive apt-get install -y {$packageList}",
            $step
        );
        
        return $result['exit_code'] === 0;
    }
    
    /**
     * Update system packages
     */
    protected function updateSystem(Server $server): bool
    {
        $this->logProgress($server, 'system_update', 'running');
        
        $result = $this->executeCommand(
            $server,
            'apt-get update && DEBIAN_FRONTEND=noninteractive apt-get upgrade -y',
            'Updating system packages'
        );
        
        if ($result['exit_code'] === 0) {
            $this->logProgress($server, 'system_update', 'completed');
            return true;
        }
        
        $this->logProgress($server, 'system_update', 'failed', null, $result['output']);
        return false;
    }
    
    /**
     * Add APT repository
     */
    protected function addRepository(Server $server, string $repo, string $name): bool
    {
        $result = $this->executeCommand(
            $server,
            "add-apt-repository {$repo} -y && apt-get update",
            "Adding {$name} repository"
        );
        
        return $result['exit_code'] === 0;
    }
    
    /**
     * Enable and start systemd service
     */
    protected function enableService(Server $server, string $service): bool
    {
        $result1 = $this->executeCommand($server, "systemctl enable {$service}", "Enabling {$service}");
        $result2 = $this->executeCommand($server, "systemctl start {$service}", "Starting {$service}");
        
        return $result1['exit_code'] === 0 && $result2['exit_code'] === 0;
    }
    
    /**
     * Restart systemd service
     */
    protected function restartService(Server $server, string $service): bool
    {
        $result = $this->executeCommand($server, "systemctl restart {$service}", "Restarting {$service}");
        return $result['exit_code'] === 0;
    }
    
    /**
     * Check if service is running
     */
    protected function isServiceRunning(Server $server, string $service): bool
    {
        $result = $this->executeCommand($server, "systemctl is-active {$service}", "Checking {$service}");
        return $result['exit_code'] === 0 && trim($result['output']) === 'active';
    }
    
    /**
     * Default implementation of configure method
     */
    public function configure(Server $server, array $config): bool
    {
        // Override in subclasses if needed
        return true;
    }
    
    /**
     * Default implementation of getDefaultConfig
     */
    public function getDefaultConfig(): array
    {
        return [];
    }
}