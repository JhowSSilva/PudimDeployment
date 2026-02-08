<?php

namespace App\Http\Controllers;

use App\Models\DeploymentStrategy;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeploymentStrategyController extends Controller
{
    public function index()
    {
        $strategies = DeploymentStrategy::where('team_id', auth()->user()->current_team_id)
            ->with('site')
            ->latest()
            ->paginate(12);

        return view('deployment-strategies.index', compact('strategies'));
    }

    public function create()
    {
        $sites = Site::where('team_id', auth()->user()->current_team_id)->get();
        
        return view('deployment-strategies.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:blue_green,canary,rolling,recreate',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'config' => 'required|array',
            'requires_approval' => 'boolean',
            'health_check_config' => 'nullable|array',
            'rollback_on_failure_percentage' => 'nullable|integer|min:1|max:100',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;

        $strategy = DeploymentStrategy::create($validated);

        return redirect()->route('deployment-strategies.show', $strategy)
            ->with('success', 'Estratégia criada com sucesso!');
    }

    public function show(DeploymentStrategy $deploymentStrategy)
    {
        Gate::authorize('view', $deploymentStrategy);

        $deploymentStrategy->load('site');

        return view('deployment-strategies.show', compact('deploymentStrategy'));
    }

    public function edit(DeploymentStrategy $deploymentStrategy)
    {
        Gate::authorize('update', $deploymentStrategy);

        $sites = Site::where('team_id', $deploymentStrategy->team_id)->get();

        return view('deployment-strategies.edit', compact('deploymentStrategy', 'sites'));
    }

    public function update(Request $request, DeploymentStrategy $deploymentStrategy)
    {
        Gate::authorize('update', $deploymentStrategy);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:blue_green,canary,rolling,recreate',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'config' => 'required|array',
            'requires_approval' => 'boolean',
            'health_check_config' => 'nullable|array',
            'rollback_on_failure_percentage' => 'nullable|integer|min:1|max:100',
        ]);

        $deploymentStrategy->update($validated);

        return redirect()->route('deployment-strategies.show', $deploymentStrategy)
            ->with('success', 'Estratégia atualizada com sucesso!');
    }

    public function destroy(DeploymentStrategy $deploymentStrategy)
    {
        Gate::authorize('delete', $deploymentStrategy);

        $deploymentStrategy->delete();

        return redirect()->route('deployment-strategies.index')
            ->with('success', 'Estratégia excluída com sucesso!');
    }

    public function makeDefault(DeploymentStrategy $deploymentStrategy)
    {
        Gate::authorize('update', $deploymentStrategy);

        $deploymentStrategy->makeDefault();

        return back()->with('success', 'Estratégia definida como padrão!');
    }
}
