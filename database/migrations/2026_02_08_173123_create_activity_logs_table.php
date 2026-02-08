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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Subject (what was acted upon)
            $table->string('subject_type'); // Server, Site, Deployment, etc
            $table->unsignedBigInteger('subject_id');
            $table->index(['subject_type', 'subject_id']);
            
            // Action details
            $table->string('action'); // created, updated, deleted, deployed, restarted, etc
            $table->string('description');
            $table->json('properties')->nullable(); // Old and new values
            $table->json('metadata')->nullable(); // IP, user agent, etc
            
            // Optional related resource
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['team_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
