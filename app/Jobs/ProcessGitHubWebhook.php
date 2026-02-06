<?php

namespace App\Jobs;

use App\Models\GitHubRepository;
use App\Models\GitHubWebhookEvent;
use App\Models\GitHubWorkflowRun;
use App\Services\GitHubPagesService;
use App\Services\WorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGitHubWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public GitHubWebhookEvent $webhookEvent
    ) {}

    public function handle(): void
    {
        try {
            $this->webhookEvent->markAsProcessing();

            $payload = $this->webhookEvent->payload;
            $eventType = $this->webhookEvent->event_type;

            // Find repository
            $repository = $this->findRepository($payload);
            
            if ($repository) {
                $this->webhookEvent->update(['repository_id' => $repository->id]);
            }

            // Handle different event types
            match($eventType) {
                'push' => $this->handlePush($repository, $payload),
                'workflow_run' => $this->handleWorkflowRun($repository, $payload),
                'page_build' => $this->handlePageBuild($repository, $payload),
                'deployment' => $this->handleDeployment($repository, $payload),
                default => Log::info("Unhandled webhook event: {$eventType}"),
            };

            $this->webhookEvent->markAsCompleted();
        } catch (\Exception $e) {
            $this->webhookEvent->markAsFailed($e->getMessage());
            Log::error('Failed to process GitHub webhook: ' . $e->getMessage(), [
                'webhook_id' => $this->webhookEvent->id,
                'event_type' => $this->webhookEvent->event_type,
            ]);
            throw $e;
        }
    }

    protected function findRepository(array $payload): ?GitHubRepository
    {
        if (!isset($payload['repository']['id'])) {
            return null;
        }

        return GitHubRepository::where('github_id', $payload['repository']['id'])->first();
    }

    protected function handlePush(?GitHubRepository $repository, array $payload): void
    {
        if (!$repository) {
            return;
        }

        Log::info("Push event received for {$repository->full_name}", [
            'ref' => $payload['ref'] ?? null,
            'commits' => count($payload['commits'] ?? []),
        ]);

        // Update repository metadata
        $repository->update([
            'github_updated_at' => $payload['repository']['updated_at'] ?? now(),
        ]);
    }

    protected function handleWorkflowRun(?GitHubRepository $repository, array $payload): void
    {
        if (!$repository) {
            return;
        }

        $workflowRun = $payload['workflow_run'];
        $user = $repository->user;

        if (!$user) {
            return;
        }

        $service = new WorkflowService($user);
        $service->syncWorkflowRun($repository, $workflowRun);

        Log::info("Workflow run synced", [
            'repository' => $repository->full_name,
            'workflow' => $workflowRun['name'],
            'status' => $workflowRun['status'],
            'conclusion' => $workflowRun['conclusion'] ?? null,
        ]);
    }

    protected function handlePageBuild(?GitHubRepository $repository, array $payload): void
    {
        if (!$repository) {
            return;
        }

        $user = $repository->user;
        
        if (!$user) {
            return;
        }

        $service = new GitHubPagesService($user);
        $service->syncPages($repository);

        Log::info("GitHub Pages build event", [
            'repository' => $repository->full_name,
            'status' => $payload['build']['status'] ?? null,
        ]);
    }

    protected function handleDeployment(?GitHubRepository $repository, array $payload): void
    {
        if (!$repository) {
            return;
        }

        Log::info("Deployment event received", [
            'repository' => $repository->full_name,
            'environment' => $payload['deployment']['environment'] ?? null,
            'ref' => $payload['deployment']['ref'] ?? null,
        ]);
    }
}
