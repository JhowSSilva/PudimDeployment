<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\DeploymentController;
use App\Http\Controllers\SiteManagementController;
use App\Http\Controllers\DockerController;
use App\Http\Controllers\LaravelToolsController;

Route::middleware(['auth:api'])->group(function () {
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
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
