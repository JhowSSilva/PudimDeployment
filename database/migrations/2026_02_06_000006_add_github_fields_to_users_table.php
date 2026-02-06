<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('github_id')->nullable()->unique()->after('id');
            $table->string('github_username')->nullable()->after('github_id');
            $table->text('github_token')->nullable()->after('github_username');
            $table->timestamp('github_token_expires_at')->nullable()->after('github_token');
            $table->text('github_refresh_token')->nullable()->after('github_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'github_id',
                'github_username',
                'github_token',
                'github_token_expires_at',
                'github_refresh_token',
            ]);
        });
    }
};
