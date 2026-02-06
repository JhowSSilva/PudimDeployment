<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_workflow_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('github_workflows')->onDelete('cascade');
            $table->foreignId('repository_id')->constrained('github_repositories')->onDelete('cascade');
            $table->bigInteger('github_id')->unique();
            $table->string('name');
            $table->string('head_branch')->nullable();
            $table->string('head_sha')->nullable();
            $table->string('status'); // queued, in_progress, completed
            $table->string('conclusion')->nullable(); // success, failure, cancelled, skipped
            $table->string('event')->nullable(); // push, pull_request, workflow_dispatch
            $table->text('html_url');
            $table->timestamp('github_created_at')->nullable();
            $table->timestamp('github_updated_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('run_number')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['repository_id', 'status']);
            $table->index(['workflow_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_workflow_runs');
    }
};
