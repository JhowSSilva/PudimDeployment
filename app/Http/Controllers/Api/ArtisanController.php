<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\ArtisanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArtisanController extends Controller
{
    /**
     * Allowed artisan commands.
     */
    private const ALLOWED_COMMANDS = [
        'list', 'env', 'about',
        'config:clear', 'config:cache',
        'cache:clear', 'cache:forget',
        'route:clear', 'route:cache', 'route:list',
        'view:clear', 'view:cache',
        'optimize', 'optimize:clear',
        'queue:failed', 'queue:retry', 'queue:restart',
        'migrate', 'migrate:status',
        'schedule:list',
        'down', 'up',
    ];

    public function __construct(
        private ArtisanService $artisanService
    ) {}

    /**
     * Run custom artisan command
     */
    public function runCommand(Site $site, Request $request): JsonResponse
    {
        $this->authorize('update', $site);

        try {
            $validated = $request->validate([
                'command' => 'required|string|max:500'
            ]);

            $baseCommand = explode(' ', trim($validated['command']))[0];
            if (!in_array($baseCommand, self::ALLOWED_COMMANDS)) {
                return response()->json([
                    'success' => false,
                    'message' => "Command '{$baseCommand}' is not allowed."
                ], 422);
            }

            $result = $this->artisanService->runCommand($site, $validated['command']);
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run migrations
     */
    public function migrate(Site $site, Request $request): JsonResponse
    {
        try {
            $fresh = $request->boolean('fresh', false);
            $seed = $request->boolean('seed', false);

            $result = $this->artisanService->migrate($site, $fresh, $seed);
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rollback migrations
     */
    public function rollback(Site $site, Request $request): JsonResponse
    {
        try {
            $steps = $request->integer('steps', 1);
            $result = $this->artisanService->rollback($site, $steps);
            
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(Site $site): JsonResponse
    {
        try {
            $result = $this->artisanService->clearCache($site);
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize application
     */
    public function optimize(Site $site): JsonResponse
    {
        try {
            $result = $this->artisanService->optimize($site);
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run database seeders
     */
    public function seed(Site $site, Request $request): JsonResponse
    {
        try {
            $class = $request->string('class', 'DatabaseSeeder');
            $result = $this->artisanService->seed($site, $class);
            
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenance(Site $site, Request $request): JsonResponse
    {
        try {
            $message = $request->string('message', 'Application is under maintenance');
            $result = $this->artisanService->enableMaintenance($site, $message);
            
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenance(Site $site): JsonResponse
    {
        try {
            $result = $this->artisanService->disableMaintenance($site);
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Setup scheduler
     */
    public function setupScheduler(Site $site): JsonResponse
    {
        try {
            $result = $this->artisanService->setupScheduler($site);
            return response()->json([
                'success' => true,
                'message' => 'Scheduler configured successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List scheduled tasks
     */
    public function listTasks(Site $site): JsonResponse
    {
        try {
            $tasks = $this->artisanService->listScheduledTasks($site);
            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart queues
     */
    public function restartQueues(Site $site): JsonResponse
    {
        try {
            $result = $this->artisanService->restartQueues($site);
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get failed jobs
     */
    public function failedJobs(Site $site): JsonResponse
    {
        try {
            $jobs = $this->artisanService->getFailedJobs($site);
            return response()->json([
                'success' => true,
                'jobs' => $jobs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry failed job
     */
    public function retryJob(Site $site, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'job_id' => 'required|string'
            ]);

            $result = $this->artisanService->retryFailedJob($site, $validated['job_id']);
            return response()->json([
                'success' => true,
                'output' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
