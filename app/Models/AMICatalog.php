<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AMICatalog extends Model
{
    protected $table = 'ami_catalog';

    protected $fillable = [
        'region',
        'ami_id',
        'os_name',
        'os_version',
        'architecture',
        'root_device_type',
        'virtualization_type',
        'is_active',
        'ami_created_at',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ami_created_at' => 'datetime',
    ];

    /**
     * Scope for specific region
     */
    public function scopeForRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope for specific architecture
     */
    public function scopeForArchitecture($query, string $architecture)
    {
        return $query->where('architecture', $architecture);
    }

    /**
     * Scope for specific OS version
     */
    public function scopeForOsVersion($query, string $version)
    {
        return $query->where('os_version', $version);
    }

    /**
     * Scope for active AMIs only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the latest Ubuntu AMI for a region and architecture
     */
    public static function getLatestUbuntu(string $region, string $architecture): ?self
    {
        return self::forRegion($region)
            ->forArchitecture($architecture)
            ->active()
            ->orderBy('os_version', 'desc')
            ->first();
    }

    /**
     * Get formatted OS name with version
     */
    public function getFormattedOsAttribute(): string
    {
        return $this->os_name;
    }
}
