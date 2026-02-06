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
        Schema::create('backup_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_configuration_id')->constrained()->cascadeOnDelete();
            $table->boolean('email_on_success')->default(false);
            $table->boolean('email_on_failure')->default(true);
            $table->json('email_recipients'); // array of emails
            $table->string('webhook_url')->nullable();
            $table->json('webhook_headers')->nullable(); // custom headers
            $table->boolean('slack_enabled')->default(false);
            $table->string('slack_webhook')->nullable();
            $table->boolean('discord_enabled')->default(false);
            $table->string('discord_webhook')->nullable();
            $table->timestamps();
            
            $table->index('backup_configuration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_notification_settings');
    }
};
