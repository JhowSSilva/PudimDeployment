<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BackupJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_configuration_id',
        'status',
        'started_at',
        'completed_at',
        'file_size',
        'storage_path',
        'error_message',
        'error_trace',
        'duration',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
        'duration' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the backup configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(BackupConfiguration::class, 'backup_configuration_id');
    }

    /**
     * Get the backup file created by this job
     */
    public function file(): HasOne
    {
        return $this->hasOne(BackupFile::class, 'backup_job_id');
    }

    /**
     * Check if job is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if job is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if job is running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if job is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        return \Illuminate\Support\Number::fileSize($this->file_size);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '-';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }
}
