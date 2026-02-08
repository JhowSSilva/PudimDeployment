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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('alert_rule_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('server_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('uptime_check_id')->nullable()->constrained()->onDelete('cascade');
            
            // Alert details
            $table->string('title');
            $table->text('message');
            $table->string('severity', 20)->default('warning'); // info, warning, critical
            $table->string('status', 20)->default('open'); // open, acknowledged, resolved
            
            // Current value that triggered alert
            $table->decimal('current_value', 10, 2)->nullable();
            $table->decimal('threshold_value', 10, 2)->nullable();
            
            // Acknowledgment
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('acknowledgment_note')->nullable();
            
            // Resolution
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            
            // Notification tracking
            $table->json('notification_sent')->nullable(); // channels that were notified
            $table->timestamp('notification_sent_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['team_id', 'status', 'severity']);
            $table->index(['created_at', 'status']);
            $table->index(['alert_rule_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
