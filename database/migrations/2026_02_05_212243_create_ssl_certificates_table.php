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
        Schema::create('ssl_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->json('domains'); // Array of domains covered by this certificate
            $table->string('provider'); // 'letsencrypt', 'custom', 'cloudflare'
            $table->string('status')->default('active'); // 'active', 'expired', 'renewal_failed'
            $table->string('cert_path')->nullable(); // Path to certificate file on server
            $table->string('key_path')->nullable(); // Path to private key file on server
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['site_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssl_certificates');
    }
};
