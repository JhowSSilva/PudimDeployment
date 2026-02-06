<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'slug',
        'personal_team',
    ];

    protected $casts = [
        'personal_team' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    public function managers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'member');
    }

    public function viewers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'viewer');
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function userRole(User $user): ?string
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;
        return $pivot?->role;
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function userCan(User $user, string $action): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        $role = $this->userRole($user);
        
        return match($action) {
            'manage-team' => in_array($role, ['admin', 'manager']),
            'manage-members' => $role === 'admin',
            'create-resources' => in_array($role, ['admin', 'manager', 'member']),
            'view-resources' => in_array($role, ['admin', 'manager', 'member', 'viewer']),
            'delete-resources' => in_array($role, ['admin', 'manager']),
            default => false,
        };
    }

    public function azureCredentials(): HasMany
    {
        return $this->hasMany(AzureCredential::class);
    }

    public function gcpCredentials(): HasMany
    {
        return $this->hasMany(GcpCredential::class);
    }

    public function digitalOceanCredentials(): HasMany
    {
        return $this->hasMany(DigitalOceanCredential::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function getRoleBadgeAttribute(): string
    {
        return match(auth()->user()?->teams()->where('teams.id', $this->id)->first()?->pivot?->role) {
            'admin' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Admin</span>',
            'manager' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Gerente</span>',
            'member' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Membro</span>',
            'viewer' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Visualizador</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Proprietário</span>',
        };
    }
}
