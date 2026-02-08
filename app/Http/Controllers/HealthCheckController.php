<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    /**
     * Comprehensive health check endpoint
     */
    public function check(): JsonResponse
    {
        $checks = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => []
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            DB::table('users')->limit(1)->count();
            $checks['services']['database'] = [
                'status' => 'ok',
                'connection' => DB::getDefaultConnection(),
            ];
        } catch (\Exception $e) {
            $checks['status'] = 'error';
            $checks['services']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed',
            ];
        }

        // Cache/Redis check
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            $value = Cache::get($testKey);
            Cache::forget($testKey);
            
            $checks['services']['cache'] = [
                'status' => $value === 'test' ? 'ok' : 'warning',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            $checks['services']['cache'] = [
                'status' => 'warning',
                'message' => 'Cache not available',
            ];
        }

        // Queue check
        try {
            $queueSize = Queue::size();
            $checks['services']['queue'] = [
                'status' => $queueSize < 1000 ? 'ok' : 'warning',
                'size' => $queueSize,
                'connection' => config('queue.default'),
            ];
            
            if ($queueSize >= 1000) {
                $checks['status'] = 'warning';
            }
        } catch (\Exception $e) {
            $checks['services']['queue'] = [
                'status' => 'warning',
                'message' => 'Queue status unavailable',
            ];
        }

        // Disk space check
        try {
            $diskFree = disk_free_space('/');
            $diskTotal = disk_total_space('/');
            $diskUsedPercent = (($diskTotal - $diskFree) / $diskTotal) * 100;
            
            $diskStatus = 'ok';
            if ($diskUsedPercent > 90) {
                $diskStatus = 'critical';
                $checks['status'] = 'critical';
            } elseif ($diskUsedPercent > 80) {
                $diskStatus = 'warning';
                if ($checks['status'] === 'ok') {
                    $checks['status'] = 'warning';
                }
            }
            
            $checks['services']['disk'] = [
                'status' => $diskStatus,
                'free' => $this->formatBytes($diskFree),
                'total' => $this->formatBytes($diskTotal),
                'used_percent' => round($diskUsedPercent, 2),
            ];
        } catch (\Exception $e) {
            $checks['services']['disk'] = [
                'status' => 'warning',
                'message' => 'Disk space check failed',
            ];
        }

        // Application info
        $checks['application'] = [
            'name' => config('app.name'),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ];

        // Set HTTP status code based on overall status
        $httpStatus = match($checks['status']) {
            'ok' => 200,
            'warning' => 200,
            'critical' => 503,
            'error' => 503,
            default => 500,
        };

        return response()->json($checks, $httpStatus);
    }

    /**
     * Simple ping endpoint
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
