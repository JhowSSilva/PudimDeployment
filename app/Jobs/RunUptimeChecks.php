<?php

namespace App\Jobs;

use App\Models\UptimeCheck;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RunUptimeChecks implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running uptime checks');

        try {
            // Get all checks that are due
            $checks = UptimeCheck::dueForCheck()->get();
            
            Log::info('Found uptime checks to run', ['count' => $checks->count()]);
            
            foreach ($checks as $check) {
                $this->runCheck($check);
            }
            
        } catch (\Exception $e) {
            Log::error('Error running uptime checks: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Run a single uptime check.
     */
    protected function runCheck(UptimeCheck $check): void
    {
        try {
            $startTime = microtime(true);
            $success = false;
            $responseTime = 0;
            
            switch ($check->check_type) {
                case UptimeCheck::TYPE_HTTP:
                case UptimeCheck::TYPE_HTTPS:
                    $success = $this->checkHttp($check, $responseTime);
                    break;
                    
                case UptimeCheck::TYPE_TCP:
                    $success = $this->checkTcp($check, $responseTime);
                    break;
                    
                case UptimeCheck::TYPE_ICMP:
                    $success = $this->checkIcmp($check, $responseTime);
                    break;
                    
                default:
                    Log::warning("Unknown check type: {$check->check_type}");
                    return;
            }
            
            if ($success) {
                $check->recordSuccess((int) $responseTime);
            } else {
                $check->recordFailure();
            }
            
        } catch (\Exception $e) {
            Log::error("Error running uptime check #{$check->id}: " . $e->getMessage());
            $check->recordFailure();
        }
    }
    
    /**
     * Check HTTP/HTTPS endpoint.
     */
    protected function checkHttp(UptimeCheck $check, &$responseTime): bool
    {
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout($check->timeout)
                ->get($check->url);
                
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            // Check status code
            if ($check->expected_status_code && $response->status() !== $check->expected_status_code) {
                return false;
            }
            
            // Check content if specified
            if ($check->expected_content && !str_contains($response->body(), $check->expected_content)) {
                return false;
            }
            
            return $response->successful();
            
        } catch (\Exception $e) {
            Log::debug("HTTP check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check TCP connection.
     */
    protected function checkTcp(UptimeCheck $check, &$responseTime): bool
    {
        try {
            $parts = parse_url($check->url);
            $host = $parts['host'] ?? $check->url;
            $port = $parts['port'] ?? 80;
            
            $startTime = microtime(true);
            
            $socket = @fsockopen($host, $port, $errno, $errstr, $check->timeout);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($socket) {
                fclose($socket);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check ICMP (ping).
     */
    protected function checkIcmp(UptimeCheck $check, &$responseTime): bool
    {
        try {
            $host = parse_url($check->url, PHP_URL_HOST) ?? $check->url;
            
            $startTime = microtime(true);
            
            $result = @exec("ping -c 1 -W {$check->timeout} {$host}", $output, $returnCode);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            return $returnCode === 0;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
