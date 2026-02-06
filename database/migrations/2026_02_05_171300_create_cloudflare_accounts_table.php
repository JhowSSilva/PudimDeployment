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
        Schema::create('cloudflare_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome descritivo da conta
            $table->text('api_token'); // Criptografado
            $table->string('account_id')->nullable();
            $table->string('zone_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_accounts');
    }
};
