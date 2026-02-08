<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ApplicationMetric extends Model
{
    // Metric type constants
    const TYPE_CPU = 'cpu';
    const TYPE_MEMORY = 'memory';
    const TYPE_DISK = 'disk';
    const TYPE_NETWORK_IN = 'network_in';
    const TYPE_NETWORK_OUT = 'network_out';
    const TYPE_RESPONSE_TIME = 'response_time';
    const TYPE_REQUESTS_PER_MINUTE = 'requests_per_minute';
    const TYPE_ERROR_RATE = 'error_rate';
    
    protected $fillable = [
        'server_id',
        'site_id',
        'metric_type',
        'value',
        'unit',
        'metadata',
        'recorded_at',
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'value' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];
    
    /**
     * Get the server that owns the metric.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
    
    /**
     * Get the site that owns the metric (optional).
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
    
    /**
     * Scope for specific server.
     */
    public function scopeForServer(Builder $query, int $serverId): Builder
    {
        return $query->where('server_id', $serverId);
    }
    
    /**
     * Scope for specific metric type.
     */
    public function scopeByType(Builder $query, string $metricType): Builder
    {
        return $query->where('metric_type', $metricType);
    }
    
    /**
     * Scope for time range.
     */
    public function scopeBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('recorded_at', [$start, $end]);
    }
    
    /**
     * Scope for recent metrics.
     */
    public function scopeRecent(Builder $query, int $minutes = 60): Builder
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }
    
    /**
     * Get average value for a metric type.
     */
    public static function getAverage(int $serverId, string $metricType, $start, $end): float
    {
        return static::forServer($serverId)
            ->byType($metricType)
            ->between($start, $end)
            ->avg('value') ?? 0;
    }
    
    /**
     * Get maximum value for a metric type.
     */
    public static function getMaximum(int $serverId, string $metricType, $start, $end): float
    {
        return static::forServer($serverId)
            ->byType($metricType)
            ->between($start, $end)
            ->max('value') ?? 0;
    }
    
    /**
     * Check if value is critical.
     */
    public function isCritical(): bool
    {
        $thresholds = [
            self::TYPE_CPU => 90,
            self::TYPE_MEMORY => 90,
            self::TYPE_DISK => 85,
            self::TYPE_ERROR_RATE => 5,
        ];
        
        return isset($thresholds[$this->metric_type]) && 
               $this->value >= $thresholds[$this->metric_type];
    }
}
