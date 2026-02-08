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
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type')->default('manual'); // manual, push, pull_request, schedule, webhook
            $table->json('trigger_config')->nullable(); // branch filters, schedule cron, webhook secret
            $table->string('status')->default('active'); // active, paused, disabled
            $table->boolean('auto_deploy')->default(false);
            $table->integer('timeout_minutes')->default(30);
            $table->json('environment_variables')->nullable();
            $table->integer('retention_days')->default(30); // keep runs for X days
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->index(['team_id', 'status']);
            $table->index('last_run_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipelines');
    }
};
