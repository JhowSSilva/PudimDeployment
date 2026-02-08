<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait StructuredLogging
{
    /**
     * Log with structured context
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, $this->enrichContext($context));
    }

    /**
     * Log warning with structured context
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, $this->enrichContext($context));
    }

    /**
     * Log error with structured context
     */
    protected function logError(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $enrichedContext = $this->enrichContext($context);
        
        if ($exception) {
            $enrichedContext['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }
        
        Log::error($message, $enrichedContext);
    }

    /**
     * Log critical error with structured context
     */
    protected function logCritical(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $enrichedContext = $this->enrichContext($context);
        
        if ($exception) {
            $enrichedContext['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }
        
        Log::critical($message, $enrichedContext);
    }

    /**
     * Enrich context with common metadata
     */
    protected function enrichContext(array $context): array
    {
        $enriched = [
            'timestamp' => now()->toIso8601String(),
            'environment' => config('app.env'),
        ];

        // Add user information if authenticated
        if (Auth::check()) {
            $user = Auth::user();
            $enriched['user'] = [
                'id' => $user->id,
                'email' => $user->email,
            ];
            
            // Add team information if available
            if (method_exists($user, 'getCurrentTeam')) {
                $team = $user->getCurrentTeam();
                if ($team) {
                    $enriched['team'] = [
                        'id' => $team->id,
                        'name' => $team->name,
                    ];
                }
            }
        }

        // Add request information if available
        if (app()->runningInConsole() === false && request()) {
            $enriched['request'] = [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];
        }

        // Merge with provided context
        return array_merge($enriched, $context);
    }

    /**
     * Log deployment related events
     */
    protected function logDeployment(string $message, array $context = []): void
    {
        $context['category'] = 'deployment';
        $this->logInfo($message, $context);
    }

    /**
     * Log security related events
     */
    protected function logSecurity(string $message, array $context = [], string $level = 'warning'): void
    {
        $context['category'] = 'security';
        
        match($level) {
            'info' => $this->logInfo($message, $context),
            'warning' => $this->logWarning($message, $context),
            'error' => $this->logError($message, $context),
            'critical' => $this->logCritical($message, $context),
            default => $this->logWarning($message, $context),
        };
    }

    /**
     * Log performance metrics
     */
    protected function logPerformance(string $message, float $duration, array $context = []): void
    {
        $context['category'] = 'performance';
        $context['duration_ms'] = round($duration * 1000, 2);
        
        $this->logInfo($message, $context);
    }
}
