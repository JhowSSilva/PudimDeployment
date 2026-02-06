<?php

namespace App\Services\Backup;

use App\Events\BackupCompleted;
use App\Events\BackupFailed;
use App\Events\BackupStarted;
use App\Models\BackupConfiguration;
use App\Models\BackupFile;
use App\Models\BackupJob;
use App\Services\Backup\Compression\CompressionFactory;
use App\Services\Backup\DatabaseBackup\DatabaseBackupFactory;
use App\Services\Backup\Storage\StorageManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public function __construct(
        private StorageManager $storageManager
    ) {}

    /**
     * Execute backup for a configuration
     */
    public function execute(BackupConfiguration $config): BackupJob
    {
        // Create job record
        $job = BackupJob::create([
            'backup_configuration_id' => $config->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            // Update config status
            $config->markAsRunning();

            // Fire event
            event(new BackupStarted($config, $job));

            // Step 1: Create database dump
            Log::info('Creating database dump', ['config_id' => $config->id]);
            $dumpPath = $this->createDatabaseDump($config);

            // Step 2: Compress if needed
            if ($config->compression !== 'none') {
                Log::info('Compressing backup', ['compression' => $config->compression]);
                $compressedPath = $this->compressFile($dumpPath, $config);
                unlink($dumpPath);
                $dumpPath = $compressedPath;
            }

            // Step 3: Calculate checksum and metrics
            $checksum = md5_file($dumpPath);
            $fileSize = filesize($dumpPath);
            $filename = basename($dumpPath);

            // Step 4: Upload to cloud storage
            Log::info('Uploading to storage', [
                'provider' => $config->storage_provider,
                'size' => $fileSize,
            ]);
            $storagePath = $this->uploadToStorage($dumpPath, $config, $filename);

            // Step 5: Calculate duration
            $duration = now()->diffInSeconds($job->started_at);

            // Step 6: Create backup file record
            $backupFile = BackupFile::create([
                'backup_configuration_id' => $config->id,
                'backup_job_id' => $job->id,
                'filename' => $filename,
                'file_size' => $fileSize,
                'storage_path' => $storagePath,
                'storage_provider' => $config->storage_provider,
                'compression_type' => $config->compression,
                'checksum' => $checksum,
                'is_encrypted' => !empty($config->encryption_password),
            ]);

            // Step 7: Cleanup old backups (retention policy)
            $this->cleanupOldBackups($config);

            // Step 8: Update job as completed
            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
                'file_size' => $fileSize,
                'storage_path' => $storagePath,
                'duration' => $duration,
                'metadata' => [
                    'compression' => $config->compression,
                    'checksum' => $checksum,
                    'database_type' => $config->database->type,
                ],
            ]);

            // Step 9: Update configuration
            $config->markAsCompleted($fileSize, $duration);

            // Step 10: Cleanup local file
            if (file_exists($dumpPath)) {
                unlink($dumpPath);
            }

            // Fire success event
            event(new BackupCompleted($config, $job, $backupFile));

            Log::info('Backup completed successfully', [
                'config_id' => $config->id,
                'job_id' => $job->id,
                'file_size' => $fileSize,
                'duration' => $duration,
            ]);

            return $job;

        } catch (\Exception $e) {
            Log::error('Backup failed', [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update job as failed
            $job->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'duration' => now()->diffInSeconds($job->started_at),
            ]);

            // Update configuration
            $config->markAsFailed();

            // Fire failure event
            event(new BackupFailed($config, $job, $e));

            // Cleanup on failure
            if ($config->delete_local_on_fail && isset($dumpPath) && file_exists($dumpPath)) {
                unlink($dumpPath);
            }

            throw $e;
        }
    }

    /**
     * Create database dump
     */
    private function createDatabaseDump(BackupConfiguration $config): string
    {
        $database = $config->database;

        $backupHandler = DatabaseBackupFactory::make($database->type);

        return $backupHandler->create($database, $config);
    }

    /**
     * Compress file
     */
    private function compressFile(string $path, BackupConfiguration $config): string
    {
        $compressor = CompressionFactory::make($config->compression);

        return $compressor->compress($path, $config->encryption_password);
    }

    /**
     * Upload to cloud storage
     */
    private function uploadToStorage(string $localPath, BackupConfiguration $config, string $filename): string
    {
        $remotePath = trim($config->storage_path, '/') . '/' . $filename;

        $disk = $this->storageManager->getDisk($config->storage_provider, $config->storage_credentials);

        $stream = fopen($localPath, 'r');
        $disk->put($remotePath, $stream);
        fclose($stream);

        return $remotePath;
    }

    /**
     * Cleanup old backups based on retention policy
     */
    private function cleanupOldBackups(BackupConfiguration $config): void
    {
        if ($config->keep_backups === 0) {
            return; // Keep all backups
        }

        $files = BackupFile::where('backup_configuration_id', $config->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $filesToDelete = $files->skip($config->keep_backups);

        foreach ($filesToDelete as $file) {
            try {
                $disk = $this->storageManager->getDisk(
                    $file->storage_provider,
                    $config->storage_credentials
                );

                $disk->delete($file->storage_path);

                $file->delete();

                Log::info('Deleted old backup file', [
                    'file_id' => $file->id,
                    'filename' => $file->filename,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to delete old backup', [
                    'file_id' => $file->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Generate filename for backup
     */
    public function generateFilename(BackupConfiguration $config): string
    {
        if ($config->custom_filename) {
            $filename = $config->custom_filename;
        } else {
            $filename = sprintf(
                '%s_%s_%s',
                $config->database->type,
                $config->database->name,
                now()->format('Y-m-d_His')
            );
        }

        // Add compression extension
        $extension = match($config->compression) {
            'zip' => '.zip',
            'tar' => '.tar',
            'tar_gz' => '.tar.gz',
            'tar_bz2' => '.tar.bz2',
            'none' => '.sql',
        };

        return $filename . $extension;
    }
}
