<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\Cloud\AWSService;
use App\Services\Cloud\ProvisionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProvisionAWSServerJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;
    public $timeout = 1800; // 30 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server,
        public array $config
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ProvisionService $provisionService): void
    {
        try {
            Log::info('Starting AWS server provisioning', [
                'server_id' => $this->server->id,
                'instance_type' => $this->config['instance_type'],
            ]);

            $this->server->update([
                'provision_status' => 'provisioning',
                'status' => 'provisioning',
            ]);

            // Execute provisioning script
            $success = $provisionService->executeProvisioning($this->server);

            if ($success) {
                Log::info('AWS server provisioned successfully', [
                    'server_id' => $this->server->id,
                ]);
            } else {
                throw new \Exception('Provisioning failed - check server logs');
            }

        } catch (\Exception $e) {
            Log::error('AWS server provisioning failed', [
                'server_id' => $this->server->id,
                'error' => $e->getMessage(),
            ]);

            $this->server->update([
                'provision_status' => 'failed',
                'status' => 'offline',
                'provision_log' => ($this->server->provision_log ?? '') . "\n\nERROR: " . $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
