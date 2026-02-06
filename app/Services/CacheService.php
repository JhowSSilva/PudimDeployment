<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Log;

class CacheService
{
    private SSHConnectionService $ssh;

    public function __construct(private Server $server)
    {
        $this->ssh = new SSHConnectionService($server);
    }

    /**
     * Enable OPcache for PHP
     */
    public function enableOPCache(): array
    {
        try {
            // Get PHP version
            $versionResult = $this->ssh->execute('php -v | head -n 1 | cut -d " " -f 2 | cut -f1-2 -d"."');
            $phpVersion = trim($versionResult['output']);

            if (!$phpVersion) {
                throw new \Exception('Could not detect PHP version');
            }

            Log::info('Configuring OPcache for PHP', [
                'server' => $this->server->name,
                'php_version' => $phpVersion
            ]);

            // OPcache configuration
            $opcacheConfig = <<<'CONFIG'
; OPcache Configuration
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=1
opcache.revalidate_freq=2
opcache.save_comments=1
opcache.fast_shutdown=1
CONFIG;

            // Write OPcache config
            $configPath = "/etc/php/{$phpVersion}/fpm/conf.d/10-opcache.ini";
            $this->ssh->execute("cat > {$configPath} << 'EOF'\n{$opcacheConfig}\nEOF");

            // Also add to CLI
            $cliConfigPath = "/etc/php/{$phpVersion}/cli/conf.d/10-opcache.ini";
            $this->ssh->execute("cat > {$cliConfigPath} << 'EOF'\n{$opcacheConfig}\nEOF");

            // Restart PHP-FPM
            $this->ssh->execute("systemctl restart php{$phpVersion}-fpm");

            Log::info('OPcache enabled successfully', [
                'server' => $this->server->name,
                'php_version' => $phpVersion
            ]);

            return [
                'success' => true,
                'message' => 'OPcache enabled successfully',
                'php_version' => $phpVersion
            ];

        } catch (\Exception $e) {
            Log::error('Failed to enable OPcache', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear OPcache
     */
    public function clearOPCache(): array
    {
        try {
            // Create a PHP script to clear OPcache
            $clearScript = <<<'PHP'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache cleared successfully';
} else {
    echo 'OPcache is not enabled';
}
PHP;

            // Write script to temp file
            $this->ssh->execute("cat > /tmp/clear_opcache.php << 'EOF'\n{$clearScript}\nEOF");

            // Execute script
            $result = $this->ssh->execute('php /tmp/clear_opcache.php');

            // Clean up
            $this->ssh->execute('rm /tmp/clear_opcache.php');

            Log::info('OPcache cleared', ['server' => $this->server->name]);

            return [
                'success' => true,
                'message' => 'OPcache cleared successfully',
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to clear OPcache', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Configure Redis
     */
    public function configureRedis(array $options = []): array
    {
        try {
            Log::info('Installing and configuring Redis', ['server' => $this->server->name]);

            // Install Redis
            $this->ssh->execute('apt-get update && apt-get install -y redis-server');

            // Redis configuration
            $maxMemory = $options['max_memory'] ?? '256mb';
            $maxMemoryPolicy = $options['max_memory_policy'] ?? 'allkeys-lru';
            $password = $options['password'] ?? null;

            $redisConfig = <<<CONFIG
# Redis Configuration
bind 127.0.0.1 ::1
protected-mode yes
port 6379
tcp-backlog 511
timeout 0
tcp-keepalive 300
daemonize yes
supervised systemd
pidfile /var/run/redis/redis-server.pid
loglevel notice
logfile /var/log/redis/redis-server.log
databases 16
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /var/lib/redis
maxmemory {$maxMemory}
maxmemory-policy {$maxMemoryPolicy}
CONFIG;

            if ($password) {
                $redisConfig .= "\nrequirepass {$password}";
            }

            // Backup original config
            $this->ssh->execute('cp /etc/redis/redis.conf /etc/redis/redis.conf.backup');

            // Write new config
            $this->ssh->execute("cat > /etc/redis/redis.conf << 'EOF'\n{$redisConfig}\nEOF");

            // Restart Redis
            $this->ssh->execute('systemctl restart redis-server');
            $this->ssh->execute('systemctl enable redis-server');

            Log::info('Redis configured successfully', [
                'server' => $this->server->name,
                'max_memory' => $maxMemory
            ]);

            return [
                'success' => true,
                'message' => 'Redis configured successfully',
                'config' => [
                    'max_memory' => $maxMemory,
                    'policy' => $maxMemoryPolicy,
                    'password_protected' => $password !== null
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to configure Redis', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear Redis cache
     */
    public function clearRedis(?string $password = null): array
    {
        try {
            $command = 'redis-cli FLUSHALL';
            
            if ($password) {
                $command = "redis-cli -a {$password} FLUSHALL";
            }

            $result = $this->ssh->execute($command);

            Log::info('Redis cache cleared', ['server' => $this->server->name]);

            return [
                'success' => true,
                'message' => 'Redis cache cleared successfully',
                'output' => $result['output']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to clear Redis cache', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Redis info
     */
    public function getRedisInfo(?string $password = null): array
    {
        try {
            $command = 'redis-cli INFO';
            
            if ($password) {
                $command = "redis-cli -a {$password} INFO";
            }

            $result = $this->ssh->execute($command);

            return [
                'success' => true,
                'info' => $this->parseRedisInfo($result['output'])
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'info' => []
            ];
        }
    }

    /**
     * Parse Redis INFO output
     */
    private function parseRedisInfo(string $output): array
    {
        $info = [];
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $info[$parts[0]] = $parts[1];
            }
        }

        return $info;
    }

    /**
     * Enable Brotli compression in Nginx
     */
    public function enableBrotli(Site $site): array
    {
        try {
            Log::info('Enabling Brotli compression', [
                'server' => $this->server->name,
                'site' => $site->domain
            ]);

            // Check if brotli module is installed
            $checkResult = $this->ssh->execute('nginx -V 2>&1 | grep -o "ngx_brotli"');
            
            if (empty(trim($checkResult['output']))) {
                // Brotli module not installed
                return [
                    'success' => false,
                    'message' => 'Brotli module not installed in Nginx. Requires compilation with ngx_brotli.'
                ];
            }

            // Brotli configuration for Nginx
            $brotliConfig = <<<'CONFIG'
# Brotli Compression
brotli on;
brotli_comp_level 6;
brotli_static on;
brotli_types text/plain text/css text/xml text/javascript application/x-javascript application/json application/xml application/rss+xml application/atom+xml image/svg+xml text/x-component text/x-js;
CONFIG;

            // Add to site's nginx config
            $nginxService = new NginxConfigService();
            $currentConfig = $this->ssh->execute("cat /etc/nginx/sites-available/{$site->domain}");
            
            // Insert brotli config in server block
            $updatedConfig = preg_replace(
                '/(server\s*{)/',
                "$1\n    {$brotliConfig}\n",
                $currentConfig['output'],
                1
            );

            // Write updated config
            $this->ssh->execute("cat > /etc/nginx/sites-available/{$site->domain} << 'EOF'\n{$updatedConfig}\nEOF");

            // Test and reload Nginx
            $testResult = $this->ssh->execute('nginx -t');
            if ($testResult['exit_code'] === 0) {
                $this->ssh->execute('systemctl reload nginx');
                
                Log::info('Brotli enabled successfully', ['site' => $site->domain]);

                return [
                    'success' => true,
                    'message' => 'Brotli compression enabled successfully'
                ];
            } else {
                throw new \Exception('Nginx configuration test failed: ' . $testResult['output']);
            }

        } catch (\Exception $e) {
            Log::error('Failed to enable Brotli', [
                'server' => $this->server->name,
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Configure Memcached
     */
    public function configureMemcached(int $memory = 64): array
    {
        try {
            Log::info('Installing and configuring Memcached', [
                'server' => $this->server->name,
                'memory' => $memory
            ]);

            // Install Memcached
            $this->ssh->execute('apt-get update && apt-get install -y memcached');

            // Configure memory limit
            $this->ssh->execute("sed -i 's/-m 64/-m {$memory}/' /etc/memcached.conf");

            // Bind to localhost only for security
            $this->ssh->execute("sed -i 's/^-l.*/-l 127.0.0.1/' /etc/memcached.conf");

            // Restart Memcached
            $this->ssh->execute('systemctl restart memcached');
            $this->ssh->execute('systemctl enable memcached');

            Log::info('Memcached configured successfully', [
                'server' => $this->server->name,
                'memory' => $memory
            ]);

            return [
                'success' => true,
                'message' => 'Memcached configured successfully',
                'memory_mb' => $memory
            ];

        } catch (\Exception $e) {
            Log::error('Failed to configure Memcached', [
                'server' => $this->server->name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear all caches for a site
     */
    public function clearAllCaches(Site $site): array
    {
        try {
            $results = [];

            // Clear OPcache
            $results['opcache'] = $this->clearOPCache();

            // Clear Redis
            $results['redis'] = $this->clearRedis();

            // Clear Laravel cache if it's a Laravel site
            if ($site->type === 'laravel' || $site->framework === 'laravel') {
                $artisanService = new ArtisanService($this->server);
                $results['laravel'] = $artisanService->clearCache($site, ['config', 'route', 'view', 'cache']);
            }

            Log::info('All caches cleared', [
                'server' => $this->server->name,
                'site' => $site->domain
            ]);

            return [
                'success' => true,
                'message' => 'All caches cleared successfully',
                'details' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Failed to clear all caches', [
                'server' => $this->server->name,
                'site' => $site->domain,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
