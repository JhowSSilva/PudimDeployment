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
        Schema::create('docker_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('set null');
            
            // Container identification
            $table->string('container_id')->unique(); // Docker container ID
            $table->string('name');
            $table->string('image');
            $table->string('image_tag')->default('latest');
            
            // Status
            $table->string('status'); // running, exited, paused, etc.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            
            // Configuration
            $table->json('ports')->nullable(); // Port mappings
            $table->json('volumes')->nullable(); // Volume mounts
            $table->json('environment')->nullable(); // Environment variables
            $table->string('network')->nullable();
            $table->string('restart_policy')->default('unless-stopped');
            
            // Resource limits
            $table->string('cpu_limit')->nullable();
            $table->string('memory_limit')->nullable();
            
            // Additional settings
            $table->boolean('privileged')->default(false);
            $table->string('working_dir')->nullable();
            $table->text('command')->nullable();
            $table->json('labels')->nullable();
            
            // Stats (cached)
            $table->json('stats')->nullable(); // Latest stats
            $table->timestamp('stats_updated_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['server_id', 'status']);
            $table->index('container_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docker_containers');
    }
};
