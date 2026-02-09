<?php

namespace App\Jobs;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionGcpServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Server $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function handle(): void
    {
        // Minimal placeholder implementation: mark provisioning progress in DB
        $this->server->update(['provision_status' => 'provisioning']);

        // TODO: implement actual GCP provisioning using credentials and compute API
        sleep(1);

        // For now, simulate success
        $this->server->update([
            'ip_address' => '1.2.3.4',
            'status' => 'online',
            'provision_status' => 'active',
            'provision_log' => array_merge($this->server->provision_log ?? [], ['gcp' => 'simulated provision complete']),
        ]);

        Log::info('Simulated GCP provisioning complete for server ' . $this->server->id);
    }
}
