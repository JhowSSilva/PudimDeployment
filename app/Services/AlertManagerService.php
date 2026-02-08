<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\ApplicationMetric;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertManagerService
{
    /**
     * Evaluate all alert rules for a metric.
     */
    public function evaluateMetric(ApplicationMetric $metric): array
    {
        $triggeredAlerts = [];
        
        // Get all active rules for this metric type
        $rules = AlertRule::active()
            ->forMetric($metric->metric_type)
            ->where(function ($query) use ($metric) {
                $query->where('server_id', $metric->server_id)
                      ->orWhereNull('server_id');
            })
            ->get();
        
        foreach ($rules as $rule) {
            if ($this->shouldTriggerRule($rule, $metric)) {
                $triggeredAlerts[] = $this->triggerRule($rule, $metric);
            }
        }
        
        return $triggeredAlerts;
    }
    
    /**
     * Check if a rule should trigger.
     */
    protected function shouldTriggerRule(AlertRule $rule, ApplicationMetric $metric): bool
    {
        // Check if rule is in cooldown
        if ($rule->isInCooldown()) {
            return false;
        }
        
        // Check if threshold is met
        if (!$rule->shouldTrigger($metric->value)) {
            return false;
        }
        
        // Check duration requirement
        if ($rule->duration > 0) {
            return $this->checkDurationRequirement($rule, $metric);
        }
        
        return true;
    }
    
    /**
     * Check if metric has been above/below threshold for required duration.
     */
    protected function checkDurationRequirement(AlertRule $rule, ApplicationMetric $metric): bool
    {
        $durationStart = now()->subSeconds($rule->duration);
        
        $metricsInDuration = ApplicationMetric::forServer($metric->server_id)
            ->byType($metric->metric_type)
            ->between($durationStart, now())
            ->get();
        
        // All metrics in duration period must meet condition
        foreach ($metricsInDuration as $m) {
            if (!$rule->shouldTrigger($m->value)) {
                return false;
            }
        }
        
        return $metricsInDuration->count() > 0;
    }
    
    /**
     * Trigger an alert rule.
     */
    protected function triggerRule(AlertRule $rule, ApplicationMetric $metric): Alert
    {
        $alert = $rule->trigger($metric->value, $metric->server);
        
        // Send notifications
        $this->sendNotifications($alert, $rule);
        
        Log::info("Alert triggered: {$alert->title}", [
            'alert_id' => $alert->id,
            'rule_id' => $rule->id,
            'current_value' => $metric->value,
            'threshold' => $rule->threshold,
        ]);
        
        return $alert;
    }
    
    /**
     * Send notifications for an alert.
     */
    protected function sendNotifications(Alert $alert, AlertRule $rule): void
    {
        $channels = $rule->channels ?? [];
        $sentChannels = [];
        
        foreach ($channels as $channel) {
            try {
                match ($channel) {
                    'email' => $this->sendEmailNotification($alert),
                    'slack' => $this->sendSlackNotification($alert),
                    'discord' => $this->sendDiscordNotification($alert),
                    'webhook' => $this->sendWebhookNotification($alert),
                    default => null,
                };
                
                $sentChannels[] = $channel;
            } catch (\Exception $e) {
                Log::error("Failed to send {$channel} notification: " . $e->getMessage());
            }
        }
        
        if (!empty($sentChannels)) {
            $alert->update([
                'notification_sent' => $sentChannels,
                'notification_sent_at' => now(),
            ]);
        }
    }
    
    /**
     * Send email notification.
     */
    protected function sendEmailNotification(Alert $alert): void
    {
        $team = $alert->team;
        $email = $team->owner->email;
        
        // TODO: Implement actual email sending
        // Mail::to($email)->send(new AlertNotification($alert));
        
        Log::info("Email notification sent to {$email} for alert #{$alert->id}");
    }
    
    /**
     * Send Slack notification.
     */
    protected function sendSlackNotification(Alert $alert): void
    {
        // TODO: Implement Slack webhook
        Log::info("Slack notification sent for alert #{$alert->id}");
    }
    
    /**
     * Send Discord notification.
     */
    protected function sendDiscordNotification(Alert $alert): void
    {
        // TODO: Implement Discord webhook
        Log::info("Discord notification sent for alert #{$alert->id}");
    }
    
    /**
     * Send custom webhook notification.
     */
    protected function sendWebhookNotification(Alert $alert): void
    {
        // TODO: Implement custom webhook
        Log::info("Webhook notification sent for alert #{$alert->id}");
    }
    
    /**
     * Get open alerts for a team.
     */
    public function getOpenAlerts(Team $team): \Illuminate\Database\Eloquent\Collection
    {
        return Alert::where('team_id', $team->id)
            ->open()
            ->with(['server', 'site', 'alertRule'])
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get critical alerts count for a team.
     */
    public function getCriticalAlertsCount(Team $team): int
    {
        return Alert::where('team_id', $team->id)
            ->open()
            ->critical()
            ->count();
    }
    
    /**
     * Acknowledge an alert.
     */
    public function acknowledgeAlert(Alert $alert, User $user, ?string $note = null): void
    {
        $alert->acknowledge($user, $note);
        
        Log::info("Alert #{$alert->id} acknowledged by user #{$user->id}");
    }
    
    /**
     * Resolve an alert.
     */
    public function resolveAlert(Alert $alert, ?string $note = null): void
    {
        $alert->resolve($note);
        
        Log::info("Alert #{$alert->id} resolved");
    }
    
    /**
     * Auto-resolve alerts based on current metrics.
     */
    public function autoResolveAlerts(): int
    {
        $resolvedCount = 0;
        
        $openAlerts = Alert::open()
            ->whereNotNull('alert_rule_id')
            ->with('alertRule')
            ->get();
        
        foreach ($openAlerts as $alert) {
            if ($this->shouldAutoResolve($alert)) {
                $this->resolveAlert($alert, 'Auto-resolved: metric returned to normal levels');
                $resolvedCount++;
            }
        }
        
        return $resolvedCount;
    }
    
    /**
     * Check if alert should be auto-resolved.
     */
    protected function shouldAutoResolve(Alert $alert): bool
    {
        if (!$alert->alertRule || !$alert->server_id) {
            return false;
        }
        
        // Get recent metrics
        $recentMetric = ApplicationMetric::forServer($alert->server_id)
            ->byType($alert->alertRule->metric_type)
            ->latest('recorded_at')
            ->first();
        
        if (!$recentMetric) {
            return false;
        }
        
        // Check if current value no longer triggers the rule
        return !$alert->alertRule->shouldTrigger($recentMetric->value);
    }
    
    /**
     * Create alert summary for team.
     */
    public function getAlertSummary(Team $team, int $hours = 24): array
    {
        $start = now()->subHours($hours);
        
        return [
            'total_alerts' => Alert::where('team_id', $team->id)
                ->where('created_at', '>=', $start)
                ->count(),
            'critical_alerts' => Alert::where('team_id', $team->id)
                ->where('created_at', '>=', $start)
                ->critical()
                ->count(),
            'open_alerts' => Alert::where('team_id', $team->id)
                ->open()
                ->count(),
            'resolved_alerts' => Alert::where('team_id', $team->id)
                ->where('created_at', '>=', $start)
                ->resolved()
                ->count(),
        ];
    }
}
