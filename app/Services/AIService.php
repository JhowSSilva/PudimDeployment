<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AIService
{
    private SSHConnectionService $ssh;
    private MetricsCollectorService $metrics;

    public function __construct(private Server $server)
    {
        $this->ssh = new SSHConnectionService($server);
        $this->metrics = new MetricsCollectorService($server);
    }

    /**
     * Predict server load based on historical data
     */
    public function predictServerLoad(int $hoursAhead = 24): array
    {
        try {
            Log::info('Predicting server load', [
                'server' => $this->server->name,
                'hours_ahead' => $hoursAhead
            ]);

            // Get historical metrics from last 30 days
            $historicalData = $this->getHistoricalMetrics(30);

            if (count($historicalData) < 10) {
                return [
                    'success' => false,
                    'message' => 'Insufficient historical data for prediction'
                ];
            }

            // Simple linear regression for prediction
            $predictions = $this->calculateLoadPrediction($historicalData, $hoursAhead);

            // Detect anomalies
            $anomalies = $this->detectAnomalies($predictions);

            return [
                'success' => true,
                'predictions' => $predictions,
                'anomalies' => $anomalies,
                'recommended_actions' => $this->recommendActions($predictions)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to predict server load', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get historical metrics
     */
    private function getHistoricalMetrics(int $days): array
    {
        $metrics = DB::table('server_metrics')
            ->where('server_id', $this->server->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();

        return array_map(function($metric) {
            return [
                'timestamp' => $metric->created_at,
                'cpu' => $metric->cpu_usage ?? 0,
                'memory' => $metric->memory_usage ?? 0,
                'disk' => $metric->disk_usage ?? 0,
                'load' => $metric->load_average ?? 0
            ];
        }, $metrics);
    }

    /**
     * Calculate load prediction using moving average
     */
    private function calculateLoadPrediction(array $historicalData, int $hoursAhead): array
    {
        $predictions = [];
        $dataPoints = count($historicalData);

        if ($dataPoints === 0) {
            return [];
        }

        // Calculate moving average for the last 7 days
        $windowSize = min(168, $dataPoints); // 7 days in hours
        $recentData = array_slice($historicalData, -$windowSize);

        // Calculate averages
        $avgCpu = array_sum(array_column($recentData, 'cpu')) / count($recentData);
        $avgMemory = array_sum(array_column($recentData, 'memory')) / count($recentData);
        $avgDisk = array_sum(array_column($recentData, 'disk')) / count($recentData);
        $avgLoad = array_sum(array_column($recentData, 'load')) / count($recentData);

        // Detect trend (increasing/decreasing)
        $trendCpu = $this->calculateTrend($recentData, 'cpu');
        $trendMemory = $this->calculateTrend($recentData, 'memory');
        $trendDisk = $this->calculateTrend($recentData, 'disk');

        // Generate predictions for each hour
        for ($i = 1; $i <= $hoursAhead; $i++) {
            $predictions[] = [
                'hour' => $i,
                'timestamp' => now()->addHours($i)->toIso8601String(),
                'cpu' => min(100, max(0, $avgCpu + ($trendCpu * $i))),
                'memory' => min(100, max(0, $avgMemory + ($trendMemory * $i))),
                'disk' => min(100, max(0, $avgDisk + ($trendDisk * $i))),
                'load' => max(0, $avgLoad + (($trendCpu / 10) * $i))
            ];
        }

        return $predictions;
    }

    /**
     * Calculate trend (slope) for a metric
     */
    private function calculateTrend(array $data, string $metric): float
    {
        $n = count($data);
        if ($n < 2) return 0;

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($data as $i => $point) {
            $x = $i;
            $y = $point[$metric];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);

        return $slope;
    }

    /**
     * Detect anomalies in predictions
     */
    private function detectAnomalies(array $predictions): array
    {
        $anomalies = [];

        foreach ($predictions as $prediction) {
            if ($prediction['cpu'] > 80) {
                $anomalies[] = [
                    'timestamp' => $prediction['timestamp'],
                    'type' => 'cpu',
                    'severity' => $prediction['cpu'] > 90 ? 'critical' : 'warning',
                    'value' => $prediction['cpu'],
                    'message' => "CPU usage predicted to reach {$prediction['cpu']}%"
                ];
            }

            if ($prediction['memory'] > 85) {
                $anomalies[] = [
                    'timestamp' => $prediction['timestamp'],
                    'type' => 'memory',
                    'severity' => $prediction['memory'] > 95 ? 'critical' : 'warning',
                    'value' => $prediction['memory'],
                    'message' => "Memory usage predicted to reach {$prediction['memory']}%"
                ];
            }

            if ($prediction['disk'] > 90) {
                $anomalies[] = [
                    'timestamp' => $prediction['timestamp'],
                    'type' => 'disk',
                    'severity' => 'critical',
                    'value' => $prediction['disk'],
                    'message' => "Disk usage predicted to reach {$prediction['disk']}%"
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Recommend actions based on predictions
     */
    private function recommendActions(array $predictions): array
    {
        $actions = [];

        // Find max predicted values
        $maxCpu = max(array_column($predictions, 'cpu'));
        $maxMemory = max(array_column($predictions, 'memory'));
        $maxDisk = max(array_column($predictions, 'disk'));

        if ($maxCpu > 80) {
            $actions[] = [
                'priority' => 'high',
                'action' => 'optimize_cpu',
                'description' => 'Consider upgrading server or optimizing CPU-intensive processes',
                'expected_impact' => 'Reduce CPU usage by 20-30%'
            ];
        }

        if ($maxMemory > 85) {
            $actions[] = [
                'priority' => 'high',
                'action' => 'optimize_memory',
                'description' => 'Enable memory caching (Redis/Memcached) or upgrade RAM',
                'expected_impact' => 'Reduce memory usage by 15-25%'
            ];
        }

        if ($maxDisk > 90) {
            $actions[] = [
                'priority' => 'critical',
                'action' => 'cleanup_disk',
                'description' => 'Clean up logs, old backups, and temporary files or expand disk',
                'expected_impact' => 'Free up 10-30% disk space'
            ];
        }

        return $actions;
    }

    /**
     * Optimize resources automatically
     */
    public function optimizeResources(): array
    {
        try {
            Log::info('Optimizing server resources', ['server' => $this->server->name]);

            $optimizations = [];

            // 1. Clean up logs
            $logCleanup = $this->cleanupLogs();
            $optimizations['log_cleanup'] = $logCleanup;

            // 2. Optimize databases
            $dbOptimization = $this->optimizeDatabases();
            $optimizations['database_optimization'] = $dbOptimization;

            // 3. Clear package manager cache
            $packageCache = $this->ssh->execute('apt-get clean && apt-get autoclean && apt-get autoremove -y');
            $optimizations['package_cleanup'] = [
                'success' => $packageCache['exit_code'] === 0
            ];

            // 4. Optimize swap usage
            $swapOptimization = $this->optimizeSwap();
            $optimizations['swap_optimization'] = $swapOptimization;

            // 5. Analyze and report disk usage
            $diskAnalysis = $this->analyzeDiskUsage();
            $optimizations['disk_analysis'] = $diskAnalysis;

            Log::info('Resource optimization completed', [
                'server' => $this->server->name,
                'optimizations' => count($optimizations)
            ]);

            return [
                'success' => true,
                'optimizations' => $optimizations,
                'message' => 'Resources optimized successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to optimize resources', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Clean up old log files
     */
    private function cleanupLogs(): array
    {
        try {
            // Find and delete logs older than 30 days
            $result = $this->ssh->execute('find /var/log -type f -name "*.log" -mtime +30 -delete');

            // Rotate Nginx logs
            $this->ssh->execute('logrotate -f /etc/logrotate.d/nginx');

            // Clear systemd journal logs older than 7 days
            $this->ssh->execute('journalctl --vacuum-time=7d');

            return [
                'success' => true,
                'message' => 'Old logs cleaned up successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Optimize all databases
     */
    private function optimizeDatabases(): array
    {
        try {
            // Get all databases
            $dbResult = $this->ssh->execute("mysql -e 'SHOW DATABASES;' | grep -Ev '(Database|information_schema|performance_schema|mysql|sys)'");
            $databases = array_filter(explode("\n", trim($dbResult['output'])));

            $optimized = 0;
            foreach ($databases as $db) {
                $db = trim($db);
                if (empty($db)) continue;

                // Optimize all tables in database
                $this->ssh->execute("mysqlcheck -o {$db}");
                $optimized++;
            }

            return [
                'success' => true,
                'databases_optimized' => $optimized,
                'message' => "Optimized {$optimized} databases"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Optimize swap usage
     */
    private function optimizeSwap(): array
    {
        try {
            // Clear swap cache
            $this->ssh->execute('swapoff -a && swapon -a');

            // Set swappiness to optimal value (10)
            $this->ssh->execute('sysctl vm.swappiness=10');
            $this->ssh->execute('echo "vm.swappiness=10" >> /etc/sysctl.conf');

            return [
                'success' => true,
                'message' => 'Swap optimized successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze disk usage and find large files
     */
    private function analyzeDiskUsage(): array
    {
        try {
            // Find largest directories
            $largestDirs = $this->ssh->execute("du -h /var /home --max-depth=2 | sort -rh | head -20");

            // Find largest files
            $largestFiles = $this->ssh->execute("find /var/log /tmp -type f -size +100M -exec ls -lh {} \\; | awk '{print \$9, \$5}'");

            return [
                'success' => true,
                'largest_directories' => $largestDirs['output'],
                'largest_files' => $largestFiles['output']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Detect security threats using ML patterns
     */
    public function detectSecurityThreats(): array
    {
        try {
            Log::info('Detecting security threats', ['server' => $this->server->name]);

            $threats = [];

            // 1. Analyze failed login attempts
            $failedLogins = $this->analyzeFailedLogins();
            if ($failedLogins['suspicious']) {
                $threats[] = $failedLogins;
            }

            // 2. Check for unusual network activity
            $networkActivity = $this->checkNetworkActivity();
            if ($networkActivity['suspicious']) {
                $threats[] = $networkActivity;
            }

            // 3. Scan for malware signatures
            $malwareScan = $this->scanForMalware();
            if ($malwareScan['threats_found'] > 0) {
                $threats[] = $malwareScan;
            }

            // 4. Check for rootkits
            $rootkitCheck = $this->checkForRootkits();
            if ($rootkitCheck['suspicious']) {
                $threats[] = $rootkitCheck;
            }

            return [
                'success' => true,
                'threats_detected' => count($threats),
                'threats' => $threats,
                'risk_level' => $this->calculateRiskLevel($threats)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to detect security threats', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze failed login attempts
     */
    private function analyzeFailedLogins(): array
    {
        $result = $this->ssh->execute("grep 'Failed password' /var/log/auth.log | tail -100");
        $failures = explode("\n", $result['output']);

        $ipCounts = [];
        foreach ($failures as $line) {
            if (preg_match('/from ([0-9.]+)/', $line, $matches)) {
                $ip = $matches[1];
                $ipCounts[$ip] = ($ipCounts[$ip] ?? 0) + 1;
            }
        }

        // Find IPs with more than 10 failed attempts
        $suspiciousIPs = array_filter($ipCounts, fn($count) => $count > 10);

        return [
            'type' => 'brute_force',
            'suspicious' => count($suspiciousIPs) > 0,
            'severity' => count($suspiciousIPs) > 5 ? 'high' : 'medium',
            'details' => $suspiciousIPs,
            'recommendation' => 'Block suspicious IPs using fail2ban'
        ];
    }

    /**
     * Check for unusual network activity
     */
    private function checkNetworkActivity(): array
    {
        $result = $this->ssh->execute("netstat -an | grep ESTABLISHED | wc -l");
        $connections = (int) trim($result['output']);

        $suspicious = $connections > 500;

        return [
            'type' => 'network_activity',
            'suspicious' => $suspicious,
            'severity' => $suspicious ? 'medium' : 'low',
            'active_connections' => $connections,
            'recommendation' => $suspicious ? 'Review active connections for unusual patterns' : null
        ];
    }

    /**
     * Scan for malware signatures (basic)
     */
    private function scanForMalware(): array
    {
        // Check for common malware patterns in web directories
        $patterns = [
            'eval(base64_decode',
            'system($_GET',
            'exec($_POST',
            'assert($_REQUEST',
            'create_function'
        ];

        $threatsFound = 0;
        $suspiciousFiles = [];

        foreach ($patterns as $pattern) {
            $result = $this->ssh->execute("grep -r '{$pattern}' /var/www --include='*.php' 2>/dev/null | head -5");
            if (!empty(trim($result['output']))) {
                $threatsFound++;
                $suspiciousFiles[] = $result['output'];
            }
        }

        return [
            'type' => 'malware',
            'threats_found' => $threatsFound,
            'severity' => $threatsFound > 0 ? 'high' : 'low',
            'suspicious_files' => array_slice($suspiciousFiles, 0, 10),
            'recommendation' => $threatsFound > 0 ? 'Investigate and remove malicious code' : null
        ];
    }

    /**
     * Check for rootkits
     */
    private function checkForRootkits(): array
    {
        // Check if chkrootkit is installed
        $check = $this->ssh->execute('which chkrootkit');
        
        if (empty(trim($check['output']))) {
            return [
                'type' => 'rootkit',
                'suspicious' => false,
                'message' => 'chkrootkit not installed'
            ];
        }

        $result = $this->ssh->execute('chkrootkit 2>&1');
        $suspicious = strpos($result['output'], 'INFECTED') !== false;

        return [
            'type' => 'rootkit',
            'suspicious' => $suspicious,
            'severity' => $suspicious ? 'critical' : 'low',
            'recommendation' => $suspicious ? 'Server may be compromised. Immediate investigation required.' : null
        ];
    }

    /**
     * Calculate overall risk level
     */
    private function calculateRiskLevel(array $threats): string
    {
        if (empty($threats)) {
            return 'low';
        }

        $highCount = count(array_filter($threats, fn($t) => ($t['severity'] ?? 'low') === 'high'));
        $criticalCount = count(array_filter($threats, fn($t) => ($t['severity'] ?? 'low') === 'critical'));

        if ($criticalCount > 0) {
            return 'critical';
        } elseif ($highCount > 2) {
            return 'high';
        } elseif ($highCount > 0 || count($threats) > 3) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Recommend server upgrades based on usage patterns
     */
    public function recommendUpgrades(): array
    {
        try {
            Log::info('Analyzing server for upgrade recommendations', [
                'server' => $this->server->name
            ]);

            $recommendations = [];

            // Get current specs
            $cpuInfo = $this->ssh->execute("nproc");
            $currentCPUs = (int) trim($cpuInfo['output']);

            $memInfo = $this->ssh->execute("free -m | grep Mem | awk '{print \$2}'");
            $currentMemoryMB = (int) trim($memInfo['output']);

            // Get average usage
            $historicalData = $this->getHistoricalMetrics(7);
            $avgCpu = array_sum(array_column($historicalData, 'cpu')) / count($historicalData);
            $avgMemory = array_sum(array_column($historicalData, 'memory')) / count($historicalData);

            // CPU recommendations
            if ($avgCpu > 80) {
                $recommendations[] = [
                    'type' => 'cpu',
                    'current' => "{$currentCPUs} vCPUs",
                    'recommended' => ($currentCPUs * 2) . " vCPUs",
                    'reason' => "Average CPU usage is {$avgCpu}%, indicating CPU bottleneck",
                    'priority' => 'high'
                ];
            }

            // Memory recommendations
            if ($avgMemory > 85) {
                $recommendedMemory = ceil($currentMemoryMB * 1.5 / 1024) * 1024;
                $recommendations[] = [
                    'type' => 'memory',
                    'current' => round($currentMemoryMB / 1024, 1) . " GB",
                    'recommended' => round($recommendedMemory / 1024) . " GB",
                    'reason' => "Average memory usage is {$avgMemory}%, upgrade needed",
                    'priority' => 'high'
                ];
            }

            return [
                'success' => true,
                'recommendations' => $recommendations,
                'current_specs' => [
                    'cpu' => "{$currentCPUs} vCPUs",
                    'memory' => round($currentMemoryMB / 1024, 1) . " GB"
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
