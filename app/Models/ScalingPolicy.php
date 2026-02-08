<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScalingPolicy extends Model
{
    protected $fillable = [
        'team_id',
        'server_pool_id',
        'name',
        'description',
        'type',
        'metric',
        'threshold_up',
        'threshold_down',
        'evaluation_periods',
        'period_duration',
        'scale_up_by',
        'scale_down_by',
        'min_servers',
        'max_servers',
        'cooldown_minutes',
        'schedule',
        'is_active',
        'last_triggered_at',
        'last_scaled_at',
    ];

    protected $casts = [
        'threshold_up' => 'decimal:2',
        'threshold_down' => 'decimal:2',
        'schedule' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'last_scaled_at' => 'datetime',
    ];

    // Policy Types
    const TYPE_CPU = 'cpu';
    const TYPE_MEMORY = 'memory';
    const TYPE_SCHEDULE = 'schedule';
    const TYPE_CUSTOM = 'custom';

    /**
     * Get the team that owns the policy.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the server pool this policy applies to.
     */
    public function serverPool(): BelongsTo
    {
        return $this->belongsTo(ServerPool::class);
    }

    /**
     * Scope for active policies.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by policy type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if policy is in cooldown period.
     */
    public function isInCooldown(): bool
    {
        if (!$this->last_scaled_at) {
            return false;
        }

        return $this->last_scaled_at->addMinutes($this->cooldown_minutes)->isFuture();
    }

    /**
     * Get next allowed scaling time.
     */
    public function getNextScalingTimeAttribute()
    {
        if (!$this->last_scaled_at) {
            return now();
        }

        return$this->last_scaled_at->addMinutes($this->cooldown_minutes);
    }

    /**
     * Check if policy should trigger based on metric.
     */
    public function shouldScale(float $currentValue, string $direction = 'up'): bool
    {
        if (!$this->is_active || $this->isInCooldown()) {
            return false;
        }

        if ($direction === 'up') {
            return $currentValue >= $this->threshold_up;
        }

        return $currentValue <= $this->threshold_down;
    }

    /**
     * Mark policy as triggered.
     */
    public function markTriggered(): void
    {
        $this->update(['last_triggered_at' => now()]);
    }

    /**
     * Mark policy as scaled.
     */
    public function markScaled(): void
    {
        $this->update([
            'last_scaled_at' => now(),
            'last_triggered_at' => now(),
        ]);
    }

    /**
     * Get policy summary.
     */
    public function getSummaryAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CPU => "Scale when CPU {$this->metric} is above {$this->threshold_up}% or below {$this->threshold_down}%",
            self::TYPE_MEMORY => "Scale when Memory {$this->metric} is above {$this->threshold_up}% or below {$this->threshold_down}%",
            self::TYPE_SCHEDULE => "Scale based on schedule: {$this->schedule}",
            default => "Custom scaling policy",
        };
    }
}
