<?php

namespace App\Jobs;

use App\Models\Pipeline;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanupPipelineRunsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Log::info("Starting pipeline runs cleanup");

        $totalDeleted = 0;

        Pipeline::chunk(50, function ($pipelines) use (&$totalDeleted) {
            foreach ($pipelines as $pipeline) {
                $deleted = $pipeline->cleanupOldRuns();
                $totalDeleted += $deleted;

                if ($deleted > 0) {
                    Log::info("Cleaned up {$deleted} old runs for pipeline #{$pipeline->id}");
                }
            }
        });

        Log::info("Pipeline runs cleanup completed", [
            'total_deleted' => $totalDeleted,
        ]);
    }
}
