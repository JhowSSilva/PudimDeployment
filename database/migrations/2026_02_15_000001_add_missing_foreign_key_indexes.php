<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safety-net migration: ensures commonly queried foreign key columns have indexes.
 *
 * As of this writing every index below is already created by the original
 * migration files (foreignId()->constrained() auto-creates an index in MySQL,
 * and explicit composite indexes were added too). The conditional checks here
 * guard against any future schema drift.
 */
return new class extends Migration
{
    public function up(): void
    {
        // deployments.site_id — already indexed via foreignId()->constrained()
        // and composite index ['site_id', 'status', 'created_at']
        if (!$this->hasIndex('deployments', 'deployments_site_id_foreign') &&
            !$this->hasIndex('deployments', 'deployments_site_id_index')) {
            Schema::table('deployments', function (Blueprint $table) {
                $table->index('site_id');
            });
        }

        // server_metrics.server_id — already indexed via foreignId()->constrained()
        // and composite index ['server_id', 'created_at']
        if (!$this->hasIndex('server_metrics', 'server_metrics_server_id_foreign') &&
            !$this->hasIndex('server_metrics', 'server_metrics_server_id_index')) {
            Schema::table('server_metrics', function (Blueprint $table) {
                $table->index('server_id');
            });
        }

        // ssl_certificates.site_id — already indexed via foreignId()->constrained()
        // and composite index ['site_id', 'status']
        if (!$this->hasIndex('ssl_certificates', 'ssl_certificates_site_id_foreign') &&
            !$this->hasIndex('ssl_certificates', 'ssl_certificates_site_id_index')) {
            Schema::table('ssl_certificates', function (Blueprint $table) {
                $table->index('site_id');
            });
        }

        // comments.(commentable_type, commentable_id) — already has explicit morph index
        if (!$this->hasIndex('comments', 'comments_commentable_type_commentable_id_index')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->index(['commentable_type', 'commentable_id']);
            });
        }

        // activity_logs.team_id — already indexed via foreignId()->constrained()
        // and composite index ['team_id', 'created_at']
        if (!$this->hasIndex('activity_logs', 'activity_logs_team_id_foreign') &&
            !$this->hasIndex('activity_logs', 'activity_logs_team_id_index')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('team_id');
            });
        }

        // queue_workers.server_id — already indexed via foreignId()->constrained()
        // and composite index ['server_id', 'status']
        if (!$this->hasIndex('queue_workers', 'queue_workers_server_id_foreign') &&
            !$this->hasIndex('queue_workers', 'queue_workers_server_id_index')) {
            Schema::table('queue_workers', function (Blueprint $table) {
                $table->index('server_id');
            });
        }
    }

    public function down(): void
    {
        // Only drop indexes this migration actually created (none expected).
        // No-op since the original migrations own these indexes.
    }

    /**
     * Check whether the given table already has an index with the given name.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        // PostgreSQL: check pg_indexes
        if ($driver === 'pgsql') {
            $row = $connection->selectOne('select indexname from pg_indexes where tablename = ? and indexname = ? limit 1', [$table, $indexName]);
            return !empty($row);
        }

        // MySQL: check information_schema.statistics
        if ($driver === 'mysql') {
            $row = $connection->selectOne('select index_name from information_schema.statistics where table_schema = database() and table_name = ? and index_name = ? limit 1', [$table, $indexName]);
            return !empty($row);
        }

        // Fallback: try Doctrine Schema Manager if available (requires doctrine/dbal)
        if (method_exists($connection, 'getDoctrineSchemaManager')) {
            $sm = $connection->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);

            return isset($indexes[$indexName]);
        }

        // Last resort: assume index does not exist
        return false;
    }
};
