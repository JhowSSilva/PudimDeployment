<?php

namespace App\Jobs;

use App\Models\ServerPool;
use App\Models\ScalingPolicy;
use App\Services\AutoScalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ScaleServerPoolJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ServerPool $pool,
        public string $direction,
        public int $count,
        public ?ScalingPolicy $policy = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AutoScalingService $scalingService): void
    {
        try {
            Log::info("Starting scaling {$this->direction} for pool {$this->pool->name} by {$this->count} servers");

            // Execute the scaling action
            $success = $scalingService->scalePool(
                $this->pool,
                $this->direction,
                $this->count,
                $this->policy
            );

            if ($success) {
                Log::info("Successfully scaled {$this->direction} pool {$this->pool->name} by {$this->count} servers");
                
                // Refresh pool to get updated counts
                $this->pool->refresh();
                $this->pool->updateCurrentServersCount();
            } else {
                Log::error("Failed to scale {$this->direction} pool {$this->pool->name}");
            }
        } catch (\Exception $e) {
            Log::error("Error scaling pool {$this->pool->name}: " . $e->getMessage());
            throw $e;
        }
    }
}
