<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CloudflareService
{
    private ?string $apiToken = null;
    private string $apiBaseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
    }

    /**
     * Set API token dynamically (for using different Cloudflare accounts)
     */
    public function setApiToken(string $token): void
    {
        $this->apiToken = $token;
    }

    /**
     * Get HTTP client with Cloudflare headers
     */
    private function client()
    {
        if (!$this->apiToken) {
            throw new \Exception('Cloudflare API token não configurado');
        }

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ])->timeout(30);
    }

    /**
     * List all zones (domains) in Cloudflare account
     */
    public function listZones()
    {
        try {
            $response = $this->client()->get("{$this->apiBaseUrl}/zones");
            
            if ($response->successful()) {
                return $response->json('result');
            }

            Log::error('Cloudflare listZones failed', ['response' => $response->json()]);
            return [];
        } catch (\Exception $e) {
            Log::error('Cloudflare API error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find zone ID by domain name
     */
    public function findZone(string $domain): ?array
    {
        // Extract root domain (e.g., exemplo.com.br from www.exemplo.com.br)
        $rootDomain = $this->extractRootDomain($domain);
        
        $cacheKey = "cloudflare_zone_{$rootDomain}";
        
        return Cache::remember($cacheKey, 3600, function () use ($rootDomain) {
            try {
                $response = $this->client()->get("{$this->apiBaseUrl}/zones", [
                    'name' => $rootDomain,
                ]);
                
                if ($response->successful() && count($response->json('result')) > 0) {
                    return $response->json('result')[0];
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Cloudflare findZone error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Create DNS record
     */
    public function createDNSRecord(
        string $zoneId,
        string $type,
        string $name,
        string $content,
        bool $proxied = true,
        int $ttl = 1
    ): ?array {
        try {
            $response = $this->client()->post("{$this->apiBaseUrl}/zones/{$zoneId}/dns_records", [
                'type' => $type,
                'name' => $name,
                'content' => $content,
                'proxied' => $proxied,
                'ttl' => $proxied ? 1 : $ttl, // TTL must be 1 when proxied
            ]);

            if ($response->successful()) {
                Log::info('DNS record created', ['name' => $name, 'type' => $type]);
                return $response->json('result');
            }

            Log::error('Failed to create DNS record', [
                'name' => $name,
                'response' => $response->json()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Cloudflare createDNSRecord error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update DNS record
     */
    public function updateDNSRecord(
        string $zoneId,
        string $recordId,
        array $data
    ): ?array {
        try {
            $response = $this->client()->patch(
                "{$this->apiBaseUrl}/zones/{$zoneId}/dns_records/{$recordId}",
                $data
            );

            if ($response->successful()) {
                Log::info('DNS record updated', ['recordId' => $recordId]);
                return $response->json('result');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Cloudflare updateDNSRecord error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete DNS record
     */
    public function deleteDNSRecord(string $zoneId, string $recordId): bool
    {
        try {
            $response = $this->client()->delete(
                "{$this->apiBaseUrl}/zones/{$zoneId}/dns_records/{$recordId}"
            );

            if ($response->successful()) {
                Log::info('DNS record deleted', ['recordId' => $recordId]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Cloudflare deleteDNSRecord error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get DNS record details
     */
    public function getDNSRecord(string $zoneId, string $recordId): ?array
    {
        try {
            $response = $this->client()->get(
                "{$this->apiBaseUrl}/zones/{$zoneId}/dns_records/{$recordId}"
            );

            if ($response->successful()) {
                return $response->json('result');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Cloudflare getDNSRecord error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create Cloudflare Origin Certificate
     */
    public function createOriginCertificate(array $hostnames, int $validityDays = 5475): ?array
    {
        try {
            $response = $this->client()->post(
                "{$this->apiBaseUrl}/certificates",
                [
                    'hostnames' => $hostnames,
                    'requested_validity' => $validityDays, // Max 15 years (5475 days)
                    'request_type' => 'origin-rsa',
                ]
            );

            if ($response->successful()) {
                Log::info('Cloudflare Origin Certificate created', ['hostnames' => $hostnames]);
                return $response->json('result');
            }

            Log::error('Failed to create Origin Certificate', [
                'hostnames' => $hostnames,
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Cloudflare createOriginCertificate error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify DNS propagation using DNS query
     */
    public function verifyDNSPropagation(string $domain, string $expectedIp, int $maxAttempts = 10): bool
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $resolvedIp = gethostbyname($domain);
            
            if ($resolvedIp === $expectedIp) {
                Log::info('DNS propagated successfully', [
                    'domain' => $domain,
                    'ip' => $expectedIp,
                    'attempts' => $i + 1
                ]);
                return true;
            }

            if ($i < $maxAttempts - 1) {
                sleep(5); // Wait 5 seconds between attempts
            }
        }

        Log::warning('DNS propagation timeout', [
            'domain' => $domain,
            'expected' => $expectedIp,
            'resolved' => $resolvedIp ?? 'none'
        ]);

        return false;
    }

    /**
     * Extract root domain from subdomain
     */
    private function extractRootDomain(string $domain): string
    {
        // Remove protocol if present
        $domain = preg_replace('#^https?://#', '', $domain);
        
        // Remove path if present
        $domain = explode('/', $domain)[0];
        
        // Split by dots
        $parts = explode('.', $domain);
        
        // Handle common TLDs
        if (count($parts) >= 2) {
            // For .com.br, .co.uk, etc
            if (in_array($parts[count($parts) - 2] . '.' . $parts[count($parts) - 1], [
                'com.br', 'co.uk', 'com.au', 'co.nz'
            ])) {
                return implode('.', array_slice($parts, -3));
            }
            
            // Standard TLD
            return implode('.', array_slice($parts, -2));
        }
        
        return $domain;
    }

    /**
     * Get zone analytics
     */
    public function getZoneAnalytics(string $zoneId, int $since = -1440): ?array
    {
        try {
            $response = $this->client()->get(
                "{$this->apiBaseUrl}/zones/{$zoneId}/analytics/dashboard",
                ['since' => $since]
            );

            if ($response->successful()) {
                return $response->json('result');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Cloudflare getZoneAnalytics error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Purge cache for specific URLs
     */
    public function purgeCache(string $zoneId, array $files = []): bool
    {
        try {
            $payload = empty($files) ? ['purge_everything' => true] : ['files' => $files];
            
            $response = $this->client()->post(
                "{$this->apiBaseUrl}/zones/{$zoneId}/purge_cache",
                $payload
            );

            if ($response->successful()) {
                Log::info('Cache purged', ['zoneId' => $zoneId]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Cloudflare purgeCache error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if API token is valid
     */
    public function verifyToken(): bool
    {
        try {
            $response = $this->client()->get("{$this->apiBaseUrl}/user/tokens/verify");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
