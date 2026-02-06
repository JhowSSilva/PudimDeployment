<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SSHKey extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'bits',
        'public_key',
        'private_key_encrypted',
        'fingerprint',
        'comment',
        'has_passphrase',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'private_key_encrypted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_passphrase' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the SSH key.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get SSH keys for a specific user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByUserId(int $userId)
    {
        return self::where('user_id', $userId)
            ->select(['id', 'name', 'type', 'bits', 'fingerprint', 'comment', 'has_passphrase', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a single SSH key by ID for a specific user.
     *
     * @param int $keyId
     * @param int $userId
     * @return SSHKey|null
     */
    public static function getById(int $keyId, int $userId): ?self
    {
        return self::where('id', $keyId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Delete an SSH key if it belongs to the user.
     *
     * @param int $keyId
     * @param int $userId
     * @return bool
     */
    public static function deleteKey(int $keyId, int $userId): bool
    {
        return self::where('id', $keyId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }
}
