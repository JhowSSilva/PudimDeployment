<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'billing_subscriptions';

    protected $fillable = [
        'team_id',
        'plan_id',
        'user_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'billing_cycle',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'ends_at',
        'amount',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'ends_at' => 'datetime',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrialing($query)
    {
        return $query->where('status', 'trialing')
            ->where('trial_ends_at', '>', now());
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('ends_at')
                  ->where('ends_at', '<', now());
            });
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'active' && (!$this->ends_at || $this->ends_at->isFuture());
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled' || $this->canceled_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->ends_at && $this->ends_at->isPast());
    }

    public function isOnGracePeriod(): bool
    {
        return $this->isCanceled() && $this->ends_at && $this->ends_at->isFuture();
    }

    public function cancel(bool $immediately = false): bool
    {
        $this->canceled_at = now();
        
        if ($immediately) {
            $this->ends_at = now();
            $this->status = 'canceled';
        } else {
            // Grace period até o fim do período atual
            $this->ends_at = $this->current_period_end ?? now()->addMonth();
        }
        
        return $this->save();
    }

    public function resume(): bool
    {
        if (!$this->isOnGracePeriod()) {
            return false;
        }
        
        $this->canceled_at = null;
        $this->ends_at = null;
        $this->status = 'active';
        
        return $this->save();
    }

    public function swap(Plan $newPlan): bool
    {
        $this->plan_id = $newPlan->id;
        $this->amount = $this->billing_cycle === 'yearly' ? $newPlan->yearly_price : $newPlan->price;
        
        // Update team plan_limits cache
        $this->team->update([
            'plan_limits' => $newPlan->getLimits(),
        ]);
        
        return $this->save();
    }

    public function getDaysUntilRenewal(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }
        
        return now()->diffInDays($this->current_period_end, false);
    }
}
