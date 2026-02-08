<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class HealthCheck extends Model
{
    protected $fillable = [
        'team_id',
        'server_id',
        'load_balancer_id',
        'type',
        'endpoint',
        'port',
        'timeout',
        'expected_status',
        'expected_body',
        'status',
        'response_time',
        'consecutive_successes',
        'consecutive_failures',
        'last_error',
        'total_checks',
        'successful_checks',
        'failed_checks',
        'uptime_percentage',
        'last_checked_at',
        'last_success_at',
        'last_failure_at',
        'unhealthy_since',
    ];

    protected $casts = [
        'uptime_percentage' => 'decimal:2',
        'last_checked_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
        'unhealthy_since' => 'datetime',
    ];

    // Check Types
    const TYPE_HTTP = 'http';
    const TYPE_HTTPS = 'https';
    const TYPE_TCP = 'tcp';
    const TYPE_PING = 'ping';

    // Status
    const STATUS_HEALTHY = 'healthy';
    const STATUS_UNHEALTHY = 'unhealthy';
    const STATUS_UNKNOWN = 'unknown';

    /**
     * Get the team that owns the health check.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the server being checked.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the load balancer if associated.
     */
    public function loadBalancer(): BelongsTo
    {
        return $this->belongsTo(LoadBalancer::class);
    }

    /**
     * Scope for healthy checks.
     */
    public function scopeHealthy(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_HEALTHY);
    }

    /**
     * Scope for unhealthy checks.
     */
    public function scopeUnhealthy(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UNHEALTHY);
    }

    /**
     * Record successful check.
     */
    public function recordSuccess(int $responseTime): void
    {
        $this->increment('total_checks');
        $this->increment('successful_checks');
        $this->increment('consecutive_successes');
        
        $this->update([
            'consecutive_failures' => 0,
            'status' => self::STATUS_HEALTHY,
            'response_time' => $responseTime,
            'last_checked_at' => now(),
            'last_success_at' => now(),
            'last_error' => null,
            'unhealthy_since' => null,
        ]);

        $this->updateUptimePercentage();
    }

    /**
     * Record failed check.
     */
    public function recordFailure(string $error): void
    {
        $this->increment('total_checks');
        $this->increment('failed_checks');
        $this->increment('consecutive_failures');
        
        $updates = [
            'consecutive_successes' => 0,
            'status' => self::STATUS_UNHEALTHY,
            'last_checked_at' => now(),
            'last_failure_at' => now(),
            'last_error' => $error,
        ];

        if ($this->consecutive_failures === 0) {
            $updates['unhealthy_since'] = now();
        }

        $this->update($updates);
        $this->updateUptimePercentage();
    }

    /**
     * Update uptime percentage.
     */
    protected function updateUptimePercentage(): void
    {
        if ($this->total_checks === 0) {
            return;
        }

        $uptime = ($this->successful_checks / $this->total_checks) * 100;
        $this->update(['uptime_percentage' => round($uptime, 2)]);
    }

    /**
     * Get check status summary.
     */
    public function getStatusSummaryAttribute(): array
    {
        return [
            'status' => $this->status,
            'uptime' => $this->uptime_percentage,
            'response_time' => $this->response_time,
            'last_checked' => $this->last_checked_at?->diffForHumans(),
            'consecutive_failures' => $this->consecutive_failures,
            'consecutive_successes' => $this->consecutive_successes,
        ];
    }

    /**
     * Check if server should be marked unhealthy.
     */
    public function shouldMarkUnhealthy(int $threshold): bool
    {
        return $this->consecutive_failures >= $threshold;
    }

    /**
     * Check if server should be marked healthy.
     */
    public function shouldMarkHealthy(int $threshold): bool
    {
        return $this->consecutive_successes >= $threshold;
    }
}
