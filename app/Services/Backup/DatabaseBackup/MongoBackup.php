<?php

namespace App\Services\Backup\DatabaseBackup;

use App\Models\BackupConfiguration;
use App\Models\BackupDatabase;

class MongoBackup implements DatabaseBackupInterface
{
    public function create(BackupDatabase $database, BackupConfiguration $config): string
    {
        // TODO: Implement mongodump
        throw new \RuntimeException('MongoDB backup not yet implemented');
    }

    public function restore(BackupDatabase $database, string $filePath): bool
    {
        // TODO: Implement mongorestore
        throw new \RuntimeException('MongoDB restore not yet implemented');
    }

    public function testConnection(BackupDatabase $database): bool
    {
        // TODO: Implement connection test
        return false;
    }
}
