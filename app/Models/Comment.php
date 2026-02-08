<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'commentable_type',
        'commentable_id',
        'body',
        'mentions',
        'parent_id',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'mentions' => 'array',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    protected $with = ['user'];

    protected $appends = ['time_since'];

    /**
     * Get the team that owns the comment.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user that created the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for threaded comments).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get the commentable model (Server, Site, etc).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get only top-level comments.
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get comments for a specific resource.
     */
    public function scopeFor(Builder $query, string $type, int $id): Builder
    {
        return $query->where('commentable_type', $type)
                     ->where('commentable_id', $id);
    }

    /**
     * Scope to get recent comments.
     */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Mark comment as edited.
     */
    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Get mentioned users.
     */
    public function mentionedUsers()
    {
        if (empty($this->mentions)) {
            return collect();
        }

        return User::whereIn('id', $this->mentions)->get();
    }

    /**
     * Check if comment can be edited by user.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id && 
               $this->created_at->gt(now()->subHours(24));
    }

    /**
     * Check if comment can be deleted by user.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->user_id === $user->id || 
               $user->ownsTeam($this->team);
    }

    /**
     * Get time since comment was created.
     */
    public function getTimeSinceAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
