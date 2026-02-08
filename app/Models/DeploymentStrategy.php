<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentStrategy extends Model
{
    protected $fillable = [
        'team_id',
        'site_id',
        'name',
        'type',
        'description',
        'config',
        'is_default',
        'requires_approval',
        'health_check_config',
        'rollback_on_failure_percentage',
    ];

    protected $casts = [
        'config' => 'array',
        'health_check_config' => 'array',
        'is_default' => 'boolean',
        'requires_approval' => 'boolean',
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

    // Business Logic
    public function isBlueGreen(): bool
    {
        return $this->type === 'blue_green';
    }

    public function isCanary(): bool
    {
        return $this->type === 'canary';
    }

    public function isRolling(): bool
    {
        return $this->type === 'rolling';
    }

    public function isRecreate(): bool
    {
        return $this->type === 'recreate';
    }

    public function needsApproval(): bool
    {
        return $this->requires_approval;
    }

    public function makeDefault(): void
    {
        // Remove default from other strategies
        static::where('team_id', $this->team_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    public function getCanaryPercentage(): int
    {
        return $this->config['canary_percentage'] ?? 10;
    }

    public function getCanarySteps(): array
    {
        return $this->config['canary_steps'] ?? [10, 25, 50, 100];
    }

    public function getRollingBatchSize(): int
    {
        return $this->config['batch_size'] ?? 1;
    }

    public function getRollingBatchDelay(): int
    {
        return $this->config['batch_delay_seconds'] ?? 30;
    }

    public function getHealthCheckInterval(): int
    {
        return $this->health_check_config['interval_seconds'] ?? 30;
    }

    public function getHealthCheckTimeout(): int
    {
        return $this->health_check_config['timeout_seconds'] ?? 10;
    }

    public function getHealthCheckThreshold(): int
    {
        return $this->health_check_config['healthy_threshold'] ?? 2;
    }

    public function shouldAutoRollback(float $errorRate): bool
    {
        if (!$this->rollback_on_failure_percentage) {
            return false;
        }

        return $errorRate >= $this->rollback_on_failure_percentage;
    }

    public function getDescription(): string
    {
        return match($this->type) {
            'blue_green' => 'Deploy to parallel environment and switch traffic instantly',
            'canary' => 'Gradually roll out to increasing percentages of users',
            'rolling' => 'Update instances in batches with health checks',
            'recreate' => 'Shut down old version and deploy new version',
            default => $this->description ?? 'Custom deployment strategy',
        };
    }
}
