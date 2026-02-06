<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GitHubWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'repository_id',
        'github_id',
        'name',
        'path',
        'state',
        'github_created_at',
        'github_updated_at',
        'metadata',
    ];

    protected $casts = [
        'github_created_at' => 'datetime',
        'github_updated_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(GitHubRepository::class, 'repository_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(GitHubWorkflowRun::class, 'workflow_id');
    }

    public function latestRuns()
    {
        return $this->runs()->orderBy('github_created_at', 'desc')->limit(10);
    }

    public function isActive(): bool
    {
        return $this->state === 'active';
    }
}
