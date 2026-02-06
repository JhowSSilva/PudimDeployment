<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'host',
        'port',
        'username',
        'password',
        'ssh_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
    ];

    protected $hidden = [
        'password',
        'ssh_key',
    ];

    /**
     * Get the team that owns the server
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the databases on this server
     */
    public function databases(): HasMany
    {
        return $this->hasMany(BackupDatabase::class, 'server_id');
    }

    /**
     * Check if server is reachable
     */
    public function isReachable(): bool
    {
        // TODO: Implement SSH connection test
        return true;
    }

    /**
     * Get SSH credentials (decrypt password if needed)
     */
    public function getDecryptedPassword(): ?string
    {
        return $this->password ? decrypt($this->password) : null;
    }

    /**
     * Set encrypted password
     */
    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? encrypt($value) : null;
    }
}
