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
        Schema::create('queue_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->uuid('worker_id')->unique();
            $table->string('queue')->default('default');
            $table->integer('processes')->default(1);
            $table->string('pid')->nullable();
            $table->string('pid_file')->nullable();
            $table->string('log_file')->nullable();
            $table->text('command');
            $table->enum('status', ['running', 'stopped', 'failed'])->default('running');
            $table->json('options')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
            
            $table->index(['server_id', 'status']);
            $table->index('queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_workers');
    }
};
