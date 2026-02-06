<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\QueueWorker;
use App\Services\QueueWorkerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class QueueWorkerController extends Controller
{
    /**
     * Global queue workers index - shows workers from all servers
     */
    public function globalIndex()
    {
        $currentTeam = Auth::user()->currentTeam();
        
        $servers = $currentTeam->servers()
            ->with('queueWorkers')
            ->get();
            
        return view('queue-workers.global-index', compact('servers'));
    }
    
    /**
     * Display queue workers for a server
     */
    public function index(Server $server)
    {
        $workers = $server->queueWorkers()
            ->latest()
            ->get();

        $queueService = new QueueWorkerService($server);
        $statsResult = $queueService->getQueueStats();
        $stats = $statsResult['success'] ? $statsResult['stats'] : [];

        return view('queue-workers.index', compact('server', 'workers', 'stats'));
    }

    /**
     * Show form to create worker
     */
    public function create(Server $server)
    {
        return view('queue-workers.create', compact('server'));
    }

    /**
     * Start new queue worker
     */
    public function store(Server $server, Request $request)
    {
        $request->validate([
            'queue' => 'required|string|max:255',
            'processes' => 'required|integer|min:1|max:10',
            'sleep' => 'nullable|integer|min:1|max:60',
            'tries' => 'nullable|integer|min:1|max:10',
            'timeout' => 'nullable|integer|min:30|max:3600',
            'memory' => 'nullable|integer|min:128|max:2048',
            'max_jobs' => 'nullable|integer|min:1|max:10000',
        ]);

        $options = array_filter([
            'sleep' => $request->input('sleep'),
            'tries' => $request->input('tries'),
            'timeout' => $request->input('timeout'),
            'memory' => $request->input('memory'),
            'max_jobs' => $request->input('max_jobs'),
        ]);

        $queueService = new QueueWorkerService($server);
        $result = $queueService->startWorker(
            $request->input('queue'),
            $request->input('processes'),
            $options
        );

        if ($result['success']) {
            return redirect()->route('servers.queue-workers.index', $server)
                ->with('success', $result['message']);
        }

        return back()->withErrors(['worker' => $result['message']])->withInput();
    }

    /**
     * Show worker details and logs
     */
    public function show(Server $server, QueueWorker $worker)
    {
        $queueService = new QueueWorkerService($server);
        $statusResult = $queueService->getWorkerStatus($worker);
        
        $status = $statusResult['success'] ? $statusResult : [
            'is_running' => false,
            'log_content' => 'Unable to fetch status',
        ];

        return view('queue-workers.show', compact('server', 'worker', 'status'));
    }

    /**
     * Stop queue worker
     */
    public function stop(Server $server, QueueWorker $worker)
    {
        $queueService = new QueueWorkerService($server);
        $result = $queueService->stopWorker($worker);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['worker' => $result['message']]);
    }

    /**
     * Restart queue worker
     */
    public function restart(Server $server, QueueWorker $worker)
    {
        $queueService = new QueueWorkerService($server);
        $result = $queueService->restartWorker($worker);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['worker' => $result['message']]);
    }

    /**
     * Delete queue worker
     */
    public function destroy(Server $server, QueueWorker $worker)
    {
        // Stop worker first if running
        if ($worker->isRunning()) {
            $queueService = new QueueWorkerService($server);
            $queueService->stopWorker($worker);
        }

        $worker->delete();

        return redirect()->route('servers.queue-workers.index', $server)
            ->with('success', 'Queue worker deleted successfully');
    }

    /**
     * Restart all workers on server
     */
    public function restartAll(Server $server)
    {
        $queueService = new QueueWorkerService($server);
        $result = $queueService->restartAllWorkers();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['workers' => $result['message']]);
    }

    /**
     * Get failed jobs (API)
     */
    public function failedJobs(Server $server): JsonResponse
    {
        $queueService = new QueueWorkerService($server);
        $result = $queueService->getFailedJobs();

        return response()->json($result);
    }

    /**
     * Retry failed jobs
     */
    public function retryFailedJobs(Server $server, Request $request)
    {
        $request->validate([
            'job_ids' => 'nullable|array',
            'job_ids.*' => 'string',
        ]);

        $queueService = new QueueWorkerService($server);
        $result = $queueService->retryFailedJobs($request->input('job_ids', []));

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['failed_jobs' => $result['message']]);
    }

    /**
     * Clear all failed jobs
     */
    public function clearFailedJobs(Server $server)
    {
        $queueService = new QueueWorkerService($server);
        $result = $queueService->clearFailedJobs();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['failed_jobs' => $result['message']]);
    }

    /**
     * Get worker logs (API)
     */
    public function logs(Server $server, QueueWorker $worker): JsonResponse
    {
        $queueService = new QueueWorkerService($server);
        $result = $queueService->getWorkerStatus($worker);

        return response()->json([
            'success' => $result['success'],
            'logs' => $result['log_content'] ?? 'No logs available',
            'is_running' => $result['is_running'] ?? false,
        ]);
    }

    /**
     * Get queue statistics (API)
     */
    public function stats(Server $server): JsonResponse
    {
        $queueService = new QueueWorkerService($server);
        $result = $queueService->getQueueStats();

        return response()->json($result);
    }
}