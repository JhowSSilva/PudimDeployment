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
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->decimal('cpu_usage', 5, 2)->nullable(); // 0.00 to 100.00
            $table->bigInteger('memory_used_mb')->nullable();
            $table->bigInteger('memory_total_mb')->nullable();
            $table->bigInteger('disk_used_gb')->nullable();
            $table->bigInteger('disk_total_gb')->nullable();
            $table->integer('uptime_seconds')->nullable();
            $table->json('processes')->nullable(); // nginx, php-fpm, mysql status
            $table->timestamps();
            
            $table->index(['server_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_metrics');
    }
};
