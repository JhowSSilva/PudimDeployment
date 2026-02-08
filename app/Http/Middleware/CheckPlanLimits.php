<?php

namespace App\Http\Middleware;

use App\Services\BillingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $action  The action to check (e.g., 'create_server', 'create_site')
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $team = $request->user()?->currentTeam;

        if (!$team) {
            return redirect()->route('teams.create')
                ->with('error', 'Você precisa criar ou selecionar um team primeiro.');
        }

        $check = $this->billingService->canPerformAction($team, $action);

        if (!$check['allowed']) {
            return back()->with('error', $check['reason'] ?? 'Limite do plano atingido. Faça upgrade para continuar.');
        }

        return $next($request);
    }
}
