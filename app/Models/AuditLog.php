<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'action',
        'model_type',
        'model_id',
        'changes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team associated with the action
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the auditable model
     */
    public function auditable()
    {
        return $this->morphTo('model');
    }

    /**
     * Log an action
     */
    public static function logAction(
        string $action,
        ?Model $model = null,
        ?array $changes = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'team_id' => auth()->user()?->current_team_id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'changes' => $changes ?? ($model?->getChanges() ?: null),
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific team
     */
    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope for specific user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get human-readable action description
     */
    public function getDescriptionAttribute(): string
    {
        $userName = $this->user?->name ?? 'System';
        $modelName = $this->model_type ? class_basename($this->model_type) : 'item';

        return match($this->action) {
            'created' => "{$userName} created {$modelName} #{$this->model_id}",
            'updated' => "{$userName} updated {$modelName} #{$this->model_id}",
            'deleted' => "{$userName} deleted {$modelName} #{$this->model_id}",
            'deployed' => "{$userName} deployed site #{$this->model_id}",
            'login' => "{$userName} logged in",
            'logout' => "{$userName} logged out",
            default => "{$userName} performed {$this->action}",
        };
    }
}
