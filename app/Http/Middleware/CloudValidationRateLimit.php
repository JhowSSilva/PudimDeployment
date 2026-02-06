<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CloudValidationRateLimit
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): ResponseAlias
    {
        $key = 'cloud-validation:' . $request->ip();
        
        if ($this->limiter->tooManyAttempts($key, 5)) {
            return response()->json([
                'error' => 'Muitas tentativas de validação. Tente novamente em alguns minutos.',
            ], 429);
        }

        $this->limiter->hit($key, 300); // 5 minutes

        return $next($request);
    }
}