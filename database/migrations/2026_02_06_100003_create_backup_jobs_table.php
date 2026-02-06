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
        Schema::create('backup_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_configuration_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'running', 'completed', 'failed']);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->bigInteger('file_size')->nullable(); // bytes
            $table->string('storage_path')->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_trace')->nullable();
            $table->integer('duration')->nullable(); // seconds
            $table->json('metadata')->nullable(); // dump info, compression info, etc
            $table->timestamps();
            
            $table->index(['backup_configuration_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_jobs');
    }
};
