<?php

namespace App\Services;

use App\Models\HealthCheck;
use App\Models\Server;
use App\Models\LoadBalancer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthCheckService
{
    /**
     * Run a health check on a server
     */
    public function runCheck(HealthCheck $healthCheck): bool
    {
        $startTime = microtime(true);
        
        try {
            $result = $this->performCheck($healthCheck);
            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            if ($result['success']) {
                $healthCheck->recordSuccess((int) $responseTime);
                $this->checkHealthThreshold($healthCheck);
                return true;
            } else {
                $healthCheck->recordFailure($result['error']);
                $this->checkUnhealthyThreshold($healthCheck);
                return false;
            }
        } catch (\Exception $e) {
            $healthCheck->recordFailure($e->getMessage());
            $this->checkUnhealthyThreshold($healthCheck);
            Log::error("Health check failed for server {$healthCheck->server->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform the actual health check based on type
     */
    protected function performCheck(HealthCheck $healthCheck): array
    {
        return match($healthCheck->type) {
            'http', 'https' => $this->performHttpCheck($healthCheck),
            'tcp' => $this->performTcpCheck($healthCheck),
            'ping' => $this->performPingCheck($healthCheck),
            default => ['success' => false, 'error' => 'Unknown check type']
        };
    }

    /**
     * Perform HTTP/HTTPS health check
     */
    protected function performHttpCheck(HealthCheck $healthCheck): array
    {
        $server = $healthCheck->server;
        $protocol = $healthCheck->type;
        $port = $healthCheck->port ?? ($protocol === 'https' ? 443 : 80);
        $endpoint = $healthCheck->endpoint ?? '/health';
        
        $url = "{$protocol}://{$server->ip_address}:{$port}{$endpoint}";

        try {
            $response = Http::timeout($healthCheck->timeout ?? 5)->get($url);
            
            $expectedStatus = $healthCheck->expected_status ?? 200;
            
            if ($response->status() !== $expectedStatus) {
                return [
                    'success' => false,
                    'error' => "Unexpected status: {$response->status()} (expected {$expectedStatus})"
                ];
            }

            // Check expected body if configured
            if ($healthCheck->expected_body) {
                $body = $response->body();
                if (strpos($body, $healthCheck->expected_body) === false) {
                    return [
                        'success' => false,
                        'error' => 'Response body does not match expected content'
                    ];
                }
            }

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Perform TCP health check
     */
    protected function performTcpCheck(HealthCheck $healthCheck): array
    {
        $server = $healthCheck->server;
        $port = $healthCheck->port ?? 80;
        $timeout = $healthCheck->timeout ?? 5;

        $connection = @fsockopen($server->ip_address, $port, $errno, $errstr, $timeout);

        if (!$connection) {
            return ['success' => false, 'error' => "TCP connection failed: {$errstr}"];
        }

        fclose($connection);
        return ['success' => true];
    }

    /**
     * Perform ICMP ping check
     */
    protected function performPingCheck(HealthCheck $healthCheck): array
    {
        $server = $healthCheck->server;
        $timeout = $healthCheck->timeout ?? 5;

        // Execute ping command (Unix-like systems)
        $output = [];
        $returnVar = 0;
        exec("ping -c 1 -W {$timeout} {$server->ip_address}", $output, $returnVar);

        if ($returnVar === 0) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Ping failed'];
    }

    /**
     * Check if server should be marked as healthy
     */
    protected function checkHealthThreshold(HealthCheck $healthCheck): void
    {
        if (!$healthCheck->loadBalancer) {
            return;
        }

        $threshold = $healthCheck->loadBalancer->healthy_threshold ?? 2;

        if ($healthCheck->shouldMarkHealthy($threshold)) {
            $this->markServerHealthy($healthCheck);
        }
    }

    /**
     * Check if server should be marked as unhealthy
     */
    protected function checkUnhealthyThreshold(HealthCheck $healthCheck): void
    {
        if (!$healthCheck->loadBalancer) {
            return;
        }

        $threshold = $healthCheck->loadBalancer->unhealthy_threshold ?? 3;

        if ($healthCheck->shouldMarkUnhealthy($threshold)) {
            $this->markServerUnhealthy($healthCheck);
            $this->triggerAutoHealing($healthCheck);
        }
    }

    /**
     * Mark server as healthy
     */
    protected function markServerHealthy(HealthCheck $healthCheck): void
    {
        $server = $healthCheck->server;
        $server->update(['status' => 'active']);

        Log::info("Server {$server->name} marked as healthy");
    }

    /**
     * Mark server as unhealthy
     */
    protected function markServerUnhealthy(HealthCheck $healthCheck): void
    {
        $server = $healthCheck->server;
        $server->update(['status' => 'unhealthy']);

        Log::warning("Server {$server->name} marked as unhealthy after {$healthCheck->consecutive_failures} failed checks");
    }

    /**
     * Trigger auto-healing if enabled
     */
    public function triggerAutoHealing(HealthCheck $healthCheck): void
    {
        $server = $healthCheck->server;
        
        // Find server pools with auto-healing enabled that contain this server
        $pools = $server->serverPools()->where('auto_healing', true)->get();

        foreach ($pools as $pool) {
            Log::info("Triggering auto-healing for server {$server->name} in pool {$pool->name}");

            // Remove unhealthy server from pool
            $pool->removeServer($server);

            // Trigger scaling service to replace the server
            $autoScalingService = app(AutoScalingService::class);
            $autoScalingService->scalePool($pool, 'up', 1);

            Log::info("Auto-healing completed for server {$server->name}");
        }
    }

    /**
     * Run all health checks for a load balancer
     */
    public function runLoadBalancerHealthChecks(LoadBalancer $loadBalancer): array
    {
        if (!$loadBalancer->health_check_enabled) {
            return ['status' => 'disabled'];
        }

        $results = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'checks' => []
        ];

        $healthChecks = $loadBalancer->healthChecks;

        foreach ($healthChecks as $healthCheck) {
            $success = $this->runCheck($healthCheck);
            
            $results['total']++;
            if ($success) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }

            $results['checks'][] = [
                'server' => $healthCheck->server->name,
                'status' => $healthCheck->status,
                'response_time' => $healthCheck->response_time,
                'success' => $success
            ];
        }

        $loadBalancer->update(['last_health_check_at' => now()]);

        return $results;
    }

    /**
     * Create health checks for all servers in a pool
     */
    public function createPoolHealthChecks(LoadBalancer $loadBalancer): void
    {
        if (!$loadBalancer->serverPool) {
            return;
        }

        foreach ($loadBalancer->serverPool->servers as $server) {
            HealthCheck::firstOrCreate([
                'team_id' => $loadBalancer->team_id,
                'server_id' => $server->id,
                'load_balancer_id' => $loadBalancer->id,
            ], [
                'type' => $loadBalancer->protocol === 'https' ? 'https' : 'http',
                'endpoint' => $loadBalancer->health_check_path ?? '/health',
                'port' => $loadBalancer->port ?? 80,
                'timeout' => $loadBalancer->health_check_timeout ?? 5,
                'expected_status' => 200,
                'status' => 'unknown',
            ]);
        }

        Log::info("Created health checks for load balancer {$loadBalancer->name}");
    }

    /**
     * Get health summary for a server pool
     */
    public function getPoolHealthSummary($pool): array
    {
        $servers = $pool->servers;
        $totalServers = $servers->count();
        
        if ($totalServers === 0) {
            return [
                'status' => 'no_servers',
                'healthy' => 0,
                'unhealthy' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }

        $healthyCount = $servers->where('status', 'active')->count();
        $unhealthyCount = $totalServers - $healthyCount;
        $percentage = ($healthyCount / $totalServers) * 100;

        $status = match(true) {
            $percentage >= 80 => 'healthy',
            $percentage >= 50 => 'degraded',
            default => 'unhealthy'
        };

        return [
            'status' => $status,
            'healthy' => $healthyCount,
            'unhealthy' => $unhealthyCount,
            'total' => $totalServers,
            'percentage' => round($percentage, 2)
        ];
    }
}
