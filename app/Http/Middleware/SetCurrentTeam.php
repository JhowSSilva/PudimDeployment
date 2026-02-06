<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // If user doesn't have a current team, set to their first team or personal team
            if (!$request->user()->current_team_id) {
                $team = $request->user()->ownedTeams()->where('personal_team', true)->first()
                    ?? $request->user()->teams()->first()
                    ?? $request->user()->ownedTeams()->first();

                if ($team) {
                    $request->user()->update(['current_team_id' => $team->id]);
                }
            }

            // Share current team with all views
            if ($request->user()->current_team_id) {
                view()->share('currentTeam', $request->user()->currentTeam());
            }
        }

        return $next($request);
    }
}
