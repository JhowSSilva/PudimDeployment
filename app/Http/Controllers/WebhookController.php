<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhookService
    ) {}

    /**
     * Receive webhook from Git provider
     */
    public function receive(Request $request, int $siteId, string $token)
    {
        $site = Site::findOrFail($siteId);

        // Verify token
        if (!hash_equals($site->webhook_secret ?? '', $token)) {
            Log::warning('Webhook: Invalid token', [
                'site_id' => $siteId,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Invalid token'], 403);
        }

        // Check if auto-deploy is enabled
        if (!$site->auto_deploy_enabled) {
            Log::info('Webhook: Auto-deploy disabled', ['site_id' => $siteId]);
            return response()->json(['message' => 'Auto-deploy is disabled'], 200);
        }

        // Get payload
        $payload = $request->json()->all();
        $deployment = null;

        // Determine provider and process webhook
        if ($request->header('X-GitHub-Event')) {
            // GitHub webhook
            $signature = $request->header('X-Hub-Signature-256');
            $rawPayload = $request->getContent();

            if (!$this->webhookService->validateGitHubSignature($rawPayload, $signature, $site->webhook_secret)) {
                Log::warning('Webhook: Invalid GitHub signature', ['site_id' => $siteId]);
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $deployment = $this->webhookService->processGitHubWebhook($site, $payload);

        } elseif ($request->header('X-Gitlab-Event')) {
            // GitLab webhook
            $gitlabToken = $request->header('X-Gitlab-Token');

            if (!$this->webhookService->validateGitLabToken($gitlabToken, $site->webhook_secret)) {
                Log::warning('Webhook: Invalid GitLab token', ['site_id' => $siteId]);
                return response()->json(['error' => 'Invalid token'], 403);
            }

            $deployment = $this->webhookService->processGitLabWebhook($site, $payload);

        } elseif ($request->header('X-Event-Key')) {
            // Bitbucket webhook
            $signature = $request->header('X-Hub-Signature');
            $rawPayload = $request->getContent();

            if ($signature && !$this->webhookService->validateBitbucketSignature($rawPayload, $signature, $site->webhook_secret)) {
                Log::warning('Webhook: Invalid Bitbucket signature', ['site_id' => $siteId]);
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $deployment = $this->webhookService->processBitbucketWebhook($site, $payload);

        } else {
            Log::warning('Webhook: Unknown provider', ['site_id' => $siteId]);
            return response()->json(['error' => 'Unknown webhook provider'], 400);
        }

        if ($deployment) {
            return response()->json([
                'message' => 'Deployment triggered successfully',
                'deployment_id' => $deployment->id
            ], 200);
        }

        return response()->json(['message' => 'Webhook received but no deployment triggered'], 200);
    }

    /**
     * Enable auto-deploy for a site
     */
    public function enable(Site $site, Request $request)
    {
        $request->validate([
            'provider' => 'required|in:github,gitlab,bitbucket',
        ]);

        $webhookUrl = $this->webhookService->generateWebhookUrl($site);
        
        $site->update([
            'auto_deploy_enabled' => true,
            'webhook_provider' => $request->provider,
            'webhook_url' => $webhookUrl,
        ]);

        $instructions = $this->webhookService->getSetupInstructions($site, $request->provider);

        return response()->json([
            'message' => 'Auto-deploy enabled',
            'webhook_url' => $webhookUrl,
            'webhook_secret' => $site->webhook_secret,
            'setup_instructions' => $instructions
        ]);
    }

    /**
     * Disable auto-deploy for a site
     */
    public function disable(Site $site)
    {
        $site->update(['auto_deploy_enabled' => false]);

        return response()->json(['message' => 'Auto-deploy disabled']);
    }

    /**
     * Get webhook configuration
     */
    public function config(Site $site)
    {
        if (!$site->webhook_secret) {
            $this->webhookService->generateWebhookUrl($site);
            $site->refresh();
        }

        $instructions = $site->webhook_provider 
            ? $this->webhookService->getSetupInstructions($site, $site->webhook_provider)
            : null;

        return response()->json([
            'enabled' => $site->auto_deploy_enabled,
            'provider' => $site->webhook_provider,
            'webhook_url' => $site->webhook_url,
            'webhook_secret' => $site->webhook_secret,
            'last_webhook_at' => $site->last_webhook_at,
            'setup_instructions' => $instructions
        ]);
    }

    /**
     * Regenerate webhook secret
     */
    public function regenerateSecret(Site $site)
    {
        $site->update(['webhook_secret' => \Illuminate\Support\Str::random(40)]);
        
        $webhookUrl = $this->webhookService->generateWebhookUrl($site);
        $site->update(['webhook_url' => $webhookUrl]);

        return response()->json([
            'message' => 'Webhook secret regenerated',
            'webhook_url' => $webhookUrl,
            'webhook_secret' => $site->webhook_secret
        ]);
    }
}
