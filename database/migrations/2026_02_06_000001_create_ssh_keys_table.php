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
        Schema::create('ssh_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['RSA', 'ED25519']);
            $table->integer('bits');
            $table->text('public_key');
            $table->text('private_key_encrypted');
            $table->string('fingerprint');
            $table->string('comment')->nullable();
            $table->boolean('has_passphrase')->default(false);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('fingerprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssh_keys');
    }
};
