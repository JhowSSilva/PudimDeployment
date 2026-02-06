<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerMetric extends Model
{
    protected $fillable = [
        'server_id',
        'cpu_usage',
        'memory_used_mb',
        'memory_total_mb',
        'disk_used_gb',
        'disk_total_gb',
        'uptime_seconds',
        'processes',
    ];

    protected $casts = [
        'cpu_usage' => 'decimal:2',
        'memory_used_mb' => 'integer',
        'memory_total_mb' => 'integer',
        'disk_used_gb' => 'integer',
        'disk_total_gb' => 'integer',
        'uptime_seconds' => 'integer',
        'processes' => 'array',
    ];

    // Relationships
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    // Helper methods
    public function getMemoryUsagePercentageAttribute(): float
    {
        if (!$this->memory_total_mb) {
            return 0;
        }
        
        return round(($this->memory_used_mb / $this->memory_total_mb) * 100, 2);
    }

    public function getDiskUsagePercentageAttribute(): float
    {
        if (!$this->disk_total_gb) {
            return 0;
        }
        
        return round(($this->disk_used_gb / $this->disk_total_gb) * 100, 2);
    }

    public function getUptimeHumanAttribute(): string
    {
        $days = floor($this->uptime_seconds / 86400);
        $hours = floor(($this->uptime_seconds % 86400) / 3600);
        $minutes = floor(($this->uptime_seconds % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }
}
