<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\SSHConnectionService;
use App\Services\MetricsCollectorService;
use App\Jobs\CollectServerMetrics;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $servers = $request->user()
            ->servers()
            ->with(['metrics' => fn($q) => $q->latest()->limit(1)])
            ->get();

        return response()->json([
            'servers' => $servers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'ssh_port' => 'sometimes|integer|between:1,65535',
            'ssh_user' => 'sometimes|string|max:255',
            'auth_type' => 'required|in:password,key',
            'ssh_key' => 'required_if:auth_type,key',
            'ssh_password' => 'required_if:auth_type,password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $server = $request->user()->servers()->create($validator->validated());

        return response()->json([
            'message' => 'Server created successfully',
            'server' => $server,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $server->load([
            'metrics' => fn($q) => $q->latest()->limit(60), // Last 60 metrics
            'sites',
        ]);

        return response()->json([
            'server' => $server,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server): JsonResponse
    {
        $this->authorize('update', $server);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'ip_address' => 'sometimes|ip',
            'ssh_port' => 'sometimes|integer|between:1,65535',
            'ssh_user' => 'sometimes|string|max:255',
            'auth_type' => 'sometimes|in:password,key',
            'ssh_key' => 'sometimes|nullable',
            'ssh_password' => 'sometimes|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $server->update($validator->validated());

        return response()->json([
            'message' => 'Server updated successfully',
            'server' => $server->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Server $server): JsonResponse
    {
        $this->authorize('delete', $server);

        $server->delete();

        return response()->json([
            'message' => 'Server deleted successfully',
        ]);
    }

    /**
     * Test SSH connection to server
     */
    public function testConnection(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        try {
            $ssh = new SSHConnectionService($server);
            $isConnected = $ssh->testConnection();

            if ($isConnected) {
                // Detect OS
                $osInfo = $ssh->detectOS();
                
                $server->update([
                    'os_type' => $osInfo['os_type'],
                    'os_version' => $osInfo['os_version'],
                    'status' => 'online',
                    'last_ping_at' => now(),
                ]);

                return response()->json([
                    'message' => 'Connection successful',
                    'connected' => true,
                    'os_info' => $osInfo,
                ]);
            }

            return response()->json([
                'message' => 'Connection failed',
                'connected' => false,
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Connection error: ' . $e->getMessage(),
                'connected' => false,
            ], 500);
        }
    }

    /**
     * Get real-time metrics
     */
    public function metrics(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        try {
            $collector = new MetricsCollectorService($server);
            $metrics = $collector->getRealTimeMetrics();

            return response()->json([
                'metrics' => $metrics,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to collect metrics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger metrics collection job
     */
    public function collectMetrics(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        CollectServerMetrics::dispatch($server);

        return response()->json([
            'message' => 'Metrics collection job dispatched',
        ]);
    }
}
