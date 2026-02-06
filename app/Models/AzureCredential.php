<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class AzureCredential extends Model
{
    use HasFactory;

    protected $table = 'azure_credentials';

    protected $fillable = [
        'team_id',
        'name',
        'subscription_id',
        'tenant_id',
        'client_id',
        'client_secret',
        'region',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        'client_secret',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($credential) {
            // Set as default if it's the first credential for this team
            if ($credential->team->azureCredentials()->count() === 1) {
                $credential->update(['is_default' => true]);
            }
        });
    }

    /**
     * Encrypt client_secret when setting
     */
    public function setClientSecretAttribute($value): void
    {
        if ($value) {
            $this->attributes['client_secret'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt client_secret when getting
     */
    public function getClientSecretAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Get masked client secret for display
     */
    public function getMaskedClientSecretAttribute(): string
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
            'eastus' => 'East US',
            'eastus2' => 'East US 2',
            'westus' => 'West US',
            'westus2' => 'West US 2',
            'centralus' => 'Central US',
            'northeurope' => 'North Europe',
            'westeurope' => 'West Europe',
            'southeastasia' => 'Southeast Asia',
            'eastasia' => 'East Asia',
            'brazilsouth' => 'Brazil South',
        ];

        return $regions[$this->region] ?? $this->region;
    }
}
