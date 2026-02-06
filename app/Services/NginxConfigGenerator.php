<?php

namespace App\Services;

use App\Models\Site;
use App\Enums\ApplicationType;

class NginxConfigGenerator
{
    /**
     * Generate Nginx configuration for a site
     */
    public function generate(Site $site): string
    {
        $applicationType = ApplicationType::from($site->application_type);

        return match ($applicationType) {
            ApplicationType::LARAVEL => $this->generateLaravelConfig($site),
            ApplicationType::WORDPRESS => $this->generateWordPressConfig($site),
            ApplicationType::STATIC_HTML => $this->generateStaticConfig($site),
            ApplicationType::NODEJS_EXPRESS,
            ApplicationType::NESTJS => $this->generateNodeProxyConfig($site),
            ApplicationType::REACT_SPA,
            ApplicationType::VUE_SPA,
            ApplicationType::ANGULAR => $this->generateSPAConfig($site),
            ApplicationType::NEXTJS,
            ApplicationType::NUXTJS => $this->generateSSRProxyConfig($site),
            ApplicationType::SYMFONY,
            ApplicationType::CODEIGNITER => $this->generatePhpConfig($site),
            ApplicationType::DJANGO,
            ApplicationType::FLASK => $this->generatePythonProxyConfig($site),
            ApplicationType::RUBY_RAILS => $this->generateRailsProxyConfig($site),
            default => $this->generateGenericPhpConfig($site),
        };
    }

    /**
     * Generate Laravel Nginx configuration
     */
    private function generateLaravelConfig(Site $site): string
    {
        $domain = $site->domain;
        $root = "/var/www/{$domain}{$site->root_directory}";
        $phpSocket = $site->dedicated_php_pool 
            ? "/run/php/" . str_replace('.', '-', $domain) . ".sock"
            : "/run/php/php{$site->php_version}-fpm.sock";

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};
    root {$root};

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
        fastcgi_pass unix:{$phpSocket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate WordPress Nginx configuration
     */
    private function generateWordPressConfig(Site $site): string
    {
        $domain = $site->domain;
        $root = "/var/www/{$domain}";
        $phpSocket = "/run/php/php{$site->php_version}-fpm.sock";

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};
    root {$root};

    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_intercept_errors on;
        fastcgi_pass unix:{$phpSocket};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires max;
        log_not_found off;
    }

    location ~ /\.ht {
        deny all;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate static site Nginx configuration
     */
    private function generateStaticConfig(Site $site): string
    {
        $domain = $site->domain;
        $root = "/var/www/{$domain}{$site->root_directory}";

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};
    root {$root};

    index index.html index.htm;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires max;
        add_header Cache-Control "public, immutable";
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate Node.js proxy configuration
     */
    private function generateNodeProxyConfig(Site $site): string
    {
        $domain = $site->domain;
        $port = $site->node_port ?? 3000;

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

upstream nodejs_{$domain} {
    server 127.0.0.1:{$port};
    keepalive 64;
}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};

    location / {
        proxy_pass http://nodejs_{$domain};
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate SPA configuration (React, Vue, Angular)
     */
    private function generateSPAConfig(Site $site): string
    {
        $domain = $site->domain;
        $root = "/var/www/{$domain}{$site->root_directory}";

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};
    root {$root};

    index index.html;

    location / {
        try_files \$uri \$uri/ /index.html;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|json)$ {
        expires max;
        add_header Cache-Control "public, immutable";
    }

    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate SSR proxy configuration (Next.js, Nuxt.js)
     */
    private function generateSSRProxyConfig(Site $site): string
    {
        $domain = $site->domain;
        $port = $site->node_port ?? 3000;

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

upstream ssr_{$domain} {
    server 127.0.0.1:{$port};
    keepalive 64;
}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};

    location /_next/static/ {
        alias /var/www/{$domain}/.next/static/;
        expires 365d;
        access_log off;
    }

    location / {
        proxy_pass http://ssr_{$domain};
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate PHP application configuration (Symfony, CodeIgniter, etc.)
     */
    private function generatePhpConfig(Site $site): string
    {
        $domain = $site->domain;
        $root = "/var/www/{$domain}{$site->root_directory}";
        $phpSocket = "/run/php/php{$site->php_version}-fpm.sock";

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};
    root {$root};

    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:{$phpSocket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate Python proxy configuration (Django, Flask)
     */
    private function generatePythonProxyConfig(Site $site): string
    {
        $domain = $site->domain;
        $port = $site->node_port ?? 8000; // Python apps typically run on 8000

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

upstream python_{$domain} {
    server 127.0.0.1:{$port};
}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};

    location /static/ {
        alias /var/www/{$domain}/static/;
        expires 30d;
    }

    location /media/ {
        alias /var/www/{$domain}/media/;
        expires 30d;
    }

    location / {
        proxy_pass http://python_{$domain};
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate Ruby on Rails proxy configuration
     */
    private function generateRailsProxyConfig(Site $site): string
    {
        $domain = $site->domain;
        $port = $site->node_port ?? 3000;

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

upstream rails_{$domain} {
    server 127.0.0.1:{$port};
}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};
    root /var/www/{$domain}/public;

    location ~* ^/assets/ {
        expires 1y;
        add_header Cache-Control public;
        add_header ETag "";
        break;
    }

    try_files \$uri/index.html \$uri @rails;

    location @rails {
        proxy_pass http://rails_{$domain};
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate generic PHP configuration
     */
    private function generateGenericPhpConfig(Site $site): string
    {
        $domain = $site->domain;
        $root = "/var/www/{$domain}";
        $phpSocket = "/run/php/php{$site->php_version}-fpm.sock";

        $sslConfig = $site->ssl_enabled ? $this->generateSSLConfig($site) : '';
        $httpsRedirect = $site->force_https ? $this->generateHTTPSRedirect($domain) : '';

        return <<<NGINX
{$httpsRedirect}

server {
    listen 80;
    listen [::]:80;
    {$sslConfig}
    
    server_name {$domain};
    root {$root};

    index index.php index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:{$phpSocket};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    access_log /var/www/{$domain}/logs/access.log;
    error_log /var/www/{$domain}/logs/error.log;
}
NGINX;
    }

    /**
     * Generate SSL configuration block
     */
    private function generateSSLConfig(Site $site): string
    {
        $domain = $site->domain;
        
        return <<<SSL
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    ssl_certificate /var/www/{$domain}/ssl/certificate.crt;
    ssl_certificate_key /var/www/{$domain}/ssl/private.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
SSL;
    }

    /**
     * Generate HTTPS redirect block
     */
    private function generateHTTPSRedirect(string $domain): string
    {
        return <<<REDIRECT
server {
    listen 80;
    listen [::]:80;
    server_name {$domain};
    return 301 https://\$server_name\$request_uri;
}
REDIRECT;
    }
}
