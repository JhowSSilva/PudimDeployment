<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\ServerMetric;
use App\Services\SSH\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CollectServerMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function handle(SSHService $sshService): void
    {
        try {
            Log::info("Collecting metrics for server", ['server_id' => $this->server->id]);

            if (!$this->server->isActive()) {
                Log::warning("Skipping metrics collection for inactive server", ['server_id' => $this->server->id]);
                return;
            }

            $connection = $sshService->connect($this->server);
            
            // Collect basic system metrics
            $metrics = $this->collectBasicMetrics($connection);
            
            // Collect language-specific metrics based on programming language
            $languageMetrics = $this->collectLanguageSpecificMetrics($connection);
            
            // Merge all metrics
            $allMetrics = array_merge($metrics, $languageMetrics);

            // Store metrics in database
            $this->storeMetrics($allMetrics);

            Log::info("Successfully collected metrics for server", [
                'server_id' => $this->server->id,
                'metrics_count' => count($allMetrics)
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to collect server metrics", [
                'server_id' => $this->server->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function collectBasicMetrics($connection): array
    {
        $metrics = [];

        try {
            // CPU Usage
            $cpuUsage = $connection->exec("top -bn1 | grep \"Cpu(s)\" | awk '{print 100 - $8}'");
            $metrics['cpu_usage'] = floatval(trim($cpuUsage));

            // Memory Usage
            $memInfo = $connection->exec("free -m | grep Mem");
            if (preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $memInfo, $matches)) {
                $total = intval($matches[1]);
                $used = intval($matches[2]);
                $metrics['memory_total'] = $total;
                $metrics['memory_used'] = $used;
                $metrics['memory_usage'] = $total > 0 ? round(($used / $total) * 100, 2) : 0;
            }

            // Disk Usage
            $diskUsage = $connection->exec("df -h / | awk 'NR==2{print $5}' | sed 's/%//'");
            $metrics['disk_usage'] = intval(trim($diskUsage));

            // Load Average
            $loadAvg = $connection->exec("uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//'");
            $metrics['load_average'] = floatval(trim($loadAvg));

            // Network connections
            $networkConnections = $connection->exec("ss -tuln | wc -l");
            $metrics['network_connections'] = intval(trim($networkConnections));

            // Process count
            $processCount = $connection->exec("ps aux | wc -l");
            $metrics['process_count'] = intval(trim($processCount));

        } catch (\Exception $e) {
            Log::warning("Failed to collect some basic metrics", [
                'server_id' => $this->server->id,
                'error' => $e->getMessage()
            ]);
        }

        return $metrics;
    }

    protected function collectLanguageSpecificMetrics($connection): array
    {
        $metrics = [];

        switch ($this->server->programming_language) {
            case 'php':
                $metrics = array_merge($metrics, $this->collectPHPMetrics($connection));
                break;
            case 'nodejs':
                $metrics = array_merge($metrics, $this->collectNodeJSMetrics($connection));
                break;
            case 'python':
                $metrics = array_merge($metrics, $this->collectPythonMetrics($connection));
                break;
        }

        return $metrics;
    }

    protected function collectPHPMetrics($connection): array
    {
        $metrics = [];

        try {
            // PHP Version
            $phpVersion = $connection->exec("php -v | head -n 1 | awk '{print $2}'");
            $metrics['php_version'] = trim($phpVersion);

            // PHP-FPM Status (if available)
            $phpFpmStatus = $connection->exec("systemctl is-active php*-fpm 2>/dev/null | head -n 1");
            $metrics['php_fpm_status'] = trim($phpFpmStatus);

            // Web server status
            $webserverStatus = $connection->exec("systemctl is-active {$this->server->webserver}");
            $metrics['webserver_status'] = trim($webserverStatus);

            // PHP processes
            $phpProcesses = $connection->exec("ps aux | grep -i php | grep -v grep | wc -l");
            $metrics['php_processes'] = intval(trim($phpProcesses));

            // Composer projects (approximation)
            $composerProjects = $connection->exec("find /var/www -name 'composer.json' 2>/dev/null | wc -l");
            $metrics['composer_projects'] = intval(trim($composerProjects));

            // PHP Extensions count
            $phpExtensions = $connection->exec("php -m | wc -l");
            $metrics['php_extensions'] = intval(trim($phpExtensions));

        } catch (\Exception $e) {
            Log::warning("Failed to collect PHP metrics", [
                'server_id' => $this->server->id,
                'error' => $e->getMessage()
            ]);
        }

        return $metrics;
    }

    protected function collectNodeJSMetrics($connection): array
    {
        $metrics = [];

        try {
            // Node.js Version
            $nodeVersion = $connection->exec("node --version 2>/dev/null || echo 'not_installed'");
            $metrics['node_version'] = trim($nodeVersion);

            // NPM Version
            $npmVersion = $connection->exec("npm --version 2>/dev/null || echo 'not_installed'");
            $metrics['npm_version'] = trim($npmVersion);

            // Node processes
            $nodeProcesses = $connection->exec("ps aux | grep -i node | grep -v grep | wc -l");
            $metrics['node_processes'] = intval(trim($nodeProcesses));

            // Package.json projects
            $npmProjects = $connection->exec("find /var/www -name 'package.json' 2>/dev/null | wc -l");
            $metrics['npm_projects'] = intval(trim($npmProjects));

            // Global NPM packages
            $globalPackages = $connection->exec("npm list -g --depth=0 2>/dev/null | grep -c '^[├└]' || echo '0'");
            $metrics['global_npm_packages'] = intval(trim($globalPackages));

            // PM2 processes (if PM2 is installed)
            $pm2Processes = $connection->exec("pm2 list 2>/dev/null | grep -c 'online\\|stopped\\|errored' || echo '0'");
            $metrics['pm2_processes'] = intval(trim($pm2Processes));

        } catch (\Exception $e) {
            Log::warning("Failed to collect Node.js metrics", [
                'server_id' => $this->server->id,
                'error' => $e->getMessage()
            ]);
        }

        return $metrics;
    }

    protected function collectPythonMetrics($connection): array
    {
        $metrics = [];

        try {
            // Python Version
            $pythonVersion = $connection->exec("python3 --version 2>/dev/null | awk '{print $2}' || echo 'not_installed'");
            $metrics['python_version'] = trim($pythonVersion);

            // PIP Version
            $pipVersion = $connection->exec("pip3 --version 2>/dev/null | awk '{print $2}' || echo 'not_installed'");
            $metrics['pip_version'] = trim($pipVersion);

            // Python processes
            $pythonProcesses = $connection->exec("ps aux | grep -i python | grep -v grep | wc -l");
            $metrics['python_processes'] = intval(trim($pythonProcesses));

            // Virtual environments
            $venvs = $connection->exec("find /var/www -name 'pyvenv.cfg' 2>/dev/null | wc -l");
            $metrics['python_venvs'] = intval(trim($venvs));

            // Requirements.txt projects
            $pythonProjects = $connection->exec("find /var/www -name 'requirements.txt' 2>/dev/null | wc -l");
            $metrics['python_projects'] = intval(trim($pythonProjects));

            // Global Python packages
            $globalPackages = $connection->exec("pip3 list 2>/dev/null | wc -l");
            $metrics['global_python_packages'] = intval(trim($globalPackages)) - 1; // Subtract header line

            // Gunicorn processes (if running)
            $gunicornProcesses = $connection->exec("ps aux | grep -i gunicorn | grep -v grep | wc -l");
            $metrics['gunicorn_processes'] = intval(trim($gunicornProcesses));

        } catch (\Exception $e) {
            Log::warning("Failed to collect Python metrics", [
                'server_id' => $this->server->id,
                'error' => $e->getMessage()
            ]);
        }

        return $metrics;
    }

    protected function storeMetrics(array $metrics): void
    {
        foreach ($metrics as $key => $value) {
            ServerMetric::create([
                'server_id' => $this->server->id,
                'metric_name' => $key,
                'metric_value' => $value,
                'collected_at' => Carbon::now(),
            ]);
        }

        // Update server's last metrics update
        $this->server->update(['last_metrics_update' => Carbon::now()]);
    }

    public function failed(\Exception $exception): void
    {
        Log::error("CollectServerMetricsJob failed", [
            'server_id' => $this->server->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
