<?php

namespace App\Http\Controllers;

use App\Models\ServerPool;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerPoolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of server pools.
     */
    public function index()
    {
        $pools = Auth::user()->currentTeam->serverPools()
            ->withCount('servers')
            ->latest()
            ->paginate(12);

        return view('scaling.pools.index', compact('pools'));
    }

    /**
     * Show the form for creating a new server pool.
     */
    public function create()
    {
        $servers = Auth::user()->currentTeam->servers;
        return view('scaling.pools.create', compact('servers'));
    }

    /**
     * Store a newly created server pool.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            'environment' => 'required|in:production,staging,development',
            'min_servers' => 'required|integer|min:1',
            'max_servers' => 'required|integer|min:1|gte:min_servers',
            'desired_servers' => 'required|integer|min:1|gte:min_servers|lte:max_servers',
            'auto_healing' => 'boolean',
            'health_check_interval' => 'required|integer|min:10',
            'servers' => 'nullable|array',
            'servers.*' => 'exists:servers,id',
        ]);

        $pool = Auth::user()->currentTeam->serverPools()->create($validated);

        // Attach servers if provided
        if (!empty($validated['servers'])) {
            foreach ($validated['servers'] as $serverId) {
                $pool->addServer(Server::find($serverId));
            }
        }

        return redirect()->route('scaling.pools.show', $pool)
            ->with('success', 'Server pool created successfully!');
    }

    /**
     * Display the specified server pool.
     */
    public function show(ServerPool $pool)
    {
        $this->authorize('view', $pool);

        $pool->load(['servers', 'scalingPolicies', 'loadBalancers']);
        $healthStatus = $pool->health_status;

        return view('scaling.pools.show', compact('pool', 'healthStatus'));
    }

    /**
     * Show the form for editing the specified server pool.
     */
    public function edit(ServerPool $pool)
    {
        $this->authorize('update', $pool);

        $servers = Auth::user()->currentTeam->servers;
        $selectedServers = $pool->servers->pluck('id')->toArray();

        return view('scaling.pools.edit', compact('pool', 'servers', 'selectedServers'));
    }

    /**
     * Update the specified server pool.
     */
    public function update(Request $request, ServerPool $pool)
    {
        $this->authorize('update', $pool);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            'environment' => 'required|in:production,staging,development',
            'min_servers' => 'required|integer|min:1',
            'max_servers' => 'required|integer|min:1|gte:min_servers',
            'desired_servers' => 'required|integer|min:1|gte:min_servers|lte:max_servers',
            'auto_healing' => 'boolean',
            'health_check_interval' => 'required|integer|min:10',
            'status' => 'in:active,inactive',
            'servers' => 'nullable|array',
            'servers.*' => 'exists:servers,id',
        ]);

        $pool->update($validated);

        // Sync servers
        if (isset($validated['servers'])) {
            $pool->servers()->sync([]);
            foreach ($validated['servers'] as $serverId) {
                $pool->addServer(Server::find($serverId));
            }
        }

        return redirect()->route('scaling.pools.show', $pool)
            ->with('success', 'Server pool updated successfully!');
    }

    /**
     * Remove the specified server pool.
     */
    public function destroy(ServerPool $pool)
    {
        $this->authorize('delete', $pool);

        $pool->delete();

        return redirect()->route('scaling.pools.index')
            ->with('success', 'Server pool deleted successfully!');
    }

    /**
     * Add a server to the pool.
     */
    public function addServer(Request $request, ServerPool $pool)
    {
        $this->authorize('update', $pool);

        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'weight' => 'nullable|integer|min:1|max:1000',
        ]);

        $server = Server::findOrFail($validated['server_id']);
        $pool->addServer($server, $validated['weight'] ?? 100);

        return redirect()->back()->with('success', 'Server added to pool!');
    }

    /**
     * Remove a server from the pool.
     */
    public function removeServer(Request $request, ServerPool $pool)
    {
        $this->authorize('update', $pool);

        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
        ]);

        $server = Server::findOrFail($validated['server_id']);
        $pool->removeServer($server);

        return redirect()->back()->with('success', 'Server removed from pool!');
    }
}
