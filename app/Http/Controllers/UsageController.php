<?php

namespace App\Http\Controllers;

use App\Models\UsageMetric;
use Illuminate\Http\Request;

class UsageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the team's usage metrics dashboard.
     */
    public function index(Request $request)
    {
        $team = $request->user()->currentTeam;
        $subscription = $team->activeSubscription();

        // Get current period metrics
        $metrics = UsageMetric::forTeam($team->id)
            ->currentPeriod()
            ->get()
            ->keyBy('metric_type');

        // If no metrics exist, calculate them
        if ($metrics->isEmpty()) {
            UsageMetric::calculateForTeam($team);
            $metrics = UsageMetric::forTeam($team->id)
                ->currentPeriod()
                ->get()
                ->keyBy('metric_type');
        }

        return view('billing.usage', compact('team', 'subscription', 'metrics'));
    }
}
