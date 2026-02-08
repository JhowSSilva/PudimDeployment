<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Server;
use App\Services\AlertManagerService;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    protected AlertManagerService $alertManager;

    public function __construct(AlertManagerService $alertManager)
    {
        $this->middleware('auth');
        $this->alertManager = $alertManager;
    }

    /**
     * Display all alerts.
     */
    public function index(Request $request)
    {
        $team = $request->user()->currentTeam;
        
        $alerts = Alert::where('team_id', $team->id)
            ->with(['server', 'site', 'alertRule', 'acknowledgedByUser'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->severity, fn($q) => $q->where('severity', $request->severity))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = $this->alertManager->getAlertSummary($team);

        return view('alerts.index', compact('alerts', 'summary'));
    }

    /**
     * Show alert details.
     */
    public function show(Alert $alert)
    {
        $this->authorize('view', $alert->server ?? $alert->team);

        $alert->load(['server', 'site', 'alertRule', 'acknowledgedByUser']);

        return view('alerts.show', compact('alert'));
    }

    /**
     * Acknowledge an alert.
     */
    public function acknowledge(Request $request, Alert $alert)
    {
        $this->authorize('manage', $alert->server ?? $alert->team);

        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $this->alertManager->acknowledgeAlert($alert, $request->user(), $request->note);

        return redirect()->back()->with('success', 'Alert acknowledged successfully');
    }

    /**
     * Resolve an alert.
     */
    public function resolve(Request $request, Alert $alert)
    {
        $this->authorize('manage', $alert->server ?? $alert->team);

        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $this->alertManager->resolveAlert($alert, $request->note);

        return redirect()->back()->with('success', 'Alert resolved successfully');
    }

    /**
     * Display alert rules.
     */
    public function rules(Request $request)
    {
        $team = $request->user()->currentTeam;
        
        $rules = AlertRule::where('team_id', $team->id)
            ->with(['server', 'site'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('alerts.rules', compact('rules'));
    }

    /**
     * Show create rule form.
     */
    public function createRule(Request $request)
    {
        $team = $request->user()->currentTeam;
        $servers = $team->servers;

        return view('alerts.create-rule', compact('servers'));
    }

    /**
     * Store a new alert rule.
     */
    public function storeRule(Request $request)
    {
        $team = $request->user()->currentTeam;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'metric_type' => 'required|string',
            'condition' => 'required|in:greater_than,less_than,equals,not_equals',
            'threshold' => 'required|numeric',
            'duration' => 'nullable|integer|min:0',
            'severity' => 'required|in:info,warning,critical',
            'server_id' => 'nullable|exists:servers,id',
            'channels' => 'nullable|array',
            'cooldown' => 'nullable|integer|min:60',
        ]);

        $validated['team_id'] = $team->id;
        $validated['duration'] = $validated['duration'] ?? 300;
        $validated['cooldown'] = $validated['cooldown'] ?? 300;

        AlertRule::create($validated);

        return redirect()->route('alerts.rules')->with('success', 'Alert rule created successfully');
    }

    /**
     * Delete an alert rule.
     */
    public function destroyRule(AlertRule $rule)
    {
        $this->authorize('view', $rule->team);

        $rule->delete();

        return redirect()->back()->with('success', 'Alert rule deleted successfully');
    }

    /**
     * Toggle alert rule active status.
     */
    public function toggleRule(AlertRule $rule)
    {
        $this->authorize('view', $rule->team);

        $rule->update(['is_active' => !$rule->is_active]);

        return redirect()->back()->with('success', 'Alert rule ' . ($rule->is_active ? 'activated' : 'deactivated'));
    }
}
