<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repository_id')->constrained('github_repositories')->onDelete('cascade');
            $table->bigInteger('github_id')->unique();
            $table->string('name');
            $table->string('path');
            $table->string('state'); // active, disabled, deleted
            $table->timestamp('github_created_at')->nullable();
            $table->timestamp('github_updated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_workflows');
    }
};
