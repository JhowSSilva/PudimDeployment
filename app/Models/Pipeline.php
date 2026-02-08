<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'site_id',
        'name',
        'description',
        'trigger_type',
        'trigger_config',
        'status',
        'auto_deploy',
        'timeout_minutes',
        'environment_variables',
        'retention_days',
        'last_run_at',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'environment_variables' => 'array',
        'auto_deploy' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(PipelineRun::class)->latest();
    }

    public function latestRun(): HasMany
    {
        return $this->hasMany(PipelineRun::class)->latest()->limit(1);
    }

    // Business Logic
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function canRun(): bool
    {
        return $this->isActive() && $this->stages()->count() > 0;
    }

    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function disable(): void
    {
        $this->update(['status' => 'disabled']);
    }

    public function updateLastRun(): void
    {
        $this->update(['last_run_at' => now()]);
    }

    public function getSuccessRate(): float
    {
        $totalRuns = $this->runs()->count();
        if ($totalRuns === 0) {
            return 0;
        }

        $successfulRuns = $this->runs()->where('status', 'success')->count();
        return round(($successfulRuns / $totalRuns) * 100, 2);
    }

    public function getAverageDuration(): ?int
    {
        return $this->runs()
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');
    }

    public function getLastSuccessfulRun(): ?PipelineRun
    {
        return $this->runs()
            ->where('status', 'success')
            ->first();
    }

    public function shouldTrigger(string $event, array $data = []): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Check trigger type
        if ($this->trigger_type === 'manual') {
            return false; // Manual pipelines don't auto-trigger
        }

        if ($this->trigger_type === 'push' && $event === 'git_push') {
            return $this->matchesBranchFilter($data['branch'] ?? '');
        }

        if ($this->trigger_type === 'pull_request' && $event === 'git_pull_request') {
            return true;
        }

        if ($this->trigger_type === 'webhook' && $event === 'webhook') {
            return true;
        }

        return false;
    }

    protected function matchesBranchFilter(?string $branch): bool
    {
        if (empty($branch)) {
            return false;
        }

        $filters = $this->trigger_config['branch_filters'] ?? [];
        if (empty($filters)) {
            return true; // No filter = all branches
        }

        foreach ($filters as $filter) {
            if (fnmatch($filter, $branch)) {
                return true;
            }
        }

        return false;
    }

    public function cleanupOldRuns(): int
    {
        $cutoffDate = now()->subDays($this->retention_days);
        
        return $this->runs()
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }
}
