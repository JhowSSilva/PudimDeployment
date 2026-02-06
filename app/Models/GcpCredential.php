<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class GcpCredential extends Model
{
    use HasFactory;

    protected $table = 'gcp_credentials';

    protected $fillable = [
        'team_id',
        'name',
        'project_id',
        'service_account_json',
        'region',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        'service_account_json',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($credential) {
            // Set as default if it's the first credential for this team
            if ($credential->team->gcpCredentials()->count() === 1) {
                $credential->update(['is_default' => true]);
            }
        });
    }

    /**
     * Encrypt service_account_json when setting
     */
    public function setServiceAccountJsonAttribute($value): void
    {
        if ($value) {
            $this->attributes['service_account_json'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt service_account_json when getting
     */
    public function getServiceAccountJsonAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Get masked service account for display
     */
    public function getMaskedServiceAccountAttribute(): string
    {
        $json = $this->service_account_json;
        if (!$json) return 'N/A';
        
        $data = json_decode($json, true);
        return $data['client_email'] ?? 'Service Account';
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getRegionNameAttribute(): string
    {
        $regions = [
            'us-central1' => 'US Central (Iowa)',
            'us-east1' => 'US East (South Carolina)',
            'us-east4' => 'US East (Virginia)',
            'us-west1' => 'US West (Oregon)',
            'us-west2' => 'US West (Los Angeles)',
            'europe-west1' => 'Europe West (Belgium)',
            'europe-west2' => 'Europe West (London)',
            'europe-west3' => 'Europe West (Frankfurt)',
            'asia-east1' => 'Asia East (Taiwan)',
            'asia-southeast1' => 'Asia Southeast (Singapore)',
            'southamerica-east1' => 'South America East (São Paulo)',
        ];

        return $regions[$this->region] ?? $this->region;
    }
}
