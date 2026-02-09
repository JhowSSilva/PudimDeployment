<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ServerPool extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'region',
        'environment',
        'min_servers',
        'max_servers',
        'desired_servers',
        'current_servers',
        'auto_healing',
        'health_check_interval',
        'status',
        'last_scaled_at',
    ];

    protected $casts = [
        'min_servers' => 'integer',
        'max_servers' => 'integer',
        'desired_servers' => 'integer',
        'current_servers' => 'integer',
        'health_check_interval' => 'integer',
        'auto_healing' => 'boolean',
        'last_scaled_at' => 'datetime',
    ];

    /**
     * Get the team that owns the pool.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the servers in this pool.
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'server_pool_server')
            ->withPivot(['weight', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get active servers only.
     */
    public function activeServers(): BelongsToMany
    {
        return $this->servers()->wherePivot('is_active', true);
    }

    /**
     * Get scaling policies for this pool.
     */
    public function scalingPolicies(): HasMany
    {
        return $this->hasMany(ScalingPolicy::class);
    }

    /**
     * Get load balancers for this pool.
     */
    public function loadBalancers(): HasMany
    {
        return $this->hasMany(LoadBalancer::class);
    }

    /**
     * Scope for active pools.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by environment.
     */
    public function scopeEnvironment(Builder $query, string $env): Builder
    {
        return $query->where('environment', $env);
    }

    /**
     * Add server to pool.
     */
    public function addServer(Server $server, int $weight = 100): void
    {
        $this->servers()->attach($server->id, [
            'weight' => $weight,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->updateCurrentServersCount();
    }

    /**
     * Remove server from pool.
     */
    public function removeServer(Server $server): void
    {
        $this->servers()->detach($server->id);
        $this->updateCurrentServersCount();
    }

    /**
     * Update current servers count.
     */
    public function updateCurrentServersCount(): void
    {
        $count = $this->activeServers()->count();
        $this->update(['current_servers' => $count]);
    }

    /**
     * Check if pool can scale up.
     */
    public function canScaleUp(): bool
    {
        return $this->current_servers < $this->max_servers;
    }

    /**
     * Check if pool can scale down.
     */
    public function canScaleDown(): bool
    {
        return $this->current_servers > $this->min_servers;
    }

    /**
     * Get scale status.
     */
    public function getScaleStatusAttribute(): string
    {
        if ($this->current_servers < $this->desired_servers) {
            return 'scaling_up';
        }
        if ($this->current_servers > $this->desired_servers) {
            return 'scaling_down';
        }
        return 'stable';
    }

    /**
     * Get health status.
     */
    public function getHealthStatusAttribute(): array
    {
        $servers = $this->servers;
        $total = $servers->count();
        
        if ($total === 0) {
            return [
                'status' => 'no_servers',
                'healthy' => 0,
                'unhealthy' => 0,
                'total' => 0,
                'percentage' => 0,
            ];
        }

        $healthy = $servers->filter(function($server) {
            return $server->status === 'online';
        })->count();

        return [
            'status' => $healthy === $total ? 'healthy' : ($healthy > 0 ? 'degraded' : 'unhealthy'),
            'healthy' => $healthy,
            'unhealthy' => $total - $healthy,
            'total' => $total,
            'percentage' => round(($healthy / $total) * 100, 2),
        ];
    }
}
