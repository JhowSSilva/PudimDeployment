<?php

namespace App\Jobs;

use App\Models\PipelineRun;
use App\Services\PipelineRunnerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ContinuePipelineJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 3600;

    public function __construct(public PipelineRun $pipelineRun) {}

    public function handle(PipelineRunnerService $runnerService): void
    {
        try {
            Log::info("Continuing pipeline run #{$this->pipelineRun->id} after approval");

            $success = $runnerService->executePipeline($this->pipelineRun);

            if ($success) {
                Log::info("Pipeline run completed after approval", [
                    'run_id' => $this->pipelineRun->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to continue pipeline run", [
                'run_id' => $this->pipelineRun->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
