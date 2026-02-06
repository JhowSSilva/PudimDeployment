<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\SSLService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RenewSSLCertificatesJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SSLService $ssl): void
    {
        Log::info('Starting SSL certificate renewal check');

        // Get all sites with SSL enabled that need renewal (expires in 30 days or less)
        $sites = Site::where('ssl_enabled', true)
            ->where('ssl_type', 'letsencrypt') // Only renew Let's Encrypt certs
            ->whereNotNull('ssl_expires_at')
            ->where('ssl_expires_at', '<=', now()->addDays(30))
            ->get();

        Log::info('Found sites needing SSL renewal', ['count' => $sites->count()]);

        foreach ($sites as $site) {
            try {
                $ssl->renewCertificate($site);
                Log::info('SSL certificate renewed', ['site' => $site->domain]);
            } catch (\Exception $e) {
                Log::error('Failed to renew SSL certificate', [
                    'site' => $site->domain,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('SSL certificate renewal check completed');
    }
}
