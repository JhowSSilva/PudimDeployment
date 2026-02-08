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
        Schema::create('server_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Pool Configuration
            $table->string('region')->nullable();
            $table->enum('environment', ['production', 'staging', 'development'])->default('production');
            
            // Auto-scaling Settings
            $table->integer('min_servers')->default(1);
            $table->integer('max_servers')->default(10);
            $table->integer('desired_servers')->default(1);
            $table->integer('current_servers')->default(0);
            
            // Health Configuration
            $table->boolean('auto_healing')->default(true);
            $table->integer('health_check_interval')->default(30); // seconds
            
            // Status
            $table->enum('status', ['active', 'inactive', 'scaling', 'error'])->default('active');
            $table->timestamp('last_scaled_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'status']);
        });
        
        // Pivot table for many-to-many relationship with servers
        Schema::create('server_pool_server', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_pool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->integer('weight')->default(100); // For weighted load balancing
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['server_pool_id', 'server_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_pool_server');
        Schema::dropIfExists('server_pools');
    }
};
