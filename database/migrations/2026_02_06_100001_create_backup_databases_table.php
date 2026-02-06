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
        Schema::create('backup_databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained('backup_servers')->cascadeOnDelete();
            $table->enum('type', ['postgresql', 'mysql', 'mongodb', 'redis']);
            $table->string('name');
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->integer('port');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['server_id', 'type', 'name']);
            $table->index(['team_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_databases');
    }
};
