<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SSHConnectionLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'server_id',
        'key_id',
        'ip_address',
        'connected_at',
        'disconnected_at',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    /**
     * Get the user that owns the connection log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the server for the connection log.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the SSH key used for the connection.
     */
    public function sshKey(): BelongsTo
    {
        return $this->belongsTo(SSHKey::class, 'key_id');
    }

    /**
     * Get connection logs for a specific user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByUserId(int $userId, int $limit = 50)
    {
        return self::where('user_id', $userId)
            ->with(['server:id,name', 'sshKey:id,name'])
            ->orderBy('connected_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Log a new SSH connection.
     *
     * @param int $userId
     * @param int $serverId
     * @param int|null $keyId
     * @param string|null $ipAddress
     * @return SSHConnectionLog
     */
    public static function logConnection(int $userId, int $serverId, ?int $keyId, ?string $ipAddress): self
    {
        return self::create([
            'user_id' => $userId,
            'server_id' => $serverId,
            'key_id' => $keyId,
            'ip_address' => $ipAddress,
            'connected_at' => now(),
            'status' => 'success',
        ]);
    }

    /**
     * Mark connection as failed.
     *
     * @param string $errorMessage
     * @return void
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'disconnected_at' => now(),
        ]);
    }

    /**
     * Mark connection as disconnected.
     *
     * @return void
     */
    public function markAsDisconnected(): void
    {
        $this->update([
            'status' => 'disconnected',
            'disconnected_at' => now(),
        ]);
    }
}
