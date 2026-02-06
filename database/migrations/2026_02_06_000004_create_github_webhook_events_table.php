<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repository_id')->nullable()->constrained('github_repositories')->onDelete('cascade');
            $table->string('event_type'); // push, pull_request, workflow_run, etc
            $table->string('delivery_id')->unique();
            $table->text('signature');
            $table->json('payload');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['event_type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_webhook_events');
    }
};
