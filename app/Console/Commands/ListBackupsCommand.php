<?php

namespace App\Console\Commands;

use App\Models\BackupConfiguration;
use Illuminate\Console\Command;

class ListBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backups:list 
                            {--status= : Filter by status (active, paused, failed)}
                            {--team= : Filter by team ID}';

    /**
     * The console command description.
     */
    protected $description = 'List all backup configurations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = BackupConfiguration::with(['database.server', 'database']);

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        if ($teamId = $this->option('team')) {
            $query->where('team_id', $teamId);
        }

        $backups = $query->get();

        if ($backups->isEmpty()) {
            $this->info('No backup configurations found');
            return Command::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Database', 'Type', 'Status', 'Last Backup', 'Next Backup'],
            $backups->map(function ($backup) {
                return [
                    $backup->id,
                    $backup->name,
                    $backup->database->name,
                    $backup->database->type,
                    $backup->status,
                    $backup->last_backup_at ? $backup->last_backup_at->diffForHumans() : 'Never',
                    $backup->next_backup_at ? $backup->next_backup_at->diffForHumans() : '-',
                ];
            })
        );

        $this->info("\nTotal: {$backups->count()} backup(s)");

        return Command::SUCCESS;
    }
}
