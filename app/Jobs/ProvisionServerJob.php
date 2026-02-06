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

    public $timeout = 2400; // 40 minutes
    public $tries = 1;

    public function __construct(public Server $server) {}

    public function handle(
        SSHService $ssh,
        ProvisionService $provision
    ): void {
        try {
            Log::info("Starting provisioning job for server: {$this->server->name} (ID: {$this->server->id})");
            
            $provision->provision($this->server);
            
            Log::info("Provisioning completed successfully for server: {$this->server->name}");
            
            // TODO: Send notification to user via email/notification
            
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
            
            // TODO: Send error notification to user
            
            throw $e;
        }
    }
}
