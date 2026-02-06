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
        Schema::table('servers', function (Blueprint $table) {
            if (!Schema::hasColumn('servers', 'default_key_id')) {
                $table->foreignId('default_key_id')->nullable()->after('ssh_user')->constrained('ssh_keys')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            if (Schema::hasColumn('servers', 'default_key_id')) {
                $table->dropForeign(['default_key_id']);
                $table->dropColumn('default_key_id');
            }
        });
    }
};
