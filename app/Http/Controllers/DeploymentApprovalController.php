<?php

namespace App\Http\Controllers;

use App\Models\DeploymentApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeploymentApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get pending count for badge
        $pendingCount = DeploymentApproval::pending()
            ->whereHas('pipelineRun.pipeline', function($q) use ($user) {
                $q->where('team_id', $user->current_team_id);
            })
            ->count();

        // Build query based on status filter
        $query = DeploymentApproval::whereHas('pipelineRun.pipeline', function($q) use ($user) {
                $q->where('team_id', $user->current_team_id);
            })
            ->with(['pipelineRun.pipeline', 'requestedBy', 'deploymentStrategy', 'reviewedBy']);

        // Filter by status if provided
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $approvals = $query->latest()->paginate(20);

        return view('cicd.deployment-approvals.index', compact('approvals', 'pendingCount'));
    }

    public function show(DeploymentApproval $deploymentApproval)
    {
        Gate::authorize('view', $deploymentApproval->pipelineRun->pipeline);

        $deploymentApproval->load([
            'pipelineRun.pipeline.stages',
            'deploymentStrategy',
            'requestedBy',
            'reviewedBy'
        ]);

        return view('cicd.deployment-approvals.show', compact('deploymentApproval'));
    }

    public function approve(Request $request, DeploymentApproval $deploymentApproval)
    {
        Gate::authorize('update', $deploymentApproval->pipelineRun->pipeline);

        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $result = $deploymentApproval->approve(auth()->user(), $validated['comment'] ?? null);

        if (!$result) {
            return back()->with('error', 'Não foi possível aprovar. Verifique se você tem permissão ou se a aprovação já expirou.');
        }

        // If fully approved, continue pipeline execution
        if ($deploymentApproval->fresh()->isApproved()) {
            \App\Jobs\ContinuePipelineJob::dispatch($deploymentApproval->pipelineRun);
        }

        return back()->with('success', 'Deployment aprovado com sucesso!');
    }

    public function reject(Request $request, DeploymentApproval $deploymentApproval)
    {
        Gate::authorize('update', $deploymentApproval->pipelineRun->pipeline);

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $result = $deploymentApproval->reject(auth()->user(), $validated['comment']);

        if (!$result) {
            return back()->with('error', 'Não foi possível rejeitar. A aprovação pode ter expirado.');
        }

        // Mark pipeline run as failed
        $deploymentApproval->pipelineRun->markFailed('Deployment rejeitado por ' . auth()->user()->name);

        return back()->with('success', 'Deployment rejeitado.');
    }
}
