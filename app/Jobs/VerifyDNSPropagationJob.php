<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\CloudflareService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class VerifyDNSPropagationJob implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 60;
    public $backoff = [30, 60, 120, 300]; // Retry delays in seconds

    /**
     * Create a new job instance.
     */
    public function __construct(public Site $site)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CloudflareService $cloudflare): void
    {
        Log::info('Verifying DNS propagation', ['site' => $this->site->domain]);

        $propagated = $cloudflare->verifyDNSPropagation(
            $this->site->domain,
            $this->site->server->ip_address
        );

        if ($propagated) {
            Log::info('DNS propagation verified', ['site' => $this->site->domain]);

            // If SSL is enabled, generate certificate
            if ($this->site->ssl_type !== 'none') {
                GenerateSSLJob::dispatch($this->site)
                    ->delay(now()->addSeconds(10));
            }
        } else {
            // Retry if not propagated yet
            if ($this->attempts() < $this->tries) {
                Log::warning('DNS not propagated yet, will retry', [
                    'site' => $this->site->domain,
                    'attempt' => $this->attempts()
                ]);
                $this->release($this->backoff[$this->attempts() - 1] ?? 300);
            } else {
                Log::error('DNS propagation failed after max attempts', [
                    'site' => $this->site->domain
                ]);
            }
        }
    }
}
