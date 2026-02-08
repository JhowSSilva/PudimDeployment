<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class UsageMetric extends Model
{
    use HasFactory;

    protected $table = 'billing_usage_metrics';

    protected $fillable = [
        'team_id',
        'metric_type',
        'current_value',
        'limit_value',
        'usage_percentage',
        'period_start',
        'period_end',
        'details',
        'last_calculated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'details' => 'array',
        'last_calculated_at' => 'datetime',
        'usage_percentage' => 'decimal:2',
    ];

    // Metric types constants
    const TYPE_SERVERS = 'servers';
    const TYPE_SITES = 'sites';
    const TYPE_DEPLOYMENTS = 'deployments';
    const TYPE_BACKUPS = 'backups';
    const TYPE_STORAGE = 'storage';
    const TYPE_TEAM_MEMBERS = 'team_members';

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // Scopes
    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    public function scopeCurrentPeriod($query)
    {
        return $query->where('period_start', '<=', now())
                     ->where('period_end', '>=', now());
    }

    public function scopeOverLimit($query, int $threshold = 80)
    {
        return $query->where('usage_percentage', '>=', $threshold);
    }

    // Methods
    public function isOverLimit(int $threshold = 100): bool
    {
        return $this->usage_percentage >= $threshold;
    }

    public function isNearLimit(int $threshold = 80): bool
    {
        return $this->usage_percentage >= $threshold && $this->usage_percentage < 100;
    }

    public function recalculate(): void
    {
        $team = $this->team;
        $plan = $team->plan;
        
        if (!$plan) {
            return;
        }
        
        switch ($this->metric_type) {
            case self::TYPE_SERVERS:
                $this->current_value = $team->servers()->count();
                $this->limit_value = $plan->max_servers;
                break;
                
            case self::TYPE_SITES:
                $this->current_value = Site::whereHas('server', function ($q) use ($team) {
                    $q->where('team_id', $team->id);
                })->count();
                $this->limit_value = $plan->max_servers * $plan->max_sites_per_server;
                break;
                
            case self::TYPE_DEPLOYMENTS:
                $this->current_value = Deployment::whereHas('site.server', function ($q) use ($team) {
                    $q->where('team_id', $team->id);
                })
                ->whereBetween('created_at', [$this->period_start, $this->period_end])
                ->count();
                $this->limit_value = $plan->max_deployments_per_month;
                break;
                
            case self::TYPE_BACKUPS:
                // Assumindo que existe modelo Backup
                $this->current_value = 0; // Implementar quando Backup model existir
                $this->limit_value = $plan->max_backups;
                break;
                
            case self::TYPE_STORAGE:
                // Calcular storage usado (em GB)
                $this->current_value = 0; // Implementar cálculo real
                $this->limit_value = $plan->max_storage_gb;
                break;
                
            case self::TYPE_TEAM_MEMBERS:
                $this->current_value = $team->users()->count();
                $this->limit_value = $plan->max_team_members;
                break;
        }
        
        $this->usage_percentage = $this->limit_value > 0 
            ? ($this->current_value / $this->limit_value) * 100 
            : 0;
            
        $this->last_calculated_at = now();
        $this->save();
    }

    /**
     * Calcula métricas para um team
     */
    public static function calculateForTeam(Team $team): void
    {
        $plan = $team->plan;
        if (!$plan) {
            return;
        }
        
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        
        $metricTypes = [
            self::TYPE_SERVERS,
            self::TYPE_SITES,
            self::TYPE_DEPLOYMENTS,
            self::TYPE_BACKUPS,
            self::TYPE_STORAGE,
            self::TYPE_TEAM_MEMBERS,
        ];
        
        foreach ($metricTypes as $type) {
            $metric = self::updateOrCreate(
                [
                    'team_id' => $team->id,
                    'metric_type' => $type,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                ],
                [
                    'current_value' => 0,
                    'limit_value' => 0,
                    'usage_percentage' => 0,
                ]
            );
            
            $metric->recalculate();
        }
    }
}
