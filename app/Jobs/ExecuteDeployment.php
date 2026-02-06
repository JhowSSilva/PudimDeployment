<?php

namespace App\Jobs;

use App\Models\Site;
use App\Models\User;
use App\Models\Deployment;
use App\Services\DeploymentService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExecuteDeployment implements ShouldQueue
{
    use Queueable;

    public Site $site;
    public User $user;
    public string $trigger;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(Site $site, User $user, string $trigger = 'manual')
    {
        $this->site = $site;
        $this->user = $user;
        $this->trigger = $trigger;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info("Starting deployment for site: {$this->site->domain}");

        // Update site status
        $this->site->update(['status' => 'deploying']);

        try {
            $deploymentService = new DeploymentService($this->site);
            $deployment = $deploymentService->deploy($this->user, $this->trigger);

            if ($deployment->isSuccessful()) {
                Log::info("Deployment successful for site: {$this->site->domain}");
                
                // Send success notification
                $notificationService->deployment(
                    user: $this->user,
                    team: $this->site->server->team,
                    site: $this->site,
                    status: 'success',
                    message: "Deploy concluído com sucesso para {$this->site->domain}",
                    actionUrl: route('sites.show', $this->site)
                );
            } else {
                Log::warning("Deployment failed for site: {$this->site->domain}");
                
                // Send failure notification
                $notificationService->deployment(
                    user: $this->user,
                    team: $this->site->server->team,
                    site: $this->site,
                    status: 'failed',
                    message: "Deploy falhou para {$this->site->domain}",
                    actionUrl: route('sites.show', $this->site)
                );
            }

        } catch (\Exception $e) {
            Log::error("Deployment error for site {$this->site->domain}: " . $e->getMessage());
            
            $this->site->update(['status' => 'error']);
            
            // Send error notification
            $notificationService->deployment(
                user: $this->user,
                team: $this->site->server->team,
                site: $this->site,
                status: 'error',
                message: "Erro no deploy de {$this->site->domain}: " . $e->getMessage(),
                actionUrl: route('sites.show', $this->site)
            );
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ExecuteDeployment job failed for site {$this->site->domain}: " . $exception->getMessage());
        
        $this->site->update(['status' => 'error']);
    }
}
