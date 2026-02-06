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
        // Add team_id to servers
        Schema::table('servers', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->onDelete('cascade');
            $table->index('team_id');
        });

        // Add team_id to sites
        Schema::table('sites', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->onDelete('cascade');
            $table->index('team_id');
        });

        // Add team_id to aws_credentials
        Schema::table('aws_credentials', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->onDelete('cascade');
            $table->index('team_id');
        });

        // Add team_id to cloudflare_accounts
        Schema::table('cloudflare_accounts', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->onDelete('cascade');
            $table->index('team_id');
        });

        // Add current_team_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_team_id')->nullable()->after('id')->constrained('teams')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_team_id']);
            $table->dropColumn('current_team_id');
        });

        Schema::table('cloudflare_accounts', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('aws_credentials', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};
