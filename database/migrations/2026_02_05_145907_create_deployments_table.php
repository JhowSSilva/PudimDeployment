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
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // Who triggered
            $table->string('commit_hash')->nullable();
            $table->string('commit_message')->nullable();
            $table->enum('status', ['pending', 'running', 'success', 'failed', 'rolled_back'])->default('pending');
            $table->enum('trigger', ['manual', 'webhook', 'scheduled'])->default('manual');
            $table->longText('output_log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
            
            $table->index(['site_id', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
