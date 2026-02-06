<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteBackupJob;
use App\Models\BackupConfiguration;
use Illuminate\Console\Command;

class RunBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backups:run {config_id : The backup configuration ID}
                            {--sync : Run synchronously instead of queued}';

    /**
     * The console command description.
     */
    protected $description = 'Execute a specific backup configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $configId = $this->argument('config_id');

        $config = BackupConfiguration::find($configId);

        if (!$config) {
            $this->error("Backup configuration #{$configId} not found");
            return Command::FAILURE;
        }

        $this->info("Executing backup: {$config->name}");

        if ($this->option('sync')) {
            // Run synchronously
            $this->line('Running backup synchronously...');
            
            try {
                $backupService = app(\App\Services\Backup\BackupService::class);
                $job = $backupService->execute($config);

                if ($job->isSuccess()) {
                    $this->info('✓ Backup completed successfully!');
                    $this->table(
                        ['Metric', 'Value'],
                        [
                            ['File Size', $job->formatted_size],
                            ['Duration', $job->formatted_duration],
                            ['Storage Path', $job->storage_path],
                        ]
                    );
                    return Command::SUCCESS;
                } else {
                    $this->error('✗ Backup failed: ' . $job->error_message);
                    return Command::FAILURE;
                }
            } catch (\Exception $e) {
                $this->error('✗ Backup failed: ' . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            // Dispatch to queue
            ExecuteBackupJob::dispatch($config);
            $this->info('✓ Backup job dispatched to queue');
            return Command::SUCCESS;
        }
    }
}
