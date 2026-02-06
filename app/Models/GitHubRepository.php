<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GitHubRepository extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'github_id',
        'name',
        'full_name',
        'description',
        'private',
        'language',
        'default_branch',
        'clone_url',
        'ssh_url',
        'html_url',
        'github_created_at',
        'github_updated_at',
        'last_synced_at',
        'metadata',
    ];

    protected $casts = [
        'private' => 'boolean',
        'github_created_at' => 'datetime',
        'github_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(GitHubWorkflow::class, 'repository_id');
    }

    public function workflowRuns(): HasMany
    {
        return $this->hasMany(GitHubWorkflowRun::class, 'repository_id');
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(GitHubWebhookEvent::class, 'repository_id');
    }

    public function pages(): HasOne
    {
        return $this->hasOne(GitHubPages::class, 'repository_id');
    }

    public function latestWorkflowRuns()
    {
        return $this->workflowRuns()
            ->orderBy('github_created_at', 'desc')
            ->limit(10);
    }

    public function getStatusAttribute(): string
    {
        $latestRun = $this->workflowRuns()->latest('github_created_at')->first();
        
        if (!$latestRun) {
            return 'no_runs';
        }

        if ($latestRun->status === 'in_progress') {
            return 'deploying';
        }

        return $latestRun->conclusion ?? 'unknown';
    }
}
