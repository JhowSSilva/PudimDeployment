<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_repositories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('github_id')->unique();
            $table->string('name');
            $table->string('full_name');
            $table->text('description')->nullable();
            $table->boolean('private')->default(false);
            $table->string('language')->nullable();
            $table->string('default_branch')->default('main');
            $table->string('clone_url');
            $table->string('ssh_url');
            $table->string('html_url');
            $table->timestamp('github_created_at')->nullable();
            $table->timestamp('github_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_repositories');
    }
};
