<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class CloudflareAccount extends Model
{
    protected $fillable = [
        'name',
        'api_token',
        'account_id',
        'zone_id',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_token',
    ];

    /**
     * Encrypt api_token when setting
     */
    public function setApiTokenAttribute($value): void
    {
        $this->attributes['api_token'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt api_token when getting
     */
    public function getApiTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Get sites using this Cloudflare account
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
