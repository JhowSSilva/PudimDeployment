<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\MetricsCollectorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CollectServerMetrics implements ShouldQueue
{
    use Queueable;

    public Server $server;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Collecting metrics for server: {$this->server->name}");

        try {
            $collector = new MetricsCollectorService($this->server);
            $metrics = $collector->collect();

            if ($metrics) {
                Log::info("Metrics collected successfully for server: {$this->server->name}");
            } else {
                Log::warning("Failed to collect metrics for server: {$this->server->name}");
            }

        } catch (\Exception $e) {
            Log::error("Error collecting metrics for server {$this->server->name}: " . $e->getMessage());
            
            // Mark server as offline on repeated failures
            if ($this->attempts() >= $this->tries) {
                $this->server->update(['status' => 'offline']);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("CollectServerMetrics job failed permanently for server {$this->server->name}: " . $exception->getMessage());
        
        $this->server->update([
            'status' => 'offline',
        ]);
    }
}
