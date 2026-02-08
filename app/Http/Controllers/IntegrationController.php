<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IntegrationController extends Controller
{
    public function index()
    {
        $integrations = Integration::where('team_id', auth()->user()->current_team_id)
            ->latest()
            ->paginate(12);

        return view('cicd.integrations.index', compact('integrations'));
    }

    public function create()
    {
        return view('cicd.integrations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|in:github,gitlab,bitbucket,slack,discord,telegram,webhook',
            'config' => 'required|array',
            'events' => 'nullable|array',
            'webhook_url' => 'nullable|url',
            'webhook_secret' => 'nullable|string',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;
        $validated['status'] = 'active';

        $integration = Integration::create($validated);

        return redirect()->route('integrations.show', $integration)
            ->with('success', 'Integração criada com sucesso!');
    }

    public function show(Integration $integration)
    {
        Gate::authorize('view', $integration);

        return view('cicd.integrations.show', compact('integration'));
    }

    public function edit(Integration $integration)
    {
        Gate::authorize('update', $integration);

        return view('cicd.integrations.edit', compact('integration'));
    }

    public function update(Request $request, Integration $integration)
    {
        Gate::authorize('update', $integration);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|in:github,gitlab,bitbucket,slack,discord,telegram,webhook',
            'config' => 'required|array',
            'events' => 'nullable|array',
            'webhook_url' => 'nullable|url',
            'webhook_secret' => 'nullable|string',
        ]);

        $integration->update($validated);

        return redirect()->route('integrations.show', $integration)
            ->with('success', 'Integração atualizada com sucesso!');
    }

    public function destroy(Integration $integration)
    {
        Gate::authorize('delete', $integration);

        $integration->delete();

        return redirect()->route('integrations.index')
            ->with('success', 'Integração excluída com sucesso!');
    }

    public function toggle(Integration $integration)
    {
        Gate::authorize('update', $integration);

        if ($integration->isActive()) {
            $integration->deactivate();
            $message = 'Integração desativada!';
        } else {
            $integration->activate();
            $message = 'Integração ativada!';
        }

        return back()->with('success', $message);
    }

    public function test(Integration $integration)
    {
        Gate::authorize('update', $integration);

        $result = $integration->trigger('test', [
            'message' => 'Teste de integração',
            'triggered_at' => now()->toIso8601String(),
        ]);

        if ($result) {
            return back()->with('success', 'Teste enviado com sucesso!');
        }

        return back()->with('error', 'Falha ao enviar teste. Verifique a configuração.');
    }
}
