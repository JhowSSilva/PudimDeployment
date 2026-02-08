<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Alert extends Model
{
    // Severity constants
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_CRITICAL = 'critical';
    
    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_ACKNOWLEDGED = 'acknowledged';
    const STATUS_RESOLVED = 'resolved';
    
    protected $fillable = [
        'team_id',
        'alert_rule_id',
        'server_id',
        'site_id',
        'uptime_check_id',
        'title',
        'message',
        'severity',
        'status',
        'current_value',
        'threshold_value',
        'acknowledged_by',
        'acknowledged_at',
        'acknowledgment_note',
        'resolved_at',
        'resolution_note',
        'notification_sent',
        'notification_sent_at',
    ];
    
    protected $casts = [
        'current_value' => 'decimal:2',
        'threshold_value' => 'decimal:2',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'notification_sent' => 'array',
        'notification_sent_at' => 'datetime',
    ];
    
    /**
     * Get the team that owns the alert.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    /**
     * Get the alert rule that triggered this alert.
     */
    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }
    
    /**
     * Get the server related to this alert.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
    
    /**
     * Get the site related to this alert.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
    
    /**
     * Get the uptime check related to this alert.
     */
    public function uptimeCheck(): BelongsTo
    {
        return $this->belongsTo(UptimeCheck::class);
    }
    
    /**
     * Get the user who acknowledged this alert.
     */
    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
    
    /**
     * Scope for open alerts.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }
    
    /**
     * Scope for acknowledged alerts.
     */
    public function scopeAcknowledged(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACKNOWLEDGED);
    }
    
    /**
     * Scope for resolved alerts.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }
    
    /**
     * Scope for critical alerts.
     */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }
    
    /**
     * Scope for recent alerts.
     */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
    
    /**
     * Acknowledge the alert.
     */
    public function acknowledge(User $user, ?string $note = null): void
    {
        $this->update([
            'status' => self::STATUS_ACKNOWLEDGED,
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
            'acknowledgment_note' => $note,
        ]);
    }
    
    /**
     * Resolve the alert.
     */
    public function resolve(?string $note = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution_note' => $note,
        ]);
    }
    
    /**
     * Check if alert is open.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
    
    /**
     * Check if alert is critical.
     */
    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }
    
    /**
     * Get time since created (human readable).
     */
    public function getTimeSinceAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
    
    /**
     * Get badge color based on severity.
     */
    public function getBadgeColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_INFO => 'primary',
            self::SEVERITY_WARNING => 'warning',
            self::SEVERITY_CRITICAL => 'error',
            default => 'neutral',
        };
    }
}
