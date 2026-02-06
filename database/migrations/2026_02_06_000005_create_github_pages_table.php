<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repository_id')->constrained('github_repositories')->onDelete('cascade');
            $table->boolean('enabled')->default(false);
            $table->string('status')->nullable(); // null, building, built, errored
            $table->string('branch')->default('gh-pages');
            $table->string('path')->default('/'); // / or /docs
            $table->string('url')->nullable();
            $table->string('custom_domain')->nullable();
            $table->boolean('https_enforced')->default(true);
            $table->text('build_error')->nullable();
            $table->timestamp('last_build_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique('repository_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_pages');
    }
};
