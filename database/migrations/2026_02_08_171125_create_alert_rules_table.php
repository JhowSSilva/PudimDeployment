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
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('server_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            
            // Rule configuration
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('metric_type', 50); // cpu, memory, disk, response_time, etc.
            
            // Condition
            $table->string('condition', 20); // greater_than, less_than, equals, etc.
            $table->decimal('threshold', 10, 2);
            $table->integer('duration')->default(300); // seconds - how long condition must be true
            
            // Alert configuration
            $table->string('severity', 20)->default('warning'); // info, warning, critical
            $table->json('channels')->nullable(); // email, slack, discord, webhook
            $table->integer('cooldown')->default(300); // seconds between alerts
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('trigger_count')->default(0);
            
            $table->timestamps();
            
            $table->index(['team_id', 'is_active']);
            $table->index(['metric_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
