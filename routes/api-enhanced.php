<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FirewallController;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\CacheController;
use App\Http\Controllers\Api\ArtisanController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\DatabaseController;

/*
|--------------------------------------------------------------------------
| Enhanced API Routes
|--------------------------------------------------------------------------
|
| Enhanced API routes for advanced server management features
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // Firewall Management
    Route::prefix('servers/{server}/firewall')->group(function () {
        Route::post('configure', [FirewallController::class, 'configure']);
        Route::get('status', [FirewallController::class, 'getStatus']);
        Route::get('rules', [FirewallController::class, 'getRules']);
        Route::post('rules', [FirewallController::class, 'addRule']);
        Route::delete('rules', [FirewallController::class, 'removeRule']);
        Route::post('block-ip', [FirewallController::class, 'blockIp']);
        Route::post('unblock-ip', [FirewallController::class, 'unblockIp']);
        Route::post('fail2ban/enable', [FirewallController::class, 'enableFail2ban']);
        Route::get('fail2ban/banned', [FirewallController::class, 'getBannedIps']);
    });

    // Performance Monitoring (APM)
    Route::prefix('sites/{site}/performance')->group(function () {
        Route::post('response-times', [PerformanceController::class, 'trackResponseTimes']);
        Route::post('monitor-queries', [PerformanceController::class, 'monitorQueries']);
        Route::post('detect-nplusone', [PerformanceController::class, 'detectNPlusOne']);
        Route::get('sessions', [PerformanceController::class, 'trackSessions']);
        Route::get('memory', [PerformanceController::class, 'monitorMemory']);
        Route::get('analyze', [PerformanceController::class, 'analyze']);
        Route::get('realtime', [PerformanceController::class, 'getRealTimeMetrics']);
    });

    // AI & Machine Learning
    Route::prefix('servers/{server}/ai')->group(function () {
        Route::get('predict-load', [AIController::class, 'predictLoad']);
        Route::post('optimize', [AIController::class, 'optimizeResources']);
        Route::get('detect-threats', [AIController::class, 'detectThreats']);
        Route::get('recommendations', [AIController::class, 'recommendUpgrades']);
    });

    // Cache Management
    Route::prefix('servers/{server}/cache')->group(function () {
        Route::post('opcache/enable', [CacheController::class, 'enableOPCache']);
        Route::post('opcache/clear', [CacheController::class, 'clearOPCache']);
        Route::post('redis/configure', [CacheController::class, 'configureRedis']);
        Route::post('redis/clear', [CacheController::class, 'clearRedis']);
        Route::get('redis/info', [CacheController::class, 'getRedisInfo']);
        Route::post('memcached/configure', [CacheController::class, 'configureMemcached']);
    });

    Route::prefix('sites/{site}/cache')->group(function () {
        Route::post('brotli/enable', [CacheController::class, 'enableBrotli']);
        Route::post('clear-all', [CacheController::class, 'clearAllCaches']);
    });

    // Laravel Artisan Commands
    Route::prefix('sites/{site}/artisan')->group(function () {
        Route::post('command', [ArtisanController::class, 'runCommand']);
        Route::post('migrate', [ArtisanController::class, 'migrate']);
        Route::post('migrate/rollback', [ArtisanController::class, 'rollback']);
        Route::post('cache/clear', [ArtisanController::class, 'clearCache']);
        Route::post('optimize', [ArtisanController::class, 'optimize']);
        Route::post('seed', [ArtisanController::class, 'seed']);
        Route::post('maintenance/enable', [ArtisanController::class, 'enableMaintenance']);
        Route::post('maintenance/disable', [ArtisanController::class, 'disableMaintenance']);
        Route::post('scheduler/setup', [ArtisanController::class, 'setupScheduler']);
        Route::get('scheduler/tasks', [ArtisanController::class, 'listTasks']);
        Route::post('queue/restart', [ArtisanController::class, 'restartQueues']);
        Route::get('queue/failed', [ArtisanController::class, 'failedJobs']);
        Route::post('queue/retry', [ArtisanController::class, 'retryJob']);
    });

    // Billing & Usage
    Route::prefix('billing')->group(function () {
        Route::get('servers/{server}/costs', [BillingController::class, 'getServerCosts']);
        Route::get('teams/{team}/invoice', [BillingController::class, 'generateInvoice']);
        Route::get('teams/{team}/forecast', [BillingController::class, 'getForecast']);
        Route::post('servers/{server}/track-usage', [BillingController::class, 'trackUsage']);
        Route::get('servers/{server}/usage-summary', [BillingController::class, 'getUsageSummary']);
        Route::post('teams/{team}/subscription', [BillingController::class, 'manageSubscription']);
    });

    // Database Management (Enhanced)
    Route::prefix('databases/{database}')->group(function () {
        Route::post('backup', [DatabaseController::class, 'createBackup']);
        Route::post('restore', [DatabaseController::class, 'restoreBackup']);
        Route::post('automated-backups', [DatabaseController::class, 'setupAutomatedBackups']);
        Route::post('replication', [DatabaseController::class, 'setupReplication']);
        Route::get('size', [DatabaseController::class, 'getSize']);
        Route::post('optimize', [DatabaseController::class, 'optimize']);
    });
});
