<?php

namespace App\Services;

use App\Models\LoadBalancer;
use App\Models\Server;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoadBalancingService
{
    /**
     * Distribute incoming request to a server using the load balancer's algorithm
     */
    public function distributeRequest(LoadBalancer $loadBalancer, array $requestData = []): ?Server
    {
        if ($loadBalancer->status !== 'active') {
            Log::warning("Load balancer {$loadBalancer->name} is not active");
            return null;
        }

        if (!$loadBalancer->serverPool) {
            Log::warning("Load balancer {$loadBalancer->name} has no server pool");
            return null;
        }

        $servers = $loadBalancer->serverPool->activeServers;

        if ($servers->isEmpty()) {
            Log::warning("No active servers in pool {$loadBalancer->serverPool->name}");
            return null;
        }

        // Filter out unhealthy servers if health checks are enabled
        if ($loadBalancer->health_check_enabled) {
            $servers = $this->filterHealthyServers($servers, $loadBalancer);
        }

        if ($servers->isEmpty()) {
            Log::warning("No healthy servers available in pool {$loadBalancer->serverPool->name}");
            return null;
        }

        // Select server based on algorithm
        $server = $this->selectServer($loadBalancer, $servers, $requestData);

        if ($server) {
            $this->trackRequest($loadBalancer, $server, $requestData);
        }

        return $server;
    }

    /**
     * Select a server based on the load balancing algorithm
     */
    protected function selectServer(LoadBalancer $loadBalancer, Collection $servers, array $requestData): ?Server
    {
        return match($loadBalancer->algorithm) {
            'round_robin' => $this->roundRobinSelect($loadBalancer, $servers),
            'least_connections' => $this->leastConnectionsSelect($servers),
            'ip_hash' => $this->ipHashSelect($servers, $requestData['client_ip'] ?? null),
            'weighted' => $this->weightedSelect($servers),
            default => $servers->random()
        };
    }

    /**
     * Round-robin server selection
     */
    protected function roundRobinSelect(LoadBalancer $loadBalancer, Collection $servers): ?Server
    {
        $key = "lb:{$loadBalancer->id}:round_robin_index";
        $currentIndex = Cache::get($key, 0);
        
        $serversList = $servers->values();
        $server = $serversList[$currentIndex % $serversList->count()] ?? null;
        
        Cache::put($key, ($currentIndex + 1) % $serversList->count(), 3600);
        
        return $server;
    }

    /**
     * Least connections server selection
     */
    protected function leastConnectionsSelect(Collection $servers): ?Server
    {
        // Get connection counts from cache
        return $servers->sortBy(function ($server) {
            return Cache::get("server:{$server->id}:connections", 0);
        })->first();
    }

    /**
     * IP hash server selection (consistent hashing)
     */
    protected function ipHashSelect(Collection $servers, ?string $clientIp): ?Server
    {
        if (!$clientIp) {
            return $servers->random();
        }

        $hash = crc32($clientIp);
        $index = $hash % $servers->count();
        
        return $servers->values()[$index] ?? null;
    }

    /**
     * Weighted random server selection
     */
    protected function weightedSelect(Collection $servers): ?Server
    {
        $totalWeight = $servers->sum('pivot.weight');
        
        if ($totalWeight === 0) {
            return $servers->random();
        }

        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($servers as $server) {
            $currentWeight += $server->pivot->weight ?? 100;
            if ($random <= $currentWeight) {
                return $server;
            }
        }

        return $servers->first();
    }

    /**
     * Filter out unhealthy servers
     */
    protected function filterHealthyServers(Collection $servers, LoadBalancer $loadBalancer): Collection
    {
        return $servers->filter(function ($server) use ($loadBalancer) {
            $healthCheck = $server->healthChecks()
                ->where('load_balancer_id', $loadBalancer->id)
                ->where('status', 'healthy')
                ->first();

            return $healthCheck !== null;
        });
    }

    /**
     * Track request for analytics and connection counting
     */
    protected function trackRequest(LoadBalancer $loadBalancer, Server $server, array $requestData): void
    {
        // Increment connection count
        $key = "server:{$server->id}:connections";
        Cache::increment($key, 1);
        
        // Auto-decrement after average request duration (5 seconds)
        Cache::put($key, max(0, Cache::get($key, 1) - 1), 5);

        // Track request on load balancer
        $loadBalancer->incrementRequests(false);

        Log::debug("Distributed request to server {$server->name} via {$loadBalancer->algorithm}");
    }

    /**
     * Update server weight in pool
     */
    public function updateServerWeight(LoadBalancer $loadBalancer, Server $server, int $weight): bool
    {
        if (!$loadBalancer->serverPool) {
            return false;
        }

        $loadBalancer->serverPool->servers()->updateExistingPivot($server->id, [
            'weight' => max(1, min(1000, $weight))
        ]);

        Log::info("Updated server {$server->name} weight to {$weight} in pool {$loadBalancer->serverPool->name}");
        
        return true;
    }

    /**
     * Get load balancer statistics
     */
    public function getStatistics(LoadBalancer $loadBalancer): array
    {
        $servers = $loadBalancer->serverPool?->servers ?? collect();
        
        $serverStats = $servers->map(function ($server) {
            return [
                'id' => $server->id,
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'weight' => $server->pivot->weight ?? 100,
                'active_connections' => Cache::get("server:{$server->id}:connections", 0),
                'is_active' => $server->pivot->is_active ?? false,
            ];
        });

        return [
            'load_balancer' => $loadBalancer->name,
            'algorithm' => $loadBalancer->algorithm,
            'total_requests' => $loadBalancer->total_requests,
            'failed_requests' => $loadBalancer->failed_requests,
            'success_rate' => $loadBalancer->success_rate,
            'error_rate' => $loadBalancer->error_rate,
            'servers' => $serverStats,
            'total_servers' => $servers->count(),
            'active_servers' => $servers->where('pivot.is_active', true)->count(),
        ];
    }

    /**
     * Check if sticky session applies
     */
    public function getStickyServer(LoadBalancer $loadBalancer, string $sessionId): ?Server
    {
        if (!$loadBalancer->sticky_sessions) {
            return null;
        }

        $serverId = Cache::get("lb:{$loadBalancer->id}:session:{$sessionId}");
        
        if (!$serverId) {
            return null;
        }

        return Server::find($serverId);
    }

    /**
     * Set sticky session
     */
    public function setStickySession(LoadBalancer $loadBalancer, string $sessionId, Server $server): void
    {
        if (!$loadBalancer->sticky_sessions) {
            return;
        }

        $ttl = $loadBalancer->session_ttl ?? 3600;
        Cache::put("lb:{$loadBalancer->id}:session:{$sessionId}", $server->id, $ttl);
    }
}
