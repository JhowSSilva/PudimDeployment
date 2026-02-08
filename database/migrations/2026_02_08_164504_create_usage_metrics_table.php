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
        Schema::create('billing_usage_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('metric_type'); // servers, sites, deployments, backups, storage
            $table->integer('current_value')->default(0);
            $table->integer('limit_value')->default(0); // From plan
            $table->decimal('usage_percentage', 5, 2)->default(0); // Calculated
            
            $table->date('period_start');
            $table->date('period_end');
            
            $table->json('details')->nullable(); // Breakdown of usage
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
            
            $table->index(['team_id', 'metric_type', 'period_start']);
            $table->index(['usage_percentage']); // For alerts
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_usage_metrics');
    }
};
