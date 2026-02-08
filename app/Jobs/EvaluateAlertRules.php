<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\ApplicationMetric;
use App\Models\AlertRule;
use App\Services\AlertManagerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class EvaluateAlertRules implements ShouldQueue
{
    use Queueable;

    public ?Server $server;
    public int $tries = 2;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(?Server $server = null)
    {
        $this->server = $server;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $alertManager = app(AlertManagerService::class);
        
        Log::info('Evaluating alert rules', [
            'server_id' => $this->server?->id,
        ]);

        try {
            // Get recent metrics to evaluate
            $query = ApplicationMetric::query()
                ->where('recorded_at', '>=', now()->subMinutes(10))
                ->latest('recorded_at');
                
            if ($this->server) {
                $query->where('server_id', $this->server->id);
            }
            
            $metrics = $query->get();
            
            $triggeredCount = 0;
            
            foreach ($metrics as $metric) {
                $alerts = $alertManager->evaluateMetric($metric);
                $triggeredCount += count($alerts);
            }
            
            Log::info('Alert rules evaluated', [
                'metrics_checked' => $metrics->count(),
                'alerts_triggered' => $triggeredCount,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error evaluating alert rules: ' . $e->getMessage());
            throw $e;
        }
    }
}
