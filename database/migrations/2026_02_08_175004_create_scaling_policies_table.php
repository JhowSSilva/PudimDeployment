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
        Schema::create('scaling_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('server_pool_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Policy Type: cpu, memory, schedule, custom
            $table->enum('type', ['cpu', 'memory', 'schedule', 'custom']);
            
            // Metric Configuration
            $table->string('metric')->nullable(); // cpu_usage, memory_usage, etc
            $table->decimal('threshold_up', 5, 2)->nullable(); // Scale up when above
            $table->decimal('threshold_down', 5, 2)->nullable(); // Scale down when below
            $table->integer('evaluation_periods')->default(2); // How many periods to check
            $table->integer('period_duration')->default(60); // Duration in seconds
            
            // Scaling Configuration
            $table->integer('scale_up_by')->default(1); // Add N servers
            $table->integer('scale_down_by')->default(1); // Remove N servers
            $table->integer('min_servers')->default(1);
            $table->integer('max_servers')->default(10);
            $table->integer('cooldown_minutes')->default(5); // Wait between scaling actions
            
            // Schedule Configuration (for schedule type)
            $table->json('schedule')->nullable(); // Cron expression or time ranges
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('last_scaled_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['team_id', 'is_active']);
            $table->index('server_pool_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scaling_policies');
    }
};
