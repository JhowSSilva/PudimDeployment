<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AlertRule extends Model
{
    // Condition constants
    const CONDITION_GREATER_THAN = 'greater_than';
    const CONDITION_LESS_THAN = 'less_than';
    const CONDITION_EQUALS = 'equals';
    const CONDITION_NOT_EQUALS = 'not_equals';
    
    // Severity constants
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_CRITICAL = 'critical';
    
    protected $fillable = [
        'team_id',
        'server_id',
        'site_id',
        'name',
        'description',
        'metric_type',
        'condition',
        'threshold',
        'duration',
        'severity',
        'channels',
        'cooldown',
        'is_active',
        'last_triggered_at',
        'trigger_count',
    ];
    
    protected $casts = [
        'channels' => 'array',
        'threshold' => 'decimal:2',
        'duration' => 'integer',
        'cooldown' => 'integer',
        'trigger_count' => 'integer',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];
    
    /**
     * Get the team that owns the alert rule.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    /**
     * Get the server (if rule is server-specific).
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
    
    /**
     * Get the site (if rule is site-specific).
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
    
    /**
     * Get alerts triggered by this rule.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
    
    /**
     * Scope for active rules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for specific metric type.
     */
    public function scopeForMetric(Builder $query, string $metricType): Builder
    {
        return $query->where('metric_type', $metricType);
    }
    
    /**
     * Check if rule should trigger based on value.
     */
    public function shouldTrigger(float $value): bool
    {
        return match ($this->condition) {
            self::CONDITION_GREATER_THAN => $value > $this->threshold,
            self::CONDITION_LESS_THAN => $value < $this->threshold,
            self::CONDITION_EQUALS => $value == $this->threshold,
            self::CONDITION_NOT_EQUALS => $value != $this->threshold,
            default => false,
        };
    }
    
    /**
     * Check if rule is in cooldown period.
     */
    public function isInCooldown(): bool
    {
        if (!$this->last_triggered_at) {
            return false;
        }
        
        return $this->last_triggered_at->addSeconds($this->cooldown)->isFuture();
    }
    
    /**
     * Trigger the alert rule.
     */
    public function trigger(float $currentValue, $resource = null): Alert
    {
        $this->increment('trigger_count');
        $this->update(['last_triggered_at' => now()]);
        
        $alert = Alert::create([
            'team_id' => $this->team_id,
            'alert_rule_id' => $this->id,
            'server_id' => $resource instanceof Server ? $resource->id : $this->server_id,
            'site_id' => $resource instanceof Site ? $resource->id : $this->site_id,
            'title' => $this->name,
            'message' => $this->generateAlertMessage($currentValue),
            'severity' => $this->severity,
            'current_value' => $currentValue,
            'threshold_value' => $this->threshold,
            'status' => 'open',
        ]);
        
        // TODO: Send notifications via configured channels
        
        return $alert;
    }
    
    /**
     * Generate alert message.
     */
    protected function generateAlertMessage(float $currentValue): string
    {
        $conditionText = match ($this->condition) {
            self::CONDITION_GREATER_THAN => 'exceeded',
            self::CONDITION_LESS_THAN => 'fell below',
            self::CONDITION_EQUALS => 'equals',
            self::CONDITION_NOT_EQUALS => 'is not equal to',
            default => 'met condition for',
        };
        
        return "{$this->metric_type} {$conditionText} threshold: current value is {$currentValue}, threshold is {$this->threshold}";
    }
}
