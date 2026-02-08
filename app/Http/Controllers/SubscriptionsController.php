<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\BillingService;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->middleware('auth');
        $this->billingService = $billingService;
    }

    /**
     * Show the current team's subscription.
     */
    public function show(Request $request)
    {
        $team = $request->user()->currentTeam;
        $subscription = $team->activeSubscription();
        
        return view('billing.subscription', compact('team', 'subscription'));
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $team = $request->user()->currentTeam;

        // Check if already subscribed
        if ($team->subscribed()) {
            return redirect()
                ->route('billing.subscription')
                ->with('error', 'Você já possui uma assinatura ativa. Use a opção de trocar plano.');
        }

        try {
            // Create subscription
            $subscription = Subscription::create([
                'team_id' => $team->id,
                'plan_id' => $plan->id,
                'user_id' => $request->user()->id,
                'status' => 'trialing',
                'billing_cycle' => $request->billing_cycle,
                'trial_ends_at' => now()->addDays(14),
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            // Update team plan
            $team->update([
                'plan_id' => $plan->id,
                'plan_limits' => $plan->getLimits(),
            ]);

            return redirect()
                ->route('billing.subscription')
                ->with('success', 'Assinatura criada com sucesso! Você tem 14 dias de teste grátis.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao criar assinatura: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(Request $request)
    {
        $team = $request->user()->currentTeam;
        $subscription = $team->activeSubscription();

        if (!$subscription) {
            return redirect()
                ->route('billing.subscription')
                ->with('error', 'Nenhuma assinatura ativa encontrada.');
        }

        $immediately = $request->boolean('immediately', false);
        $subscription->cancel($immediately);

        $message = $immediately 
            ? 'Assinatura cancelada imediatamente.'
            : 'Assinatura cancelada. Você ainda terá acesso até ' . $subscription->ends_at->format('d/m/Y') . '.';

        return redirect()
            ->route('billing.subscription')
            ->with('success', $message);
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume(Request $request)
    {
        $team = $request->user()->currentTeam;
        $subscription = $team->subscriptions()
            ->where('status', 'canceled')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now())
            ->first();

        if (!$subscription) {
            return redirect()
                ->route('billing.subscription')
                ->with('error', 'Nenhuma assinatura cancelada encontrada para reativar.');
        }

        $subscription->resume();

        return redirect()
            ->route('billing.subscription')
            ->with('success', 'Assinatura reativada com sucesso!');
    }

    /**
     * Swap to a different plan.
     */
    public function swap(Request $request, Plan $plan)
    {
        $team = $request->user()->currentTeam;
        $subscription = $team->activeSubscription();

        if (!$subscription) {
            return redirect()
                ->route('billing.plans')
                ->with('error', 'Você precisa ter uma assinatura ativa para trocar de plano.');
        }

        if ($subscription->plan_id === $plan->id) {
            return redirect()
                ->route('billing.subscription')
                ->with('error', 'Você já está no plano ' . $plan->name . '.');
        }

        try {
            $oldPlan = $subscription->plan;
            $subscription->swap($plan);

            $message = $plan->price > $oldPlan->price
                ? "Upgrade para o plano {$plan->name} realizado com sucesso!"
                : "Downgrade para o plano {$plan->name} realizado. Será aplicado no próximo ciclo.";

            return redirect()
                ->route('billing.subscription')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao trocar de plano: ' . $e->getMessage());
        }
    }
}
