<?php

namespace App\Jobs;

use App\Models\LoadBalancer;
use App\Services\LoadBalancingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateLoadBalancerStatsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public LoadBalancer $loadBalancer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LoadBalancingService $loadBalancingService): void
    {
        try {
            // Get comprehensive statistics
            $stats = $loadBalancingService->getStatistics($this->loadBalancer);

            Log::debug("Updated stats for load balancer {$this->loadBalancer->name}: " . 
                       "{$stats['total_requests']} requests, {$stats['success_rate']}% success rate");

            // Stats are automatically tracked via the model's incrementRequests method
            // This job can be extended to aggregate historical data or push to monitoring systems
        } catch (\Exception $e) {
            Log::error("Failed to update load balancer stats for {$this->loadBalancer->name}: " . $e->getMessage());
        }
    }
}
