<?php

namespace App\Services;

use App\Models\Site;

class NginxConfigService
{
    private Site $site;

    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Generate Nginx configuration for site
     */
    public function generateConfig(Site $site): string
    {
        if ($site->ssl_enabled) {
            return $this->generateConfigWithSSL($site);
        }
        
        return $this->generateBasicConfig($site);
    }

    /**
     * Generate basic HTTP configuration
     */
    private function generateBasicConfig(Site $site): string
    {
        $domain = $site->domain;
        $rootPath = $site->full_path ?? "/var/www/{$site->domain}";
        $publicPath = rtrim($rootPath, '/') . ($site->document_root ?? '/public');
        $phpVersion = $site->php_version;
        $phpFpmSocket = "/run/php/php{$phpVersion}-fpm.sock";

        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    
    server_name {$domain} www.{$domain};
    root {$publicPath};

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html index.htm;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:{$phpFpmSocket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/log/nginx/{$domain}-access.log;
    error_log /var/log/nginx/{$domain}-error.log;

    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "no-referrer-when-downgrade";

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
}
NGINX;
    }

    /**
     * Generate HTTPS configuration with SSL
     */
    private function generateConfigWithSSL(Site $site): string
    {
        $domain = $site->domain;
        $rootPath = $site->full_path ?? "/var/www/{$site->domain}";
        $publicPath = rtrim($rootPath, '/') . ($site->document_root ?? '/public');
        $phpVersion = $site->php_version;
        $phpFpmSocket = "/run/php/php{$phpVersion}-fpm.sock";
        $sslCert = "/etc/nginx/ssl/{$domain}/certificate.crt";
        $sslKey = "/etc/nginx/ssl/{$domain}/private.key";

        return <<<NGINX
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name {$domain} www.{$domain};
    
    location /.well-known/acme-challenge/ {
        root /var/www/letsencrypt;
    }
    
    location / {
        return 301 https://\$server_name\$request_uri;
    }
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name {$domain} www.{$domain};
    root {$publicPath};

    # SSL Configuration
    ssl_certificate {$sslCert};
    ssl_certificate_key {$sslKey};
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;
    
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;
    
    ssl_stapling on;
    ssl_stapling_verify on;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    index index.php index.html index.htm;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:{$phpFpmSocket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/log/nginx/{$domain}-access.log;
    error_log /var/log/nginx/{$domain}-error.log;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
}
NGINX;
    }

