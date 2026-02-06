<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'action_text',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get icon based on type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'deployment' => '🚀',
            'security' => '🔒',
            'error' => '❌',
            'warning' => '⚠️',
            'success' => '✅',
            default => 'ℹ️',
        };
    }

    /**
     * Get color class based on type
     */
    public function getColorClassAttribute(): string
    {
        return match($this->type) {
            'deployment' => 'bg-blue-100 text-blue-800 border-blue-200',
            'security' => 'bg-red-100 text-red-800 border-red-200',
            'error' => 'bg-red-100 text-red-800 border-red-200',
            'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'success' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
