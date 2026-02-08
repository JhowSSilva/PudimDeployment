<?php

namespace App\Http\Controllers;

use App\Models\ScalingPolicy;
use App\Models\ServerPool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScalingPolicyController extends Controller
{
    /**
     * Display a listing of scaling policies.
     */
    public function index()
    {
        $policies = Auth::user()->currentTeam->scalingPolicies()
            ->with('serverPool')
            ->latest()
            ->paginate(12);

        return view('scaling.policies.index', compact('policies'));
    }

    /**
     * Show the form for creating a new scaling policy.
     */
    public function create()
    {
        $pools = Auth::user()->currentTeam->serverPools;
        return view('scaling.policies.create', compact('pools'));
    }

    /**
     * Store a newly created scaling policy.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'server_pool_id' => 'nullable|exists:server_pools,id',
            'type' => 'required|in:cpu,memory,schedule,custom',
            'metric' => 'nullable|string',
            'threshold_up' => 'nullable|numeric|min:0|max:100',
            'threshold_down' => 'nullable|numeric|min:0|max:100',
            'evaluation_periods' => 'required|integer|min:1',
            'period_duration' => 'required|integer|min:10',
            'scale_up_by' => 'required|integer|min:1',
            'scale_down_by' => 'required|integer|min:1',
            'min_servers' => 'required|integer|min:1',
            'max_servers' => 'required|integer|min:1|gte:min_servers',
            'cooldown_minutes' => 'required|integer|min:1',
            'schedule' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $policy = Auth::user()->currentTeam->scalingPolicies()->create($validated);

        return redirect()->route('scaling.policies.show', $policy)
            ->with('success', 'Scaling policy created successfully!');
    }

    /**
     * Display the specified scaling policy.
     */
    public function show(ScalingPolicy $policy)
    {
        $this->authorize('view', $policy);

        $policy->load('serverPool');

        return view('scaling.policies.show', compact('policy'));
    }

    /**
     * Show the form for editing the specified scaling policy.
     */
    public function edit(ScalingPolicy $policy)
    {
        $this->authorize('update', $policy);

        $pools = Auth::user()->currentTeam->serverPools;

        return view('scaling.policies.edit', compact('policy', 'pools'));
    }

    /**
     * Update the specified scaling policy.
     */
    public function update(Request $request, ScalingPolicy $policy)
    {
        $this->authorize('update', $policy);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'server_pool_id' => 'nullable|exists:server_pools,id',
            'type' => 'required|in:cpu,memory,schedule,custom',
            'metric' => 'nullable|string',
            'threshold_up' => 'nullable|numeric|min:0|max:100',
            'threshold_down' => 'nullable|numeric|min:0|max:100',
            'evaluation_periods' => 'required|integer|min:1',
            'period_duration' => 'required|integer|min:10',
            'scale_up_by' => 'required|integer|min:1',
            'scale_down_by' => 'required|integer|min:1',
            'min_servers' => 'required|integer|min:1',
            'max_servers' => 'required|integer|min:1|gte:min_servers',
            'cooldown_minutes' => 'required|integer|min:1',
            'schedule' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $policy->update($validated);

        return redirect()->route('scaling.policies.show', $policy)
            ->with('success', 'Scaling policy updated successfully!');
    }

    /**
     * Remove the specified scaling policy.
     */
    public function destroy(ScalingPolicy $policy)
    {
        $this->authorize('delete', $policy);

        $policy->delete();

        return redirect()->route('scaling.policies.index')
            ->with('success', 'Scaling policy deleted successfully!');
    }

    /**
     * Toggle policy active state.
     */
    public function toggle(ScalingPolicy $policy)
    {
        $this->authorize('update', $policy);

        $policy->update(['is_active' => !$policy->is_active]);

        return redirect()->back()->with('success', 
            $policy->is_active ? 'Policy activated!' : 'Policy deactivated!');
    }
}
