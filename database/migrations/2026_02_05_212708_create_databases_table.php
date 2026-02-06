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
        Schema::create('databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['mysql', 'postgresql'])->default('mysql');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('size_mb')->nullable();
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamps();
            
            $table->unique(['server_id', 'name']);
            $table->index(['server_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('databases');
    }
};
