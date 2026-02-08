<?php

namespace App\Providers;

use App\Models\Deployment;
use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use App\Observers\DeploymentObserver;
use App\Observers\ServerObserver;
use App\Observers\SiteObserver;
use App\Observers\UserObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register StackInstallationService as singleton
        $this->app->singleton(\App\Services\StackInstallationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        User::observe(UserObserver::class);
        Server::observe(ServerObserver::class);
        Site::observe(SiteObserver::class);
        Deployment::observe(DeploymentObserver::class);

        // Configure rate limiters
        $this->configureRateLimiters();
    }

    /**
     * Configure custom rate limiters
     */
    protected function configureRateLimiters(): void
    {
        // Deployments rate limiting (10 per minute per user)
        RateLimiter::for('deployments', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many deployment requests. Please slow down.',
                    ], 429);
                });
        });

        // GitHub API calls (30 per minute per user)
        RateLimiter::for('github', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many GitHub API requests. Please try again later.',
                    ], 429);
                });
        });

        // SSH commands (20 per minute per user)
        RateLimiter::for('ssh-commands', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many SSH commands. Please slow down.',
                    ], 429);
                });
        });

        // Cloudflare API calls (60 per minute per user)
        RateLimiter::for('cloudflare', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Backup operations (5 per minute per user)
        RateLimiter::for('backups', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many backup operations. Please wait a moment.',
                    ], 429);
                });
        });

        // Login attempts (5 per minute per IP)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again in a minute.',
                    ], 429);
                });
        });
    }
}

