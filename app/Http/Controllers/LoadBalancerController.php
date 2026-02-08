<?php

namespace App\Http\Controllers;

use App\Models\LoadBalancer;
use App\Models\ServerPool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoadBalancerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of load balancers.
     */
    public function index()
    {
        $loadBalancers = Auth::user()->currentTeam->loadBalancers()
            ->with('serverPool')
            ->latest()
            ->paginate(12);

        return view('scaling.load-balancers.index', compact('loadBalancers'));
    }

    /**
     * Show the form for creating a new load balancer.
     */
    public function create()
    {
        $serverPools = Auth::user()->currentTeam->serverPools;
        return view('scaling.load-balancers.create', compact('serverPools'));
    }

    /**
     * Store a newly created load balancer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'server_pool_id' => 'nullable|exists:server_pools,id',
            'ip_address' => 'nullable|ip',
            'port' => 'required|integer|min:1|max:65535',
            'protocol' => 'required|in:http,https,tcp,udp',
            'algorithm' => 'required|in:round_robin,least_connections,ip_hash,weighted',
            'ssl_enabled' => 'boolean',
            'ssl_certificate' => 'nullable|string',
            'ssl_private_key' => 'nullable|string',
            'health_check_enabled' => 'boolean',
            'health_check_path' => 'nullable|string',
            'health_check_interval' => 'required|integer|min:5',
            'health_check_timeout' => 'required|integer|min:1',
            'healthy_threshold' => 'required|integer|min:1',
            'unhealthy_threshold' => 'required|integer|min:1',
            'sticky_sessions' => 'boolean',
            'session_ttl' => 'nullable|integer|min:60',
        ]);

        $loadBalancer = Auth::user()->currentTeam->loadBalancers()->create($validated);

        return redirect()->route('scaling.load-balancers.show', $loadBalancer)
            ->with('success', 'Load balancer created successfully!');
    }

    /**
     * Display the specified load balancer.
     */
    public function show(LoadBalancer $loadBalancer)
    {
        $this->authorize('view', $loadBalancer);

        $loadBalancer->load(['serverPool.servers', 'healthChecks']);

        return view('scaling.load-balancers.show', compact('loadBalancer'));
    }

    /**
     * Show the form for editing the specified load balancer.
     */
    public function edit(LoadBalancer $loadBalancer)
    {
        $this->authorize('update', $loadBalancer);

        $serverPools = Auth::user()->currentTeam->serverPools;

        return view('scaling.load-balancers.edit', compact('loadBalancer', 'serverPools'));
    }

    /**
     * Update the specified load balancer.
     */
    public function update(Request $request, LoadBalancer $loadBalancer)
    {
        $this->authorize('update', $loadBalancer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'server_pool_id' => 'nullable|exists:server_pools,id',
            'ip_address' => 'nullable|ip',
            'port' => 'required|integer|min:1|max:65535',
            'protocol' => 'required|in:http,https,tcp,udp',
            'algorithm' => 'required|in:round_robin,least_connections,ip_hash,weighted',
            'ssl_enabled' => 'boolean',
            'ssl_certificate' => 'nullable|string',
            'ssl_private_key' => 'nullable|string',
            'health_check_enabled' => 'boolean',
            'health_check_path' => 'nullable|string',
            'health_check_interval' => 'required|integer|min:5',
            'health_check_timeout' => 'required|integer|min:1',
            'healthy_threshold' => 'required|integer|min:1',
            'unhealthy_threshold' => 'required|integer|min:1',
            'sticky_sessions' => 'boolean',
            'session_ttl' => 'nullable|integer|min:60',
            'status' => 'in:active,inactive,error',
        ]);

        $loadBalancer->update($validated);

        return redirect()->route('scaling.load-balancers.show', $loadBalancer)
            ->with('success', 'Load balancer updated successfully!');
    }

    /**
     * Remove the specified load balancer.
     */
    public function destroy(LoadBalancer $loadBalancer)
    {
        $this->authorize('delete', $loadBalancer);

        $loadBalancer->delete();

        return redirect()->route('scaling.load-balancers.index')
            ->with('success', 'Load balancer deleted successfully!');
    }

    /**
     * Get load balancer statistics.
     */
    public function stats(LoadBalancer $loadBalancer)
    {
        $this->authorize('view', $loadBalancer);

        return response()->json([
            'total_requests' => $loadBalancer->total_requests,
            'failed_requests' => $loadBalancer->failed_requests,
            'success_rate' => $loadBalancer->success_rate,
            'error_rate' => $loadBalancer->error_rate,
            'last_health_check' => $loadBalancer->last_health_check_at?->diffForHumans(),
        ]);
    }
}
