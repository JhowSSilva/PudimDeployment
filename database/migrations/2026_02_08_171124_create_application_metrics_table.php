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
        Schema::create('application_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            
            // Metric type: cpu, memory, disk, network, response_time, etc.
            $table->string('metric_type', 50)->index();
            
            // Metric values
            $table->decimal('value', 10, 2);
            $table->string('unit', 20); // %, MB, GB, ms, etc.
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional context
            
            // Timestamps
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['server_id', 'metric_type', 'recorded_at']);
            $table->index(['recorded_at', 'metric_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_metrics');
    }
};
