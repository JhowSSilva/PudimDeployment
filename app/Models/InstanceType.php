<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstanceType extends Model
{
    protected $table = 'instance_types';

    protected $fillable = [
        'name',
        'architecture',
        'family',
        'vcpu',
        'memory_gib',
        'price_per_hour',
        'price_per_month',
        'network_performance',
        'is_available',
        'regions',
        'description',
    ];

    protected $casts = [
        'vcpu' => 'integer',
        'memory_gib' => 'decimal:2',
        'price_per_hour' => 'decimal:6',
        'price_per_month' => 'decimal:2',
        'is_available' => 'boolean',
        'regions' => 'array',
    ];

    /**
     * Scope for arm64 instances (Graviton)
     */
    public function scopeArm64($query)
    {
        return $query->where('architecture', 'arm64');
    }

    /**
     * Scope for x86_64 instances
     */
    public function scopeX86($query)
    {
        return $query->where('architecture', 'x86_64');
    }

    /**
     * Scope for available instances
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Check if is Graviton (ARM64) instance
     */
    public function isGraviton(): bool
    {
        return $this->architecture === 'arm64';
    }

    /**
     * Get savings percentage vs equivalent x86 instance
     */
    public function getSavingsPercentageAttribute(): ?float
    {
        if ($this->architecture !== 'arm64') {
            return null;
        }

        // Typical Graviton savings is ~40%
        return 40.0;
    }

    /**
     * Format price for display
     */
    public function getFormattedPricePerMonthAttribute(): string
    {
        return '$' . number_format($this->price_per_month, 2) . '/month';
    }

    /**
     * Get instance size (micro, small, medium, large, etc)
     */
    public function getSizeAttribute(): string
    {
        preg_match('/\.(micro|nano|small|medium|large|xlarge|2xlarge|4xlarge|8xlarge|12xlarge|16xlarge|24xlarge|32xlarge)/', $this->name, $matches);
        return $matches[1] ?? 'unknown';
    }
}
