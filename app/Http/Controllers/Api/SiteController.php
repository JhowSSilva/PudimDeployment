<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Server;
use App\Services\NginxConfigService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Site::query()
            ->whereHas('server', fn($q) => $q->where('user_id', $request->user()->id))
            ->with(['server', 'deployments' => fn($q) => $q->latest()->limit(5)]);

        if ($request->has('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $sites = $query->get();

        return response()->json([
            'sites' => $sites,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'server_id' => 'required|exists:servers,id',
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:sites,domain',
            'document_root' => 'sometimes|string|max:255',
            'php_version' => 'sometimes|in:8.1,8.2,8.3',
            'git_repository' => 'nullable|url',
            'git_branch' => 'sometimes|string|max:255',
            'git_token' => 'nullable|string',
            'deploy_script' => 'nullable|string',
            'env_variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify server belongs to user
        $server = Server::where('id', $request->server_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $site = Site::create($validator->validated());

        return response()->json([
            'message' => 'Site created successfully',
            'site' => $site->load('server'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Site $site): JsonResponse
    {
        $this->authorize('view', $site);

        $site->load([
            'server',
            'deployments' => fn($q) => $q->latest()->limit(20),
        ]);

        return response()->json([
            'site' => $site,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site): JsonResponse
    {
        $this->authorize('update', $site);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255|unique:sites,domain,' . $site->id,
            'document_root' => 'sometimes|string|max:255',
            'php_version' => 'sometimes|in:8.1,8.2,8.3',
            'git_repository' => 'nullable|url',
            'git_branch' => 'sometimes|string|max:255',
            'git_token' => 'nullable|string',
            'deploy_script' => 'nullable|string',
            'env_variables' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $site->update($validator->validated());

        return response()->json([
            'message' => 'Site updated successfully',
            'site' => $site->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Site $site): JsonResponse
    {
        $this->authorize('delete', $site);

        // Remove nginx config
        $nginxService = new NginxConfigService($site);
        $nginxService->remove();

        $site->delete();

        return response()->json([
            'message' => 'Site deleted successfully',
        ]);
    }

    /**
     * Generate and deploy Nginx configuration
     */
    public function deployNginxConfig(Request $request, Site $site): JsonResponse
    {
        $this->authorize('update', $site);

        $validator = Validator::make($request->all(), [
            'ssl' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $ssl = $request->input('ssl', false);

        try {
            $nginxService = new NginxConfigService($site);
            $success = $nginxService->deploy($ssl);

            if ($success) {
                return response()->json([
                    'message' => 'Nginx configuration deployed successfully',
                    'config_path' => $nginxService->getConfigPath(),
                ]);
            }

            return response()->json([
                'message' => 'Failed to deploy Nginx configuration',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deploying Nginx configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview Nginx configuration
     */
    public function previewNginxConfig(Request $request, Site $site): JsonResponse
    {
        $this->authorize('view', $site);

        $ssl = $request->input('ssl', false);

        $nginxService = new NginxConfigService($site);
        $config = $ssl 
            ? $nginxService->generateLaravelConfigWithSSL() 
            : $nginxService->generateLaravelConfig();

        return response()->json([
            'config' => $config,
        ]);
    }
}
