<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class DigitalOceanCredential extends Model
{
    use HasFactory;

    protected $table = 'digitalocean_credentials';

    protected $fillable = [
        'team_id',
        'name',
        'api_token',
        'region',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        'api_token',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($credential) {
            // Set as default if it's the first credential for this team
            if ($credential->team->digitalOceanCredentials()->count() === 1) {
                $credential->update(['is_default' => true]);
            }
        });
    }

    /**
     * Encrypt api_token when setting
     */
    public function setApiTokenAttribute($value): void
    {
        if ($value) {
            $this->attributes['api_token'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt api_token when getting
     */
    public function getApiTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Get masked API token for display
     */
    public function getMaskedApiTokenAttribute(): string
    {
        return '••••••••••••••••';
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getRegionNameAttribute(): string
    {
        $regions = [
            'nyc1' => 'New York 1',
            'nyc3' => 'New York 3',
            'sfo3' => 'San Francisco 3',
            'ams3' => 'Amsterdam 3',
            'sgp1' => 'Singapore 1',
            'lon1' => 'London 1',
            'fra1' => 'Frankfurt 1',
            'tor1' => 'Toronto 1',
            'blr1' => 'Bangalore 1',
            'syd1' => 'Sydney 1',
        ];

        return $regions[$this->region] ?? $this->region;
    }
}
