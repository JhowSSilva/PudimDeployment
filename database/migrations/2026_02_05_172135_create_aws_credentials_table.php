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
        Schema::create('aws_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome descritivo da conta
            $table->text('access_key_id'); // Criptografado
            $table->text('secret_access_key'); // Criptografado
            $table->string('default_region')->default('us-east-1');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_validated_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_credentials');
    }
};
