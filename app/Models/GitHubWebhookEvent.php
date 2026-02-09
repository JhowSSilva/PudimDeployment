<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GitHubWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'repository_id',
        'event_type',
        'delivery_id',
        'signature',
        'payload',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $hidden = [
        'signature',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(GitHubRepository::class, 'repository_id');
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'processed_at' => now(),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, ['completed', 'failed']);
    }
}
