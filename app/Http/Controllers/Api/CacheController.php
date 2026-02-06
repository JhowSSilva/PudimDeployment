<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Site;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CacheController extends Controller
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Enable OPCache on server
     */
    public function enableOPCache(Server $server): JsonResponse
    {
        try {
            $result = $this->cacheService->enableOPCache($server);
            return response()->json([
                'success' => true,
                'message' => 'OPCache enabled successfully',
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
     * Clear OPCache on server
     */
    public function clearOPCache(Server $server): JsonResponse
    {
        try {
            $result = $this->cacheService->clearOPCache($server);
            return response()->json([
                'success' => true,
                'message' => 'OPCache cleared successfully',
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
     * Configure Redis on server
     */
    public function configureRedis(Server $server, Request $request): JsonResponse
    {
        try {
            $config = $request->validate([
                'maxmemory' => 'sometimes|string',
                'maxmemory_policy' => 'sometimes|string',
                'password' => 'sometimes|string'
            ]);

            $result = $this->cacheService->configureRedis($server, $config);
            return response()->json([
                'success' => true,
                'message' => 'Redis configured successfully',
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
     * Clear Redis cache
     */
    public function clearRedis(Server $server): JsonResponse
    {
        try {
            $result = $this->cacheService->clearRedis($server);
            return response()->json([
                'success' => true,
                'message' => 'Redis cache cleared successfully',
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
     * Get Redis info
     */
    public function getRedisInfo(Server $server): JsonResponse
    {
        try {
            $info = $this->cacheService->getRedisInfo($server);
            return response()->json([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Configure Memcached on server
     */
    public function configureMemcached(Server $server, Request $request): JsonResponse
    {
        try {
            $config = $request->validate([
                'memory' => 'sometimes|integer',
                'connections' => 'sometimes|integer'
            ]);

            $result = $this->cacheService->configureMemcached($server, $config);
            return response()->json([
                'success' => true,
                'message' => 'Memcached configured successfully',
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
     * Enable Brotli compression
     */
    public function enableBrotli(Site $site): JsonResponse
    {
        try {
            $result = $this->cacheService->enableBrotli($site);
            return response()->json([
                'success' => true,
                'message' => 'Brotli compression enabled successfully',
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
     * Clear all caches for a site
     */
    public function clearAllCaches(Site $site): JsonResponse
    {
        try {
            $result = $this->cacheService->clearAllCaches($site);
            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
