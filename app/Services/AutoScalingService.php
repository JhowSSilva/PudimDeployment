<?php

namespace App\Services;

use App\Models\ScalingPolicy;
use App\Models\ServerPool;
use App\Models\Server;
use Illuminate\Support\Facades\Log;

class AutoScalingService
{
    /**
     * Evaluate a scaling policy and determine if scaling is needed
     */
    public function evaluatePolicy(ScalingPolicy $policy): array
    {
        if (!$policy->is_active) {
            return ['should_scale' => false, 'reason' => 'Policy is inactive'];
        }

        if ($policy->isInCooldown()) {
            return [
                'should_scale' => false,
                'reason' => 'Policy is in cooldown period',
                'next_scaling_time' => $policy->next_scaling_time
            ];
        }

        if (!$policy->serverPool) {
            return ['should_scale' => false, 'reason' => 'No server pool associated'];
        }

        $pool = $policy->serverPool;

        // Handle schedule-based policies
        if ($policy->type === 'schedule' && $policy->schedule) {
            return $this->evaluateSchedulePolicy($policy, $pool);
        }

        // Get current metric value
        $currentValue = $this->getCurrentMetricValue($pool, $policy->metric);

        // Check if we should scale up
        if ($policy->shouldScale($currentValue, 'up')) {
            if (!$pool->canScaleUp()) {
                return ['should_scale' => false, 'reason' => 'Already at maximum capacity'];
            }

            return [
                'should_scale' => true,
                'direction' => 'up',
                'current_value' => $currentValue,
                'threshold' => $policy->threshold_up,
                'scale_by' => $policy->scale_up_by
            ];
        }

        // Check if we should scale down
        if ($policy->shouldScale($currentValue, 'down')) {
            if (!$pool->canScaleDown()) {
                return ['should_scale' => false, 'reason' => 'Already at minimum capacity'];
            }

            return [
                'should_scale' => true,
                'direction' => 'down',
                'current_value' => $currentValue,
                'threshold' => $policy->threshold_down,
                'scale_by' => $policy->scale_down_by
            ];
        }

        return [
            'should_scale' => false,
            'reason' => 'Metric within acceptable range',
            'current_value' => $currentValue
        ];
    }

    /**
     * Execute scaling action on a server pool
     */
    public function scalePool(ServerPool $pool, string $direction, int $count, ScalingPolicy $policy = null): bool
    {
        try {
            $pool->update(['status' => 'scaling']);

            if ($direction === 'up') {
                $this->scaleUp($pool, $count);
            } else {
                $this->scaleDown($pool, $count);
            }

            $pool->update(['status' => 'active', 'last_scaled_at' => now()]);
            
            if ($policy) {
                $policy->markScaled();
            }

            Log::info("Scaled {$direction} pool {$pool->name} by {$count} servers");
            return true;
        } catch (\Exception $e) {
            $pool->update(['status' => 'error']);
            Log::error("Failed to scale pool {$pool->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Scale up a server pool
     */
    protected function scaleUp(ServerPool $pool, int $count): void
    {
        $team = $pool->team;
        $newDesired = min($pool->desired_servers + $count, $pool->max_servers);
        
        for ($i = 0; $i < $count; $i++) {
            if ($pool->current_servers >= $pool->max_servers) {
                break;
            }

            // Create new server instance
            $server = Server::create([
                'team_id' => $team->id,
                'name' => "{$pool->name}-auto-" . uniqid(),
                'ip_address' => $this->generateIpAddress(),
                'status' => 'provisioning',
                'environment' => $pool->environment,
                'region' => $pool->region,
            ]);

            // Add server to pool
            $pool->addServer($server);

            // Trigger server provisioning (would integrate with cloud provider)
            $this->provisionServer($server);
        }

        $pool->update(['desired_servers' => $newDesired]);
    }

    /**
     * Scale down a server pool
     */
    protected function scaleDown(ServerPool $pool, int $count): void
    {
        $newDesired = max($pool->desired_servers - $count, $pool->min_servers);
        
        // Get servers to remove (prefer inactive or oldest)
        $serversToRemove = $pool->servers()
            ->orderBy('pivot_is_active', 'asc')
            ->orderBy('pivot_joined_at', 'desc')
            ->limit($count)
            ->get();

        foreach ($serversToRemove as $server) {
            if ($pool->current_servers <= $pool->min_servers) {
                break;
            }

            // Remove server from pool
            $pool->removeServer($server);

            // Gracefully terminate server
            $this->terminateServer($server);
        }

        $pool->update(['desired_servers' => $newDesired]);
    }

    /**
     * Get current metric value for a server pool
     */
    protected function getCurrentMetricValue(ServerPool $pool, ?string $metric): float
    {
        if (!$metric) {
            return 0;
        }

        // Get average metric across all active servers in pool
        $activeServers = $pool->activeServers;
        
        if ($activeServers->isEmpty()) {
            return 0;
        }

        // Simulate metric gathering (would integrate with monitoring system)
        // In production, this would query Prometheus, CloudWatch, etc.
        return match($metric) {
            'cpu_percent' => $this->getAverageCpuUsage($activeServers),
            'memory_percent' => $this->getAverageMemoryUsage($activeServers),
            default => 0
        };
    }

    /**
     * Evaluate schedule-based scaling policy
     */
    protected function evaluateSchedulePolicy(ScalingPolicy $policy, ServerPool $pool): array
    {
        $now = now();
        $currentTime = $now->format('H:i');

        foreach ($policy->schedule as $schedule) {
            if ($schedule['time'] === $currentTime) {
                $targetServers = $schedule['servers'];
                $currentServers = $pool->current_servers;

                if ($targetServers > $currentServers) {
                    return [
                        'should_scale' => true,
                        'direction' => 'up',
                        'scale_by' => $targetServers - $currentServers,
                        'reason' => 'Scheduled scale-up',
                    ];
                } elseif ($targetServers < $currentServers) {
                    return [
                        'should_scale' => true,
                        'direction' => 'down',
                        'scale_by' => $currentServers - $targetServers,
                        'reason' => 'Scheduled scale-down',
                    ];
                }
            }
        }

        return ['should_scale' => false, 'reason' => 'Not at scheduled time'];
    }

    /**
     * Provision a new server (placeholder for cloud provider integration)
     */
    protected function provisionServer(Server $server): void
    {
        // This would integrate with cloud providers (AWS, Azure, GCP, DigitalOcean, etc.)
        // For now, just mark as active after a delay
        $server->update(['status' => 'active']);
        
        Log::info("Provisioned server {$server->name}");
    }

    /**
     * Terminate a server (placeholder for cloud provider integration)
     */
    protected function terminateServer(Server $server): void
    {
        // This would integrate with cloud providers to terminate the instance
        // For now, just mark as terminated
        $server->update(['status' => 'terminated']);
        
        Log::info("Terminated server {$server->name}");
    }

    /**
     * Get average CPU usage across servers
     */
    protected function getAverageCpuUsage($servers): float
    {
        // Placeholder - would integrate with monitoring system
        // Return simulated value for demonstration
        return rand(20, 90);
    }

    /**
     * Get average memory usage across servers
     */
    protected function getAverageMemoryUsage($servers): float
    {
        // Placeholder - would integrate with monitoring system
        // Return simulated value for demonstration
        return rand(30, 85);
    }

    /**
     * Generate a placeholder IP address
     */
    protected function generateIpAddress(): string
    {
        return '10.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254);
    }
}
