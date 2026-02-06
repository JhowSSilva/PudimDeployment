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
        Schema::table('sites', function (Blueprint $table) {
            $table->string('webhook_url')->nullable()->after('deploy_script');
            $table->string('webhook_secret')->nullable()->after('webhook_url');
            $table->boolean('auto_deploy_enabled')->default(false)->after('webhook_secret');
            $table->timestamp('last_webhook_at')->nullable()->after('auto_deploy_enabled');
            $table->string('webhook_provider')->nullable()->after('last_webhook_at'); // github, gitlab, bitbucket
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['webhook_url', 'webhook_secret', 'auto_deploy_enabled', 'last_webhook_at', 'webhook_provider']);
        });
    }
};
