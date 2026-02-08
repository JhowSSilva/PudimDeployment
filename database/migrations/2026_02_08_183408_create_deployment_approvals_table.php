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
        Schema::create('deployment_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pipeline_run_id');
            $table->unsignedBigInteger('deployment_strategy_id')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, expired
            $table->unsignedBigInteger('requested_by_user_id');
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('request_message')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('required_approvers')->nullable(); // user IDs or role names
            $table->integer('required_approvals')->default(1);
            $table->json('approval_history')->nullable(); // track all approvers
            $table->timestamps();
            
            $table->foreign('pipeline_run_id')->references('id')->on('pipeline_runs')->onDelete('cascade');
            $table->foreign('deployment_strategy_id')->references('id')->on('deployment_strategies')->onDelete('set null');
            $table->foreign('requested_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_approvals');
    }
};
