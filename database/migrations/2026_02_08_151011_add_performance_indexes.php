<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Servers table indexes
        Schema::table('servers', function (Blueprint $table) {
            $table->index(['team_id', 'status'], 'idx_servers_team_status');
            $table->index('last_ping_at', 'idx_servers_last_ping');
            $table->index('provision_status', 'idx_servers_provision_status');
        });
        
        // Sites table indexes
        Schema::table('sites', function (Blueprint $table) {
            $table->index(['server_id', 'status'], 'idx_sites_server_status');
            $table->index('team_id', 'idx_sites_team');
            $table->index('status', 'idx_sites_status');
        });
        
        // Deployments table indexes
        Schema::table('deployments', function (Blueprint $table) {
            $table->index(['site_id', 'created_at'], 'idx_deployments_site_created');
            $table->index('status', 'idx_deployments_status');
            $table->index('user_id', 'idx_deployments_user');
        });
        
        // Server metrics table indexes
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->index(['server_id', 'created_at'], 'idx_metrics_server_created');
        });
        
        // Backup configurations table indexes
        Schema::table('backup_configurations', function (Blueprint $table) {
            $table->index(['team_id', 'status'], 'idx_backup_configs_team_status');
        });
        
        // Backup jobs table indexes
        Schema::table('backup_jobs', function (Blueprint $table) {
            $table->index(['backup_configuration_id', 'status'], 'idx_backup_jobs_config_status');
            $table->index(['status', 'created_at'], 'idx_backup_jobs_status_created');
        });
        
        // GitHub repositories table indexes
        Schema::table('github_repositories', function (Blueprint $table) {
            $table->index('user_id', 'idx_github_repos_user');
        });
        
        // Notifications table - índices já existem na migration original
        // Removido para evitar duplicação
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop servers indexes
        Schema::table('servers', function (Blueprint $table) {
            $table->dropIndex('idx_servers_team_status');
            $table->dropIndex('idx_servers_last_ping');
            $table->dropIndex('idx_servers_provision_status');
        });
        
        // Drop sites indexes
        Schema::table('sites', function (Blueprint $table) {
            $table->dropIndex('idx_sites_server_status');
            $table->dropIndex('idx_sites_team');
            $table->dropIndex('idx_sites_status');
        });
        
        // Drop deployments indexes
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropIndex('idx_deployments_site_created');
            $table->dropIndex('idx_deployments_status');
            $table->dropIndex('idx_deployments_user');
        });
        
        // Drop server metrics indexes
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->dropIndex('idx_metrics_server_created');
        });
        
        // Drop backup configurations indexes
        Schema::table('backup_configurations', function (Blueprint $table) {
            $table->dropIndex('idx_backup_configs_team_status');
        });
        
        // Drop backup jobs indexes
        Schema::table('backup_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_backup_jobs_config_status');
            $table->dropIndex('idx_backup_jobs_status_created');
        });
        
        // Drop GitHub repositories indexes
        Schema::table('github_repositories', function (Blueprint $table) {
            $table->dropIndex('idx_github_repos_user');
        });
    }
};
