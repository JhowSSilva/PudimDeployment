<?php

namespace App\Services;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Support\Facades\Log;

class MetricsCollectorService
{
    private SSHConnectionService $ssh;
    private Server $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->ssh = new SSHConnectionService($server);
    }

    /**
     * Collect all metrics from the server and save to database
     * 
     * @return ServerMetric|null
     */
    public function collect(): ?ServerMetric
    {
        try {
            $this->ssh->connect();

            $metrics = [
                'server_id' => $this->server->id,
                'cpu_usage' => $this->collectCPU(),
                'memory_used_mb' => $this->collectMemory()['used'],
                'memory_total_mb' => $this->collectMemory()['total'],
                'disk_used_gb' => $this->collectDisk()['used'],
                'disk_total_gb' => $this->collectDisk()['total'],
                'uptime_seconds' => $this->collectUptime(),
                'processes' => $this->collectProcesses(),
            ];

            $serverMetric = ServerMetric::create($metrics);

            // Update server status
            $this->server->update([
                'status' => 'online',
                'last_ping_at' => now(),
            ]);

            Log::info("Metrics collected for server {$this->server->name}");
            
            return $serverMetric;

        } catch (\Exception $e) {
            Log::error("Failed to collect metrics for server {$this->server->name}: " . $e->getMessage());
            
            // Mark server as offline
            $this->server->update([
                'status' => 'offline',
            ]);

            return null;
        } finally {
            $this->ssh->disconnect();
        }
    }

    /**
     * Collect CPU usage percentage
     * 
     * Example command: top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1
     * Alternative: grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage}'
     * 
     * @return float
     */
    private function collectCPU(): float
    {
        try {
            // Using top command
            $result = $this->ssh->execute("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1");
            
            if ($result['exit_code'] === 0) {
                return (float) trim($result['output']);
            }

            // Fallback: use /proc/stat
            $result = $this->ssh->execute("grep 'cpu ' /proc/stat | awk '{usage=(\$2+\$4)*100/(\$2+\$4+\$5)} END {print usage}'");
            
            return $result['exit_code'] === 0 ? (float) trim($result['output']) : 0.0;

        } catch (\Exception $e) {
            Log::warning("CPU collection failed: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Collect memory usage in MB
     * 
     * Example command: free -m | grep Mem | awk '{print $3, $2}'
     * 
     * @return array ['used' => int, 'total' => int]
     */
    private function collectMemory(): array
    {
        try {
            $result = $this->ssh->execute("free -m | grep Mem | awk '{print \$3, \$2}'");
            
            if ($result['exit_code'] === 0) {
                [$used, $total] = explode(' ', trim($result['output']));
                return [
                    'used' => (int) $used,
                    'total' => (int) $total,
                ];
            }

            return ['used' => 0, 'total' => 0];

        } catch (\Exception $e) {
            Log::warning("Memory collection failed: " . $e->getMessage());
            return ['used' => 0, 'total' => 0];
        }
    }

    /**
     * Collect disk usage in GB for root partition
     * 
     * Example command: df -BG / | tail -1 | awk '{print $3, $2}' | sed 's/G//g'
     * 
     * @return array ['used' => int, 'total' => int]
     */
    private function collectDisk(): array
    {
        try {
            $result = $this->ssh->execute("df -BG / | tail -1 | awk '{print \$3, \$2}' | sed 's/G//g'");
            
            if ($result['exit_code'] === 0) {
                [$used, $total] = explode(' ', trim($result['output']));
                return [
                    'used' => (int) $used,
                    'total' => (int) $total,
                ];
            }

            return ['used' => 0, 'total' => 0];

        } catch (\Exception $e) {
            Log::warning("Disk collection failed: " . $e->getMessage());
            return ['used' => 0, 'total' => 0];
        }
    }

    /**
     * Collect server uptime in seconds
     * 
     * Example command: cat /proc/uptime | awk '{print $1}' | cut -d'.' -f1
     * 
     * @return int
     */
    private function collectUptime(): int
    {
        try {
            $result = $this->ssh->execute("cat /proc/uptime | awk '{print \$1}' | cut -d'.' -f1");
            
            return $result['exit_code'] === 0 ? (int) trim($result['output']) : 0;

        } catch (\Exception $e) {
            Log::warning("Uptime collection failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Collect process status (nginx, php-fpm, mysql, redis, postgresql)
     * 
     * Example command: systemctl status nginx
     * 
     * @return array
     */
    private function collectProcesses(): array
    {
        $services = ['nginx', 'php8.3-fpm', 'php8.2-fpm', 'php8.1-fpm', 'mysql', 'redis-server', 'postgresql'];
        $processes = [];

        foreach ($services as $service) {
            try {
                $result = $this->ssh->execute("systemctl is-active {$service}");
                $isActive = trim($result['output']) === 'active';

                if ($isActive) {
                    // Get additional info
                    $pidResult = $this->ssh->execute("systemctl show -p MainPID {$service} | cut -d'=' -f2");
                    $pid = (int) trim($pidResult['output']);

                    $processes[$service] = [
                        'status' => 'running',
                        'pid' => $pid,
                    ];
                } else {
                    $processes[$service] = [
                        'status' => 'stopped',
                        'pid' => null,
                    ];
                }
            } catch (\Exception $e) {
                // Service doesn't exist or error occurred
                continue;
            }
        }

        return $processes;
    }

    /**
     * Get quick server status (lightweight check)
     * 
     * @return bool
     */
    public function isServerOnline(): bool
    {
        try {
            return $this->ssh->testConnection();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get real-time metrics without saving to database
     * 
     * @return array
     */
    public function getRealTimeMetrics(): array
    {
        try {
            $this->ssh->connect();

            return [
                'cpu_usage' => $this->collectCPU(),
                'memory' => $this->collectMemory(),
                'disk' => $this->collectDisk(),
                'uptime_seconds' => $this->collectUptime(),
                'processes' => $this->collectProcesses(),
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get real-time metrics: " . $e->getMessage());
            return [];
        } finally {
            $this->ssh->disconnect();
        }
    }
}
