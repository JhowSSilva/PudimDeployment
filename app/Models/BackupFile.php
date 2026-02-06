<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_configuration_id',
        'backup_job_id',
        'filename',
        'file_size',
        'storage_path',
        'storage_provider',
        'compression_type',
        'checksum',
        'is_encrypted',
        'expires_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_encrypted' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the backup configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(BackupConfiguration::class, 'backup_configuration_id');
    }

    /**
     * Get the job that created this file
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(BackupJob::class, 'backup_job_id');
    }

    /**
     * Check if file is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        return \Illuminate\Support\Number::fileSize($this->file_size);
    }

    /**
     * Get download URL (temporary signed URL for security)
     */
    public function getDownloadUrl(): string
    {
        return route('backups.files.download', [
            'file' => $this->id,
        ]);
    }

    /**
     * Verify file integrity
     */
    public function verifyIntegrity(string $actualChecksum): bool
    {
        return $this->checksum === $actualChecksum;
    }
}
