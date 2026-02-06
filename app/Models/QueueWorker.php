<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueWorker extends Model
{
    use HasFactory;

    protected $table = 'queue_workers';

    protected $fillable = [
        'server_id',
        'worker_id',
        'queue',
        'processes',
        'pid',
        'pid_file',
        'log_file',
        'command',
        'status',
        'options',
        'started_at',
        'stopped_at',
    ];

    protected $casts = [
        'processes' => 'integer',
        'options' => 'array',
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Scope for running workers
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope for stopped workers
     */
    public function scopeStopped($query)
    {
        return $query->where('status', 'stopped');
    }

    /**
     * Get runtime in minutes
     */
    public function getRuntimeMinutesAttribute(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->stopped_at ?? now();
        return $this->started_at->diffInMinutes($endTime);
    }

    /**
     * Check if worker is running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Get formatted options
     */
    public function getFormattedOptionsAttribute(): string
    {
        if (empty($this->options)) {
            return 'Default options';
        }

        $formatted = [];
        foreach ($this->options as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }

        return implode(', ', $formatted);
    }
}