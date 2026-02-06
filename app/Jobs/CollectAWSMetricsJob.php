<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\ServerMetric;
use App\Services\Cloud\AWSService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CollectAWSMetricsJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Server $server
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Only collect metrics for AWS servers
            if (!$this->server->isAWS() || !$this->server->awsCredential) {
                return;
            }

            Log::info('Collecting AWS metrics', ['server_id' => $this->server->id]);

            $awsService = new AWSService($this->server->awsCredential);

            // Collect CPU metrics
            $cpuMetrics = $awsService->getMetrics($this->server->instance_id, 'cpu', 300);
            
            if (!empty($cpuMetrics)) {
                $latestCPU = collect($cpuMetrics)->sortByDesc('Timestamp')->first();
                $cpuUsage = $latestCPU['Average'] ?? 0;
            } else {
                $cpuUsage = 0;
            }

            // Collect Network metrics
            $networkInMetrics = $awsService->getMetrics($this->server->instance_id, 'network_in', 300);
            $networkOutMetrics = $awsService->getMetrics($this->server->instance_id, 'network_out', 300);

            $networkIn = 0;
            $networkOut = 0;

            if (!empty($networkInMetrics)) {
                $latest = collect($networkInMetrics)->sortByDesc('Timestamp')->first();
                $networkIn = $latest['Average'] ?? 0;
            }

            if (!empty($networkOutMetrics)) {
                $latest = collect($networkOutMetrics)->sortByDesc('Timestamp')->first();
                $networkOut = $latest['Average'] ?? 0;
            }

            // Memory is not available via CloudWatch by default
            // Would need CloudWatch agent installed
            $memoryUsage = null;

            // Store metrics
            ServerMetric::create([
                'server_id' => $this->server->id,
                'cpu_usage' => round($cpuUsage, 2),
                'memory_usage' => $memoryUsage,
                'disk_usage' => null, // Not available via basic CloudWatch
                'network_rx' => round($networkIn / 1024 / 1024, 2), // Convert to MB
                'network_tx' => round($networkOut / 1024 / 1024, 2), // Convert to MB
                'uptime' => null,
                'load_average' => null,
            ]);

            // Update server's last_ping_at
            $this->server->update(['last_ping_at' => now()]);

            Log::info('AWS metrics collected successfully', [
                'server_id' => $this->server->id,
                'cpu' => $cpuUsage,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to collect AWS metrics', [
                'server_id' => $this->server->id,
                'error' => $e->getMessage(),
            ]);

            // Don't fail the job - metrics collection is non-critical
        }
    }
}
