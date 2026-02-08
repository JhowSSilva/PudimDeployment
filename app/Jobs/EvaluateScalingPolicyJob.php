<?php

namespace App\Jobs;

use App\Models\ScalingPolicy;
use App\Services\AutoScalingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class EvaluateScalingPolicyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ScalingPolicy $policy
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AutoScalingService $scalingService): void
    {
        try {
            // Mark policy as triggered
            $this->policy->markTriggered();

            // Evaluate the policy
            $evaluation = $scalingService->evaluatePolicy($this->policy);

            if (!$evaluation['should_scale']) {
                Log::debug("Policy {$this->policy->name} evaluation: {$evaluation['reason']}");
                return;
            }

            // Dispatch scaling job if needed
            if ($this->policy->serverPool) {
                ScaleServerPoolJob::dispatch(
                    $this->policy->serverPool,
                    $evaluation['direction'],
                    $evaluation['scale_by'],
                    $this->policy
                );

                Log::info("Triggered scaling {$evaluation['direction']} for pool {$this->policy->serverPool->name} by {$evaluation['scale_by']} servers");
            }
        } catch (\Exception $e) {
            Log::error("Failed to evaluate scaling policy {$this->policy->name}: " . $e->getMessage());
            throw $e;
        }
    }
}
