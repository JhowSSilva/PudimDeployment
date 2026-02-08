<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Deployment;
use App\Jobs\ExecuteDeployment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookService
{
    /**
     * Validate GitHub webhook signature
     */
    public function validateGitHubSignature(?string $payload, ?string $signature, ?string $secret): bool
    {
        if (empty($payload) || empty($signature) || empty($secret)) {
            return false;
        }
        
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Validate GitLab webhook token
     */
    public function validateGitLabToken(?string $token, ?string $secret): bool
    {
        if (empty($token) || empty($secret)) {
            return false;
        }
        
        return hash_equals($secret, $token);
    }

    /**
     * Validate Bitbucket webhook signature
     */
    public function validateBitbucketSignature(?string $payload, ?string $signature, ?string $secret): bool
    {
        if (empty($payload) || empty($signature) || empty($secret)) {
            return false;
        }
        
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process GitHub webhook
     */
    public function processGitHubWebhook(Site $site, array $payload): ?Deployment
    {
        // Only process push events
        if (!isset($payload['ref'])) {
            Log::info('GitHub webhook: Not a push event', ['site_id' => $site->id]);
            return null;
        }

        // Extract branch from ref (refs/heads/main -> main)
        $branch = str_replace('refs/heads/', '', $payload['ref']);

        // Check if the push is for the configured branch
        if ($branch !== $site->git_branch) {
            Log::info('GitHub webhook: Branch mismatch', [
                'site_id' => $site->id,
                'expected' => $site->git_branch,
                'received' => $branch
            ]);
            return null;
        }

        return $this->triggerDeployment($site, [
            'commit_hash' => $payload['after'] ?? null,
            'commit_message' => $payload['head_commit']['message'] ?? 'Auto-deploy from webhook',
            'author' => $payload['head_commit']['author']['name'] ?? 'Unknown',
            'trigger' => 'webhook:github'
        ]);
    }

    /**
     * Process GitLab webhook
     */
    public function processGitLabWebhook(Site $site, array $payload): ?Deployment
    {
        // Only process push events
        if (!isset($payload['ref'])) {
            Log::info('GitLab webhook: Not a push event', ['site_id' => $site->id]);
            return null;
        }

        // Extract branch from ref
        $branch = str_replace('refs/heads/', '', $payload['ref']);

        // Check if the push is for the configured branch
        if ($branch !== $site->git_branch) {
            Log::info('GitLab webhook: Branch mismatch', [
                'site_id' => $site->id,
                'expected' => $site->git_branch,
                'received' => $branch
            ]);
            return null;
        }

        return $this->triggerDeployment($site, [
            'commit_hash' => $payload['after'] ?? $payload['checkout_sha'] ?? null,
            'commit_message' => $payload['commits'][0]['message'] ?? 'Auto-deploy from webhook',
            'author' => $payload['user_name'] ?? 'Unknown',
            'trigger' => 'webhook:gitlab'
        ]);
    }

    /**
     * Process Bitbucket webhook
     */
    public function processBitbucketWebhook(Site $site, array $payload): ?Deployment
    {
        // Only process push events
        if (!isset($payload['push']['changes'])) {
            Log::info('Bitbucket webhook: Not a push event', ['site_id' => $site->id]);
            return null;
        }

        $change = $payload['push']['changes'][0] ?? null;
        if (!$change) {
            return null;
        }

        $branch = $change['new']['name'] ?? null;

        // Check if the push is for the configured branch
        if ($branch !== $site->git_branch) {
            Log::info('Bitbucket webhook: Branch mismatch', [
                'site_id' => $site->id,
                'expected' => $site->git_branch,
                'received' => $branch
            ]);
            return null;
        }

        return $this->triggerDeployment($site, [
            'commit_hash' => $change['new']['target']['hash'] ?? null,
            'commit_message' => $change['new']['target']['message'] ?? 'Auto-deploy from webhook',
            'author' => $payload['actor']['display_name'] ?? 'Unknown',
            'trigger' => 'webhook:bitbucket'
        ]);
    }

    /**
     * Trigger deployment
     */
    protected function triggerDeployment(Site $site, array $metadata): Deployment
    {
        $deployment = Deployment::create([
            'site_id' => $site->id,
            'server_id' => $site->server_id,
            'status' => 'pending',
            'commit_hash' => $metadata['commit_hash'],
            'commit_message' => $metadata['commit_message'],
            'deployed_by' => $metadata['author'],
            'trigger_type' => $metadata['trigger'],
            'started_at' => now(),
        ]);

        // Dispatch deployment job
        ExecuteDeployment::dispatch($deployment);

        // Update site's last webhook timestamp
        $site->update(['last_webhook_at' => now()]);

        Log::info('Deployment triggered from webhook', [
            'site_id' => $site->id,
            'deployment_id' => $deployment->id,
            'trigger' => $metadata['trigger']
        ]);

        return $deployment;
    }

    /**
     * Generate webhook URL for a site
     */
    public function generateWebhookUrl(Site $site): string
    {
        if (!$site->webhook_secret) {
            $site->update(['webhook_secret' => Str::random(40)]);
        }

        return route('webhooks.receive', [
            'siteId' => $site->id,
            'token' => $site->webhook_secret
        ]);
    }

    /**
     * Get webhook setup instructions
     */
    public function getSetupInstructions(Site $site, string $provider): array
    {
        $webhookUrl = $this->generateWebhookUrl($site);

        return match($provider) {
            'github' => [
                'url' => $webhookUrl,
                'content_type' => 'application/json',
                'secret' => $site->webhook_secret,
                'events' => ['push'],
                'instructions' => [
                    '1. Vá para Settings > Webhooks no seu repositório GitHub',
                    '2. Clique em "Add webhook"',
                    '3. Cole a Payload URL: ' . $webhookUrl,
                    '4. Selecione Content type: application/json',
                    '5. Cole o Secret: ' . $site->webhook_secret,
                    '6. Selecione "Just the push event"',
                    '7. Marque "Active" e clique em "Add webhook"',
                ]
            ],
            'gitlab' => [
                'url' => $webhookUrl,
                'token' => $site->webhook_secret,
                'events' => ['Push events'],
                'instructions' => [
                    '1. Vá para Settings > Webhooks no seu projeto GitLab',
                    '2. Cole a URL: ' . $webhookUrl,
                    '3. Cole o Secret token: ' . $site->webhook_secret,
                    '4. Marque "Push events"',
                    '5. Clique em "Add webhook"',
                ]
            ],
            'bitbucket' => [
                'url' => $webhookUrl,
                'events' => ['Repository push'],
                'instructions' => [
                    '1. Vá para Settings > Webhooks no seu repositório Bitbucket',
                    '2. Clique em "Add webhook"',
                    '3. Cole a URL: ' . $webhookUrl,
                    '4. Marque "Repository push"',
                    '5. Clique em "Save"',
                ]
            ],
            default => []
        };
    }
}
