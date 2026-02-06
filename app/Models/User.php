<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id',
        'github_id',
        'github_username',
        'github_token',
        'github_token_expires_at',
        'github_refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'github_token',
        'github_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'github_token_expires_at' => 'datetime',
        ];
    }

    // Relationships
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function githubRepositories(): HasMany
    {
        return $this->hasMany(GitHubRepository::class);
    }

    public function hasGitHubConnected(): bool
    {
        return !empty($this->github_token);
    }

    public function getGitHubToken(): ?string
    {
        return $this->github_token ? decrypt($this->github_token) : null;
    }

    public function setGitHubToken(?string $token): void
    {
        $this->github_token = $token ? encrypt($token) : null;
    }

    /**
     * Get the current team relationship.
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Get the current team instance with fallback.
     */
    public function getCurrentTeam(): ?Team
    {
        if ($this->current_team_id) {
            return $this->currentTeam;
        }
        return $this->teams()->first() ?? $this->ownedTeams()->where('personal_team', true)->first();
    }

    public function switchTeam(Team $team): bool
    {
        if ($this->ownedTeams()->where('id', $team->id)->exists() || $this->teams()->where('teams.id', $team->id)->exists()) {
            $this->update(['current_team_id' => $team->id]);
            return true;
        }
        return false;
    }

    public function isTeamOwner(Team $team): bool
    {
        return $this->id === $team->user_id;
    }

    public function hasTeamRole(Team $team, string $role): bool
    {
        return $this->teams()->where('teams.id', $team->id)->wherePivot('role', $role)->exists();
    }
}
