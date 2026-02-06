<?php

namespace App\Console\Commands;

use App\Models\Database;
use App\Services\DatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'databases:backup {--database_id=}';
    protected $description = 'Backup databases';

    public function handle(DatabaseService $databaseService)
    {
        $this->info('Starting database backups...');

        $query = Database::query();
        
        if ($databaseId = $this->option('database_id')) {
            $query->where('id', $databaseId);
        }

        $databases = $query->get();
        $backedUp = 0;

        foreach ($databases as $database) {
            try {
                $this->info("Backing up database: {$database->name}");
                
                $result = $databaseService->createBackup($database);
                
                $backedUp++;
                $this->info("✓ Backup created: {$result['file']} ({$result['size']})");
            } catch (\Exception $e) {
                $this->error("✗ Error backing up database {$database->name}: {$e->getMessage()}");
                Log::error("Database backup failed for database {$database->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\nBacked up {$backedUp} out of {$databases->count()} databases.");
        return Command::SUCCESS;
    }
}
