<?php

namespace App\Services\Backup\DatabaseBackup;

use App\Models\BackupDatabase;

class DatabaseBackupFactory
{
    /**
     * Create database backup handler for given type
     */
    public static function make(string $type): DatabaseBackupInterface
    {
        return match($type) {
            'postgresql' => new PostgresBackup(),
            'mysql' => new MysqlBackup(),
            'mongodb' => new MongoBackup(),
            'redis' => new RedisBackup(),
            default => throw new \InvalidArgumentException("Unsupported database type: {$type}"),
        };
    }
}