    /**
     * Deploy nginx configuration to server
     */
    public function deployConfig(Site $site, string $config): bool
    {
        try {
            $ssh = app(SSHConnectionService::class);
            $connection = $ssh->connect($site->server);

            if (!$connection) {
                return false;
            }

            $configFile = "/etc/nginx/sites-available/{$site->domain}";
            $enabledLink = "/etc/nginx/sites-enabled/{$site->domain}";

            // Write configuration file
            $ssh->execute($connection, sprintf(
                "echo '%s' > %s",
                addslashes($config),
                $configFile
            ));

            // Create symlink if not exists
            $ssh->execute($connection, "ln -sf {$configFile} {$enabledLink}");

            // Test nginx configuration
            $testResult = $ssh->execute($connection, "nginx -t 2>&1");

            if (str_contains($testResult, 'syntax is ok') && str_contains($testResult, 'test is successful')) {
                return true;
            }

            \Log::error('Nginx configuration test failed', ['output' => $testResult]);
            return false;

        } catch (\Exception $e) {
            \Log::error('Failed to deploy nginx config: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reload nginx
     */
    public function reloadNginx($server): bool
    {
        try {
            $ssh = app(SSHConnectionService::class);
            $connection = $ssh->connect($server);

            if (!$connection) {
                return false;
            }

            $result = $ssh->execute($connection, "systemctl reload nginx");
            \Log::info('Nginx reloaded', ['server' => $server->name]);
            
            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to reload nginx: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Nginx configuration for Laravel site
     * 
     * @return string
     */
    public function generateLaravelConfig(): string
    {
        return $this->generateBasicConfig($this->site);
    }

    /**
     * Generate Nginx configuration with SSL (Let's Encrypt)
     * 
     * @return string
     */
    public function generateLaravelConfigWithSSL(): string
    {
        return $this->generateConfigWithSSL($this->site);
        $phpFpmSocket = "/run/php/php{$phpVersion}-fpm.sock";

        return <<<NGINX
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name {$domain} www.{$domain};
    
    return 301 https://\$server_name\$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name {$domain} www.{$domain};
    root {$publicPath};

    # SSL certificates (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/{$domain}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{$domain}/privkey.pem;
    
    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php index.html index.htm;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:{$phpFpmSocket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Logging
    access_log /var/log/nginx/{$domain}-access.log;
    error_log /var/log/nginx/{$domain}-error.log;

    # Security headers
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "no-referrer-when-downgrade";

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
    gzip_disable "MSIE [1-6]\.";
}
NGINX;
    }

    /**
     * Generate static site configuration
     * 
     * @return string
     */
    public function generateStaticSiteConfig(): string
    {
        $domain = $this->site->domain;
        $rootPath = $this->site->full_path;
        $publicPath = rtrim($rootPath, '/') . $this->site->document_root;

        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    
    server_name {$domain} www.{$domain};
    root {$publicPath};

    index index.html index.htm;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /404.html;

    # Logging
    access_log /var/log/nginx/{$domain}-access.log;
    error_log /var/log/nginx/{$domain}-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
NGINX;
    }

    /**
     * Get configuration file path on server
     * 
     * @return string
     */
    public function getConfigPath(): string
    {
        return "/etc/nginx/sites-available/{$this->site->domain}";
    }

    /**
     * Get symlink path (sites-enabled)
     * 
     * @return string
     */
    public function getEnabledPath(): string
    {
        return "/etc/nginx/sites-enabled/{$this->site->domain}";
    }

    /**
     * Deploy Nginx configuration to server
     * 
     * @param bool $ssl Enable SSL configuration
     * @return bool
     */
    public function deploy(bool $ssl = false): bool
    {
        try {
            $ssh = new SSHConnectionService($this->site->server);
            $ssh->connect();

            // Generate config content
            $config = $ssl ? $this->generateLaravelConfigWithSSL() : $this->generateLaravelConfig();
            
            // Create temporary file locally
            $tempFile = sys_get_temp_dir() . '/' . $this->site->domain . '.conf';
            file_put_contents($tempFile, $config);

            // Upload to server
            $configPath = $this->getConfigPath();
            $ssh->uploadFile($tempFile, $configPath);

            // Create symlink to sites-enabled
            $enabledPath = $this->getEnabledPath();
            $ssh->execute("ln -sf {$configPath} {$enabledPath}");

            // Test Nginx configuration
            $testResult = $ssh->execute("nginx -t");
            
            if ($testResult['exit_code'] !== 0) {
                throw new \Exception("Nginx configuration test failed: " . $testResult['output']);
            }

            // Reload Nginx
            $ssh->execute("systemctl reload nginx");

            // Update site record
            $this->site->update([
                'nginx_config_path' => $configPath,
            ]);

            // Cleanup
            unlink($tempFile);
            $ssh->disconnect();

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to deploy Nginx config for {$this->site->domain}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove Nginx configuration from server
     * 
     * @return bool
     */
    public function remove(): bool
    {
        try {
            $ssh = new SSHConnectionService($this->site->server);
            $ssh->connect();

            $configPath = $this->getConfigPath();
            $enabledPath = $this->getEnabledPath();

            // Remove symlink
            $ssh->execute("rm -f {$enabledPath}");

            // Remove config file
            $ssh->execute("rm -f {$configPath}");

            // Reload Nginx
            $ssh->execute("systemctl reload nginx");

            $ssh->disconnect();

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to remove Nginx config for {$this->site->domain}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable SSL for a site
     */
    public function enableSSL(Site $site, $certificate): bool
    {
        try {
            $ssh = new SSHConnectionService($site->server);
            
            // Generate SSL-enabled config
            $config = $this->generateSSLConfig($site, $certificate);
            
            // Write config file
            $configPath = "/etc/nginx/sites-available/{$site->domain}";
            $ssh->execute("echo '{$config}' | sudo tee {$configPath}");
            
            // Enable site
            $enabledPath = "/etc/nginx/sites-enabled/{$site->domain}";
            $ssh->execute("sudo ln -sf {$configPath} {$enabledPath}");
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Failed to enable SSL for {$site->domain}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable SSL for a site
     */
    public function disableSSL(Site $site): bool
    {
        try {
            $ssh = new SSHConnectionService($site->server);
            
            // Generate basic HTTP config
            $config = $this->generateBasicConfig($site);
            
            // Write config file
            $configPath = "/etc/nginx/sites-available/{$site->domain}";
            $ssh->execute("echo '{$config}' | sudo tee {$configPath}");
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Failed to disable SSL for {$site->domain}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate SSL configuration
     */
    private function generateSSLConfig(Site $site, $certificate): string
    {
        $domain = $site->domain;
        $rootPath = $site->full_path ?? "/var/www/{$site->domain}";
        $publicPath = rtrim($rootPath, '/') . ($site->document_root ?? '/public');
        $phpVersion = $site->php_version;
        $phpFpmSocket = "/run/php/php{$phpVersion}-fpm.sock";
        $certPath = $certificate->cert_path;
        $keyPath = $certificate->key_path;

        $config = <<<NGINX
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    
    server_name {$domain} www.{$domain};
    
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }
    
    location / {
        return 301 https://\$host\$request_uri;
    }
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name {$domain} www.{$domain};
    root {$publicPath};

    # SSL Configuration
    ssl_certificate {$certPath};
    ssl_certificate_key {$keyPath};
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA:ECDHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA;
    ssl_prefer_server_ciphers on;
    ssl_dhparam /etc/nginx/dhparam.pem;
    
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload";
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "no-referrer-when-downgrade";

    index index.php index.html index.htm;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:{$phpFpmSocket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/log/nginx/{$domain}-access.log;
    error_log /var/log/nginx/{$domain}-error.log;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
}
NGINX;

        return $config;
    }
}
