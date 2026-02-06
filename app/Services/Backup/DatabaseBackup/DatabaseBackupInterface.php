<?php

namespace App\Services\Backup\DatabaseBackup;

use App\Models\BackupConfiguration;
use App\Models\BackupDatabase;

interface DatabaseBackupInterface
{
    /**
     * Create backup and return local file path
     */
    public function create(BackupDatabase $database, BackupConfiguration $config): string;

    /**
     * Restore backup from file
     */
    public function restore(BackupDatabase $database, string $filePath): bool;

    /**
     * Test database connection
     */
    public function testConnection(BackupDatabase $database): bool;
}
