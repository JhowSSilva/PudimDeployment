<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteBackupJob;
use App\Models\BackupConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backups:schedule';

    /**
     * The console command description.
     */
    protected $description = 'Check for due backups and dispatch jobs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dueBackups = BackupConfiguration::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('next_backup_at')
                    ->orWhere('next_backup_at', '<=', now());
            })
            ->get();

        if ($dueBackups->isEmpty()) {
            $this->info('No backups due at this time');
            return Command::SUCCESS;
        }

        $this->info("Found {$dueBackups->count()} backup(s) due for execution");

        foreach ($dueBackups as $backup) {
            $this->line("Dispatching backup: {$backup->name}");
            
            ExecuteBackupJob::dispatch($backup);

            Log::info('Dispatched backup job', [
                'config_id' => $backup->id,
                'config_name' => $backup->name,
            ]);
        }

        $this->info('All due backups have been dispatched!');

        return Command::SUCCESS;
    }
}
