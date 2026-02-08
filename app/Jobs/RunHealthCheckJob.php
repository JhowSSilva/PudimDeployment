<?php

namespace App\Jobs;

use App\Models\HealthCheck;
use App\Models\LoadBalancer;
use App\Services\HealthCheckService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunHealthCheckJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds before the job should timeout.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public HealthCheck|LoadBalancer $target
    ) {}

    /**
     * Execute the job.
     */
    public function handle(HealthCheckService $healthCheckService): void
    {
        try {
            if ($this->target instanceof HealthCheck) {
                // Run single health check
                $success = $healthCheckService->runCheck($this->target);
                
                Log::debug("Health check for server {$this->target->server->name}: " . ($success ? 'passed' : 'failed'));
            } elseif ($this->target instanceof LoadBalancer) {
                // Run all health checks for the load balancer
                $results = $healthCheckService->runLoadBalancerHealthChecks($this->target);
                
                Log::info("Load balancer {$this->target->name} health checks: {$results['successful']}/{$results['total']} passed");
            }
        } catch (\Exception $e) {
            Log::error("Health check job failed: " . $e->getMessage());
            // Don't re-throw - health checks should fail gracefully
        }
    }
}
