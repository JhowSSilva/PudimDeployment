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
        // Update Azure credentials table
        Schema::table('azure_credentials', function (Blueprint $table) {
            $table->text('client_secret')->change();
        });

        // Update GCP credentials table
        Schema::table('gcp_credentials', function (Blueprint $table) {
            $table->text('service_account_json')->change();
        });

        // Update DigitalOcean credentials table
        Schema::table('digitalocean_credentials', function (Blueprint $table) {
            $table->text('api_token')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert Azure credentials table
        Schema::table('azure_credentials', function (Blueprint $table) {
            $table->string('client_secret')->change();
        });

        // Revert GCP credentials table
        Schema::table('gcp_credentials', function (Blueprint $table) {
            $table->string('service_account_json')->change();
        });

        // Revert DigitalOcean credentials table
        Schema::table('digitalocean_credentials', function (Blueprint $table) {
            $table->string('api_token')->change();
        });
    }
};
