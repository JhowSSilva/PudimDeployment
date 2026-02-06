<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class APMService
{
    private SSHConnectionService $ssh;

    public function __construct(private Server $server)
    {
        $this->ssh = new SSHConnectionService($server);
    }

    /**
     * Track response times for a site
     */
    public function trackResponseTimes(Site $site, int $requests = 100): array
    {
        try {
            Log::info('Tracking response times', [
                'site' => $site->domain,
                'requests' => $requests
            ]);

            // Use Apache Bench to test
            $command = "ab -n {$requests} -c 10 https://{$site->domain}/ 2>&1";
            $result = $this->ssh->execute($command);

            $metrics = $this->parseApacheBenchOutput($result['output']);

            // Store metrics
            DB::table('performance_metrics')->insert([
                'site_id' => $site->id,
                'type' => 'response_time',
                'metrics' => json_encode($metrics),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'metrics' => $metrics
            ];

        } catch (\Exception $e) {
            Log::error('Failed to track response times', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse Apache Bench output
     */
    private function parseApacheBenchOutput(string $output): array
    {
        $metrics = [];

        // Parse mean time per request
        if (preg_match('/Time per request:\s+([0-9.]+)\s+\[ms\]\s+\(mean\)/', $output, $matches)) {
            $metrics['mean_response_time'] = (float) $matches[1];
        }

        // Parse requests per second
        if (preg_match('/Requests per second:\s+([0-9.]+)/', $output, $matches)) {
            $metrics['requests_per_second'] = (float) $matches[1];
        }

        // Parse failed requests
        if (preg_match('/Failed requests:\s+(\d+)/', $output, $matches)) {
            $metrics['failed_requests'] = (int) $matches[1];
        }

        // Parse total time
        if (preg_match('/Time taken for tests:\s+([0-9.]+)\s+seconds/', $output, $matches)) {
            $metrics['total_time'] = (float) $matches[1];
        }

        return $metrics;
    }

    /**
     * Monitor database queries for a Laravel site
     */
    public function monitorDatabaseQueries(Site $site, int $duration = 60): array
    {
        try {
            Log::info('Monitoring database queries', [
                'site' => $site->domain,
                'duration' => $duration
            ]);

            // Enable MySQL slow query log
            $slowLogConfig = [
                'slow_query_log' => 1,
                'slow_query_log_file' => '/var/log/mysql/slow-query.log',
                'long_query_time' => 1
            ];

            foreach ($slowLogConfig as $key => $value) {
                $this->ssh->execute("mysql -e \"SET GLOBAL {$key} = {$value};\"");
            }

            // Wait for specified duration
            sleep($duration);

            // Read slow query log
            $logResult = $this->ssh->execute("tail -n 500 /var/log/mysql/slow-query.log");

            $queries = $this->parseSlowQueryLog($logResult['output']);

            return [
                'success' => true,
                'slow_queries' => $queries,
                'count' => count($queries)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to monitor database queries', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse MySQL slow query log
     */
    private function parseSlowQueryLog(string $output): array
    {
        $queries = [];
        $lines = explode("\n", $output);
        $currentQuery = null;

        foreach ($lines as $line) {
            // Query time line
            if (preg_match('/# Query_time: ([0-9.]+)/', $line, $matches)) {
                if ($currentQuery) {
                    $queries[] = $currentQuery;
                }
                $currentQuery = [
                    'query_time' => (float) $matches[1],
                    'query' => ''
                ];
            }

            // SQL query line
            if ($currentQuery && !str_starts_with($line, '#') && !empty(trim($line))) {
                $currentQuery['query'] .= $line . "\n";
            }
        }

        if ($currentQuery) {
            $queries[] = $currentQuery;
        }

        return $queries;
    }

    /**
     * Detect N+1 queries in Laravel application
     */
    public function detectNPlusOneQueries(Site $site): array
    {
        try {
            Log::info('Detecting N+1 queries', ['site' => $site->domain]);

            // Enable Laravel query logging
            $logPath = $site->path . '/storage/logs/queries.log';

            // Install query analyzer package if not present
            $composerCheck = $this->ssh->execute("cd {$site->path} && composer show | grep 'beyondcode/laravel-query-detector'");
            
            if (empty(trim($composerCheck['output']))) {
                $this->ssh->execute("cd {$site->path} && composer require beyondcode/laravel-query-detector --dev");
            }

            // Publish config
            $this->ssh->execute("cd {$site->path} && php artisan vendor:publish --provider='BeyondCode\\QueryDetector\\QueryDetectorServiceProvider'");

            return [
                'success' => true,
                'message' => 'N+1 query detector installed. Check logs for detection results.'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to detect N+1 queries', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Track user sessions and concurrent users
     */
    public function trackUserSessions(Site $site): array
    {
        try {
            // Parse Nginx access log for active sessions
            $command = "tail -n 10000 /var/log/nginx/{$site->domain}.access.log | awk '{print \$1}' | sort | uniq -c | sort -nr | head -n 50";
            $result = $this->ssh->execute($command);

            $sessions = $this->parseAccessLog($result['output']);

            // Get current connections
            $connectionsResult = $this->ssh->execute("netstat -an | grep ':80\\|:443' | grep ESTABLISHED | wc -l");
            $activeConnections = (int) trim($connectionsResult['output']);

            return [
                'success' => true,
                'unique_ips' => count($sessions),
                'active_connections' => $activeConnections,
                'top_visitors' => array_slice($sessions, 0, 10)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to track user sessions', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse access log for IP addresses
     */
    private function parseAccessLog(string $output): array
    {
        $sessions = [];
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            if (preg_match('/(\d+)\s+([0-9.]+)/', $line, $matches)) {
                $sessions[] = [
                    'ip' => $matches[2],
                    'requests' => (int) $matches[1]
                ];
            }
        }

        return $sessions;
    }

    /**
     * Monitor memory usage
     */
    public function monitorMemoryUsage(Site $site): array
    {
        try {
            // Get PHP-FPM memory usage
            $phpMemResult = $this->ssh->execute("ps aux | grep php-fpm | awk '{sum+=\$6} END {print sum}'");
            $phpMemory = (int) trim($phpMemResult['output']);

            // Get MySQL memory usage
            $mysqlMemResult = $this->ssh->execute("ps aux | grep mysql | awk '{sum+=\$6} END {print sum}'");
            $mysqlMemory = (int) trim($mysqlMemResult['output']);

            // Get Nginx memory usage
            $nginxMemResult = $this->ssh->execute("ps aux | grep nginx | awk '{sum+=\$6} END {print sum}'");
            $nginxMemory = (int) trim($nginxMemResult['output']);

            return [
                'success' => true,
                'php_fpm_mb' => round($phpMemory / 1024, 2),
                'mysql_mb' => round($mysqlMemory / 1024, 2),
                'nginx_mb' => round($nginxMemory / 1024, 2),
                'total_mb' => round(($phpMemory + $mysqlMemory + $nginxMemory) / 1024, 2)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to monitor memory usage', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze application performance
     */
    public function analyzePerformance(Site $site): array
    {
        try {
            Log::info('Analyzing application performance', ['site' => $site->domain]);

            $analysis = [];

            // Response times
            $analysis['response_times'] = $this->trackResponseTimes($site, 50);

            // Memory usage
            $analysis['memory'] = $this->monitorMemoryUsage($site);

            // Active sessions
            $analysis['sessions'] = $this->trackUserSessions($site);

            // Check for common issues
            $analysis['issues'] = $this->checkCommonIssues($site);

            // Generate performance score
            $analysis['score'] = $this->calculatePerformanceScore($analysis);

            return [
                'success' => true,
                'analysis' => $analysis
            ];

        } catch (\Exception $e) {
            Log::error('Failed to analyze performance', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check for common performance issues
     */
    private function checkCommonIssues(Site $site): array
    {
        $issues = [];

        // Check if OPcache is enabled
        $opcacheCheck = $this->ssh->execute("php -i | grep 'opcache.enable'");
        if (strpos($opcacheCheck['output'], 'Off') !== false) {
            $issues[] = [
                'severity' => 'warning',
                'message' => 'OPcache is not enabled',
                'recommendation' => 'Enable OPcache for better PHP performance'
            ];
        }

        // Check if gzip compression is enabled
        $gzipCheck = $this->ssh->execute("curl -I -H 'Accept-Encoding: gzip' https://{$site->domain}/ 2>&1 | grep 'Content-Encoding: gzip'");
        if (empty(trim($gzipCheck['output']))) {
            $issues[] = [
                'severity' => 'info',
                'message' => 'Gzip compression not detected',
                'recommendation' => 'Enable gzip compression in Nginx'
            ];
        }

        // Check SSL certificate
        $sslCheck = $this->ssh->execute("echo | openssl s_client -servername {$site->domain} -connect {$site->domain}:443 2>&1 | grep 'Verify return code'");
        if (strpos($sslCheck['output'], 'ok') === false) {
            $issues[] = [
                'severity' => 'error',
                'message' => 'SSL certificate issue detected',
                'recommendation' => 'Check SSL certificate configuration'
            ];
        }

        return $issues;
    }

    /**
     * Calculate performance score (0-100)
     */
    private function calculatePerformanceScore(array $analysis): int
    {
        $score = 100;

        // Deduct points for slow response times
        if (isset($analysis['response_times']['metrics']['mean_response_time'])) {
            $responseTime = $analysis['response_times']['metrics']['mean_response_time'];
            if ($responseTime > 1000) {
                $score -= 30;
            } elseif ($responseTime > 500) {
                $score -= 15;
            } elseif ($responseTime > 200) {
                $score -= 5;
            }
        }

        // Deduct points for high memory usage
        if (isset($analysis['memory']['total_mb'])) {
            $totalMemory = $analysis['memory']['total_mb'];
            if ($totalMemory > 2000) {
                $score -= 20;
            } elseif ($totalMemory > 1000) {
                $score -= 10;
            }
        }

        // Deduct points for issues
        foreach ($analysis['issues'] ?? [] as $issue) {
            if ($issue['severity'] === 'error') {
                $score -= 15;
            } elseif ($issue['severity'] === 'warning') {
                $score -= 10;
            } elseif ($issue['severity'] === 'info') {
                $score -= 5;
            }
        }

        return max(0, min(100, $score));
    }

    /**
     * Get real-time metrics
     */
    public function getRealTimeMetrics(Site $site): array
    {
        try {
            $metrics = [];

            // CPU usage
            $cpuResult = $this->ssh->execute("top -bn1 | grep 'Cpu(s)' | awk '{print \$2}' | cut -d'%' -f1");
            $metrics['cpu_usage'] = (float) trim($cpuResult['output']);

            // Memory usage
            $memResult = $this->ssh->execute("free | grep Mem | awk '{print (\$3/\$2) * 100.0}'");
            $metrics['memory_usage'] = (float) trim($memResult['output']);

            // Disk usage
            $diskResult = $this->ssh->execute("df -h {$site->path} | tail -1 | awk '{print \$5}' | sed 's/%//'");
            $metrics['disk_usage'] = (float) trim($diskResult['output']);

            // Active connections
            $connResult = $this->ssh->execute("netstat -an | grep ':80\\|:443' | grep ESTABLISHED | wc -l");
            $metrics['active_connections'] = (int) trim($connResult['output']);

            // Load average
            $loadResult = $this->ssh->execute("uptime | awk -F'load average:' '{print \$2}' | cut -d, -f1");
            $metrics['load_average'] = (float) trim($loadResult['output']);

            return [
                'success' => true,
                'metrics' => $metrics,
                'timestamp' => now()->toIso8601String()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get real-time metrics', [
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
