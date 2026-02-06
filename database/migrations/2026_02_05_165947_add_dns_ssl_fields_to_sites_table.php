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
            // Cloudflare DNS Fields
            $table->string('cloudflare_zone_id')->nullable()->after('git_token');
            $table->string('cloudflare_record_id')->nullable()->after('cloudflare_zone_id');
            $table->boolean('cloudflare_proxy')->default(true)->after('cloudflare_record_id');
            $table->boolean('auto_dns')->default(false)->after('cloudflare_proxy');
            
            // SSL Fields
            $table->enum('ssl_type', ['none', 'letsencrypt', 'cloudflare'])->default('cloudflare')->after('auto_dns');
            $table->boolean('ssl_enabled')->default(false)->after('ssl_type');
            $table->timestamp('ssl_expires_at')->nullable()->after('ssl_enabled');
            $table->timestamp('ssl_last_check')->nullable()->after('ssl_expires_at');
            $table->text('ssl_certificate')->nullable()->after('ssl_last_check');
            $table->text('ssl_private_key')->nullable()->after('ssl_certificate');
            $table->text('ssl_ca_bundle')->nullable()->after('ssl_private_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'cloudflare_zone_id',
                'cloudflare_record_id',
                'cloudflare_proxy',
                'auto_dns',
                'ssl_type',
                'ssl_enabled',
                'ssl_expires_at',
                'ssl_last_check',
                'ssl_certificate',
                'ssl_private_key',
                'ssl_ca_bundle',
            ]);
        });
    }
};
