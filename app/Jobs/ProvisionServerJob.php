<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\SSHService;
use App\Services\ProvisionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 1800;
    public array $backoff = [60, 120, 300];

    public function __construct(public Server $server, public array $config = []) {}

    public function handle(): void
    {
        try {
            Log::info("Starting modern provisioning job for server: {$this->server->name} (ID: {$this->server->id})");
            
            // Use new stack installation job for modern multi-language support
            if ($this->server->programming_language && $this->server->programming_language !== 'php') {
                // Use new multi-language system
                InstallServerStackJob::dispatch($this->server, $this->config);
                return;
            }
            
            // Fallback to legacy system for PHP or undefined language
            $this->runLegacyProvisioning();
            
            Log::info("Provisioning completed successfully for server: {$this->server->name}");
            
        } catch (\Exception $e) {
            Log::error("Provisioning job failed for server {$this->server->name}: {$e->getMessage()}", [
                'server_id' => $this->server->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->server->update([
                'status' => 'error',
                'provision_status' => 'failed',
                'provision_log' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'failed_at' => now()->toIso8601String()
                ],
            ]);
            
            throw $e;
        }
    }
    
    protected function runLegacyProvisioning(): void
    {
        $ssh = app(SSHService::class);
        $provision = app(\App\Services\ProvisionService::class);
        
        $provision->provision($this->server);
    }
}
