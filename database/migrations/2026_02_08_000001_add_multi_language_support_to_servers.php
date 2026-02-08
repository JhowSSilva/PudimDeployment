<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            // Add programming language support (expand existing fields)
            $table->enum('programming_language', [
                'php', 'nodejs', 'python', 'ruby', 'go', 
                'java', 'dotnet', 'rust', 'elixir', 'static'
            ])->default('php')->after('cache_service');
            
            $table->string('language_version', 20)->nullable()->after('programming_language');
            
            // Improve webserver field (expandir enum existente se houver)
            if (Schema::hasColumn('servers', 'webserver')) {
                $table->string('webserver_version', 20)->nullable()->after('webserver');
            } else {
                $table->enum('webserver', [
                    'nginx', 'apache', 'openlitespeed', 'caddy', 'none'
                ])->nullable()->after('programming_language');
                $table->string('webserver_version', 20)->nullable()->after('webserver');
            }
            
            // Improve database fields
            $table->string('database_version_new', 20)->nullable()->after('database_version');
            
            // Installed tools (JSON)
            $table->json('installed_tools')->nullable()->after('installed_software');
            
            // Process manager
            $table->string('process_manager', 50)->nullable()->after('installed_tools');
            
            // Region and cost info (if not exists from AWS fields)
            if (!Schema::hasColumn('servers', 'region')) {
                $table->string('region', 100)->nullable()->after('process_manager');
            }
            $table->string('size_slug', 100)->nullable()->after('region');
            if (!Schema::hasColumn('servers', 'monthly_cost')) {
                $table->decimal('monthly_cost', 10, 2)->nullable()->after('size_slug');
            }
            
            // IPv6 support
            $table->string('ipv6_address', 45)->nullable()->after('ip_address');
            
            // Provisioning timestamp (if not exists)
            if (!Schema::hasColumn('servers', 'provisioned_at')) {
                $table->timestamp('provisioned_at')->nullable()->after('provision_completed_at');
            }
        });
        
        // New table for installation templates
        Schema::create('server_installation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('language');
            $table->string('version');
            $table->text('description')->nullable();
            $table->json('dependencies'); // Required packages
            $table->text('install_script'); // Installation script
            $table->text('configure_script')->nullable(); // Configuration script
            $table->json('default_config')->nullable(); // Default configurations
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['language', 'version']);
            $table->index(['language', 'is_active']);
        });
        
        // New table for SSH commands history (expand existing if exists)
        if (!Schema::hasTable('server_ssh_commands')) {
            Schema::create('server_ssh_commands', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('command');
                $table->text('output')->nullable();
                $table->integer('exit_code')->nullable();
                $table->timestamp('executed_at')->useCurrent();
                
                $table->index(['server_id', 'executed_at']);
            });
        }
        
        // New table for server metrics (expand existing if needed)
        if (!Schema::hasTable('server_metrics_enhanced')) {
            Schema::create('server_metrics_enhanced', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained()->onDelete('cascade');
                $table->decimal('cpu_usage', 5, 2)->nullable();
                $table->decimal('memory_usage', 5, 2)->nullable();
                $table->decimal('disk_usage', 5, 2)->nullable();
                $table->bigInteger('network_in')->nullable();
                $table->bigInteger('network_out')->nullable();
                $table->string('load_average', 50)->nullable();
                $table->json('process_list')->nullable();
                $table->timestamp('recorded_at')->useCurrent();
                
                $table->index(['server_id', 'recorded_at']);
            });
        }
        
        // New table for firewall rules (if not exists)
        if (!Schema::hasTable('server_firewall_rules')) {
            Schema::create('server_firewall_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained()->onDelete('cascade');
                $table->string('name')->nullable();
                $table->integer('port');
                $table->enum('protocol', ['tcp', 'udp', 'both'])->default('tcp');
                $table->string('source')->default('0.0.0.0/0');
                $table->enum('action', ['allow', 'deny'])->default('allow');
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->index(['server_id', 'is_active']);
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('server_firewall_rules');
        Schema::dropIfExists('server_metrics_enhanced');
        Schema::dropIfExists('server_ssh_commands');
        Schema::dropIfExists('server_installation_templates');
        
        Schema::table('servers', function (Blueprint $table) {
            $columns = [
                'programming_language',
                'language_version',
                'webserver_version',
                'database_version_new',
                'installed_tools',
                'process_manager',
                'size_slug',
                'ipv6_address'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('servers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};