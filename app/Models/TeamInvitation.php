<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeamInvitation extends Model
{
    protected $fillable = [
        'team_id',
        'invited_by',
        'email',
        'token',
        'role',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = Carbon::now()->addDays(7);
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() || $this->status === 'expired';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function accept(User $user): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        // Attach user to team with role
        $this->team->users()->attach($user->id, ['role' => $this->role]);

        // Mark invitation as accepted
        $this->update(['status' => 'accepted']);

        return true;
    }

    public function reject(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->update(['status' => 'rejected']);

        return true;
    }

    public function getInviteUrlAttribute(): string
    {
        return url("/invites/{$this->token}");
    }

    public function getRoleBadgeAttribute(): string
    {
        return match($this->role) {
            'admin' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Admin</span>',
            'manager' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Gerente</span>',
            'member' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Membro</span>',
            'viewer' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Visualizador</span>',
        };
    }
}
