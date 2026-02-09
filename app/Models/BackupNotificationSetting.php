<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_configuration_id',
        'email_on_success',
        'email_on_failure',
        'email_recipients',
        'webhook_url',
        'webhook_headers',
        'slack_enabled',
        'slack_webhook',
        'discord_enabled',
        'discord_webhook',
    ];

    protected $casts = [
        'email_on_success' => 'boolean',
        'email_on_failure' => 'boolean',
        'email_recipients' => 'array',
        'webhook_headers' => 'array',
        'slack_enabled' => 'boolean',
        'discord_enabled' => 'boolean',
    ];

    protected $hidden = [
        'webhook_url',
        'slack_webhook',
        'discord_webhook',
    ];

    /**
     * Get the backup configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(BackupConfiguration::class, 'backup_configuration_id');
    }

    /**
     * Check if email notifications are enabled
     */
    public function hasEmailNotifications(): bool
    {
        return ($this->email_on_success || $this->email_on_failure) 
            && !empty($this->email_recipients);
    }

    /**
     * Check if webhook is configured
     */
    public function hasWebhook(): bool
    {
        return !empty($this->webhook_url);
    }

    /**
     * Check if Slack is configured
     */
    public function hasSlack(): bool
    {
        return $this->slack_enabled && !empty($this->slack_webhook);
    }

    /**
     * Check if Discord is configured
     */
    public function hasDiscord(): bool
    {
        return $this->discord_enabled && !empty($this->discord_webhook);
    }
}
