<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'stripe_price_id',
        'stripe_yearly_price_id',
        'max_servers',
        'max_sites_per_server',
        'max_deployments_per_month',
        'max_backups',
        'max_team_members',
        'max_storage_gb',
        'has_ssl_auto_renewal',
        'has_priority_support',
        'has_advanced_analytics',
        'has_custom_domains',
        'has_api_access',
        'has_audit_logs',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'max_servers' => 'integer',
        'max_sites_per_server' => 'integer',
        'max_deployments_per_month' => 'integer',
        'max_backups' => 'integer',
        'max_team_members' => 'integer',
        'max_storage_gb' => 'integer',
        'has_ssl_auto_renewal' => 'boolean',
        'has_priority_support' => 'boolean',
        'has_advanced_analytics' => 'boolean',
        'has_custom_domains' => 'boolean',
        'has_api_access' => 'boolean',
        'has_audit_logs' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeFree($query)
    {
        return $query->where('slug', 'free');
    }

    // Methods
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function getMonthlyPriceFormatted(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getYearlyPriceFormatted(): string
    {
        return '$' . number_format($this->yearly_price, 2);
    }

    public function getYearlySavings(): float
    {
        $monthlyTotal = $this->price * 12;
        return $monthlyTotal - $this->yearly_price;
    }

    public function getLimits(): array
    {
        return [
            'servers' => $this->max_servers,
            'sites_per_server' => $this->max_sites_per_server,
            'deployments_per_month' => $this->max_deployments_per_month,
            'backups' => $this->max_backups,
            'team_members' => $this->max_team_members,
            'storage_gb' => $this->max_storage_gb,
        ];
    }

    public function getFeatures(): array
    {
        return [
            'ssl_auto_renewal' => $this->has_ssl_auto_renewal,
            'priority_support' => $this->has_priority_support,
            'advanced_analytics' => $this->has_advanced_analytics,
            'custom_domains' => $this->has_custom_domains,
            'api_access' => $this->has_api_access,
            'audit_logs' => $this->has_audit_logs,
        ];
    }
}
