<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class UptimeCheck extends Model
{
    // Check type constants
    const TYPE_HTTP = 'http';
    const TYPE_HTTPS = 'https';
    const TYPE_TCP = 'tcp';
    const TYPE_ICMP = 'icmp';
    const TYPE_SSL = 'ssl';
    
    // Status constants
    const STATUS_UP = 'up';
    const STATUS_DOWN = 'down';
    const STATUS_DEGRADED = 'degraded';
    const STATUS_UNKNOWN = 'unknown';
    
    protected $fillable = [
        'team_id',
        'site_id',
        'server_id',
        'name',
        'url',
        'check_type',
        'interval',
        'timeout',
        'expected_status_code',
        'expected_content',
        'status',
        'last_checked_at',
        'response_time',
        'uptime_percentage',
        'total_checks',
        'failed_checks',
        'last_downtime_at',
        'alert_enabled',
        'alert_channels',
        'is_active',
    ];
    
    protected $casts = [
        'alert_channels' => 'array',
        'last_checked_at' => 'datetime',
        'last_downtime_at' => 'datetime',
        'alert_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the team that owns the uptime check.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    /**
     * Get the site being monitored.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
    
    /**
     * Get the server being monitored.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
    
    /**
     * Get the alerts for this uptime check.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
    
    /**
     * Scope for active checks.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for checks that need to be run.
     */
    public function scopeDueForCheck(Builder $query): Builder
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_checked_at')
                  ->orWhereRaw('last_checked_at <= NOW() - INTERVAL \'' . '1 second\' * interval');
            });
    }
    
    /**
     * Scope for down checks.
     */
    public function scopeDown(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DOWN);
    }
    
    /**
     * Record a successful check.
     */
    public function recordSuccess(int $responseTime): void
    {
        $this->increment('total_checks');
        
        $this->update([
            'status' => self::STATUS_UP,
            'last_checked_at' => now(),
            'response_time' => $responseTime,
            'uptime_percentage' => $this->calculateUptimePercentage(),
        ]);
    }
    
    /**
     * Record a failed check.
     */
    public function recordFailure(): void
    {
        $this->increment('total_checks');
        $this->increment('failed_checks');
        
        $previousStatus = $this->status;
        
        $this->update([
            'status' => self::STATUS_DOWN,
            'last_checked_at' => now(),
            'last_downtime_at' => now(),
            'uptime_percentage' => $this->calculateUptimePercentage(),
        ]);
        
        // Trigger alert if status changed
        if ($previousStatus !== self::STATUS_DOWN && $this->alert_enabled) {
            $this->triggerAlert();
        }
    }
    
    /**
     * Calculate uptime percentage.
     */
    protected function calculateUptimePercentage(): int
    {
        if ($this->total_checks === 0) {
            return 100;
        }
        
        $successfulChecks = $this->total_checks - $this->failed_checks;
        return (int) round(($successfulChecks / $this->total_checks) * 100);
    }
    
    /**
     * Trigger alert.
     */
    protected function triggerAlert(): void
    {
        Alert::create([
            'team_id' => $this->team_id,
            'uptime_check_id' => $this->id,
            'server_id' => $this->server_id,
            'site_id' => $this->site_id,
            'title' => "Downtime detected: {$this->name}",
            'message' => "The uptime check '{$this->name}' is currently down.",
            'severity' => 'critical',
            'status' => 'open',
        ]);
    }
    
    /**
     * Check if currently up.
     */
    public function isUp(): bool
    {
        return $this->status === self::STATUS_UP;
    }
    
    /**
     * Check if currently down.
     */
    public function isDown(): bool
    {
        return $this->status === self::STATUS_DOWN;
    }
}
