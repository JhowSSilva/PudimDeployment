<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SSLCertificate extends Model
{
    use HasFactory;

    protected $table = 'ssl_certificates';

    protected $fillable = [
        'site_id',
        'domains',
        'provider',
        'status',
        'cert_path',
        'key_path',
        'expires_at',
        'renewed_at',
    ];

    protected $casts = [
        'domains' => 'array',
        'expires_at' => 'datetime',
        'renewed_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Check if certificate is expiring soon
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at && $this->expires_at->diffInDays(now()) <= $days;
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        return $this->expires_at ? $this->expires_at->diffInDays(now()) : null;
    }

    /**
     * Check if certificate is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Get the primary domain (first domain in the array)
     */
    public function getPrimaryDomainAttribute(): ?string
    {
        return $this->domains[0] ?? null;
    }

    /**
     * Scope for expiring certificates
     */
    public function scopeExpiring($query, int $days = 30)
    {
        return $query->where('expires_at', '<=', Carbon::now()->addDays($days))
                    ->where('status', 'active');
    }

    /**
     * Scope for Let's Encrypt certificates
     */
    public function scopeLetsEncrypt($query)
    {
        return $query->where('provider', 'letsencrypt');
    }

    /**
     * Scope for custom certificates
     */
    public function scopeCustom($query)
    {
        return $query->where('provider', 'custom');
    }
}