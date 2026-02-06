<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Database;
use App\Models\DatabaseUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseService
{
    private SSHConnectionService $ssh;

    public function __construct(private Server $server)
    {
        $this->ssh = new SSHConnectionService($server);
    }

    /**
     * Create a new database
     */
    public function createDatabase(string $name, string $type = 'mysql'): array
    {
        try {
            $sanitizedName = $this->sanitizeDatabaseName($name);
            
            if ($type === 'mysql') {
                return $this->createMySQLDatabase($sanitizedName);
            } elseif ($type === 'postgresql') {
                return $this->createPostgreSQLDatabase($sanitizedName);
            }
            
            throw new \InvalidArgumentException("Unsupported database type: {$type}");
            
        } catch (\Exception $e) {
            Log::error("Failed to create {$type} database", [
                'server_id' => $this->server->id,
                'database_name' => $name,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create MySQL database
     */
    private function createMySQLDatabase(string $name): array
    {
        // Create database
        $command = "mysql -e \"CREATE DATABASE IF NOT EXISTS \`{$name}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"";
        $result = $this->ssh->execute($command);
        
        if ($result['exit_code'] !== 0) {
            throw new \Exception("Failed to create MySQL database: " . $result['output']);
        }
        
        // Create database record
        $database = Database::create([
            'server_id' => $this->server->id,
            'name' => $name,
            'type' => 'mysql',
            'status' => 'active',
        ]);
        
        Log::info("MySQL database created", [
            'server_id' => $this->server->id,
            'database_id' => $database->id,
            'database_name' => $name,
        ]);
        
        return [
            'success' => true,
            'database' => $database,
            'message' => 'MySQL database created successfully',
        ];
    }

    /**
     * Create PostgreSQL database
     */
    private function createPostgreSQLDatabase(string $name): array
    {
        // Create database
        $command = "sudo -u postgres createdb \"{$name}\"";
        $result = $this->ssh->execute($command);
        
        if ($result['exit_code'] !== 0) {
            // Check if database already exists
            if (str_contains($result['output'], 'already exists')) {
                throw new \Exception("Database '{$name}' already exists");
            }
            throw new \Exception("Failed to create PostgreSQL database: " . $result['output']);
        }
        
        // Create database record
        $database = Database::create([
            'server_id' => $this->server->id,
            'name' => $name,
            'type' => 'postgresql',
            'status' => 'active',
        ]);
        
        Log::info("PostgreSQL database created", [
            'server_id' => $this->server->id,
            'database_id' => $database->id,
            'database_name' => $name,
        ]);
        
        return [
            'success' => true,
            'database' => $database,
            'message' => 'PostgreSQL database created successfully',
        ];
    }

    /**
     * Delete database
     */
    public function deleteDatabase(Database $database): array
    {
        try {
            if ($database->type === 'mysql') {
                $command = "mysql -e \"DROP DATABASE IF EXISTS \`{$database->name}\`;\"";
            } elseif ($database->type === 'postgresql') {
                $command = "sudo -u postgres dropdb \"{$database->name}\"";
            } else {
                throw new \InvalidArgumentException("Unsupported database type: {$database->type}");
            }
            
            $result = $this->ssh->execute($command);
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to delete database: " . $result['output']);
            }
            
            // Delete all associated users
            $database->users()->delete();
            
            // Delete database record
            $database->delete();
            
            Log::info("Database deleted", [
                'server_id' => $this->server->id,
                'database_name' => $database->name,
                'database_type' => $database->type,
            ]);
            
            return [
                'success' => true,
                'message' => 'Database deleted successfully',
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to delete database", [
                'server_id' => $this->server->id,
                'database_id' => $database->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create database user
     */
    public function createUser(Database $database, string $username, string $password, array $privileges = ['SELECT', 'INSERT', 'UPDATE', 'DELETE']): array
    {
        try {
            $sanitizedUsername = $this->sanitizeUsername($username);
            
            if ($database->type === 'mysql') {
                return $this->createMySQLUser($database, $sanitizedUsername, $password, $privileges);
            } elseif ($database->type === 'postgresql') {
                return $this->createPostgreSQLUser($database, $sanitizedUsername, $password, $privileges);
            }
            
            throw new \InvalidArgumentException("Unsupported database type: {$database->type}");
            
        } catch (\Exception $e) {
            Log::error("Failed to create database user", [
                'server_id' => $this->server->id,
                'database_id' => $database->id,
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create MySQL user
     */
    private function createMySQLUser(Database $database, string $username, string $password, array $privileges): array
    {
        $privilegesStr = implode(', ', $privileges);
        
        $commands = [
            "mysql -e \"CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$password}';\"",
            "mysql -e \"GRANT {$privilegesStr} ON \`{$database->name}\`.* TO '{$username}'@'localhost';\"",
            "mysql -e \"FLUSH PRIVILEGES;\"",
        ];
        
        foreach ($commands as $command) {
            $result = $this->ssh->execute($command);
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to create MySQL user: " . $result['output']);
            }
        }
        
        // Create user record
        $user = DatabaseUser::create([
            'database_id' => $database->id,
            'username' => $username,
            'privileges' => $privileges,
            'status' => 'active',
        ]);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'MySQL user created successfully',
        ];
    }

    /**
     * Create PostgreSQL user
     */
    private function createPostgreSQLUser(Database $database, string $username, string $password, array $privileges): array
    {
        $commands = [
            "sudo -u postgres createuser \"{$username}\"",
            "sudo -u postgres psql -c \"ALTER USER \\\"{$username}\\\" WITH PASSWORD '{$password}';\"",
            "sudo -u postgres psql -c \"GRANT CONNECT ON DATABASE \\\"{$database->name}\\\" TO \\\"{$username}\\\";\"",
        ];
        
        // Grant table privileges
        if (in_array('SELECT', $privileges) || in_array('ALL', $privileges)) {
            $commands[] = "sudo -u postgres psql -d \"{$database->name}\" -c \"GRANT SELECT ON ALL TABLES IN SCHEMA public TO \\\"{$username}\\\";\"";
        }
        if (in_array('INSERT', $privileges) || in_array('UPDATE', $privileges) || in_array('DELETE', $privileges) || in_array('ALL', $privileges)) {
            $commands[] = "sudo -u postgres psql -d \"{$database->name}\" -c \"GRANT INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO \\\"{$username}\\\";\"";
        }
        
        foreach ($commands as $command) {
            $result = $this->ssh->execute($command);
            if ($result['exit_code'] !== 0 && !str_contains($result['output'], 'already exists')) {
                throw new \Exception("Failed to create PostgreSQL user: " . $result['output']);
            }
        }
        
        // Create user record
        $user = DatabaseUser::create([
            'database_id' => $database->id,
            'username' => $username,
            'privileges' => $privileges,
            'status' => 'active',
        ]);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'PostgreSQL user created successfully',
        ];
    }

    /**
     * Delete database user
     */
    public function deleteUser(DatabaseUser $user): array
    {
        try {
            $database = $user->database;
            
            if ($database->type === 'mysql') {
                $commands = [
                    "mysql -e \"DROP USER IF EXISTS '{$user->username}'@'localhost';\"",
                    "mysql -e \"FLUSH PRIVILEGES;\"",
                ];
            } elseif ($database->type === 'postgresql') {
                $commands = [
                    "sudo -u postgres dropuser \"{$user->username}\"",
                ];
            } else {
                throw new \InvalidArgumentException("Unsupported database type: {$database->type}");
            }
            
            foreach ($commands as $command) {
                $result = $this->ssh->execute($command);
                if ($result['exit_code'] !== 0 && !str_contains($result['output'], 'does not exist')) {
                    throw new \Exception("Failed to delete user: " . $result['output']);
                }
            }
            
            // Delete user record
            $user->delete();
            
            Log::info("Database user deleted", [
                'server_id' => $this->server->id,
                'database_id' => $database->id,
                'username' => $user->username,
            ]);
            
            return [
                'success' => true,
                'message' => 'Database user deleted successfully',
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to delete database user", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create database backup
     */
    public function createBackup(Database $database, string $backupName = null): array
    {
        try {
            $backupName = $backupName ?: $database->name . '_' . date('Y_m_d_H_i_s');
            $backupDir = '/var/backups/databases';
            
            // Ensure backup directory exists
            $this->ssh->execute("sudo mkdir -p {$backupDir}");
            
            if ($database->type === 'mysql') {
                $backupPath = "{$backupDir}/{$backupName}.sql";
                $command = "mysqldump \"{$database->name}\" | sudo tee {$backupPath} > /dev/null";
            } elseif ($database->type === 'postgresql') {
                $backupPath = "{$backupDir}/{$backupName}.sql";
                $command = "sudo -u postgres pg_dump \"{$database->name}\" | sudo tee {$backupPath} > /dev/null";
            } else {
                throw new \InvalidArgumentException("Unsupported database type: {$database->type}");
            }
            
            $result = $this->ssh->execute($command);
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to create backup: " . $result['output']);
            }
            
            // Compress backup
            $this->ssh->execute("sudo gzip {$backupPath}");
            $compressedPath = $backupPath . '.gz';
            
            Log::info("Database backup created", [
                'server_id' => $this->server->id,
                'database_id' => $database->id,
                'backup_path' => $compressedPath,
            ]);
            
            return [
                'success' => true,
                'backup_path' => $compressedPath,
                'message' => 'Database backup created successfully',
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to create database backup", [
                'database_id' => $database->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * List databases on server
     */
    public function listDatabases(string $type = 'mysql'): array
    {
        try {
            if ($type === 'mysql') {
                $command = "mysql -e \"SHOW DATABASES;\" | grep -v -E '^(Database|information_schema|performance_schema|mysql|sys)$'";
            } elseif ($type === 'postgresql') {
                $command = "sudo -u postgres psql -l | grep -E '^\\s+\\w' | awk '{print \$1}' | grep -v -E '^(template0|template1|postgres)$'";
            } else {
                throw new \InvalidArgumentException("Unsupported database type: {$type}");
            }
            
            $result = $this->ssh->execute($command);
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to list databases: " . $result['output']);
            }
            
            $databases = array_filter(explode("\n", trim($result['output'])));
            
            return [
                'success' => true,
                'databases' => $databases,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sanitize database name
     */
    private function sanitizeDatabaseName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    }

    /**
     * Sanitize username
     */
    private function sanitizeUsername(string $username): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $username);
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup(Database $database, string $backupPath): array
    {
        try {
            Log::info("Restoring database from backup", [
                'server_id' => $this->server->id,
                'database_id' => $database->id,
                'backup_path' => $backupPath,
            ]);

            // Check if backup file exists
            $checkFile = $this->ssh->execute("test -f {$backupPath} && echo 'exists'");
            if (trim($checkFile['output']) !== 'exists') {
                throw new \Exception("Backup file not found: {$backupPath}");
            }

            // Decompress if needed
            if (str_ends_with($backupPath, '.gz')) {
                $this->ssh->execute("gunzip -c {$backupPath} > /tmp/restore.sql");
                $sqlFile = '/tmp/restore.sql';
            } else {
                $sqlFile = $backupPath;
            }

            // Restore based on database type
            if ($database->type === 'mysql') {
                $command = "mysql {$database->name} < {$sqlFile}";
            } elseif ($database->type === 'postgresql') {
                $command = "sudo -u postgres psql {$database->name} < {$sqlFile}";
            } else {
                throw new \InvalidArgumentException("Unsupported database type: {$database->type}");
            }

            $result = $this->ssh->execute($command);

            // Clean up temp file
            if ($sqlFile === '/tmp/restore.sql') {
                $this->ssh->execute("rm /tmp/restore.sql");
            }

            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to restore backup: " . $result['output']);
            }

            Log::info("Database restored successfully", [
                'database_id' => $database->id,
                'backup_path' => $backupPath,
            ]);

            return [
                'success' => true,
                'message' => 'Database restored successfully',
            ];

        } catch (\Exception $e) {
            Log::error("Failed to restore database", [
                'database_id' => $database->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Setup automated backups
     */
    public function setupAutomatedBackups(Database $database, string $schedule = 'daily', int $retention = 7): array
    {
        try {
            Log::info("Setting up automated backups", [
                'database_id' => $database->id,
                'schedule' => $schedule,
                'retention' => $retention,
            ]);

            // Create backup script
            $backupScript = <<<SCRIPT
#!/bin/bash
BACKUP_DIR="/var/backups/databases"
DB_NAME="{$database->name}"
DB_TYPE="{$database->type}"
DATE=\$(date +%Y_%m_%d_%H_%M_%S)
BACKUP_FILE="\${BACKUP_DIR}/\${DB_NAME}_\${DATE}.sql"
RETENTION_DAYS={$retention}

# Create backup directory
mkdir -p \$BACKUP_DIR

# Perform backup based on database type
if [ "\$DB_TYPE" = "mysql" ]; then
    mysqldump "\$DB_NAME" | gzip > "\${BACKUP_FILE}.gz"
elif [ "\$DB_TYPE" = "postgresql" ]; then
    sudo -u postgres pg_dump "\$DB_NAME" | gzip > "\${BACKUP_FILE}.gz"
fi

# Remove old backups
find \$BACKUP_DIR -name "\${DB_NAME}_*.sql.gz" -mtime +\$RETENTION_DAYS -delete

echo "Backup completed: \${BACKUP_FILE}.gz"
SCRIPT;

            $scriptPath = "/usr/local/bin/backup-{$database->name}.sh";
            $this->ssh->execute("cat > {$scriptPath} << 'EOF'\n{$backupScript}\nEOF");
            $this->ssh->execute("chmod +x {$scriptPath}");

            // Add to crontab
            $cronSchedule = match($schedule) {
                'hourly' => '0 * * * *',
                'daily' => '0 2 * * *',
                'weekly' => '0 2 * * 0',
                'monthly' => '0 2 1 * *',
                default => '0 2 * * *'
            };

            $cronEntry = "{$cronSchedule} {$scriptPath} >> /var/log/database-backups.log 2>&1";
            
            // Check if cron entry already exists
            $checkCron = $this->ssh->execute('crontab -l 2>/dev/null');
            
            if (strpos($checkCron['output'], $scriptPath) === false) {
                $this->ssh->execute("(crontab -l 2>/dev/null; echo '{$cronEntry}') | crontab -");
            }

            Log::info("Automated backups configured", [
                'database_id' => $database->id,
                'schedule' => $schedule,
            ]);

            return [
                'success' => true,
                'script_path' => $scriptPath,
                'schedule' => $schedule,
                'retention_days' => $retention,
                'message' => 'Automated backups configured successfully',
            ];

        } catch (\Exception $e) {
            Log::error("Failed to setup automated backups", [
                'database_id' => $database->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Configure master-slave replication
     */
    public function setupReplication(Database $primaryDb, Server $replicaServer): array
    {
        try {
            Log::info("Setting up database replication", [
                'primary_db' => $primaryDb->id,
                'replica_server' => $replicaServer->id,
            ]);

            if ($primaryDb->type !== 'mysql') {
                throw new \Exception("Replication currently only supported for MySQL");
            }

            // Configure master server
            $masterConfig = <<<CONFIG
[mysqld]
server-id=1
log-bin=mysql-bin
binlog-do-db={$primaryDb->name}
CONFIG;

            $this->ssh->execute("cat >> /etc/mysql/mysql.conf.d/mysqld.cnf << 'EOF'\n{$masterConfig}\nEOF");
            $this->ssh->execute("systemctl restart mysql");

            // Create replication user
            $replPassword = Str::random(32);
            $this->ssh->execute("mysql -e \"CREATE USER 'repl'@'%' IDENTIFIED BY '{$replPassword}';\"");
            $this->ssh->execute("mysql -e \"GRANT REPLICATION SLAVE ON *.* TO 'repl'@'%';\"");
            $this->ssh->execute("mysql -e \"FLUSH PRIVILEGES;\"");

            // Get master status
            $masterStatus = $this->ssh->execute("mysql -e 'SHOW MASTER STATUS\G'");

            // Configure slave server
            $replicaSsh = new SSHConnectionService($replicaServer);
            
            $slaveConfig = <<<CONFIG
[mysqld]
server-id=2
CONFIG;

            $replicaSsh->execute("cat >> /etc/mysql/mysql.conf.d/mysqld.cnf << 'EOF'\n{$slaveConfig}\nEOF");
            $replicaSsh->execute("systemctl restart mysql");

            // Configure replication on slave
            $primaryIp = $this->server->ip;
            $replicaSsh->execute("mysql -e \"CHANGE MASTER TO MASTER_HOST='{$primaryIp}', MASTER_USER='repl', MASTER_PASSWORD='{$replPassword}', MASTER_LOG_FILE='mysql-bin.000001', MASTER_LOG_POS=0;\"");
            $replicaSsh->execute("mysql -e \"START SLAVE;\"");

            Log::info("Database replication configured", [
                'primary_db' => $primaryDb->id,
                'replica_server' => $replicaServer->id,
            ]);

            return [
                'success' => true,
                'message' => 'Replication configured successfully',
                'replication_user' => 'repl',
            ];

        } catch (\Exception $e) {
            Log::error("Failed to setup replication", [
                'primary_db' => $primaryDb->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get database size
     */
    public function getDatabaseSize(Database $database): array
    {
        try {
            if ($database->type === 'mysql') {
                $command = "mysql -e \"SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = '{$database->name}' GROUP BY table_schema;\"";
            } elseif ($database->type === 'postgresql') {
                $command = "sudo -u postgres psql -c \"SELECT pg_size_pretty(pg_database_size('{$database->name}'));\"";
            } else {
                throw new \InvalidArgumentException("Unsupported database type");
            }

            $result = $this->ssh->execute($command);

            return [
                'success' => true,
                'size' => trim($result['output']),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize database tables
     */
    public function optimizeTables(Database $database): array
    {
        try {
            Log::info("Optimizing database tables", ['database_id' => $database->id]);

            if ($database->type === 'mysql') {
                $command = "mysqlcheck -o {$database->name}";
            } elseif ($database->type === 'postgresql') {
                $command = "sudo -u postgres vacuumdb --analyze {$database->name}";
            } else {
                throw new \InvalidArgumentException("Unsupported database type");
            }

            $result = $this->ssh->execute($command);

            Log::info("Database tables optimized", ['database_id' => $database->id]);

            return [
                'success' => $result['exit_code'] === 0,
                'output' => $result['output'],
                'message' => 'Database tables optimized successfully',
            ];

        } catch (\Exception $e) {
            Log::error("Failed to optimize database", [
                'database_id' => $database->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}