<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGitHubTokenValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasGitHubConnected()) {
            return redirect()->route('github.connect')
                ->with('info', 'Please connect your GitHub account to access this feature');
        }

        return $next($request);
    }
}
