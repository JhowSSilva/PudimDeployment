<?php

namespace App\Jobs;

use App\Models\BackupConfiguration;
use App\Services\Backup\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 3600; // 1 hour
    public $backoff = 300; // 5 minutes between retries

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BackupConfiguration $configuration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BackupService $backupService): void
    {
        Log::info('Starting backup job', [
            'config_id' => $this->configuration->id,
            'config_name' => $this->configuration->name,
        ]);

        $backupService->execute($this->configuration);

        Log::info('Backup job completed', [
            'config_id' => $this->configuration->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Backup job failed permanently', [
            'config_id' => $this->configuration->id,
            'error' => $exception->getMessage(),
        ]);

        $this->configuration->markAsFailed();
    }
}
