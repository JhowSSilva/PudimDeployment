<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Database extends Model
{
    use HasFactory;

    protected $table = 'databases';

    protected $fillable = [
        'server_id',
        'name',
        'type',
        'status',
        'size_mb',
        'last_backup_at',
    ];

    protected $casts = [
        'size_mb' => 'integer',
        'last_backup_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(DatabaseUser::class);
    }

    /**
     * Scope for active databases
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for MySQL databases
     */
    public function scopeMysql($query)
    {
        return $query->where('type', 'mysql');
    }

    /**
     * Scope for PostgreSQL databases
     */
    public function scopePostgresql($query)
    {
        return $query->where('type', 'postgresql');
    }

    /**
     * Get human readable size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size_mb) {
            return 'Unknown';
        }

        if ($this->size_mb < 1024) {
            return number_format($this->size_mb, 1) . ' MB';
        }

        return number_format($this->size_mb / 1024, 1) . ' GB';
    }

    /**
     * Check if backup is needed
     */
    public function needsBackup(int $hours = 24): bool
    {
        return !$this->last_backup_at || $this->last_backup_at->addHours($hours)->isPast();
    }
}