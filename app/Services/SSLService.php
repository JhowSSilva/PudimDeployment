<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Server;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SSLService
{
    private CloudflareService $cloudflare;
    private SSHConnectionService $ssh;

    public function __construct(CloudflareService $cloudflare, SSHConnectionService $ssh)
    {
        $this->cloudflare = $cloudflare;
        $this->ssh = $ssh;
    }

    /**
     * Generate SSL certificate based on site configuration
     */
    public function generateCertificate(Site $site): bool
    {
        if ($site->ssl_type === 'cloudflare') {
            return $this->generateCloudflareOrigin($site);
        } elseif ($site->ssl_type === 'letsencrypt') {
            return $this->generateLetsEncrypt($site);
        }

        return false;
    }

    /**
     * Generate Cloudflare Origin Certificate
     */
    public function generateCloudflareOrigin(Site $site): bool
    {
        try {
            Log::info('Generating Cloudflare Origin Certificate', ['site' => $site->domain]);

            // Load the Cloudflare account for this site
            if (!$site->cloudflareAccount) {
                Log::error('Site does not have a Cloudflare account configured', ['site' => $site->domain]);
                return false;
            }

            // Set the API token from the site's Cloudflare account
            $this->cloudflare->setApiToken($site->cloudflareAccount->api_token);

            $hostnames = [
                $site->domain,
                '*.' . $site->domain, // Wildcard for subdomains
            ];

            $certificate = $this->cloudflare->createOriginCertificate($hostnames);

            if (!$certificate) {
                Log::error('Failed to generate Cloudflare Origin Certificate', ['site' => $site->domain]);
                return false;
            }

            // Update site with certificate data
            $site->update([
                'ssl_certificate' => $certificate['certificate'],
                'ssl_private_key' => $certificate['private_key'],
                'ssl_ca_bundle' => $certificate['ca'] ?? null,
                'ssl_enabled' => true,
                'ssl_expires_at' => Carbon::parse($certificate['expires_on']),
                'ssl_last_check' => now(),
            ]);

            // Install certificate on server
            return $this->installCertificate($site);

        } catch (\Exception $e) {
            Log::error('Error generating Cloudflare Origin Certificate: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Let's Encrypt certificate
     */
    public function generateLetsEncrypt(Site $site): bool
    {
        try {
            Log::info('Generating Let\'s Encrypt certificate', ['site' => $site->domain]);

            $connection = $this->ssh->connect($site->server);

            if (!$connection) {
                Log::error('SSH connection failed for Let\'s Encrypt', ['server' => $site->server->name]);
                return false;
            }

            // Check if certbot is installed
            $certbotCheck = $this->ssh->execute($connection, 'which certbot');
            
            if (empty($certbotCheck)) {
                // Install certbot
                $this->installCertbot($connection, $site->server);
            }

            // Generate certificate using nginx plugin
            $command = sprintf(
                'certbot certonly --nginx --non-interactive --agree-tos --email admin@%s -d %s',
                $site->domain,
                $site->domain
            );

            $output = $this->ssh->execute($connection, $command);

            // Check if certificate was generated
            $certPath = "/etc/letsencrypt/live/{$site->domain}/fullchain.pem";
            $keyPath = "/etc/letsencrypt/live/{$site->domain}/privkey.pem";

            $certExists = $this->ssh->execute($connection, "test -f {$certPath} && echo 'exists'");

            if (trim($certExists) === 'exists') {
                // Read certificate files
                $cert = $this->ssh->execute($connection, "cat {$certPath}");
                $key = $this->ssh->execute($connection, "cat {$keyPath}");

                $site->update([
                    'ssl_certificate' => $cert,
                    'ssl_private_key' => $key,
                    'ssl_enabled' => true,
                    'ssl_expires_at' => now()->addDays(90), // Let's Encrypt certs valid for 90 days
                    'ssl_last_check' => now(),
                ]);

                Log::info('Let\'s Encrypt certificate generated successfully', ['site' => $site->domain]);
                return true;
            }

            Log::error('Let\'s Encrypt certificate generation failed', ['output' => $output]);
            return false;

        } catch (\Exception $e) {
            Log::error('Error generating Let\'s Encrypt certificate: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Install certificate files on server
     */
    public function installCertificate(Site $site): bool
    {
        try {
            $connection = $this->ssh->connect($site->server);

            if (!$connection) {
                return false;
            }

            $sslDir = "/etc/nginx/ssl/{$site->domain}";

            // Create SSL directory
            $this->ssh->execute($connection, "mkdir -p {$sslDir}");

            // Write certificate files
            $this->ssh->execute($connection, sprintf(
                "echo '%s' > {$sslDir}/certificate.crt",
                addslashes($site->ssl_certificate)
            ));

            $this->ssh->execute($connection, sprintf(
                "echo '%s' > {$sslDir}/private.key",
                addslashes($site->ssl_private_key)
            ));

            if ($site->ssl_ca_bundle) {
                $this->ssh->execute($connection, sprintf(
                    "echo '%s' > {$sslDir}/ca_bundle.crt",
                    addslashes($site->ssl_ca_bundle)
                ));
            }

            // Set proper permissions
            $this->ssh->execute($connection, "chmod 600 {$sslDir}/private.key");
            $this->ssh->execute($connection, "chmod 644 {$sslDir}/certificate.crt");

            // Reload nginx
            $this->ssh->execute($connection, "nginx -t && systemctl reload nginx");

            Log::info('SSL certificate installed successfully', ['site' => $site->domain]);
            return true;

        } catch (\Exception $e) {
            Log::error('Error installing SSL certificate: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Install certbot on server
     */
    private function installCertbot($connection, Server $server): void
    {
        Log::info('Installing certbot', ['server' => $server->name]);

        if (str_contains(strtolower($server->os_type ?? ''), 'ubuntu') || 
            str_contains(strtolower($server->os_type ?? ''), 'debian')) {
            $this->ssh->execute($connection, 'apt-get update && apt-get install -y certbot python3-certbot-nginx');
        } elseif (str_contains(strtolower($server->os_type ?? ''), 'centos') || 
                  str_contains(strtolower($server->os_type ?? ''), 'rhel')) {
            $this->ssh->execute($connection, 'yum install -y certbot python3-certbot-nginx');
        }
    }

    /**
     * Check certificate expiration
     */
    public function checkExpiration(Site $site): array
    {
        if (!$site->ssl_enabled || !$site->ssl_expires_at) {
            return [
                'expired' => true,
                'days_remaining' => 0,
                'should_renew' => true,
            ];
        }

        $expiresAt = Carbon::parse($site->ssl_expires_at);
        $daysRemaining = now()->diffInDays($expiresAt, false);

        return [
            'expired' => $daysRemaining <= 0,
            'days_remaining' => max(0, (int)$daysRemaining),
            'should_renew' => $daysRemaining <= 30, // Renew 30 days before expiration
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Renew SSL certificate
     */
    public function renewCertificate(Site $site): bool
    {
        Log::info('Renewing SSL certificate', ['site' => $site->domain]);

        $check = $this->checkExpiration($site);

        if (!$check['should_renew']) {
            Log::info('Certificate renewal not needed yet', [
                'site' => $site->domain,
                'days_remaining' => $check['days_remaining']
            ]);
            return true;
        }

        // For Cloudflare Origin certificates, no renewal needed (15-year validity)
        if ($site->ssl_type === 'cloudflare') {
            Log::info('Cloudflare Origin Certificate doesn\'t need renewal', ['site' => $site->domain]);
            $site->update(['ssl_last_check' => now()]);
            return true;
        }

        // Renew Let's Encrypt certificate
        if ($site->ssl_type === 'letsencrypt') {
            return $this->renewLetsEncrypt($site);
        }

        return false;
    }

    /**
     * Renew Let's Encrypt certificate
     */
    private function renewLetsEncrypt(Site $site): bool
    {
        try {
            $connection = $this->ssh->connect($site->server);

            if (!$connection) {
                return false;
            }

            $command = sprintf(
                'certbot renew --cert-name %s --nginx --non-interactive',
                $site->domain
            );

            $output = $this->ssh->execute($connection, $command);

            // Update expiration date
            $site->update([
                'ssl_expires_at' => now()->addDays(90),
                'ssl_last_check' => now(),
            ]);

            Log::info('Let\'s Encrypt certificate renewed', ['site' => $site->domain]);
            return true;

        } catch (\Exception $e) {
            Log::error('Error renewing Let\'s Encrypt certificate: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove SSL certificate from site
     */
    public function removeCertificate(Site $site): bool
    {
        try {
            $connection = $this->ssh->connect($site->server);

            if (!$connection) {
                return false;
            }

            $sslDir = "/etc/nginx/ssl/{$site->domain}";

            // Remove SSL directory
            $this->ssh->execute($connection, "rm -rf {$sslDir}");

            // If Let's Encrypt, remove certbot certificate
            if ($site->ssl_type === 'letsencrypt') {
                $this->ssh->execute($connection, "certbot delete --cert-name {$site->domain} --non-interactive");
            }

            $site->update([
                'ssl_certificate' => null,
                'ssl_private_key' => null,
                'ssl_ca_bundle' => null,
                'ssl_enabled' => false,
                'ssl_expires_at' => null,
            ]);

            Log::info('SSL certificate removed', ['site' => $site->domain]);
            return true;

        } catch (\Exception $e) {
            Log::error('Error removing SSL certificate: ' . $e->getMessage());
            return false;
        }
    }
}
