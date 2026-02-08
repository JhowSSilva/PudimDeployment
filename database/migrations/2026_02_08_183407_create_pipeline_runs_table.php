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
        Schema::create('pipeline_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pipeline_id');
            $table->unsignedBigInteger('triggered_by_user_id')->nullable();
            $table->string('trigger_source'); // manual, git_push, webhook, schedule
            $table->string('status')->default('pending'); // pending, running, success, failed, cancelled
            $table->string('git_branch')->nullable();
            $table->string('git_commit_hash')->nullable();
            $table->string('git_commit_message')->nullable();
            $table->json('stage_results')->nullable(); // results of each stage execution
            $table->text('output_log')->nullable(); // combined output of all stages
            $table->text('error_log')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('deployment_id')->nullable(); // link to deployment if created
            $table->timestamps();
            
            $table->foreign('pipeline_id')->references('id')->on('pipelines')->onDelete('cascade');
            $table->foreign('triggered_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deployment_id')->references('id')->on('deployments')->onDelete('set null');
            $table->index(['pipeline_id', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_runs');
    }
};
