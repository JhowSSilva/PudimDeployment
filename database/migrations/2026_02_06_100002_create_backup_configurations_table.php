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
        Schema::create('backup_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('database_id')->constrained('backup_databases')->cascadeOnDelete();
            $table->string('name');
            
            // Storage Configuration
            $table->enum('storage_provider', [
                'aws_s3', 'azure_blob', 'google_cloud', 'do_spaces',
                'backblaze_b2', 'wasabi', 'minio', 'local'
            ]);
            $table->string('storage_path');
            $table->json('storage_credentials');
            
            // Schedule Configuration
            $table->enum('frequency', [
                'hourly', 'every_6_hours', 'every_12_hours',
                'daily', 'weekly', 'monthly'
            ]);
            $table->time('start_time')->nullable();
            $table->string('timezone')->default('UTC');
            $table->integer('day_of_week')->nullable(); // 0-6 (Sunday-Saturday) for weekly
            $table->integer('day_of_month')->nullable(); // 1-31 for monthly
            
            // Retention & Compression
            $table->integer('keep_backups')->default(7); // 0 = unlimited
            $table->enum('compression', ['zip', 'tar', 'tar_gz', 'tar_bz2', 'none'])->default('tar_gz');
            $table->text('encryption_password')->nullable();
            
            // Advanced Options
            $table->json('excluded_tables')->nullable();
            $table->boolean('delete_local_on_fail')->default(true);
            $table->boolean('verify_backup')->default(false);
            $table->string('custom_filename')->nullable();
            
            // Status & Metrics
            $table->enum('status', ['active', 'paused', 'running', 'failed'])->default('active');
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamp('next_backup_at')->nullable();
            $table->bigInteger('last_backup_size')->nullable(); // bytes
            $table->integer('last_backup_duration')->nullable(); // seconds
            $table->integer('total_backups')->default(0);
            $table->integer('failed_backups')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'status']);
            $table->index('next_backup_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_configurations');
    }
};
