<?php

namespace App\Http\Controllers;

use App\Models\DockerContainer;
use App\Models\Server;
use App\Models\Site;
use App\Services\DockerManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DockerController extends Controller
{
    public function __construct(
        private DockerManager $dockerManager
    ) {}

    /**
     * List all containers on a server
     */
    public function index(Request $request, Server $server)
    {
        $all = $request->boolean('all', false);

        try {
            $containers = $this->dockerManager->listContainers($server, $all);

            return response()->json([
                'success' => true,
                'containers' => $containers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list containers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get database-tracked containers for a server
     */
    public function tracked(Server $server)
    {
        $containers = DockerContainer::where('server_id', $server->id)
            ->with(['site'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'containers' => $containers,
        ]);
    }

    /**
     * Sync containers from Docker to database
     */
    public function sync(Server $server)
    {
        try {
            $synced = $this->dockerManager->syncContainers($server);

            return response()->json([
                'success' => true,
                'message' => "Synced {$synced} containers",
                'count' => $synced,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new container
     */
    public function store(Request $request, Server $server)
    {
        $validated = $request->validate([
            'site_id' => 'nullable|exists:sites,id',
            'name' => 'required|string',
            'image' => 'required|string',
            'ports' => 'nullable|array',
            'volumes' => 'nullable|array',
            'environment' => 'nullable|array',
            'network' => 'nullable|string',
            'restart' => 'nullable|string',
            'memory' => 'nullable|string',
            'cpus' => 'nullable|numeric',
            'privileged' => 'nullable|boolean',
            'working_dir' => 'nullable|string',
            'command' => 'nullable|string',
            'labels' => 'nullable|array',
        ]);

        try {
            $site = $validated['site_id'] ? Site::find($validated['site_id']) : null;
            $container = $this->dockerManager->createContainer($server, $validated, $site);

            return response()->json([
                'success' => true,
                'message' => 'Container created successfully',
                'container' => $container,
            ], 201);

        } catch (\Exception $e) {
            Log::error("Failed to create container: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create container: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get container details
     */
    public function show(DockerContainer $container)
    {
        $container->load(['server', 'site']);

        return response()->json([
            'success' => true,
            'container' => $container,
        ]);
    }

    /**
     * Start a container
     */
    public function start(DockerContainer $container)
    {
        try {
            $this->dockerManager->startContainer($container);

            return response()->json([
                'success' => true,
                'message' => 'Container started',
                'container' => $container->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start container: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop a container
     */
    public function stop(DockerContainer $container)
    {
        try {
            $timeout = request()->input('timeout', 10);
            $this->dockerManager->stopContainer($container, $timeout);

            return response()->json([
                'success' => true,
                'message' => 'Container stopped',
                'container' => $container->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop container: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restart a container
     */
    public function restart(DockerContainer $container)
    {
        try {
            $timeout = request()->input('timeout', 10);
            $this->dockerManager->restartContainer($container, $timeout);

            return response()->json([
                'success' => true,
                'message' => 'Container restarted',
                'container' => $container->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart container: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a container
     */
    public function destroy(DockerContainer $container)
    {
        try {
            $force = request()->boolean('force', false);
            $volumes = request()->boolean('volumes', false);
            
            $this->dockerManager->removeContainer($container, $force, $volumes);

            return response()->json([
                'success' => true,
                'message' => 'Container removed',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove container: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get container logs
     */
    public function logs(DockerContainer $container)
    {
        try {
            $lines = request()->input('lines', 100);
            $logs = $this->dockerManager->getLogs($container, $lines);

            return response()->json([
                'success' => true,
                'logs' => $logs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get container stats
     */
    public function stats(DockerContainer $container)
    {
        try {
            $stats = $this->dockerManager->getStats($container);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute command in container
     */
    public function exec(Request $request, DockerContainer $container)
    {
        $validated = $request->validate([
            'command' => 'required|string',
            'interactive' => 'nullable|boolean',
        ]);

        try {
            $output = $this->dockerManager->executeCommand(
                $container,
                $validated['command'],
                $validated['interactive'] ?? false
            );

            return response()->json([
                'success' => true,
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Command execution failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List images on server
     */
    public function images(Server $server)
    {
        try {
            $images = $this->dockerManager->listImages($server);

            return response()->json([
                'success' => true,
                'images' => $images,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pull an image
     */
    public function pullImage(Request $request, Server $server)
    {
        $validated = $request->validate([
            'image' => 'required|string',
            'tag' => 'nullable|string',
        ]);

        try {
            $this->dockerManager->pullImage(
                $server,
                $validated['image'],
                $validated['tag'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Image pulled successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pull image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove an image
     */
    public function removeImage(Request $request, Server $server)
    {
        $validated = $request->validate([
            'image_id' => 'required|string',
            'force' => 'nullable|boolean',
        ]);

        try {
            $this->dockerManager->removeImage(
                $server,
                $validated['image_id'],
                $validated['force'] ?? false
            );

            return response()->json([
                'success' => true,
                'message' => 'Image removed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List volumes on server
     */
    public function volumes(Server $server)
    {
        try {
            $volumes = $this->dockerManager->listVolumes($server);

            return response()->json([
                'success' => true,
                'volumes' => $volumes,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list volumes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a volume
     */
    public function createVolume(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'driver' => 'nullable|string',
        ]);

        try {
            $this->dockerManager->createVolume(
                $server,
                $validated['name'],
                $validated['driver'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Volume created successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create volume: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a volume
     */
    public function removeVolume(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'force' => 'nullable|boolean',
        ]);

        try {
            $this->dockerManager->removeVolume(
                $server,
                $validated['name'],
                $validated['force'] ?? false
            );

            return response()->json([
                'success' => true,
                'message' => 'Volume removed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove volume: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List networks on server
     */
    public function networks(Server $server)
    {
        try {
            $networks = $this->dockerManager->listNetworks($server);

            return response()->json([
                'success' => true,
                'networks' => $networks,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list networks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a network
     */
    public function createNetwork(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'driver' => 'nullable|string',
        ]);

        try {
            $this->dockerManager->createNetwork(
                $server,
                $validated['name'],
                $validated['driver'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Network created successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create network: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a network
     */
    public function removeNetwork(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        try {
            $this->dockerManager->removeNetwork(
                $server,
                $validated['name']
            );

            return response()->json([
                'success' => true,
                'message' => 'Network removed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove network: ' . $e->getMessage(),
            ], 500);
        }
    }
}
