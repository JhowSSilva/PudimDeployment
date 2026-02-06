<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\CloudflareService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ConfigureDNSJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 120;

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
        Log::info('Configuring DNS for site', ['site' => $this->site->domain]);

        // Load the Cloudflare account for this site
        if (!$this->site->cloudflareAccount) {
            Log::error('Site does not have a Cloudflare account configured', ['site' => $this->site->domain]);
            $this->fail(new \Exception('Site does not have a Cloudflare account configured'));
            return;
        }

        // Set the API token from the site's Cloudflare account
        $cloudflare->setApiToken($this->site->cloudflareAccount->api_token);

        // Find Cloudflare zone for the domain
        $zone = $cloudflare->findZone($this->site->domain);

        if (!$zone) {
            Log::error('Cloudflare zone not found', ['domain' => $this->site->domain]);
            $this->fail(new \Exception('Cloudflare zone not found for domain'));
            return;
        }

        // Create or update DNS record
        if ($this->site->cloudflare_record_id) {
            // Update existing record
            $record = $cloudflare->updateDNSRecord(
                $zone['id'],
                $this->site->cloudflare_record_id,
                [
                    'content' => $this->site->server->ip_address,
                    'proxied' => $this->site->cloudflare_proxy,
                ]
            );
        } else {
            // Create new record
            $record = $cloudflare->createDNSRecord(
                $zone['id'],
                'A',
                $this->site->domain,
                $this->site->server->ip_address,
                $this->site->cloudflare_proxy
            );
        }

        if ($record) {
            $this->site->update([
                'cloudflare_zone_id' => $zone['id'],
                'cloudflare_record_id' => $record['id'],
            ]);

            Log::info('DNS configured successfully', ['site' => $this->site->domain]);

            // Dispatch job to verify DNS propagation
            VerifyDNSPropagationJob::dispatch($this->site)
                ->delay(now()->addSeconds(30));
        } else {
            Log::error('Failed to configure DNS', ['site' => $this->site->domain]);
            $this->fail(new \Exception('Failed to create/update DNS record'));
        }
    }
}
