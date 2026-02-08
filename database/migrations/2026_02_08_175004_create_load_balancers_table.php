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
        Schema::create('load_balancers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('server_pool_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Load Balancer Configuration
            $table->string('ip_address')->nullable();
            $table->integer('port')->default(80);
            $table->enum('protocol', ['http', 'https', 'tcp', 'udp'])->default('http');
            
            // Algorithm: round_robin, least_connections, ip_hash, weighted
            $table->enum('algorithm', ['round_robin', 'least_connections', 'ip_hash', 'weighted'])->default('round_robin');
            
            // SSL Configuration
            $table->boolean('ssl_enabled')->default(false);
            $table->text('ssl_certificate')->nullable();
            $table->text('ssl_private_key')->nullable();
            
            // Health Check Configuration
            $table->boolean('health_check_enabled')->default(true);
            $table->string('health_check_path')->default('/');
            $table->integer('health_check_interval')->default(30); // seconds
            $table->integer('health_check_timeout')->default(5); // seconds
            $table->integer('healthy_threshold')->default(2); // consecutive successes
            $table->integer('unhealthy_threshold')->default(3); // consecutive failures
            
            // Session Persistence
            $table->boolean('sticky_sessions')->default(false);
            $table->integer('session_ttl')->default(3600); // seconds
            
            // Traffic Configuration
            $table->json('rules')->nullable(); // Custom routing rules
            $table->json('headers')->nullable(); // Custom headers to add
            
            // Monitoring
            $table->bigInteger('total_requests')->default(0);
            $table->bigInteger('failed_requests')->default(0);
            $table->timestamp('last_health_check_at')->nullable();
            
            // Status
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'status']);
            $table->index('server_pool_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_balancers');
    }
};
