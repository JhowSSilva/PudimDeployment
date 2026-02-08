<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineStage extends Model
{
    protected $fillable = [
        'pipeline_id',
        'name',
        'type',
        'order',
        'commands',
        'docker_image',
        'artifacts',
        'parallel',
        'allow_failure',
        'when',
        'timeout_minutes',
        'environment_variables',
        'depends_on_stage_id',
    ];

    protected $casts = [
        'commands' => 'array',
        'artifacts' => 'array',
        'environment_variables' => 'array',
        'parallel' => 'boolean',
        'allow_failure' => 'boolean',
    ];

    // Relationships
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function dependsOnStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'depends_on_stage_id');
    }

    public function dependentStages()
    {
        return $this->hasMany(PipelineStage::class, 'depends_on_stage_id');
    }

    // Business Logic
    public function isBuildStage(): bool
    {
        return $this->type === 'build';
    }

    public function isTestStage(): bool
    {
        return $this->type === 'test';
    }

    public function isDeployStage(): bool
    {
        return $this->type === 'deploy';
    }

    public function canRunInParallel(): bool
    {
        return $this->parallel && !$this->depends_on_stage_id;
    }

    public function shouldRunAlways(): bool
    {
        return $this->when === 'always';
    }

    public function shouldRunOnSuccess(): bool
    {
        return $this->when === 'on_success';
    }

    public function shouldRunOnFailure(): bool
    {
        return $this->when === 'on_failure';
    }

    public function requiresManualApproval(): bool
    {
        return $this->when === 'manual';
    }

    public function canFailSafely(): bool
    {
        return $this->allow_failure;
    }

    public function hasDependencies(): bool
    {
        return $this->depends_on_stage_id !== null;
    }

    public function getCommandsAsString(): string
    {
        return implode("\n", $this->commands ?? []);
    }

    public function mergeEnvironmentVariables(): array
    {
        $pipelineVars = $this->pipeline->environment_variables ?? [];
        $stageVars = $this->environment_variables ?? [];
        
        return array_merge($pipelineVars, $stageVars);
    }

    public function shouldRun(string $previousStageStatus): bool
    {
        if ($this->shouldRunAlways()) {
            return true;
        }

        if ($this->shouldRunOnSuccess() && $previousStageStatus === 'success') {
            return true;
        }

        if ($this->shouldRunOnFailure() && $previousStageStatus === 'failed') {
            return true;
        }

        return false;
    }
}
