<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Server;
use App\Jobs\CollectServerMetrics;
use App\Jobs\RunUptimeChecks;
use App\Jobs\EvaluateAlertRules;
use App\Services\AlertManagerService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule metrics collection for all servers every minute
Schedule::call(function () {
    Server::where('status', '!=', 'offline')
        ->chunk(10, function ($servers) {
            foreach ($servers as $server) {
                CollectServerMetrics::dispatch($server);
            }
        });
})->everyMinute()->name('collect-server-metrics');

// Schedule SSL certificate expiration check daily at 2 AM
Schedule::command('ssl:check-expiring --days=30')
    ->daily()
    ->at('02:00')
    ->name('check-ssl-certificates');

// Schedule SSL certificate renewal check daily at 2 AM
Schedule::job(new \App\Jobs\RenewSSLCertificatesJob)
    ->daily()
    ->at('02:00')
    ->name('renew-ssl-certificates');

// Backup System Scheduler
// Check for due backups every minute
Schedule::command('backups:schedule')
    ->everyMinute()
    ->name('schedule-backups')
    ->withoutOverlapping();

// Cleanup old backups daily at 3 AM (based on retention policies)
Schedule::command('backups:cleanup')
    ->daily()
    ->at('03:00')
    ->name('cleanup-old-backups')
    ->onOneServer();

// Cleanup old backup job records weekly on Sunday at 4 AM
Schedule::command('backups:cleanup-records')
    ->weekly()
    ->sundays()
    ->at('04:00')
    ->name('cleanup-backup-records')
    ->onOneServer();

// ========================================
// MONITORING & ALERTS SCHEDULING
// ========================================

// Run uptime checks every 2 minutes
Schedule::job(new RunUptimeChecks)
    ->everyTwoMinutes()
    ->name('run-uptime-checks')
    ->withoutOverlapping();

// Evaluate alert rules globally every 10 minutes
Schedule::job(new EvaluateAlertRules)
    ->everyTenMinutes()
    ->name('evaluate-alert-rules')
    ->withoutOverlapping();

// Auto-resolve alerts when metrics return to normal (every hour)
Schedule::call(function () {
    $service = app(AlertManagerService::class);
    $resolvedCount = $service->autoResolveAlerts();
    if ($resolvedCount > 0) {
        info("Auto-resolved {$resolvedCount} alerts");
    }
})->hourly()->name('auto-resolve-alerts');

// ========================================
// AUTO-SCALING & LOAD BALANCING SCHEDULING
// ========================================

// Evaluate all active scaling policies every 2 minutes
Schedule::call(function () {
    \App\Models\ScalingPolicy::where('is_active', true)
        ->chunk(10, function ($policies) {
            foreach ($policies as $policy) {
                \App\Jobs\EvaluateScalingPolicyJob::dispatch($policy);
            }
        });
})->everyTwoMinutes()
    ->name('evaluate-scaling-policies')
    ->withoutOverlapping();

// Run health checks for all active load balancers every minute
Schedule::call(function () {
    \App\Models\LoadBalancer::where('status', 'active')
        ->where('health_check_enabled', true)
        ->chunk(5, function ($loadBalancers) {
            foreach ($loadBalancers as $lb) {
                \App\Jobs\RunHealthCheckJob::dispatch($lb);
            }
        });
})->everyMinute()
    ->name('run-load-balancer-health-checks')
    ->withoutOverlapping();

// Update load balancer statistics every 10 minutes
Schedule::call(function () {
    \App\Models\LoadBalancer::where('status', 'active')
        ->chunk(10, function ($loadBalancers) {
            foreach ($loadBalancers as $lb) {
                \App\Jobs\UpdateLoadBalancerStatsJob::dispatch($lb);
            }
        });
})->everyTenMinutes()
    ->name('update-load-balancer-stats');

// Run health checks for individual servers in pools (every 30 seconds via daemon)
// Note: This should be run in a separate long-running process for real-time health checks
Schedule::call(function () {
    \App\Models\HealthCheck::whereHas('server', function($q) {
        $q->where('status', '!=', 'terminated');
    })
    ->chunk(20, function ($healthChecks) {
        foreach ($healthChecks as $healthCheck) {
            \App\Jobs\RunHealthCheckJob::dispatch($healthCheck);
        }
    });
})->everyMinute()
    ->name('run-individual-health-checks')
    ->withoutOverlapping();

// Cleanup old scaling activity logs (weekly on Sunday at 5 AM)
Schedule::call(function () {
    // Delete scaling logs older than 90 days
    $deleted = \Illuminate\Support\Facades\DB::table('activity_log')
        ->where('log_name', 'scaling')
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
    
    if ($deleted > 0) {
        info("Cleaned up {$deleted} old scaling activity logs");
    }
})->weekly()
    ->sundays()
    ->at('05:00')
    ->name('cleanup-scaling-logs')
    ->onOneServer();

// ========================================
// CI/CD & PIPELINE SCHEDULING
// ========================================

// Cleanup old pipeline runs (daily at 3 AM based on retention policy)
Schedule::job(new \App\Jobs\CleanupPipelineRunsJob)
    ->daily()
    ->at('03:00')
    ->name('cleanup-pipeline-runs')
    ->onOneServer();

// Check for scheduled pipeline triggers (every 5 minutes)
Schedule::call(function () {
    \App\Models\Pipeline::where('status', 'active')
        ->where('trigger_type', 'schedule')
        ->chunk(10, function ($pipelines) {
            foreach ($pipelines as $pipeline) {
                $schedule = $pipeline->trigger_config['schedule'] ?? null;
                
                if ($schedule && $this->shouldRunScheduled($schedule)) {
                    \App\Jobs\RunPipelineJob::dispatch(
                        $pipeline,
                        \App\Models\User::first(), // System user
                        ['trigger_source' => 'schedule']
                    );
                }
            }
        });
})->everyFiveMinutes()
    ->name('check-scheduled-pipelines');

// Auto-expire deployment approvals (every hour)
Schedule::call(function () {
    \App\Models\DeploymentApproval::expired()
        ->chunk(10, function ($approvals) {
            foreach ($approvals as $approval) {
                $approval->expire();
                info("Deployment approval #{$approval->id} expired");
            }
        });
})->hourly()
    ->name('expire-deployment-approvals');
