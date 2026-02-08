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
        Schema::create('team_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            
            // Role details
            $table->string('name'); // Developer, Manager, Viewer, etc
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Permissions (JSON array of permission slugs)
            $table->json('permissions');
            
            // System roles cannot be deleted
            $table->boolean('is_system')->default(false);
            
            // Color for UI
            $table->string('color')->default('#3b82f6');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['team_id', 'slug']);
        });
        
        // Pivot table for user-role assignment
        Schema::create('team_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['team_id', 'user_id', 'team_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user_roles');
        Schema::dropIfExists('team_roles');
    }
};
