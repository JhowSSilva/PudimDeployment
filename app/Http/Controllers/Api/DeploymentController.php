<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deployment;
use App\Models\Site;
use App\Jobs\ExecuteDeployment;
use App\Services\DeploymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeploymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Deployment::query()
            ->whereHas('site.server', fn($q) => $q->where('user_id', $request->user()->id))
            ->with(['site', 'user'])
            ->latest();

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $deployments = $query->paginate(20);

        return response()->json($deployments);
    }

    /**
     * Store a newly created resource in storage (trigger deployment).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
        ]);

        $site = Site::findOrFail($request->site_id);
        
        $this->authorize('deploy', $site);

        // Dispatch deployment job
        ExecuteDeployment::dispatch($site, $request->user(), 'manual');

        return response()->json([
            'message' => 'Deployment job dispatched',
        ], 202);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Deployment $deployment): JsonResponse
    {
        $this->authorize('view', $deployment);

        $deployment->load(['site', 'user']);

        return response()->json([
            'deployment' => $deployment,
        ]);
    }

    /**
     * Rollback to a previous deployment
     */
    public function rollback(Request $request, Site $site): JsonResponse
    {
        $this->authorize('deploy', $site);

        try {
            $deploymentService = new DeploymentService($site);
            $deployment = $deploymentService->rollback($request->user());

            return response()->json([
                'message' => 'Rollback initiated',
                'deployment' => $deployment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Rollback failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test deployment configuration
     */
    public function test(Request $request, Site $site): JsonResponse
    {
        $this->authorize('view', $site);

        try {
            $deploymentService = new DeploymentService($site);
            $tests = $deploymentService->testDeployment();

            return response()->json([
                'tests' => $tests,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
