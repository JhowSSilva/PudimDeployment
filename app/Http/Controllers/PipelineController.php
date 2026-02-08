<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PipelineController extends Controller
{
    public function index()
    {
        $pipelines = Pipeline::where('team_id', auth()->user()->current_team_id)
            ->with(['site', 'stages'])
            ->withCount('runs')
            ->latest()
            ->paginate(12);

        return view('cicd.pipelines.index', compact('pipelines'));
    }

    public function create()
    {
        $sites = Site::where('team_id', auth()->user()->current_team_id)->get();
        
        return view('cicd.pipelines.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'trigger_type' => 'required|in:manual,push,pull_request,schedule,webhook',
            'trigger_config' => 'nullable|array',
            'auto_deploy' => 'boolean',
            'timeout_minutes' => 'integer|min:1|max:180',
            'environment_variables' => 'nullable|array',
            'retention_days' => 'integer|min:1|max:365',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;
        $validated['status'] = 'active';

        $pipeline = Pipeline::create($validated);

        return redirect()->route('pipelines.show', $pipeline)
            ->with('success', 'Pipeline criado com sucesso!');
    }

    public function show(Pipeline $pipeline)
    {
        Gate::authorize('view', $pipeline);

        $pipeline->load(['stages', 'site', 'runs' => function($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'total_runs' => $pipeline->runs()->count(),
            'success_rate' => $pipeline->getSuccessRate(),
            'avg_duration' => $pipeline->getAverageDuration(),
            'last_success' => $pipeline->getLastSuccessfulRun(),
        ];

        return view('cicd.pipelines.show', compact('pipeline', 'stats'));
    }

    public function edit(Pipeline $pipeline)
    {
        Gate::authorize('update', $pipeline);

        $sites = Site::where('team_id', $pipeline->team_id)->get();

        return view('cicd.pipelines.edit', compact('pipeline', 'sites'));
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        Gate::authorize('update', $pipeline);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
            'trigger_type' => 'required|in:manual,push,pull_request,schedule,webhook',
            'trigger_config' => 'nullable|array',
            'status' => 'required|in:active,paused,disabled',
            'auto_deploy' => 'boolean',
            'timeout_minutes' => 'integer|min:1|max:180',
            'environment_variables' => 'nullable|array',
            'retention_days' => 'integer|min:1|max:365',
        ]);

        $pipeline->update($validated);

        return redirect()->route('pipelines.show', $pipeline)
            ->with('success', 'Pipeline atualizado com sucesso!');
    }

    public function destroy(Pipeline $pipeline)
    {
        Gate::authorize('delete', $pipeline);

        $pipeline->delete();

        return redirect()->route('pipelines.index')
            ->with('success', 'Pipeline excluído com sucesso!');
    }

    public function pause(Pipeline $pipeline)
    {
        Gate::authorize('update', $pipeline);

        $pipeline->pause();

        return back()->with('success', 'Pipeline pausado!');
    }

    public function activate(Pipeline $pipeline)
    {
        Gate::authorize('update', $pipeline);

        $pipeline->activate();

        return back()->with('success', 'Pipeline ativado!');
    }

    public function run(Pipeline $pipeline)
    {
        Gate::authorize('update', $pipeline);

        if (!$pipeline->canRun()) {
            return back()->with('error', 'Pipeline não pode ser executado. Verifique se está ativo e tem stages configurados.');
        }

        // Dispatch job to run pipeline
        \App\Jobs\RunPipelineJob::dispatch($pipeline, auth()->user());

        return back()->with('success', 'Pipeline iniciado!');
    }
}

