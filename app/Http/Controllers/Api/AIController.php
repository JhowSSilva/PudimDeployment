<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIController extends Controller
{
    /**
     * Predict server load
     */
    public function predictLoad(Request $request, Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $validated = $request->validate([
            'hours_ahead' => 'nullable|integer|min:1|max:168',
        ]);

        $ai = new AIService($server);
        $result = $ai->predictServerLoad($validated['hours_ahead'] ?? 24);

        return response()->json($result);
    }

    /**
     * Optimize server resources
     */
    public function optimizeResources(Server $server): JsonResponse
    {
        $this->authorize('manage', $server);

        $ai = new AIService($server);
        $result = $ai->optimizeResources();

        return response()->json($result);
    }

    /**
     * Detect security threats
     */
    public function detectThreats(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $ai = new AIService($server);
        $result = $ai->detectSecurityThreats();

        return response()->json($result);
    }

    /**
     * Get upgrade recommendations
     */
    public function recommendUpgrades(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        $ai = new AIService($server);
        $result = $ai->recommendUpgrades();

        return response()->json($result);
    }
}
