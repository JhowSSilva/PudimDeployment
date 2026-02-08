<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\DeploymentController;
use App\Http\Controllers\SiteManagementController;
use App\Http\Controllers\DockerController;
use App\Http\Controllers\LaravelToolsController;
use App\Http\Controllers\GitHubRepositoryController;

// Health check endpoint (public, no auth required)
Route::get('/health', function () {
    $checks = [];
    $healthy = true;

    // Database check
    try {
        DB::connection()->getPdo();
        $checks['database'] = [
            'status' => 'healthy',
            'message' => 'Database connection successful',
        ];
    } catch (\Exception $e) {
        $healthy = false;
        $checks['database'] = [
            'status' => 'unhealthy',
            'message' => 'Database connection failed: ' . $e->getMessage(),
        ];
    }

    // Redis check
    try {
        Redis::connection()->ping();
        $checks['redis'] = [
            'status' => 'healthy',
            'message' => 'Redis connection successful',
        ];
    } catch (\Exception $e) {
        $healthy = false;
        $checks['redis'] = [
            'status' => 'unhealthy',
            'message' => 'Redis connection failed: ' . $e->getMessage(),
        ];
    }

    // Storage check
    try {
        $testFile = 'health-check-' . time() . '.txt';
        Storage::disk('local')->put($testFile, 'test');
        Storage::disk('local')->delete($testFile);
        $checks['storage'] = [
            'status' => 'healthy',
            'message' => 'Storage read/write successful',
        ];
    } catch (\Exception $e) {
        $healthy = false;
        $checks['storage'] = [
            'status' => 'unhealthy',
            'message' => 'Storage failed: ' . $e->getMessage(),
        ];
    }

    // Queue check
    try {
        $queueSize = Queue::size();
        $checks['queue'] = [
            'status' => 'healthy',
            'message' => 'Queue connection successful',
            'size' => $queueSize,
        ];
    } catch (\Exception $e) {
        $healthy = false;
        $checks['queue'] = [
            'status' => 'unhealthy',
            'message' => 'Queue connection failed: ' . $e->getMessage(),
        ];
    }

    // Disk space check
    $diskFree = disk_free_space('/');
    $diskTotal = disk_total_space('/');
    $diskUsedPercent = (($diskTotal - $diskFree) / $diskTotal) * 100;
    
    if ($diskUsedPercent > 90) {
        $healthy = false;
        $checks['disk'] = [
            'status' => 'unhealthy',
            'message' => 'Disk space critical',
            'used_percent' => round($diskUsedPercent, 2),
        ];
    } else {
        $checks['disk'] = [
            'status' => 'healthy',
            'message' => 'Disk space sufficient',
            'used_percent' => round($diskUsedPercent, 2),
        ];
    }

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
        'version' => config('app.version', '1.0.0'),
    ], $healthy ? 200 : 503);
});

Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // GitHub Repositories
    Route::get('/github/repositories', function (Request $request) {
        $user = $request->user();
        
        if (!$user->hasGitHubConnected()) {
            return response()->json(['error' => 'GitHub não conectado'], 403);
        }

        try {
            $service = new \App\Services\RepositoryService($user);
            $repositories = $service->syncRepositories();
            
            return response()->json([
                'repositories' => $repositories->map(function ($repo) {
                    return [
                        'id' => $repo->id,
                        'name' => $repo->name,
                        'full_name' => $repo->full_name,
                        'clone_url' => $repo->clone_url,
                        'ssh_url' => $repo->ssh_url,
                        'default_branch' => $repo->default_branch,
                        'description' => $repo->description,
                        'is_private' => $repo->is_private,
                        'language' => $repo->language,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    // Servers
    Route::apiResource('servers', ServerController::class);
    Route::post('/servers/{server}/test-connection', [ServerController::class, 'testConnection']);
    Route::get('/servers/{server}/metrics', [ServerController::class, 'metrics']);
    Route::post('/servers/{server}/collect-metrics', [ServerController::class, 'collectMetrics']);

    // Sites
    Route::apiResource('sites', SiteController::class);
    Route::post('/sites/{site}/nginx/deploy', [SiteController::class, 'deployNginxConfig']);
    Route::get('/sites/{site}/nginx/preview', [SiteController::class, 'previewNginxConfig']);

    // Deployments
    Route::apiResource('deployments', DeploymentController::class)->only(['index', 'show', 'store']);
    Route::post('/sites/{site}/rollback', [DeploymentController::class, 'rollback']);
    Route::get('/sites/{site}/test-deployment', [DeploymentController::class, 'test']);

    // Site Management (Advanced Site Creation & Management)
    Route::prefix('site-management')->group(function () {
        Route::post('/sites', [SiteManagementController::class, 'create']);
        Route::get('/sites/{site}', [SiteManagementController::class, 'show']);
        Route::put('/sites/{site}', [SiteManagementController::class, 'update']);
        Route::delete('/sites/{site}', [SiteManagementController::class, 'destroy']);
        Route::get('/application-types', [SiteManagementController::class, 'applicationTypes']);
        Route::post('/sites/{site}/deploy', [SiteManagementController::class, 'deploy']);
        Route::get('/sites/{site}/env', [SiteManagementController::class, 'getEnv']);
        Route::put('/sites/{site}/env', [SiteManagementController::class, 'updateEnv']);
    });

    // Docker Management
    Route::prefix('servers/{server}/docker')->group(function () {
        // Containers
        Route::get('/containers', [DockerController::class, 'index']);
        Route::get('/containers/tracked', [DockerController::class, 'tracked']);
        Route::post('/containers/sync', [DockerController::class, 'sync']);
        Route::post('/containers', [DockerController::class, 'store']);
        
        // Images
        Route::get('/images', [DockerController::class, 'images']);
        Route::post('/images/pull', [DockerController::class, 'pullImage']);
        Route::delete('/images', [DockerController::class, 'removeImage']);
        
        // Volumes
        Route::get('/volumes', [DockerController::class, 'volumes']);
        Route::post('/volumes', [DockerController::class, 'createVolume']);
        Route::delete('/volumes', [DockerController::class, 'removeVolume']);
        
        // Networks
        Route::get('/networks', [DockerController::class, 'networks']);
        Route::post('/networks', [DockerController::class, 'createNetwork']);
        Route::delete('/networks', [DockerController::class, 'removeNetwork']);
    });

    // Docker Container Operations
    Route::prefix('docker/containers/{container}')->group(function () {
        Route::get('/', [DockerController::class, 'show']);
        Route::post('/start', [DockerController::class, 'start']);
        Route::post('/stop', [DockerController::class, 'stop']);
        Route::post('/restart', [DockerController::class, 'restart']);
        Route::delete('/', [DockerController::class, 'destroy']);
        Route::get('/logs', [DockerController::class, 'logs']);
        Route::get('/stats', [DockerController::class, 'stats']);
        Route::post('/exec', [DockerController::class, 'exec']);
    });

    // Laravel Tools
    Route::prefix('sites/{site}/laravel')->group(function () {
        Route::post('/artisan', [LaravelToolsController::class, 'artisan']);
        Route::get('/artisan/commands', [LaravelToolsController::class, 'listCommands']);
        Route::post('/migrate', [LaravelToolsController::class, 'migrate']);
        Route::post('/cache/clear', [LaravelToolsController::class, 'clearCache']);
        Route::post('/optimize', [LaravelToolsController::class, 'optimize']);
        Route::get('/logs', [LaravelToolsController::class, 'logs']);
        Route::post('/composer', [LaravelToolsController::class, 'composer']);
        Route::get('/queue/status', [LaravelToolsController::class, 'queueStatus']);
        Route::get('/environment', [LaravelToolsController::class, 'environment']);
    });
});
