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
        Schema::create('uptime_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('server_id')->nullable()->constrained()->onDelete('cascade');
            
            // Check configuration
            $table->string('name');
            $table->string('url')->nullable(); // For HTTP checks
            $table->string('check_type', 20); // http, tcp, icmp, ssl
            $table->integer('interval')->default(60); // seconds
            $table->integer('timeout')->default(10); // seconds
            
            // Expected response
            $table->integer('expected_status_code')->nullable(); // For HTTP
            $table->text('expected_content')->nullable(); // For HTTP content check
            
            // Status
            $table->string('status', 20)->default('unknown'); // up, down, degraded, unknown
            $table->timestamp('last_checked_at')->nullable();
            $table->integer('response_time')->nullable(); // milliseconds
            
            // Uptime stats
            $table->integer('uptime_percentage')->default(100);
            $table->integer('total_checks')->default(0);
            $table->integer('failed_checks')->default(0);
            $table->timestamp('last_downtime_at')->nullable();
            
            // Alert settings
            $table->boolean('alert_enabled')->default(true);
            $table->json('alert_channels')->nullable(); // email, slack, discord
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['team_id', 'status']);
            $table->index(['is_active', 'last_checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uptime_checks');
    }
};
