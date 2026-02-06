<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GitHubWorkflowRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'repository_id',
        'github_id',
        'name',
        'head_branch',
        'head_sha',
        'status',
        'conclusion',
        'event',
        'html_url',
        'github_created_at',
        'github_updated_at',
        'started_at',
        'completed_at',
        'run_number',
        'metadata',
    ];

    protected $casts = [
        'github_created_at' => 'datetime',
        'github_updated_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(GitHubWorkflow::class, 'workflow_id');
    }

    public function repository(): BelongsTo
    {
        return $this->belongsTo(GitHubRepository::class, 'repository_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isSuccess(): bool
    {
        return $this->isCompleted() && $this->conclusion === 'success';
    }

    public function isFailed(): bool
    {
        return $this->isCompleted() && in_array($this->conclusion, ['failure', 'cancelled', 'timed_out']);
    }

    public function isRunning(): bool
    {
        return in_array($this->status, ['queued', 'in_progress']);
    }

    public function getDurationInSeconds(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->conclusion ?? $this->status) {
            'success' => 'green',
            'failure', 'cancelled', 'timed_out' => 'red',
            'in_progress' => 'blue',
            'queued' => 'yellow',
            default => 'gray',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->conclusion ?? $this->status) {
            'success' => '✓',
            'failure' => '✗',
            'cancelled' => '⊘',
            'in_progress' => '⟳',
            'queued' => '⋯',
            default => '?',
        };
    }
}
