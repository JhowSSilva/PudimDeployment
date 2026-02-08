<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Deployment;
use Illuminate\Support\Facades\Log;

class DeploymentObserver
{
    /**
     * Handle the Deployment "created" event.
     */
    public function created(Deployment $deployment): void
    {
        AuditLog::logAction('deployment_created', $deployment, null, [
            'site_id' => $deployment->site_id,
            'site_name' => $deployment->site->name,
            'git_branch' => $deployment->git_branch,
            'git_commit' => $deployment->git_commit,
        ]);
    }

    /**
     * Handle the Deployment "updated" event.
     */
    public function updated(Deployment $deployment): void
    {
        // Log status changes
        if ($deployment->wasChanged('status')) {
            $action = 'deployment_' . $deployment->status;
            
            AuditLog::logAction($action, $deployment, $deployment->getChanges(), [
                'site_name' => $deployment->site->name,
                'old_status' => $deployment->getOriginal('status'),
                'new_status' => $deployment->status,
                'duration' => $deployment->completed_at 
                    ? $deployment->completed_at->diffInSeconds($deployment->started_at) 
                    : null,
            ]);

            // Alert on failures
            if ($deployment->status === 'failed') {
                $this->handleDeploymentFailure($deployment);
            }
        }
    }

    /**
     * Handle deployment failure with alerting
     */
    protected function handleDeploymentFailure(Deployment $deployment): void
    {
        // Count recent failures for this site
        $recentFailures = Deployment::where('site_id', $deployment->site_id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentFailures >= 3) {
            Log::channel('slack')->critical('Multiple deployment failures detected', [
                'site_id' => $deployment->site_id,
                'site_name' => $deployment->site->name,
                'domain' => $deployment->site->domain,
                'failures_count' => $recentFailures,
                'last_error' => $deployment->error_output,
            ]);

            // Could also send email notification here
            // Notification::route('mail', $deployment->site->team->owner->email)
            //     ->notify(new DeploymentFailureAlert($deployment, $recentFailures));
        }
    }

    /**
     * Handle the Deployment "deleted" event.
     */
    public function deleted(Deployment $deployment): void
    {
        AuditLog::logAction('deployment_deleted', $deployment, null, [
            'site_name' => $deployment->site->name,
            'git_commit' => $deployment->git_commit,
            'status' => $deployment->status,
        ]);
    }
}
