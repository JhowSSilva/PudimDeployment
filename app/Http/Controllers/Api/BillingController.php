<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Team;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BillingController extends Controller
{
    public function __construct(
        private BillingService $billingService
    ) {}

    /**
     * Get server costs
     */
    public function getServerCosts(Server $server): JsonResponse
    {
        try {
            $costs = $this->billingService->calculateServerCost($server);
            return response()->json([
                'success' => true,
                'data' => $costs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate invoice for team
     */
    public function generateInvoice(Team $team, Request $request): JsonResponse
    {
        try {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $invoice = $this->billingService->generateInvoice($team, $month, $year);
            return response()->json([
                'success' => true,
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cost forecast
     */
    public function getForecast(Team $team): JsonResponse
    {
        try {
            $forecast = $this->billingService->getForecast($team);
            return response()->json([
                'success' => true,
                'forecast' => $forecast
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track usage for server
     */
    public function trackUsage(Server $server, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cpu_hours' => 'sometimes|numeric',
                'bandwidth_gb' => 'sometimes|numeric',
                'storage_gb_hours' => 'sometimes|numeric',
                'backup_gb' => 'sometimes|numeric'
            ]);

            $this->billingService->trackUsage($server, $validated);
            return response()->json([
                'success' => true,
                'message' => 'Usage tracked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get usage summary for server
     */
    public function getUsageSummary(Server $server, Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'current_month');
            $summary = $this->billingService->getUsageSummary($server, $period);
            
            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manage subscription
     */
    public function manageSubscription(Team $team, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:subscribe,cancel,upgrade,downgrade',
                'plan' => 'required_if:action,subscribe,upgrade,downgrade|string'
            ]);

            $result = $this->billingService->manageSubscription(
                $team,
                $validated['action'],
                $validated['plan'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
