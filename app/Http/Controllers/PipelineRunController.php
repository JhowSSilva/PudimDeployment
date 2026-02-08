<?php

namespace App\Http\Controllers;

use App\Models\PipelineRun;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PipelineRunController extends Controller
{
    public function index(Pipeline $pipeline)
    {
        Gate::authorize('view', $pipeline);

        $runs = $pipeline->runs()
            ->with('triggeredBy')
            ->paginate(20);

        return view('cicd.pipeline-runs.index', compact('pipeline', 'runs'));
    }

    public function show(PipelineRun $pipelineRun)
    {
        Gate::authorize('view', $pipelineRun->pipeline);

        $pipelineRun->load(['pipeline.stages', 'triggeredBy', 'deployment']);

        return view('cicd.pipeline-runs.show', compact('pipelineRun'));
    }

    public function cancel(PipelineRun $pipelineRun)
    {
        Gate::authorize('update', $pipelineRun->pipeline);

        if (!$pipelineRun->isRunning() && !$pipelineRun->isPending()) {
            return back()->with('error', 'Apenas execuções pendentes ou em andamento podem ser canceladas.');
        }

        $pipelineRun->cancel();

        return back()->with('success', 'Execução cancelada com sucesso!');
    }

    public function retry(PipelineRun $pipelineRun)
    {
        Gate::authorize('update', $pipelineRun->pipeline);

        if (!$pipelineRun->isFinished()) {
            return back()->with('error', 'Apenas execuções finalizadas podem ser reexecutadas.');
        }

        // Create a new run with same configuration
        \App\Jobs\RunPipelineJob::dispatch(
            $pipelineRun->pipeline,
            auth()->user(),
            [
                'git_branch' => $pipelineRun->git_branch,
                'git_commit_hash' => $pipelineRun->git_commit_hash,
            ]
        );

        return redirect()->route('pipelines.show', $pipelineRun->pipeline)
            ->with('success', 'Nova execução iniciada!');
    }

    public function logs(PipelineRun $pipelineRun)
    {
        Gate::authorize('view', $pipelineRun->pipeline);

        return response()->json([
            'output' => $pipelineRun->output_log,
            'error' => $pipelineRun->error_log,
            'status' => $pipelineRun->status,
        ]);
    }

    public function destroy(PipelineRun $pipelineRun)
    {
        Gate::authorize('delete', $pipelineRun->pipeline);

        if ($pipelineRun->isRunning()) {
            return back()->with('error', 'Não é possível excluir uma execução em andamento.');
        }

        $pipelineRun->delete();

        return back()->with('success', 'Execução excluída com sucesso!');
    }
}
