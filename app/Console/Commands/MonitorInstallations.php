<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\ServerProvisionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class MonitorInstallations extends Command
{
    protected $signature = 'monitor:installations {--server-id=} {--refresh=5}';
    protected $description = 'Monitor server installations in real-time';

    private $colors = [
        'pending' => 'yellow',
        'provisioning' => 'cyan',
        'active' => 'green',
        'failed' => 'red',
        'offline' => 'gray'
    ];

    public function handle()
    {
        $serverId = $this->option('server-id');
        $refresh = (int) $this->option('refresh');

        if ($serverId) {
            $this->monitorSpecificServer($serverId, $refresh);
        } else {
            $this->monitorAllInstallations($refresh);
        }
    }

    private function monitorSpecificServer($serverId, $refresh)
    {
        $server = Server::find($serverId);
        
        if (!$server) {
            $this->error("Server with ID {$serverId} not found.");
            return;
        }

        $this->info("Monitoring installation for server: {$server->name} (ID: {$serverId})");
        $this->line("Press Ctrl+C to stop monitoring\n");

        while (true) {
            $this->displayServerDetail($server->fresh());
            
            if ($server->fresh()->status !== 'provisioning') {
                $this->info("\nInstallation completed or stopped.");
                break;
            }

            sleep($refresh);
            $this->output->write("\033[2J\033[;H"); // Clear screen
        }
    }

    private function monitorAllInstallations($refresh)
    {
        $this->info("Monitoring all server installations");
        $this->line("Press Ctrl+C to stop monitoring\n");

        while (true) {
            $servers = Server::whereIn('status', ['pending', 'provisioning'])
                ->with('provisionLogs')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($servers->isEmpty()) {
                $this->warn("No active installations found.");
                $this->line("Checking again in {$refresh} seconds...\n");
            } else {
                $this->displayServersSummary($servers);
            }

            sleep($refresh);
            $this->output->write("\033[2J\033[;H"); // Clear screen
        }
    }

    private function displayServerDetail($server)
    {
        $color = $this->colors[$server->status] ?? 'white';
        
        $this->line("╔══════════════════════════════════════════════════════════════════════════════╗");
        $this->line("║                          SERVER INSTALLATION DETAIL                           ║");  
        $this->line("╠══════════════════════════════════════════════════════════════════════════════╣");
        
        $this->line(sprintf("║ Server: %-67s ║", $server->name));
        $this->line(sprintf("║ ID: %-71s ║", $server->id));
        $this->line(sprintf("║ Status: <fg={$color}>%-63s</> ║", strtoupper($server->status)));
        $this->line(sprintf("║ Language: %-65s ║", strtoupper($server->programming_language ?? 'php')));
        $this->line(sprintf("║ Version: %-66s ║", $server->language_version ?? 'N/A'));
        $this->line(sprintf("║ Created: %-66s ║", $server->created_at->format('Y-m-d H:i:s')));
        
        $this->line("╠══════════════════════════════════════════════════════════════════════════════╣");
        $this->line("║                                PROGRESS LOGS                                  ║");
        $this->line("╠══════════════════════════════════════════════════════════════════════════════╣");

        // Get installation progress from cache/database
        $progress = $this->getInstallationProgress($server);
        
        if (!empty($progress)) {
            foreach ($progress as $step => $data) {
                $status = $data['status'] ?? 'pending';
                $timestamp = isset($data['timestamp']) ? Carbon::parse($data['timestamp'])->format('H:i:s') : '';
                
                $stepColor = match($status) {
                    'completed' => 'green',
                    'running' => 'yellow', 
                    'failed' => 'red',
                    default => 'gray'
                };
                
                $statusIcon = match($status) {
                    'completed' => '✓',
                    'running' => '⚡',
                    'failed' => '✗',
                    default => '○'
                };
                
                $this->line(sprintf("║ <fg={$stepColor}>%s %s</> %-50s [%s] ║", 
                    $statusIcon, 
                    str_pad(ucfirst(str_replace('_', ' ', $step)), 20),
                    '', 
                    $timestamp
                ));
            }
        } else {
            $this->line("║ No progress data available yet...                                            ║");
        }

        // Show recent logs
        $logs = ServerProvisionLog::where('server_id', $server->id)
            ->latest()
            ->take(5)
            ->get();
            
        if ($logs->isNotEmpty()) {
            $this->line("╠══════════════════════════════════════════════════════════════════════════════╣");
            $this->line("║                                RECENT LOGS                                    ║");
            $this->line("╠══════════════════════════════════════════════════════════════════════════════╣");
            
            foreach ($logs as $log) {
                $logColor = match($log->level) {
                    'error' => 'red',
                    'warning' => 'yellow',
                    'info' => 'cyan',
                    default => 'white'
                };
                
                $message = str_limit($log->message, 60);
                $time = $log->created_at->format('H:i:s');
                
                $this->line(sprintf("║ <fg={$logColor}>[%s]</> %-59s ║", $time, $message));
            }
        }
        
        $this->line("╚══════════════════════════════════════════════════════════════════════════════╝");
        $this->line("Last updated: " . now()->format('Y-m-d H:i:s') . " (refresh every {$this->option('refresh')}s)");
    }

    private function displayServersSummary($servers)
    {
        $this->line("═══════════════════════════════════════════════════════════════════════════════");
        $this->line("                           ACTIVE SERVER INSTALLATIONS");
        $this->line("═══════════════════════════════════════════════════════════════════════════════");
        
        $headers = ['ID', 'Name', 'Status', 'Language', 'Progress', 'Created'];
        $rows = [];
        
        foreach ($servers as $server) {
            $color = $this->colors[$server->status] ?? 'white';
            $progress = $this->calculateProgress($server);
            
            $rows[] = [
                $server->id,
                str_limit($server->name, 20),
                "<fg={$color}>" . strtoupper($server->status) . "</>",
                strtoupper($server->programming_language ?? 'php'),
                $progress . '%',
                $server->created_at->format('H:i:s')
            ];
        }
        
        $this->table($headers, $rows);
        $this->line("Last updated: " . now()->format('Y-m-d H:i:s') . " | Total: " . count($servers) . " installations");
    }

    private function getInstallationProgress($server)
    {
        try {
            // Try to get progress from Redis cache
            $progressKey = "server_installation_progress:{$server->id}";
            $progressData = Redis::get($progressKey);
            
            if ($progressData) {
                return json_decode($progressData, true);
            }
            
            // Fallback to database logs
            $logs = ServerProvisionLog::where('server_id', $server->id)
                ->orderBy('created_at', 'asc')
                ->get();
                
            $progress = [];
            foreach ($logs as $log) {
                if (strpos($log->message, 'Starting') !== false) {
                    $step = $this->extractStepFromMessage($log->message);
                    $progress[$step] = [
                        'status' => 'running',
                        'timestamp' => $log->created_at
                    ];
                } elseif (strpos($log->message, 'Completed') !== false) {
                    $step = $this->extractStepFromMessage($log->message);
                    $progress[$step] = [
                        'status' => 'completed', 
                        'timestamp' => $log->created_at
                    ];
                }
            }
            
            return $progress;
            
        } catch (\Exception $e) {
            return [];
        }
    }

    private function extractStepFromMessage($message)
    {
        if (strpos($message, 'base system') !== false) return 'base_system';
        if (strpos($message, 'programming stack') !== false) return 'programming_stack';
        if (strpos($message, 'web server') !== false) return 'web_server';
        if (strpos($message, 'database') !== false) return 'database';
        if (strpos($message, 'cache') !== false) return 'cache';
        if (strpos($message, 'firewall') !== false) return 'firewall';
        
        return 'general';
    }

    private function calculateProgress($server)
    {
        $progress = $this->getInstallationProgress($server);
        
        if (empty($progress)) {
            return $server->status === 'provisioning' ? 10 : 0;
        }
        
        $totalSteps = 6; // base_system, programming_stack, web_server, database, cache, firewall
        $completedSteps = 0;
        
        foreach ($progress as $step => $data) {
            if (($data['status'] ?? '') === 'completed') {
                $completedSteps++;
            }
        }
        
        return round(($completedSteps / $totalSteps) * 100);
    }
}
