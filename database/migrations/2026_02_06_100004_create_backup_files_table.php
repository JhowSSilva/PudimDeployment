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
        Schema::create('backup_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_configuration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('backup_job_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->bigInteger('file_size'); // bytes
            $table->string('storage_path');
            $table->string('storage_provider');
            $table->string('compression_type');
            $table->string('checksum')->nullable(); // MD5 hash
            $table->boolean('is_encrypted')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['backup_configuration_id', 'created_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_files');
    }
};
