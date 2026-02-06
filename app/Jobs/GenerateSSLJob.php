<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\SSLService;
use App\Services\NginxConfigService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateSSLJob implements ShouldQueue
{
    use Queueable;

    public $tries = 2;
    public $timeout = 300; // 5 minutes

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
    public function handle(SSLService $ssl, NginxConfigService $nginx): void
    {
        Log::info('Generating SSL certificate', [
            'site' => $this->site->domain,
            'type' => $this->site->ssl_type
        ]);

        $success = $ssl->generateCertificate($this->site);

        if ($success) {
            Log::info('SSL certificate generated successfully', ['site' => $this->site->domain]);

            // Regenerate nginx config with SSL
            $nginxConfig = $nginx->generateConfig($this->site);
            $nginx->deployConfig($this->site, $nginxConfig);
            $nginx->reloadNginx($this->site->server);

            Log::info('Nginx configuration updated with SSL', ['site' => $this->site->domain]);
        } else {
            Log::error('Failed to generate SSL certificate', ['site' => $this->site->domain]);
            $this->fail(new \Exception('SSL certificate generation failed'));
        }
    }
}
