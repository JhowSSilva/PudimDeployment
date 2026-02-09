<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DockerContainer extends Model
{
    protected $fillable = [
        'server_id',
        'site_id',
        'container_id',
        'name',
        'image',
        'image_tag',
        'status',
        'started_at',
        'finished_at',
        'ports',
        'volumes',
        'environment',
        'network',
        'restart_policy',
        'cpu_limit',
        'memory_limit',
        'privileged',
        'working_dir',
        'command',
        'labels',
        'stats',
        'stats_updated_at',
    ];

    protected $hidden = [
        'environment',
    ];

    protected $casts = [
        'ports' => 'array',
        'volumes' => 'array',
        'environment' => 'array',
        'labels' => 'array',
        'stats' => 'array',
        'privileged' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'stats_updated_at' => 'datetime',
    ];

    // Relationships
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    // Helper methods
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isStopped(): bool
    {
        return in_array($this->status, ['stopped', 'exited', 'created']);
    }

    public function getFormattedMemoryUsageAttribute(): ?string
    {
        if (!$this->stats || !isset($this->stats['memory_usage'])) {
            return null;
        }

        $bytes = $this->stats['memory_usage'];
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    public function getCpuPercentageAttribute(): ?float
    {
        if (!$this->stats || !isset($this->stats['cpu_percentage'])) {
            return null;
        }

        return round($this->stats['cpu_percentage'], 2);
    }
}
