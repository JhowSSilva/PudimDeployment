<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PipelineRun extends Model
{
    protected $fillable = [
        'pipeline_id',
        'triggered_by_user_id',
        'trigger_source',
        'status',
        'git_branch',
        'git_commit_hash',
        'git_commit_message',
        'stage_results',
        'output_log',
        'error_log',
        'duration_seconds',
        'started_at',
        'finished_at',
        'deployment_id',
    ];

    protected $casts = [
        'stage_results' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    // Relationships
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    public function approval()
    {
        return $this->hasOne(DeploymentApproval::class);
    }

    // Business Logic
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['success', 'failed', 'cancelled']);
    }

    public function start(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->pipeline->updateLastRun();
    }

    public function markSuccess(): void
    {
        $this->finish('success');
    }

    public function markFailed(string $errorMessage = null): void
    {
        if ($errorMessage) {
            $this->appendErrorLog($errorMessage);
        }
        $this->finish('failed');
    }

    public function cancel(): void
    {
        $this->finish('cancelled');
    }

    protected function finish(string $status): void
    {
        $finishedAt = now();
        $duration = $this->started_at ? $finishedAt->diffInSeconds($this->started_at) : null;

        $this->update([
            'status' => $status,
            'finished_at' => $finishedAt,
            'duration_seconds' => $duration,
        ]);
    }

    public function appendOutputLog(string $output): void
    {
        $this->update([
            'output_log' => ($this->output_log ?? '') . "\n" . $output,
        ]);
    }

    public function appendErrorLog(string $error): void
    {
        $this->update([
            'error_log' => ($this->error_log ?? '') . "\n" . $error,
        ]);

        Log::error("Pipeline Run #{$this->id} Error", [
            'pipeline_id' => $this->pipeline_id,
            'error' => $error,
        ]);
    }

    public function recordStageResult(int $stageId, array $result): void
    {
        $results = $this->stage_results ?? [];
        $results[$stageId] = $result;

        $this->update(['stage_results' => $results]);
    }

    public function getStageResult(int $stageId): ?array
    {
        return $this->stage_results[$stageId] ?? null;
    }

    public function getDurationFormatted(): string
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";
    }

    public function getStatusBadge(): string
    {
        return match($this->status) {
            'success' => 'success',
            'failed' => 'danger',
            'running' => 'info',
            'cancelled' => 'warning',
            default => 'secondary',
        };
    }
}
