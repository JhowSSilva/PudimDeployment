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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('ip_address');
            $table->integer('ssh_port')->default(22);
            $table->string('ssh_user')->default('root');
            $table->enum('auth_type', ['password', 'key'])->default('key');
            $table->text('ssh_key')->nullable(); // Encrypted
            $table->text('ssh_password')->nullable(); // Encrypted
            $table->string('os_type')->nullable(); // Ubuntu, Debian, etc
            $table->string('os_version')->nullable(); // 22.04, 24.04
            $table->enum('status', ['online', 'offline', 'provisioning', 'error'])->default('offline');
            $table->timestamp('last_ping_at')->nullable();
            $table->json('metadata')->nullable(); // Additional info
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
