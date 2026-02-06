<?php

namespace App\Services\Backup\DatabaseBackup;

use App\Models\BackupConfiguration;
use App\Models\BackupDatabase;

class RedisBackup implements DatabaseBackupInterface
{
    public function create(BackupDatabase $database, BackupConfiguration $config): string
    {
        // TODO: Implement Redis backup (BGSAVE/RDB/AOF)
        throw new \RuntimeException('Redis backup not yet implemented');
    }

    public function restore(BackupDatabase $database, string $filePath): bool
    {
        // TODO: Implement Redis restore
        throw new \RuntimeException('Redis restore not yet implemented');
    }

    public function testConnection(BackupDatabase $database): bool
    {
        // TODO: Implement connection test
        return false;
    }
}
