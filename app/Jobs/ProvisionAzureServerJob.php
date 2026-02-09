<?php

namespace App\Jobs;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionAzureServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Server $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function handle(): void
    {
        $this->server->update(['provision_status' => 'provisioning']);

        // TODO: call Azure compute API to create VM using stored credential
        sleep(1);

        $this->server->update([
            'ip_address' => '5.6.7.8',
            'status' => 'online',
            'provision_status' => 'active',
            'provision_log' => array_merge($this->server->provision_log ?? [], ['azure' => 'simulated provision complete']),
        ]);

        Log::info('Simulated Azure provisioning complete for server ' . $this->server->id);
    }
}
