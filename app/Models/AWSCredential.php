<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AWSCredential extends Model
{
    protected $table = 'aws_credentials';

    protected $fillable = [
        'name',
        'access_key_id',
        'secret_access_key',
        'default_region',
        'is_active',
        'last_validated_at',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_validated_at' => 'datetime',
    ];

    protected $hidden = [
        'access_key_id',
        'secret_access_key',
    ];

    /**
     * Encrypt access_key_id when setting
     */
    public function setAccessKeyIdAttribute($value): void
    {
        $this->attributes['access_key_id'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt access_key_id when getting
     */
    public function getAccessKeyIdAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Encrypt secret_access_key when setting
     */
    public function setSecretAccessKeyAttribute($value): void
    {
        $this->attributes['secret_access_key'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt secret_access_key when getting
     */
    public function getSecretAccessKeyAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Get servers using this AWS credential
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class, 'aws_credential_id');
    }

    /**
     * Get masked access key for display
     */
    public function getMaskedAccessKeyAttribute(): string
    {
        $key = $this->access_key_id;
        if (!$key) return '****';
        return substr($key, 0, 8) . '...' . substr($key, -4);
    }

    /**
     * Check if credentials are valid (simple check)
     */
    public function isValid(): bool
    {
        return !empty($this->access_key_id) && 
               !empty($this->secret_access_key) &&
               $this->is_active;
    }
}
