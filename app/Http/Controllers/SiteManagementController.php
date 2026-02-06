<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Server;
use App\Services\SiteManager;
use App\Enums\ApplicationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteManagementController extends Controller
{
    public function __construct(
        private SiteManager $siteManager
    ) {}

    /**
     * Create a new site
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:sites,domain',
            'application_type' => 'required|string',
            'custom_type' => 'nullable|string',
            'root_directory' => 'nullable|string',
            'document_root' => 'nullable|string',
            'php_version' => 'nullable|string',
            'node_version' => 'nullable|string',
            'package_manager' => 'nullable|string',
            'git_repository' => 'nullable|url',
            'git_branch' => 'nullable|string',
            'git_provider' => 'nullable|string',
            'git_token' => 'nullable|string',
            'web_server' => 'nullable|string',
            'nginx_template' => 'nullable|string',
            'auto_ssl' => 'nullable|boolean',
            'ssl_type' => 'nullable|string',
            'force_https' => 'nullable|boolean',
            'auto_deploy' => 'nullable|boolean',
            'auto_create_database' => 'nullable|boolean',
        ]);

        try {
            $server = Server::findOrFail($validated['server_id']);
            $site = $this->siteManager->createSite($server, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Site created successfully',
                'site' => $site,
            ], 201);

        } catch (\Exception $e) {
            Log::error("Failed to create site: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create site: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get site details
     */
    public function show(Site $site)
    {
        $site->load(['server', 'linkedDatabase', 'dockerContainers', 'deployments']);

        return response()->json([
            'success' => true,
            'site' => $site,
        ]);
    }

    /**
     * Update site configuration
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'application_type' => 'sometimes|string',
            'php_version' => 'sometimes|string',
            'node_version' => 'sometimes|string',
            'auto_ssl' => 'sometimes|boolean',
            'force_https' => 'sometimes|boolean',
            'auto_deploy' => 'sometimes|boolean',
            // Add more updateable fields
        ]);

        $site->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Site updated successfully',
            'site' => $site->fresh(),
        ]);
    }

    /**
     * Delete a site
     */
    public function destroy(Site $site)
    {
        try {
            // TODO: Remove site files and nginx config from server
            $site->delete();

            return response()->json([
                'success' => true,
                'message' => 'Site deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete site: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available application types
     */
    public function applicationTypes()
    {
        $types = collect(ApplicationType::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->label(),
                'requires_php' => $type->requiresPhp(),
                'requires_node' => $type->requiresNode(),
                'requires_database' => $type->requiresDatabase(),
                'default_root_directory' => $type->defaultRootDirectory(),
            ];
        });

        return response()->json([
            'success' => true,
            'types' => $types,
        ]);
    }

    /**
     * Deploy site
     */
    public function deploy(Site $site)
    {
        try {
            // TODO: Implement deploy logic
            // This will pull from git, run build commands, restart services, etc.

            return response()->json([
                'success' => true,
                'message' => 'Deployment started',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deployment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get site environment variables
     */
    public function getEnv(Site $site)
    {
        return response()->json([
            'success' => true,
            'env_variables' => $site->env_variables ?? [],
        ]);
    }

    /**
     * Update environment variables
     */
    public function updateEnv(Request $request, Site $site)
    {
        $validated = $request->validate([
            'variables' => 'required|array',
        ]);

        $site->update(['env_variables' => $validated['variables']]);

        return response()->json([
            'success' => true,'message' => 'Environment variables updated',
        ]);
    }
}
