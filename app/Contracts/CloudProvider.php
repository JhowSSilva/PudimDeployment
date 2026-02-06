<?php

namespace App\Contracts;

use App\Models\Server;

interface CloudProvider
{
    /**
     * Create a new server instance
     */
    public function createServer(array $config): Server;

    /**
     * Start a stopped server
     */
    public function startServer(string $instanceId): bool;

    /**
     * Stop a running server
     */
    public function stopServer(string $instanceId): bool;

    /**
     * Reboot a server
     */
    public function rebootServer(string $instanceId): bool;

    /**
     * Terminate (delete) a server
     */
    public function terminateServer(string $instanceId): bool;

    /**
     * Get instance status
     */
    public function getInstanceStatus(string $instanceId): string;

    /**
     * Get available instance types for a region
     */
    public function getInstanceTypes(string $region): array;

    /**
     * Get available AMIs for a region, OS and architecture
     */
    public function getAvailableAMIs(string $region, string $os, string $arch): array;

    /**
     * Get server metrics (CPU, RAM, Network)
     */
    public function getMetrics(string $instanceId, string $metric, int $period = 300): array;

    /**
     * Estimate monthly cost for a configuration
     */
    public function estimateCost(array $config): float;

    /**
     * Validate credentials
     */
    public function validateCredentials(): bool;
}
