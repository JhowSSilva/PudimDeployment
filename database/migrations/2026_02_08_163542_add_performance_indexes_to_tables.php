<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Deployments performance indexes
        Schema::table('deployments', function (Blueprint $table) {
            if (!$this->indexExists('deployments', 'idx_deployments_status_time')) {
                $table->index(['status', 'created_at'], 'idx_deployments_status_time');
            }
            if (!$this->indexExists('deployments', 'idx_deployments_user_time')) {
                $table->index(['user_id', 'created_at'], 'idx_deployments_user_time');
            }
            if (!$this->indexExists('deployments', 'idx_deployments_trigger')) {
                $table->index(['trigger'], 'idx_deployments_trigger');
            }
        });

        // Sites performance indexes
        Schema::table('sites', function (Blueprint $table) {
            if (!$this->indexExists('sites', 'idx_sites_domain')) {
                $table->index(['domain'], 'idx_sites_domain');
            }
            if (!$this->indexExists('sites', 'idx_sites_created')) {
                $table->index(['created_at'], 'idx_sites_created');
            }
            if (!$this->indexExists('sites', 'idx_sites_active')) {
                $table->index(['is_active'], 'idx_sites_active');
            }
        });

        // Servers performance indexes
        Schema::table('servers', function (Blueprint $table) {
            if (!$this->indexExists('servers', 'idx_servers_ip')) {
                $table->index(['ip_address'], 'idx_servers_ip');
            }
            if (!$this->indexExists('servers', 'idx_servers_os')) {
                $table->index(['os_type', 'os_version'], 'idx_servers_os');
            }
            if (!$this->indexExists('servers', 'idx_servers_last_ping')) {
                $table->index(['last_ping_at'], 'idx_servers_last_ping');
            }
        });

        // GitHub repositories performance indexes (if table exists)
        if (Schema::hasTable('github_repositories')) {
            Schema::table('github_repositories', function (Blueprint $table) {
                if (!$this->indexExists('github_repositories', 'idx_github_repos_user_updated')) {
                    $table->index(['user_id', 'updated_at'], 'idx_github_repos_user_updated');
                }
                if (!$this->indexExists('github_repositories', 'idx_github_repos_full_name')) {
                    $table->index(['full_name'], 'idx_github_repos_full_name');
                }
            });
        }

        // Backups performance indexes (if table exists)
        if (Schema::hasTable('backups')) {
            Schema::table('backups', function (Blueprint $table) {
                if (!$this->indexExists('backups', 'idx_backups_site_status_created')) {
                    $table->index(['site_id', 'status', 'created_at'], 'idx_backups_site_status_created');
                }
                if (!$this->indexExists('backups', 'idx_backups_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_backups_status_created');
                }
            });
        }

        // SSL certificates performance indexes (if table exists)
        if (Schema::hasTable('ssl_certificates')) {
            Schema::table('ssl_certificates', function (Blueprint $table) {
                if (!$this->indexExists('ssl_certificates', 'idx_ssl_site_expires')) {
                    $table->index(['site_id', 'expires_at'], 'idx_ssl_site_expires');
                }
                if (!$this->indexExists('ssl_certificates', 'idx_ssl_status')) {
                    $table->index(['status'], 'idx_ssl_status');
                }
            });
        }

        // Cron jobs performance indexes (if table exists)
        if (Schema::hasTable('cron_jobs')) {
            Schema::table('cron_jobs', function (Blueprint $table) {
                if (!$this->indexExists('cron_jobs', 'idx_cron_site_status')) {
                    $table->index(['site_id', 'status'], 'idx_cron_site_status');
                }
                if (!$this->indexExists('cron_jobs', 'idx_cron_next_run')) {
                    $table->index(['next_run_at'], 'idx_cron_next_run');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deployments indexes
        Schema::table('deployments', function (Blueprint $table) {
            if ($this->indexExists('deployments', 'idx_deployments_status_time')) {
                $table->dropIndex('idx_deployments_status_time');
            }
            if ($this->indexExists('deployments', 'idx_deployments_user_time')) {
                $table->dropIndex('idx_deployments_user_time');
            }
            if ($this->indexExists('deployments', 'idx_deployments_trigger')) {
                $table->dropIndex('idx_deployments_trigger');
            }
        });

        // Sites indexes
        Schema::table('sites', function (Blueprint $table) {
            if ($this->indexExists('sites', 'idx_sites_domain')) {
                $table->dropIndex('idx_sites_domain');
            }
            if ($this->indexExists('sites', 'idx_sites_created')) {
                $table->dropIndex('idx_sites_created');
            }
            if ($this->indexExists('sites', 'idx_sites_active')) {
                $table->dropIndex('idx_sites_active');
            }
        });

        // Servers indexes
        Schema::table('servers', function (Blueprint $table) {
            if ($this->indexExists('servers', 'idx_servers_ip')) {
                $table->dropIndex('idx_servers_ip');
            }
            if ($this->indexExists('servers', 'idx_servers_os')) {
                $table->dropIndex('idx_servers_os');
            }
            if ($this->indexExists('servers', 'idx_servers_last_ping')) {
                $table->dropIndex('idx_servers_last_ping');
            }
        });

        // GitHub repositories indexes
        if (Schema::hasTable('github_repositories')) {
            Schema::table('github_repositories', function (Blueprint $table) {
                if ($this->indexExists('github_repositories', 'idx_github_repos_user_updated')) {
                    $table->dropIndex('idx_github_repos_user_updated');
                }
                if ($this->indexExists('github_repositories', 'idx_github_repos_full_name')) {
                    $table->dropIndex('idx_github_repos_full_name');
                }
            });
        }

        // Backups indexes
        if (Schema::hasTable('backups')) {
            Schema::table('backups', function (Blueprint $table) {
                if ($this->indexExists('backups', 'idx_backups_site_status_created')) {
                    $table->dropIndex('idx_backups_site_status_created');
                }
                if ($this->indexExists('backups', 'idx_backups_status_created')) {
                    $table->dropIndex('idx_backups_status_created');
                }
            });
        }

        // SSL certificates indexes
        if (Schema::hasTable('ssl_certificates')) {
            Schema::table('ssl_certificates', function (Blueprint $table) {
                if ($this->indexExists('ssl_certificates', 'idx_ssl_site_expires')) {
                    $table->dropIndex('idx_ssl_site_expires');
                }
                if ($this->indexExists('ssl_certificates', 'idx_ssl_status')) {
                    $table->dropIndex('idx_ssl_status');
                }
            });
        }

        // Cron jobs indexes
        if (Schema::hasTable('cron_jobs')) {
            Schema::table('cron_jobs', function (Blueprint $table) {
                if ($this->indexExists('cron_jobs', 'idx_cron_site_status')) {
                    $table->dropIndex('idx_cron_site_status');
                }
                if ($this->indexExists('cron_jobs', 'idx_cron_next_run')) {
                    $table->dropIndex('idx_cron_next_run');
                }
            });
        }
    }

    /**
     * Check if index exists (PostgreSQL compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $schema = $connection->getConfig('schema') ?? 'public';
        
        if ($connection->getDriverName() === 'pgsql') {
            $result = DB::selectOne(
                "SELECT COUNT(*) as count FROM pg_indexes WHERE schemaname = ? AND indexname = ?",
                [$schema, $indexName]
            );
            return $result->count > 0;
        }
        
        // MySQL fallback
        $result = DB::selectOne(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            [$indexName]
        );
        return $result !== null;
    }
};
