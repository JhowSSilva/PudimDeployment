<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class LoadBalancer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'server_pool_id',
        'name',
        'description',
        'ip_address',
        'port',
        'protocol',
        'algorithm',
        'ssl_enabled',
        'ssl_certificate',
        'ssl_private_key',
        'health_check_enabled',
        'health_check_path',
        'health_check_interval',
        'health_check_timeout',
        'healthy_threshold',
        'unhealthy_threshold',
        'sticky_sessions',
        'session_ttl',
        'rules',
        'headers',
        'total_requests',
        'failed_requests',
        'last_health_check_at',
        'status',
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'health_check_enabled' => 'boolean',
        'sticky_sessions' => 'boolean',
        'rules' => 'array',
        'headers' => 'array',
        'last_health_check_at' => 'datetime',
    ];

    protected $hidden = [
        'ssl_private_key',
    ];

    // Algorithms
    const ALGORITHM_ROUND_ROBIN = 'round_robin';
    const ALGORITHM_LEAST_CONNECTIONS = 'least_connections';
    const ALGORITHM_IP_HASH = 'ip_hash';
    const ALGORITHM_WEIGHTED = 'weighted';

    // Protocols
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';

    /**
     * Get the team that owns the load balancer.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the server pool.
     */
    public function serverPool(): BelongsTo
    {
        return $this->belongsTo(ServerPool::class);
    }

    /**
     * Get health checks for this load balancer.
     */
    public function healthChecks(): HasMany
    {
        return $this->hasMany(HealthCheck::class);
    }

    /**
     * Scope for active load balancers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Increment request counters.
     */
    public function incrementRequests(bool $failed = false): void
    {
        $this->increment('total_requests');
        if ($failed) {
            $this->increment('failed_requests');
        }
    }

    /**
     * Get success rate percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_requests === 0) {
            return 100;
        }

        $successful = $this->total_requests - $this->failed_requests;
        return round(($successful / $this->total_requests) * 100, 2);
    }

    /**
     * Get error rate percentage.
     */
    public function getErrorRateAttribute(): float
    {
        return 100 - $this->success_rate;
    }

    /**
     * Check if SSL is configured.
     */
    public function hasSsl(): bool
    {
        return $this->ssl_enabled && 
               !empty($this->ssl_certificate) && 
               !empty($this->ssl_private_key);
    }

    /**
     * Get next server based on algorithm.
     */
    public function getNextServer()
    {
        if (!$this->serverPool) {
            return null;
        }

        $servers = $this->serverPool->servers()->where('is_active', true)->get();

        if ($servers->isEmpty()) {
            return null;
        }

        return match($this->algorithm) {
            self::ALGORITHM_ROUND_ROBIN => $servers->random(),
            self::ALGORITHM_LEAST_CONNECTIONS => $servers->sortBy('active_connections')->first(),
            self::ALGORITHM_WEIGHTED => $this->getWeightedServer($servers),
            default => $servers->first(),
        };
    }

    /**
     * Get server based on weighted distribution.
     */
    protected function getWeightedServer($servers)
    {
        $totalWeight = $servers->sum(function($server) {
            return $server->pivot->weight ?? 100;
        });

        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($servers as $server) {
            $currentWeight += $server->pivot->weight ?? 100;
            if ($random <= $currentWeight) {
                return $server;
            }
        }

        return $servers->first();
    }
}
