<?php

namespace App\Services\Backup\DatabaseBackup;

use App\Models\BackupConfiguration;
use App\Models\BackupDatabase;
use Illuminate\Support\Facades\Process;

class MysqlBackup implements DatabaseBackupInterface
{
    /**
     * Create MySQL backup using mysqldump
     */
    public function create(BackupDatabase $database, BackupConfiguration $config): string
    {
        $tempPath = config('backup-providers.temp_path');
        
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $filename = sprintf(
            '%s_%s_%s.sql',
            $database->type,
            $database->name,
            now()->format('Y-m-d_His')
        );

        $outputPath = $tempPath . '/' . $filename;

        // Build mysqldump command
        $command = $this->buildDumpCommand($database, $config, $outputPath);

        // Execute command
        $result = Process::timeout(3600)->run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('mysqldump failed: ' . $result->errorOutput());
        }

        if (!file_exists($outputPath)) {
            throw new \RuntimeException('Backup file was not created');
        }

        return $outputPath;
    }

    /**
     * Build mysqldump command
     */
    private function buildDumpCommand(BackupDatabase $database, BackupConfiguration $config, string $outputPath): string
    {
        $parts = [
            'mysqldump',
            '-h', escapeshellarg($database->server->host),
            '-P', escapeshellarg($database->port),
            '-u', escapeshellarg($database->username),
            '-p' . escapeshellarg($database->getDecryptedPassword()),
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            '--routines',
            '--triggers',
            '--events',
            escapeshellarg($database->name),
        ];

        // Add excluded tables
        if (!empty($config->excluded_tables)) {
            foreach ($config->excluded_tables as $table) {
                $parts[] = '--ignore-table=' . escapeshellarg($database->name . '.' . $table);
            }
        }

        $parts[] = '>';
        $parts[] = escapeshellarg($outputPath);

        return implode(' ', $parts);
    }

    /**
     * Restore MySQL backup
     */
    public function restore(BackupDatabase $database, string $filePath): bool
    {
        $command = sprintf(
            'mysql -h %s -P %d -u %s -p%s %s < %s',
            escapeshellarg($database->server->host),
            escapeshellarg($database->port),
            escapeshellarg($database->username),
            escapeshellarg($database->getDecryptedPassword()),
            escapeshellarg($database->name),
            escapeshellarg($filePath)
        );

        $result = Process::timeout(3600)->run($command);

        return $result->successful();
    }

    /**
     * Test MySQL connection
     */
    public function testConnection(BackupDatabase $database): bool
    {
        $command = sprintf(
            'mysql -h %s -P %d -u %s -p%s -e "SELECT 1" %s',
            escapeshellarg($database->server->host),
            escapeshellarg($database->port),
            escapeshellarg($database->username),
            escapeshellarg($database->getDecryptedPassword()),
            escapeshellarg($database->name)
        );

        $result = Process::timeout(10)->run($command);

        return $result->successful();
    }
}
