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
        Schema::create('instance_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // t4g.micro, t3.small, etc
            $table->string('architecture'); // x86_64 ou arm64
            $table->string('family'); // t3, t4g, m5, m6g, m7g
            $table->integer('vcpu'); // Número de vCPUs
            $table->decimal('memory_gib', 8, 2); // Memória em GB
            $table->decimal('price_per_hour', 10, 6); // Preço por hora USD
            $table->decimal('price_per_month', 10, 2); // Preço mensal estimado (730h)
            $table->string('network_performance'); // Low, Moderate, High, Up to 5 Gigabit
            $table->boolean('is_available')->default(true);
            $table->json('regions')->nullable(); // Regiões onde está disponível
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['architecture', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instance_types');
    }
};
