<?php

namespace App\Jobs;

use App\Models\BackupJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupBackupJobsRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job - cleanup job records older than 90 days
     */
    public function handle(): void
    {
        Log::info('Starting cleanup of old backup job records');

        $threshold = now()->subDays(90);

        $deleted = BackupJob::where('created_at', '<', $threshold)
            ->where('status', '!=', 'running')
            ->delete();

        Log::info('Completed cleanup of old backup job records', [
            'deleted_count' => $deleted,
        ]);
    }
}
