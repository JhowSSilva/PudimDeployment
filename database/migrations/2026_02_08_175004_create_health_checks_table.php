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
        Schema::create('health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('load_balancer_id')->nullable();
            
            // Check Configuration
            $table->enum('type', ['http', 'https', 'tcp', 'ping'])->default('http');
            $table->string('endpoint')->default('/health'); // URL path or IP:port
            $table->integer('port')->default(80);
            $table->integer('timeout')->default(5); // seconds
            $table->string('expected_status')->default('200'); // HTTP status or 'up'
            $table->text('expected_body')->nullable(); // Optional body match
            
            // Check Results
            $table->enum('status', ['healthy', 'unhealthy', 'unknown'])->default('unknown');
            $table->integer('response_time')->nullable(); // milliseconds
            $table->integer('consecutive_successes')->default(0);
            $table->integer('consecutive_failures')->default(0);
            $table->text('last_error')->nullable();
            
            // Statistics
            $table->integer('total_checks')->default(0);
            $table->integer('successful_checks')->default(0);
            $table->integer('failed_checks')->default(0);
            $table->decimal('uptime_percentage', 5, 2)->default(100);
            
            // Timing
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->timestamp('unhealthy_since')->nullable();
            
            $table->timestamps();
            
            $table->index(['server_id', 'status']);
            $table->index(['team_id', 'created_at']);
            $table->index('load_balancer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_checks');
    }
};
