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
        Schema::create('deployment_strategies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('name');
            $table->string('type'); // blue_green, canary, rolling, recreate
            $table->text('description')->nullable();
            $table->json('config'); // strategy-specific configuration
            $table->boolean('is_default')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->json('health_check_config')->nullable(); // health check settings
            $table->integer('rollback_on_failure_percentage')->nullable(); // auto rollback threshold
            $table->timestamps();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->index(['team_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_strategies');
    }
};
