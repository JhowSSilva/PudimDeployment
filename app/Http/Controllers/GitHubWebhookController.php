<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessGitHubWebhook;
use App\Models\GitHubWebhookEvent;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    /**
     * Handle incoming GitHub webhook
     */
    public function handle(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');
        $event = $request->header('X-GitHub-Event');
        $deliveryId = $request->header('X-GitHub-Delivery');
        $payload = $request->getContent();

        // Verify signature
        $secret = config('services.github.webhook_secret');
        
        if (!GitHubService::verifyWebhookSignature($payload, $signature, $secret)) {
            Log::warning('Invalid GitHub webhook signature', [
                'delivery_id' => $deliveryId,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Store webhook event
        $webhookEvent = GitHubWebhookEvent::create([
            'event_type' => $event,
            'delivery_id' => $deliveryId,
            'signature' => $signature,
            'payload' => json_decode($payload, true),
            'status' => 'pending',
        ]);

        // Dispatch job to process webhook
        ProcessGitHubWebhook::dispatch($webhookEvent);

        return response()->json(['status' => 'accepted'], 202);
    }

    /**
     * Test webhook
     */
    public function test(Request $request)
    {
        return response()->json([
            'message' => 'Webhook endpoint is working',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
