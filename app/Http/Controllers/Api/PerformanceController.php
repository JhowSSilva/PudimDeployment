<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Site;
use App\Services\APMService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PerformanceController extends Controller
{
    /**
     * Track response times
     */
    public function trackResponseTimes(Request $request, Site $site): JsonResponse
    {
        $this->authorize('view', $site->server);

        $validated = $request->validate([
            'requests' => 'nullable|integer|min:10|max:1000',
        ]);

        $apm = new APMService($site->server);
        $result = $apm->trackResponseTimes($site, $validated['requests'] ?? 100);

        return response()->json($result);
    }

    /**
     * Monitor database queries
     */
    public function monitorQueries(Request $request, Site $site): JsonResponse
    {
        $this->authorize('view', $site->server);

        $validated = $request->validate([
            'duration' => 'nullable|integer|min:10|max:300',
        ]);

        $apm = new APMService($site->server);
        $result = $apm->monitorDatabaseQueries($site, $validated['duration'] ?? 60);

        return response()->json($result);
    }

    /**
     * Detect N+1 queries
     */
    public function detectNPlusOne(Site $site): JsonResponse
    {
        $this->authorize('manage', $site->server);

        $apm = new APMService($site->server);
        $result = $apm->detectNPlusOneQueries($site);

        return response()->json($result);
    }

    /**
     * Track user sessions
     */
    public function trackSessions(Site $site): JsonResponse
    {
        $this->authorize('view', $site->server);

        $apm = new APMService($site->server);
        $result = $apm->trackUserSessions($site);

        return response()->json($result);
    }

    /**
     * Monitor memory usage
     */
    public function monitorMemory(Site $site): JsonResponse
    {
        $this->authorize('view', $site->server);

        $apm = new APMService($site->server);
        $result = $apm->monitorMemoryUsage($site);

        return response()->json($result);
    }

    /**
     * Get complete performance analysis
     */
    public function analyze(Site $site): JsonResponse
    {
        $this->authorize('view', $site->server);

        $apm = new APMService($site->server);
        $result = $apm->analyzePerformance($site);

        return response()->json($result);
    }

    /**
     * Get real-time metrics
     */
    public function getRealTimeMetrics(Site $site): JsonResponse
    {
        $this->authorize('view', $site->server);

        $apm = new APMService($site->server);
        $result = $apm->getRealTimeMetrics($site);

        return response()->json($result);
    }
}
