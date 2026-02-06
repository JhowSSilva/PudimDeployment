<?php

namespace App\Jobs;

use App\Models\BackupConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupOldBackupsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 600;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting cleanup of old backups');

        $configurations = BackupConfiguration::where('keep_backups', '>', 0)->get();

        foreach ($configurations as $config) {
            $files = $config->files()
                ->orderBy('created_at', 'desc')
                ->get();

            $filesToDelete = $files->skip($config->keep_backups);

            foreach ($filesToDelete as $file) {
                try {
                    // Delete from storage
                    $storageManager = app(\App\Services\Backup\Storage\StorageManager::class);
                    $disk = $storageManager->getDisk($file->storage_provider, $config->storage_credentials);
                    $disk->delete($file->storage_path);

                    // Delete record
                    $file->delete();

                    Log::info('Deleted old backup file', [
                        'file_id' => $file->id,
                        'config_id' => $config->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old backup file', [
                        'file_id' => $file->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('Completed cleanup of old backups');
    }
}
