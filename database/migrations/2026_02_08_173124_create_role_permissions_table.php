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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            
            // Permission details
            $table->string('name'); // Manage Servers, Deploy Sites, View Logs, etc
            $table->string('slug')->unique(); // manage-servers, deploy-sites, view-logs
            $table->string('category'); // servers, sites, deployments, databases, ssl, billing, team
            $table->text('description')->nullable();
            
            // Metadata
            $table->boolean('is_dangerous')->default(false); // Requires extra confirmation
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('category');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
