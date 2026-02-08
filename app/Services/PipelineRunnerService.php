<?php

namespace App\Services;

use App\Models\Pipeline;
use App\Models\PipelineRun;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class PipelineRunnerService
{
    public function createRun(Pipeline $pipeline, User $triggeredBy, array $config = []): PipelineRun
    {
        return PipelineRun::create([
            'pipeline_id' => $pipeline->id,
            'triggered_by_user_id' => $triggeredBy->id,
            'trigger_source' => $config['trigger_source'] ?? 'manual',
            'status' => 'pending',
            'git_branch' => $config['git_branch'] ?? null,
            'git_commit_hash' => $config['git_commit_hash'] ?? null,
            'git_commit_message' => $config['git_commit_message'] ?? null,
        ]);
    }

    public function executePipeline(PipelineRun $run): bool
    {
        try {
            $run->start();

            Log::info("Starting pipeline run #{$run->id}", [
                'pipeline_id' => $run->pipeline_id,
                'trigger_source' => $run->trigger_source,
            ]);

            $stages = $run->pipeline->stages()->orderBy('order')->get();

            if ($stages->isEmpty()) {
                $run->markFailed('No stages configured');
                return false;
            }

            $previousStatus = 'success';

            foreach ($stages as $stage) {
                // Check if stage should run based on previous result
                if (!$stage->shouldRun($previousStatus)) {
                    Log::info("Skipping stage #{$stage->id} - {$stage->name}");
                    continue;
                }

                // Check for manual approval requirement
                if ($stage->requiresManualApproval()) {
                    $this->requestApproval($run, $stage);
                    return true; // Pause execution
                }

                $result = $this->executeStage($run, $stage);
                $previousStatus = $result['status'];

                $run->recordStageResult($stage->id, $result);

                if ($result['status'] === 'failed' && !$stage->canFailSafely()) {
                    $run->markFailed("Stage '{$stage->name}' failed");
                    return false;
                }
            }

            $run->markSuccess();

            // Auto-deploy if configured
            if ($run->pipeline->auto_deploy && $run->pipeline->site) {
                $this->createDeployment($run);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Pipeline run #{$run->id} failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $run->markFailed($e->getMessage());
            return false;
        }
    }

    protected function executeStage(PipelineRun $run, PipelineStage $stage): array
    {
        $startTime = microtime(true);

        Log::info("Executing stage #{$stage->id} - {$stage->name}");

        $run->appendOutputLog("\n=== Stage: {$stage->name} ({$stage->type}) ===\n");

        try {
            $commands = $stage->commands ?? [];
            $envVars = $stage->mergeEnvironmentVariables();

            $output = '';
            $exitCode = 0;

            foreach ($commands as $command) {
                $run->appendOutputLog("$ {$command}\n");

                // Execute command
                $result = Process::timeout($stage->timeout_minutes * 60)
                    ->env($envVars)
                    ->run($command);

                $output .= $result->output();
                $exitCode = $result->exitCode();

                $run->appendOutputLog($result->output());

                if ($result->failed()) {
                    $run->appendErrorLog($result->errorOutput());
                    throw new \Exception("Command failed with exit code {$exitCode}");
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            return [
                'status' => 'success',
                'duration' => $duration,
                'output' => $output,
                'exit_code' => $exitCode,
            ];

        } catch (\Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);

            Log::error("Stage #{$stage->id} failed", [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'duration' => $duration,
                'error' => $e->getMessage(),
                'exit_code' => $exitCode ?? 1,
            ];
        }
    }

    protected function requestApproval(PipelineRun $run, PipelineStage $stage): void
    {
        $strategy = $run->pipeline->site?->deploymentStrategies()
            ->where('requires_approval', true)
            ->first();

        if (!$strategy) {
            // Create default approval request
            $strategy = null;
        }

        \App\Models\DeploymentApproval::create([
            'pipeline_run_id' => $run->id,
            'deployment_strategy_id' => $strategy?->id,
            'status' => 'pending',
            'requested_by_user_id' => $run->triggered_by_user_id,
            'request_message' => "Approval required for stage: {$stage->name}",
            'requested_at' => now(),
            'expires_at' => now()->addHours(24),
            'required_approvals' => 1,
        ]);

        $run->update(['status' => 'pending']);

        Log::info("Approval requested for pipeline run #{$run->id}");
    }

    protected function createDeployment(PipelineRun $run): void
    {
        if (!$run->pipeline->site) {
            return;
        }

        $deployment = \App\Models\Deployment::create([
            'site_id' => $run->pipeline->site_id,
            'user_id' => $run->triggered_by_user_id,
            'status' => 'deploying',
            'commit_hash' => $run->git_commit_hash,
            'commit_message' => $run->git_commit_message,
            'started_at' => now(),
        ]);

        $run->update(['deployment_id' => $deployment->id]);

        // Dispatch deployment job
        \App\Jobs\DeploySiteJob::dispatch($deployment);
    }

    public function cancelRun(PipelineRun $run): void
    {
        $run->cancel();
        
        Log::info("Pipeline run #{$run->id} cancelled");
    }

    public function getRunLogs(PipelineRun $run): array
    {
        return [
            'output' => $run->output_log,
            'error' => $run->error_log,
            'status' => $run->status,
            'stage_results' => $run->stage_results,
        ];
    }
}
