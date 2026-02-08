<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $provider): Response
    {
        if ($provider === 'github') {
            return $this->verifyGitHubSignature($request, $next);
        }

        if ($provider === 'stripe') {
            return $this->verifyStripeSignature($request, $next);
        }

        if ($provider === 'cloudflare') {
            return $this->verifyCloudflareSignature($request, $next);
        }

        // Unknown provider, reject
        abort(403, 'Unknown webhook provider');
    }

    protected function verifyGitHubSignature(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Hub-Signature-256');
        
        if (!$signature) {
            Log::warning('GitHub webhook missing signature', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            abort(403, 'Missing signature');
        }

        $payload = $request->getContent();
        $secret = config('services.github.webhook_secret');
        
        $computed = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        if (!hash_equals($computed, $signature)) {
            Log::warning('GitHub webhook invalid signature', [
                'ip' => $request->ip(),
                'provided_signature' => substr($signature, 0, 20) . '...',
            ]);
            abort(403, 'Invalid signature');
        }

        return $next($request);
    }

    protected function verifyStripeSignature(Request $request, Closure $next): Response
    {
        $signature = $request->header('Stripe-Signature');
        
        if (!$signature) {
            Log::warning('Stripe webhook missing signature', [
                'ip' => $request->ip(),
            ]);
            abort(403, 'Missing signature');
        }

        $payload = $request->getContent();
        $secret = config('services.stripe.webhook.secret');

        try {
            \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            abort(400, 'Invalid payload');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook invalid signature', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            abort(403, 'Invalid signature');
        }

        return $next($request);
    }

    protected function verifyCloudflareSignature(Request $request, Closure $next): Response
    {
        // Cloudflare uses timestamp + signature verification
        $timestamp = $request->header('Webhook-Timestamp');
        $signature = $request->header('Webhook-Signature');
        
        if (!$timestamp || !$signature) {
            Log::warning('Cloudflare webhook missing headers', [
                'ip' => $request->ip(),
            ]);
            abort(403, 'Missing webhook headers');
        }

        // Verify timestamp is recent (within 5 minutes)
        $timestampInt = (int) $timestamp;
        if (abs(time() - $timestampInt) > 300) {
            Log::warning('Cloudflare webhook timestamp too old', [
                'timestamp' => $timestamp,
                'current_time' => time(),
            ]);
            abort(403, 'Webhook timestamp too old');
        }

        $payload = $request->getContent();
        $secret = config('services.cloudflare.webhook_secret');
        
        $computed = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        
        if (!hash_equals($computed, $signature)) {
            Log::warning('Cloudflare webhook invalid signature', [
                'ip' => $request->ip(),
            ]);
            abort(403, 'Invalid signature');
        }

        return $next($request);
    }
}
