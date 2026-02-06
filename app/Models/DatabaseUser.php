<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseUser extends Model
{
    use HasFactory;

    protected $table = 'database_users';

    protected $fillable = [
        'database_id',
        'username',
        'privileges',
        'status',
    ];

    protected $casts = [
        'privileges' => 'array',
    ];

    public function database(): BelongsTo
    {
        return $this->belongsTo(Database::class);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if user has privilege
     */
    public function hasPrivilege(string $privilege): bool
    {
        return in_array($privilege, $this->privileges) || in_array('ALL', $this->privileges);
    }

    /**
     * Get formatted privileges string
     */
    public function getFormattedPrivilegesAttribute(): string
    {
        if (in_array('ALL', $this->privileges)) {
            return 'All Privileges';
        }

        return implode(', ', $this->privileges);
    }
}