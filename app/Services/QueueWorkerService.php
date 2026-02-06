<?php

namespace App\Services;

use App\Models\Server;
use App\Models\QueueWorker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueueWorkerService
{
    private SSHConnectionService $ssh;

    public function __construct(private Server $server)
    {
        $this->ssh = new SSHConnectionService($server);
    }

    /**
     * Start a queue worker
     */
    public function startWorker(string $queue = 'default', int $processes = 1, array $options = []): array
    {
        try {
            $workerId = Str::uuid();
            $pidFile = "/var/run/queue-worker-{$workerId}.pid";
            
            // Build command
            $command = $this->buildWorkerCommand($queue, $options);
            $daemonCommand = "nohup {$command} > /var/log/queue-worker-{$workerId}.log 2>&1 & echo \$! > {$pidFile}";
            
            // Start worker processes
            for ($i = 0; $i < $processes; $i++) {
                $result = $this->ssh->execute($daemonCommand);
                
                if ($result['exit_code'] !== 0) {
                    throw new \Exception("Failed to start worker process {$i}: " . $result['output']);
                }
            }
            
            // Read PID
            $pidResult = $this->ssh->execute("cat {$pidFile}");
            $pid = trim($pidResult['output']);
            
            // Create worker record
            $worker = QueueWorker::create([
                'server_id' => $this->server->id,
                'worker_id' => $workerId,
                'queue' => $queue,
                'processes' => $processes,
                'pid' => $pid,
                'pid_file' => $pidFile,
                'log_file' => "/var/log/queue-worker-{$workerId}.log",
                'command' => $command,
                'status' => 'running',
                'options' => $options,
                'started_at' => now(),
            ]);
            
            Log::info("Queue worker started", [
                'server_id' => $this->server->id,
                'worker_id' => $workerId,
                'queue' => $queue,
                'processes' => $processes,
            ]);
            
            return [
                'success' => true,
                'worker' => $worker,
                'message' => "Queue worker started successfully",
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to start queue worker", [
                'server_id' => $this->server->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Stop a queue worker
     */
    public function stopWorker(QueueWorker $worker): array
    {
        try {
            // Kill process using PID
            if ($worker->pid) {
                $result = $this->ssh->execute("kill {$worker->pid}");
                if ($result['exit_code'] !== 0) {
                    // Try force kill
                    $this->ssh->execute("kill -9 {$worker->pid}");
                }
            }
            
            // Remove PID file
            if ($worker->pid_file) {
                $this->ssh->execute("rm -f {$worker->pid_file}");
            }
            
            // Update worker status
            $worker->update([
                'status' => 'stopped',
                'stopped_at' => now(),
            ]);
            
            Log::info("Queue worker stopped", [
                'worker_id' => $worker->worker_id,
            ]);
            
            return [
                'success' => true,
                'message' => "Queue worker stopped successfully",
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to stop queue worker", [
                'worker_id' => $worker->worker_id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restart a queue worker
     */
    public function restartWorker(QueueWorker $worker): array
    {
        $stopResult = $this->stopWorker($worker);
        
        if (!$stopResult['success']) {
            return $stopResult;
        }
        
        // Wait a moment
        sleep(2);
        
        $startResult = $this->startWorker(
            $worker->queue,
            $worker->processes,
            $worker->options ?? []
        );
        
        if ($startResult['success']) {
            // Delete old worker record
            $worker->delete();
            
            return [
                'success' => true,
                'worker' => $startResult['worker'],
                'message' => "Queue worker restarted successfully",
            ];
        }
        
        return $startResult;
    }

    /**
     * Restart all workers on server
     */
    public function restartAllWorkers(): array
    {
        try {
            $workers = $this->server->queueWorkers()
                ->where('status', 'running')
                ->get();
            
            $restartedCount = 0;
            $errors = [];
            
            foreach ($workers as $worker) {
                $result = $this->restartWorker($worker);
                
                if ($result['success']) {
                    $restartedCount++;
                } else {
                    $errors[] = "Worker {$worker->worker_id}: {$result['message']}";
                }
            }
            
            $message = "Restarted {$restartedCount} workers";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode('; ', $errors);
            }
            
            return [
                'success' => empty($errors),
                'restarted_count' => $restartedCount,
                'message' => $message,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get worker status
     */
    public function getWorkerStatus(QueueWorker $worker): array
    {
        try {
            // Check if process is running
            $result = $this->ssh->execute("ps -p {$worker->pid}");
            $isRunning = $result['exit_code'] === 0;
            
            // Get log tail
            $logResult = $this->ssh->execute("tail -n 50 {$worker->log_file}");
            $logContent = $logResult['exit_code'] === 0 ? $logResult['output'] : 'Log file not found';
            
            // Update status if needed
            if (!$isRunning && $worker->status === 'running') {
                $worker->update([
                    'status' => 'stopped',
                    'stopped_at' => now(),
                ]);
            }
            
            return [
                'success' => true,
                'is_running' => $isRunning,
                'log_content' => $logContent,
                'worker' => $worker->fresh(),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Monitor failed jobs
     */
    public function getFailedJobs(): array
    {
        try {
            // Check if we have artisan available on server
            $result = $this->ssh->execute("cd /var/www && php artisan queue:failed --format=json");
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to get failed jobs: " . $result['output']);
            }
            
            $failedJobs = [];
            $output = trim($result['output']);
            
            if (!empty($output)) {
                $lines = explode("\n", $output);
                foreach ($lines as $line) {
                    $job = json_decode($line, true);
                    if ($job) {
                        $failedJobs[] = $job;
                    }
                }
            }
            
            return [
                'success' => true,
                'failed_jobs' => $failedJobs,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retry failed jobs
     */
    public function retryFailedJobs(array $jobIds = []): array
    {
        try {
            $command = "cd /var/www && php artisan queue:retry";
            
            if (!empty($jobIds)) {
                $command .= " " . implode(' ', $jobIds);
            } else {
                $command .= " all";
            }
            
            $result = $this->ssh->execute($command);
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to retry jobs: " . $result['output']);
            }
            
            return [
                'success' => true,
                'message' => "Failed jobs retried successfully",
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear failed jobs
     */
    public function clearFailedJobs(): array
    {
        try {
            $result = $this->ssh->execute("cd /var/www && php artisan queue:flush");
            
            if ($result['exit_code'] !== 0) {
                throw new \Exception("Failed to clear failed jobs: " . $result['output']);
            }
            
            return [
                'success' => true,
                'message' => "Failed jobs cleared successfully",
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build worker command
     */
    private function buildWorkerCommand(string $queue, array $options): string
    {
        $command = "cd /var/www && php artisan queue:work";
        
        if ($queue !== 'default') {
            $command .= " --queue={$queue}";
        }
        
        // Add common options
        $command .= " --daemon";
        $command .= " --sleep=" . ($options['sleep'] ?? 3);
        $command .= " --tries=" . ($options['tries'] ?? 3);
        $command .= " --timeout=" . ($options['timeout'] ?? 60);
        
        if (isset($options['memory'])) {
            $command .= " --memory={$options['memory']}";
        }
        
        if (isset($options['max_jobs'])) {
            $command .= " --max-jobs={$options['max_jobs']}";
        }
        
        return $command;
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        try {
            $stats = [];
            
            // Get queue sizes (requires Redis/Database check)
            $result = $this->ssh->execute("cd /var/www && php artisan queue:monitor default,high,low --max=0");
            
            if ($result['exit_code'] === 0) {
                // Parse queue monitor output
                $lines = explode("\n", trim($result['output']));
                foreach ($lines as $line) {
                    if (preg_match('/(\w+):\s*(\d+)/', $line, $matches)) {
                        $stats['queue_sizes'][$matches[1]] = (int) $matches[2];
                    }
                }
            }
            
            return [
                'success' => true,
                'stats' => $stats,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}