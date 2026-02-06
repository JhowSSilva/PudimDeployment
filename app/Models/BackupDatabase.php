<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupDatabase extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'server_id',
        'type',
        'name',
        'username',
        'password',
        'port',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the team that owns the database
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the server this database is on
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(BackupServer::class, 'server_id');
    }

    /**
     * Get backup configurations for this database
     */
    public function backupConfigurations(): HasMany
    {
        return $this->hasMany(BackupConfiguration::class, 'database_id');
    }

    /**
     * Get active backup configurations
     */
    public function activeBackups(): HasMany
    {
        return $this->hasMany(BackupConfiguration::class, 'database_id')
            ->where('status', 'active');
    }

    /**
     * Get default port for database type
     */
    public static function getDefaultPort(string $type): int
    {
        return match($type) {
            'postgresql' => 5432,
            'mysql' => 3306,
            'mongodb' => 27017,
            'redis' => 6379,
            default => 0,
        };
    }

    /**
     * Get decrypted password
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

    /**
     * Get connection string for this database
     */
    public function getConnectionString(): string
    {
        return match($this->type) {
            'postgresql' => sprintf('host=%s port=%d dbname=%s user=%s password=%s',
                $this->server->host,
                $this->port,
                $this->name,
                $this->username,
                $this->getDecryptedPassword()
            ),
            'mysql' => sprintf('-h %s -P %d -u %s -p%s %s',
                $this->server->host,
                $this->port,
                $this->username,
                $this->getDecryptedPassword(),
                $this->name
            ),
            default => '',
        };
    }
}
