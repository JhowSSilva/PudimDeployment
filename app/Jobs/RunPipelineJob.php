<?php

namespace App\Jobs;

use App\Models\Pipeline;
use App\Models\User;
use App\Services\PipelineRunnerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunPipelineJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 3600;

    public function __construct(
        public Pipeline $pipeline,
        public User $triggeredBy,
        public array $config = []
    ) {}

    public function handle(PipelineRunnerService $runnerService): void
    {
        try {
            Log::info("Running pipeline: {$this->pipeline->name}", [
                'pipeline_id' => $this->pipeline->id,
                'triggered_by' => $this->triggeredBy->name,
            ]);

            $run = $runnerService->createRun($this->pipeline, $this->triggeredBy, $this->config);
            
            $success = $runnerService->executePipeline($run);

            if ($success) {
                Log::info("Pipeline completed successfully", ['run_id' => $run->id]);
                $this->notifyIntegrations('pipeline_success', $run);
            } else {
                Log::warning("Pipeline failed", ['run_id' => $run->id]);
                $this->notifyIntegrations('pipeline_failed', $run);
            }

        } catch (\Exception $e) {
            Log::error("Pipeline job failed", [
                'pipeline_id' => $this->pipeline->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function notifyIntegrations(string $event, $run): void
    {
        $integrations = \App\Models\Integration::where('team_id', $this->pipeline->team_id)
            ->where('status', 'active')
            ->get();

        foreach ($integrations as $integration) {
            $integration->trigger($event, [
                'pipeline_name' => $this->pipeline->name,
                'run_id' => $run->id,
                'status' => $run->status,
                'duration' => $run->getDurationFormatted(),
                'branch' => $run->git_branch,
                'commit_hash' => $run->git_commit_hash,
            ]);
        }
    }
}
