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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Pro, Enterprise
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0); // Preço mensal em USD
            $table->decimal('yearly_price', 10, 2)->nullable(); // Preço anual (desconto)
            $table->string('stripe_price_id')->nullable(); // Stripe Price ID
            $table->string('stripe_yearly_price_id')->nullable();
            
            // Limites de recursos
            $table->integer('max_servers')->default(1);
            $table->integer('max_sites_per_server')->default(3);
            $table->integer('max_deployments_per_month')->default(50);
            $table->integer('max_backups')->default(5);
            $table->integer('max_team_members')->default(1);
            $table->integer('max_storage_gb')->default(1); // Storage total em GB
            
            // Features booleanas
            $table->boolean('has_ssl_auto_renewal')->default(false);
            $table->boolean('has_priority_support')->default(false);
            $table->boolean('has_advanced_analytics')->default(false);
            $table->boolean('has_custom_domains')->default(true);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_audit_logs')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
