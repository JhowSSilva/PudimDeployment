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
        // Performance Metrics table
        if (!Schema::hasTable('performance_metrics')) {
            Schema::create('performance_metrics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // response_time, database_query, etc.
                $table->json('metrics');
                $table->timestamps();
                
                $table->index(['site_id', 'type', 'created_at']);
            });
        }

        // Usage Metrics table for billing
        if (!Schema::hasTable('usage_metrics')) {
            Schema::create('usage_metrics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained()->cascadeOnDelete();
                $table->decimal('cpu_usage', 5, 2);
                $table->integer('memory_used_mb');
                $table->integer('disk_used_gb');
                $table->bigInteger('bandwidth_in')->default(0);
                $table->bigInteger('bandwidth_out')->default(0);
                $table->timestamp('recorded_at');
                $table->timestamps();
                
                $table->index(['server_id', 'recorded_at']);
            });
        }

        // Invoices table
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();
                $table->string('invoice_number')->unique();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('subtotal', 10, 2);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->string('currency', 3)->default('USD');
                $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
                $table->json('line_items')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
                
                $table->index(['team_id', 'status']);
            });
        }

        // Subscriptions table
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();
                $table->string('plan'); // basic, professional, enterprise
                $table->string('billing_cycle'); // monthly, yearly
                $table->enum('status', ['active', 'cancelled', 'expired', 'suspended'])->default('active');
                $table->timestamp('started_at');
                $table->timestamp('next_billing_date')->nullable();
                $table->boolean('downgrade_at_end_of_period')->default(false);
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
                
                $table->index(['team_id', 'status']);
            });
        }

        // Firewall Rules table
        if (!Schema::hasTable('firewall_rules')) {
            Schema::create('firewall_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained()->cascadeOnDelete();
                $table->integer('port');
                $table->string('protocol')->default('tcp');
                $table->string('source')->nullable(); // IP or CIDR
                $table->string('action')->default('allow'); // allow, deny
                $table->string('comment')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['server_id', 'is_active']);
            });
        }

        // Security Threats table
        if (!Schema::hasTable('security_threats')) {
            Schema::create('security_threats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // brute_force, malware, rootkit, etc.
                $table->string('severity'); // low, medium, high, critical
                $table->text('description');
                $table->json('details')->nullable();
                $table->string('status')->default('detected'); // detected, investigating, resolved
                $table->timestamp('detected_at');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                
                $table->index(['server_id', 'severity', 'status']);
            });
        }

        // Blocked IPs table
        if (!Schema::hasTable('blocked_ips')) {
            Schema::create('blocked_ips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained()->cascadeOnDelete();
                $table->string('ip_address');
                $table->string('reason')->nullable();
                $table->string('blocked_by')->default('manual'); // manual, fail2ban, auto
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                
                $table->index(['server_id', 'ip_address']);
            });
        }

        // Add new columns to existing tables
        if (Schema::hasTable('sites')) {
            Schema::table('sites', function (Blueprint $table) {
                if (!Schema::hasColumn('sites', 'auto_migrate')) {
                    $table->boolean('auto_migrate')->default(true);
                }
                if (!Schema::hasColumn('sites', 'maintenance_mode')) {
                    $table->boolean('maintenance_mode')->default(false);
                }
                if (!Schema::hasColumn('sites', 'framework')) {
                    $table->string('framework')->nullable(); // laravel, wordpress, etc.
                }
            });
        }

        if (Schema::hasTable('servers')) {
            Schema::table('servers', function (Blueprint $table) {
                if (!Schema::hasColumn('servers', 'custom_hourly_rate')) {
                    $table->decimal('custom_hourly_rate', 8, 4)->nullable();
                }
                if (!Schema::hasColumn('servers', 'disk_size')) {
                    $table->integer('disk_size')->nullable(); // in GB
                }
                if (!Schema::hasColumn('servers', 'firewall_enabled')) {
                    $table->boolean('firewall_enabled')->default(false);
                }
                if (!Schema::hasColumn('servers', 'fail2ban_enabled')) {
                    $table->boolean('fail2ban_enabled')->default(false);
                }
            });
        }

        if (Schema::hasTable('deployments')) {
            Schema::table('deployments', function (Blueprint $table) {
                if (!Schema::hasColumn('deployments', 'commit_hash')) {
                    $table->string('commit_hash')->nullable();
                }
                if (!Schema::hasColumn('deployments', 'branch')) {
                    $table->string('branch')->default('main');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('security_threats');
        Schema::dropIfExists('firewall_rules');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('usage_metrics');
        Schema::dropIfExists('performance_metrics');

        // Remove added columns
        if (Schema::hasTable('sites')) {
            Schema::table('sites', function (Blueprint $table) {
                $table->dropColumn(['auto_migrate', 'maintenance_mode', 'framework']);
            });
        }

        if (Schema::hasTable('servers')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->dropColumn(['custom_hourly_rate', 'disk_size', 'firewall_enabled', 'fail2ban_enabled']);
            });
        }

        if (Schema::hasTable('deployments')) {
            Schema::table('deployments', function (Blueprint $table) {
                $table->dropColumn(['commit_hash', 'branch']);
            });
        }
    }
};
