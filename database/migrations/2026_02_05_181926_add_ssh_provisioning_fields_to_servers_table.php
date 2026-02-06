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
        Schema::table('servers', function (Blueprint $table) {
            // SSH provisioning fields
            $table->text('ssh_key_private')->nullable()->after('private_key');
            $table->text('ssh_key_public')->nullable()->after('ssh_key_private');
            $table->string('deploy_user')->default('admin_agile')->after('ssh_key_public');
            
            // Stack configuration (if not exists from AWS)
            if (!Schema::hasColumn('servers', 'webserver')) {
                $table->string('webserver')->nullable()->after('deploy_user');
            }
            if (!Schema::hasColumn('servers', 'php_versions')) {
                $table->json('php_versions')->nullable()->after('webserver');
            }
            if (!Schema::hasColumn('servers', 'database_type')) {
                $table->string('database_type')->nullable()->after('php_versions');
                $table->string('database_version')->nullable()->after('database_type');
            }
            if (!Schema::hasColumn('servers', 'cache_service')) {
                $table->string('cache_service')->nullable()->after('database_version');
            }
            if (!Schema::hasColumn('servers', 'nodejs_version')) {
                $table->string('nodejs_version')->nullable()->after('cache_service');
            }
            if (!Schema::hasColumn('servers', 'installed_software')) {
                $table->json('installed_software')->nullable()->after('nodejs_version');
            }
            
            // Provisioning tracking
            if (!Schema::hasColumn('servers', 'provision_script')) {
                $table->text('provision_script')->nullable()->after('installed_software');
            }
            if (!Schema::hasColumn('servers', 'provision_started_at')) {
                $table->timestamp('provision_started_at')->nullable()->after('provision_script');
            }
            if (!Schema::hasColumn('servers', 'provision_completed_at')) {
                $table->timestamp('provision_completed_at')->nullable()->after('provision_started_at');
            }
            
            // Server type (if not exists)
            if (!Schema::hasColumn('servers', 'type')) {
                $table->enum('type', ['server', 'database', 'cache', 'load_balancer'])->default('server')->after('provision_completed_at');
            }
            
            // OS (if not exists from os_type/os_version)
            if (!Schema::hasColumn('servers', 'os')) {
                $table->enum('os', ['ubuntu-20.04', 'ubuntu-22.04', 'ubuntu-24.04'])->nullable()->after('type');
            }
            
            // System info (if not exists)
            if (!Schema::hasColumn('servers', 'kernel_version')) {
                $table->string('kernel_version')->nullable()->after('os');
            }
            if (!Schema::hasColumn('servers', 'cpu_cores')) {
                $table->integer('cpu_cores')->nullable()->after('kernel_version');
            }
            if (!Schema::hasColumn('servers', 'ram_mb')) {
                $table->integer('ram_mb')->nullable()->after('cpu_cores');
            }
            if (!Schema::hasColumn('servers', 'disk_gb')) {
                $table->integer('disk_gb')->nullable()->after('ram_mb');
            }
            if (!Schema::hasColumn('servers', 'system_info')) {
                $table->json('system_info')->nullable()->after('disk_gb');
            }
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $columns = [
                'ssh_key_private',
                'ssh_key_public',
                'deploy_user',
                'provision_started_at',
                'provision_completed_at',
                'kernel_version',
                'cpu_cores',
                'ram_mb',
                'disk_gb',
                'system_info'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('servers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
