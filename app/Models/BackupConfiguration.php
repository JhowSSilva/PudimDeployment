<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'database_id',
        'name',
        'storage_provider',
        'storage_path',
        'storage_credentials',
        'frequency',
        'start_time',
        'timezone',
        'day_of_week',
        'day_of_month',
        'keep_backups',
        'compression',
        'encryption_password',
        'excluded_tables',
        'delete_local_on_fail',
        'verify_backup',
        'custom_filename',
        'status',
        'last_backup_at',
        'next_backup_at',
        'last_backup_size',
        'last_backup_duration',
        'total_backups',
        'failed_backups',
    ];

    protected $casts = [
        'storage_credentials' => 'array',
        'excluded_tables' => 'array',
        'delete_local_on_fail' => 'boolean',
        'verify_backup' => 'boolean',
        'last_backup_at' => 'datetime',
        'next_backup_at' => 'datetime',
        'last_backup_size' => 'integer',
        'last_backup_duration' => 'integer',
        'total_backups' => 'integer',
        'failed_backups' => 'integer',
        'keep_backups' => 'integer',
    ];

    protected $hidden = [
        'storage_credentials',
        'encryption_password',
    ];

    /**
     * Get the team that owns this configuration
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the database to backup
     */
    public function database(): BelongsTo
    {
        return $this->belongsTo(BackupDatabase::class, 'database_id');
    }

    /**
     * Get backup jobs
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(BackupJob::class, 'backup_configuration_id');
    }

    /**
     * Get backup files
     */
    public function files(): HasMany
    {
        return $this->hasMany(BackupFile::class, 'backup_configuration_id');
    }

    /**
     * Get recent jobs
     */
    public function recentJobs(): HasMany
    {
        return $this->hasMany(BackupJob::class, 'backup_configuration_id')
            ->latest()
            ->limit(10);
    }

    /**
     * Get notification settings
     */
    public function notificationSettings(): HasOne
    {
        return $this->hasOne(BackupNotificationSetting::class, 'backup_configuration_id');
    }

    /**
     * Get last successful backup
     */
    public function lastSuccessfulBackup()
    {
        return $this->jobs()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_backups === 0) {
            return 0;
        }
        
        $successful = $this->total_backups - $this->failed_backups;
        return round(($successful / $this->total_backups) * 100, 1);
    }

    /**
     * Get total size of all backups
     */
    public function getTotalSizeAttribute(): int
    {
        return $this->files()->sum('file_size');
    }

    /**
     * Check if backup is due
     */
    public function isDue(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (!$this->next_backup_at) {
            return true; // Never backed up
        }

        return $this->next_backup_at->isPast();
    }

    /**
     * Calculate next backup time
     */
    public function calculateNextBackup(): Carbon
    {
        $now = now()->timezone($this->timezone);
        
        $next = match($this->frequency) {
            'hourly' => $now->addHour(),
            'every_6_hours' => $now->addHours(6),
            'every_12_hours' => $now->addHours(12),
            'daily' => $now->addDay()->setTimeFromTimeString($this->start_time ?? '02:00'),
            'weekly' => $now->next($this->day_of_week ?? 1)->setTimeFromTimeString($this->start_time ?? '02:00'),
            'monthly' => $now->addMonth()->day($this->day_of_month ?? 1)->setTimeFromTimeString($this->start_time ?? '02:00'),
        };

        return $next;
    }

    /**
     * Pause this backup
     */
    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    /**
     * Resume this backup
     */
    public function resume(): void
    {
        $this->update([
            'status' => 'active',
            'next_backup_at' => $this->calculateNextBackup(),
        ]);
    }

    /**
     * Mark as running
     */
    public function markAsRunning(): void
    {
        $this->update(['status' => 'running']);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(int $fileSize, int $duration): void
    {
        $this->update([
            'status' => 'active',
            'last_backup_at' => now(),
            'next_backup_at' => $this->calculateNextBackup(),
            'last_backup_size' => $fileSize,
            'last_backup_duration' => $duration,
            'total_backups' => $this->total_backups + 1,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
            'failed_backups' => $this->failed_backups + 1,
        ]);
    }
}
