<?php

namespace App\Http\Controllers;

use App\Models\GitHubRepository;
use App\Models\GitHubWorkflow;
use App\Models\GitHubWorkflowRun;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GitHubWorkflowController extends Controller
{
    public function index(GitHubRepository $repository)
    {
        $this->authorize('view', $repository);
        
        $workflows = $repository->workflows()->with(['latestRuns'])->get();
        $runs = $repository->workflowRuns()
            ->with('workflow')
            ->latest('github_created_at')
            ->paginate(20);

        return view('github.workflows.index', compact('repository', 'workflows', 'runs'));
    }

    public function sync(GitHubRepository $repository)
    {
        $this->authorize('update', $repository);
        
        $service = new WorkflowService(Auth::user());
        
        try {
            $service->syncWorkflows($repository);
            $service->syncWorkflowRuns($repository);
            
            return back()->with('success', 'Workflows synced successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to sync workflows: ' . $e->getMessage());
        }
    }

    public function dispatch(Request $request, GitHubRepository $repository, GitHubWorkflow $workflow)
    {
        $this->authorize('update', $repository);
        
        $request->validate([
            'ref' => 'required|string',
            'inputs' => 'sometimes|array',
        ]);

        $service = new WorkflowService(Auth::user());
        
        if ($service->dispatchWorkflow($repository, $workflow, $request->ref, $request->input('inputs', []))) {
            return back()->with('success', 'Workflow triggered successfully!');
        }
        
        return back()->with('error', 'Failed to trigger workflow');
    }

    public function cancel(GitHubRepository $repository, GitHubWorkflowRun $run)
    {
        $this->authorize('update', $repository);
        
        $service = new WorkflowService(Auth::user());
        
        if ($service->cancelWorkflowRun($repository, $run)) {
            return back()->with('success', 'Workflow cancelled successfully!');
        }
        
        return back()->with('error', 'Failed to cancel workflow');
    }

    public function rerun(GitHubRepository $repository, GitHubWorkflowRun $run)
    {
        $this->authorize('update', $repository);
        
        $service = new WorkflowService(Auth::user());
        
        if ($service->rerunWorkflow($repository, $run)) {
            return back()->with('success', 'Workflow rerun initiated!');
        }
        
        return back()->with('error', 'Failed to rerun workflow');
    }

    public function templates()
    {
        $templates = WorkflowService::getTemplates();
        return view('github.workflows.templates', compact('templates'));
    }
}
