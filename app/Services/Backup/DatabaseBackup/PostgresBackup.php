<?php

namespace App\Services\Backup\DatabaseBackup;

use App\Models\BackupConfiguration;
use App\Models\BackupDatabase;
use Illuminate\Support\Facades\Process;

class PostgresBackup implements DatabaseBackupInterface
{
    /**
     * Create PostgreSQL backup using pg_dump
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

        // Build pg_dump command
        $command = $this->buildDumpCommand($database, $config, $outputPath);

        // Execute command
        $result = Process::timeout(3600)->run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('pg_dump failed: ' . $result->errorOutput());
        }

        if (!file_exists($outputPath)) {
            throw new \RuntimeException('Backup file was not created');
        }

        return $outputPath;
    }

    /**
     * Build pg_dump command
     */
    private function buildDumpCommand(BackupDatabase $database, BackupConfiguration $config, string $outputPath): string
    {
        $parts = [
            'PGPASSWORD=' . escapeshellarg($database->getDecryptedPassword()),
            'pg_dump',
            '-h', escapeshellarg($database->server->host),
            '-p', escapeshellarg($database->port),
            '-U', escapeshellarg($database->username),
            '-d', escapeshellarg($database->name),
            '--clean',
            '--if-exists',
            '--create',
            '-f', escapeshellarg($outputPath),
        ];

        // Add excluded tables
        if (!empty($config->excluded_tables)) {
            foreach ($config->excluded_tables as $table) {
                $parts[] = '--exclude-table=' . escapeshellarg($table);
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Restore PostgreSQL backup
     */
    public function restore(BackupDatabase $database, string $filePath): bool
    {
        $command = sprintf(
            'PGPASSWORD=%s psql -h %s -p %d -U %s -d %s -f %s',
            escapeshellarg($database->getDecryptedPassword()),
            escapeshellarg($database->server->host),
            escapeshellarg($database->port),
            escapeshellarg($database->username),
            escapeshellarg($database->name),
            escapeshellarg($filePath)
        );

        $result = Process::timeout(3600)->run($command);

        return $result->successful();
    }

    /**
     * Test PostgreSQL connection
     */
    public function testConnection(BackupDatabase $database): bool
    {
        $command = sprintf(
            'PGPASSWORD=%s psql -h %s -p %d -U %s -d %s -c "SELECT 1" -q',
            escapeshellarg($database->getDecryptedPassword()),
            escapeshellarg($database->server->host),
            escapeshellarg($database->port),
            escapeshellarg($database->username),
            escapeshellarg($database->name)
        );

        $result = Process::timeout(10)->run($command);

        return $result->successful();
    }
}
