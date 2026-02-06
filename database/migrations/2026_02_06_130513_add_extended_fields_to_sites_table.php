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
        Schema::table('sites', function (Blueprint $table) {
            // Application Type
            $table->string('application_type')->default('laravel')->after('status');
            $table->string('custom_type')->nullable()->after('application_type');
            
            // Aliases/Subdomains (domain_aliases já existe em migration anterior)
            // root_directory como complemento ao document_root
            $table->string('root_directory')->default('/public')->after('document_root');
            
            // PHP Configuration (php_version já existe)
            $table->boolean('dedicated_php_pool')->default(false);
            $table->string('php_memory_limit')->default('256M')->nullable();
            $table->string('php_upload_max_filesize')->default('64M')->nullable();
            $table->string('php_post_max_size')->default('64M')->nullable();
            $table->integer('php_max_execution_time')->default(60)->nullable();
            
            // Node.js Configuration
            $table->string('node_version')->nullable();
            $table->string('package_manager')->default('npm')->nullable();
            $table->integer('node_port')->nullable();
            $table->string('node_start_command')->nullable();
            $table->string('process_manager')->default('pm2')->nullable();
            // environment_variables já existe como env_variables
            
            // Database Configuration
            $table->boolean('auto_create_database')->default(false);
            $table->unsignedBigInteger('linked_database_id')->nullable();
            $table->foreign('linked_database_id')->references('id')->on('databases')->onDelete('set null');
            
            // Web Server
            $table->string('web_server')->default('nginx');
            $table->string('nginx_template')->default('laravel');
            
            // SSL Configuration (ssl_type, ssl_enabled, force_https já existem em migrations anteriores)
            $table->boolean('auto_ssl')->default(true);
            
            // Git Repository (git_repository, git_branch já existem)
            $table->string('git_provider')->nullable(); // github, gitlab, bitbucket
            $table->boolean('auto_deploy')->default(false);
            $table->boolean('has_staging')->default(false);
            
            // Backup & CDN
            $table->boolean('daily_backup')->default(false);
            $table->boolean('cdn_enabled')->default(false);
            $table->string('cdn_provider')->nullable();
            
            // Additional metadata
            $table->json('firewall_rules')->nullable();
            $table->timestamp('last_deployed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['linked_database_id']);
            $table->dropColumn([
                'application_type',
                'custom_type',
                'root_directory',
                'dedicated_php_pool',
                'php_memory_limit',
                'php_upload_max_filesize',
                'php_post_max_size',
                'php_max_execution_time',
                'node_version',
                'package_manager',
                'node_port',
                'node_start_command',
                'process_manager',
                'auto_create_database',
                'linked_database_id',
                'web_server',
                'nginx_template',
                'auto_ssl',
                'git_provider',
                'auto_deploy',
                'has_staging',
                'daily_backup',
                'cdn_enabled',
                'cdn_provider',
                'firewall_rules',
                'last_deployed_at',
            ]);
        });
    }
};
