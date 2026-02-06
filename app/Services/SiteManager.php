<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Server;
use App\Models\Database;
use App\Enums\ApplicationType;
use App\Enums\PhpVersion;
use App\Enums\NodeVersion;
use App\Enums\DatabaseType;
use App\Enums\PackageManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SiteManager
{
    public function __construct(
        private SSHService $sshService,
        private NginxConfigGenerator $nginxGenerator,
        private SSLManager $sslManager,
    ) {}

    /**
     * Create a new site with complete configuration
     */
    public function createSite(Server $server, array $config): Site
    {
        Log::info("Creating site {$config['domain']} on server {$server->name}");

        // Create site record
        $site = Site::create([
            'server_id' => $server->id,
            'name' => $config['name'] ?? $config['domain'],
            'domain' => $config['domain'],
            'application_type' => $config['application_type'] ?? ApplicationType::LARAVEL->value,
            'custom_type' => $config['custom_type'] ?? null,
            'root_directory' => $config['root_directory'] ?? $this->getDefaultRootDirectory($config['application_type'] ?? ApplicationType::LARAVEL->value),
            'document_root' => $config['document_root'] ?? '/public',
            'php_version' => $config['php_version'] ?? '8.3',
            'node_version' => $config['node_version'] ?? null,
            'package_manager' => $config['package_manager'] ?? PackageManager::NPM->value,
            'git_repository' => $config['git_repository'] ?? null,
            'git_branch' => $config['git_branch'] ?? 'main',
            'git_provider' => $config['git_provider'] ?? null,
            'web_server' => $config['web_server'] ?? 'nginx',
            'nginx_template' => $config['nginx_template'] ?? 'laravel',
            'auto_ssl' => $config['auto_ssl'] ?? true,
            'ssl_type' => $config['ssl_type'] ?? 'letsencrypt',
            'force_https' => $config['force_https'] ?? false,
            'auto_deploy' => $config['auto_deploy'] ?? false,
            'auto_create_database' => $config['auto_create_database'] ?? true,
            'status' => 'inactive',
        ]);

        try {
            // 1. Create directory structure
            $this->createDirectoryStructure($server, $site);

            // 2. Configure PHP if needed
            if ($this->requiresPhp($site->application_type)) {
                $this->configurePHP($server, $site, $config);
            }

            // 3. Configure Node.js if needed
            if ($this->requiresNode($site->application_type)) {
                $this->configureNodeJS($server, $site, $config);
            }

            // 4. Create database if needed
            if ($site->auto_create_database) {
                $this->createDatabase($server, $site);
            }

            // 5. Clone repository if provided
            if ($site->git_repository) {
                $this->cloneRepository($server, $site);
            }

            // 6. Generate nginx configuration
            $this->configureNginx($server, $site);

            // 7. Setup SSL if enabled
            if ($site->auto_ssl) {
                $this->sslManager->generateCertificate($site);
            }

            // 8. Run application-specific setup
            $this->runApplicationSetup($server, $site, $config);

            $site->update(['status' => 'active', 'is_active' => true]);

            Log::info("Site {$site->domain} created successfully");

            return $site->fresh();

        } catch (\Exception $e) {
            Log::error("Failed to create site {$site->domain}: {$e->getMessage()}");
            $site->update(['status' => 'error']);
            throw $e;
        }
    }

    /**
     * Create directory structure for the site
     */
    private function createDirectoryStructure(Server $server, Site $site): void
    {
        $basePath = "/var/www/{$site->domain}";
        
        $commands = [
            "sudo mkdir -p {$basePath}",
            "sudo mkdir -p {$basePath}/logs",
            "sudo mkdir -p {$basePath}/ssl",
            "sudo chown -R www-data:www-data {$basePath}",
            "sudo chmod -R 755 {$basePath}",
        ];

        foreach ($commands as $command) {
            $this->sshService->execute($server, $command);
        }
    }

    /**
     * Configure PHP for the site
     */
    private function configurePHP(Server $server, Site $site, array $config): void
    {
        Log::info("Configuring PHP {$site->php_version} for {$site->domain}");

        // Get PHP-FPM version
        $phpVersion = str_replace('.', '', substr($site->php_version, 0, 3)); // 8.3 -> 83
        $fpmService = "php{$site->php_version}-fpm";
        $poolName = str_replace('.', '-', $site->domain);

        // Create dedicated PHP-FPM pool if requested
        if ($site->dedicated_php_pool) {
            $poolConfig = $this->generatePhpFpmPoolConfig($site, $poolName);
            
            $commands = [
                "sudo bash -c 'echo \"$poolConfig\" > /etc/php/{$site->php_version}/fpm/pool.d/{$poolName}.conf'",
                "sudo systemctl reload {$fpmService}",
            ];

            foreach ($commands as $command) {
                $this->sshService->execute($server, $command);
            }
        }
    }

    /**
     * Generate PHP-FPM pool configuration
     */
    private function generatePhpFpmPoolConfig(Site $site, string $poolName): string
    {
        $domain = str_replace('.', '-', $site->domain);
        
        return <<<EOL
[{$poolName}]
user = www-data
group = www-data
listen = /run/php/{$poolName}.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3

php_admin_value[memory_limit] = {$site->php_memory_limit}
php_admin_value[upload_max_filesize] = {$site->php_upload_max_filesize}
php_admin_value[post_max_size] = {$site->php_post_max_size}
php_admin_value[max_execution_time] = {$site->php_max_execution_time}
php_admin_value[error_log] = /var/www/{$site->domain}/logs/php-error.log
EOL;
    }

    /**
     * Configure Node.js for the site
     */
    private function configureNodeJS(Server $server, Site $site, array $config): void
    {
        Log::info("Configuring Node.js {$site->node_version} for {$site->domain}");

        $basePath = "/var/www/{$site->domain}";
        
        $commands = [
            // Install Node.js version using nvm
            "source ~/.nvm/nvm.sh && nvm install {$site->node_version}",
            "source ~/.nvm/nvm.sh && nvm use {$site->node_version}",
            
            // Set default Node version for this directory
            "cd {$basePath} && source ~/.nvm/nvm.sh && nvm alias default {$site->node_version}",
        ];

        foreach ($commands as $command) {
            $this->sshService->execute($server, $command);
        }

        // Configure PM2 if using process manager
        if ($site->process_manager === 'pm2') {
            $this->configurePM2($server, $site);
        }
    }

    /**
     * Configure PM2 process manager
     */
    private function configurePM2(Server $server, Site $site): void
    {
        $basePath = "/var/www/{$site->domain}";
        $appName = str_replace('.', '-', $site->domain);

        $ecosystem = [
            'apps' => [
                [
                    'name' => $appName,
                    'cwd' => $basePath,
                    'script' => $site->node_start_command ?? 'npm start',
                    'env' => [
                        'NODE_ENV' => 'production',
                        'PORT' => $site->node_port ?? 3000,
                    ],
                    'instances' => 1,
                    'exec_mode' => 'cluster',
                    'watch' => false,
                    'max_memory_restart' => '500M',
                    'error_file' => "{$basePath}/logs/pm2-error.log",
                    'out_file' => "{$basePath}/logs/pm2-out.log",
                ]
            ]
        ];

        $ecosystemJson = json_encode($ecosystem, JSON_PRETTY_PRINT);
        
        $commands = [
            "sudo bash -c 'echo '$ecosystemJson' > {$basePath}/ecosystem.config.json'",
            "sudo chown www-data:www-data {$basePath}/ecosystem.config.json",
        ];

        foreach ($commands as $command) {
            $this->sshService->execute($server, $command);
        }
    }

    /**
     * Create database for the site
     */
    private function createDatabase(Server $server, Site $site): void
    {
        $dbName = 'db_' . str_replace(['.', '-'], '_', $site->domain);
        $dbUser = 'user_' . Str::random(8);
        $dbPassword = Str::random(32);

        // Create database record
        $database = Database::create([
            'server_id' => $server->id,
            'name' => $dbName,
            'type' => DatabaseType::MYSQL->value,
            'status' => 'active',
        ]);

        // Create database on server
        $commands = [
            "sudo mysql -e \"CREATE DATABASE IF NOT EXISTS {$dbName};\"",
            "sudo mysql -e \"CREATE USER '{$dbUser}'@'localhost' IDENTIFIED BY '{$dbPassword}';\"",
            "sudo mysql -e \"GRANT ALL PRIVILEGES ON {$dbName}.* TO '{$dbUser}'@'localhost';\"",
            "sudo mysql -e \"FLUSH PRIVILEGES;\"",
        ];

        foreach ($commands as $command) {
            $this->sshService->execute($server, $command);
        }

        // Link database to site
        $site->update(['linked_database_id' => $database->id]);

        // Store credentials in .env
        $this->addEnvVariables($server, $site, [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_DATABASE' => $dbName,
            'DB_USERNAME' => $dbUser,
            'DB_PASSWORD' => $dbPassword,
        ]);

        Log::info("Created database {$dbName} for site {$site->domain}");
    }

    /**
     * Clone git repository
     */
    private function cloneRepository(Server $server, Site $site): void
    {
        $basePath = "/var/www/{$site->domain}";
        
        Log::info("Cloning repository {$site->git_repository} to {$basePath}");

        $gitUrl = $site->git_repository;
        
        // Add token if provided
        if ($site->git_token) {
            // Format: https://token@github.com/user/repo.git
            $gitUrl = preg_replace('/^https:\/\//', "https://{$site->git_token}@", $gitUrl);
        }

        $commands = [
            "cd {$basePath}",
            "sudo git clone -b {$site->git_branch} {$gitUrl} .",
            "sudo chown -R www-data:www-data {$basePath}",
        ];

        $this->sshService->execute($server, implode(' && ', $commands));
    }

    /**
     * Configure Nginx for the site
     */
    private function configureNginx(Server $server, Site $site): void
    {
        Log::info("Configuring Nginx for {$site->domain}");

        $config = $this->nginxGenerator->generate($site);
        
        $configPath = "/etc/nginx/sites-available/{$site->domain}";
        $enabledPath = "/etc/nginx/sites-enabled/{$site->domain}";

        $commands = [
            "sudo bash -c 'echo \"$config\" > {$configPath}'",
            "sudo ln -sf {$configPath} {$enabledPath}",
            "sudo nginx -t",
            "sudo systemctl reload nginx",
        ];

        foreach ($commands as $command) {
            $this->sshService->execute($server, $command);
        }

        $site->update(['nginx_config_path' => $configPath]);
    }

    /**
     * Run application-specific setup commands
     */
    private function runApplicationSetup(Server $server, Site $site, array $config): void
    {
        $applicationType = ApplicationType::from($site->application_type);
        $basePath = "/var/www/{$site->domain}";

        switch ($applicationType) {
            case ApplicationType::LARAVEL:
                $this->setupLaravel($server, $site, $basePath);
                break;
            
            case ApplicationType::WORDPRESS:
                $this->setupWordPress($server, $site, $basePath);
                break;
            
            case ApplicationType::NODEJS_EXPRESS:
            case ApplicationType::NESTJS:
                $this->setupNodeApp($server, $site, $basePath);
                break;
            
            case ApplicationType::REACT_SPA:
            case ApplicationType::VUE_SPA:
            case ApplicationType::ANGULAR:
                $this->setupSPA($server, $site, $basePath);
                break;
            
            case ApplicationType::NEXTJS:
            case ApplicationType::NUXTJS:
                $this->setupSSRFramework($server, $site, $basePath);
                break;
            
            case ApplicationType::DJANGO:
            case ApplicationType::FLASK:
                $this->setupPythonApp($server, $site, $basePath);
                break;

            case ApplicationType::RUBY_RAILS:
                $this->setupRails($server, $site, $basePath);
                break;

            default:
                Log::info("No specific setup for {$applicationType->value}");
        }
    }

    /**
     * Setup Laravel application
     */
    private function setupLaravel(Server $server, Site $site, string $basePath): void
    {
        Log::info("Setting up Laravel application at {$basePath}");

        $commands = [
            "cd {$basePath}",
            "composer install --no-dev --optimize-autoloader",
            "cp .env.example .env || true",
            "php artisan key:generate",
            "php artisan config:cache",
            "php artisan route:cache",
            "php artisan view:cache",
            "chmod -R 775 storage bootstrap/cache",
            "chown -R www-data:www-data storage bootstrap/cache",
        ];

        if ($site->linked_database_id) {
            $commands[] = "php artisan migrate --force";
        }

        $this->sshService->execute($server, 'sudo bash -c "' . implode(' && ', $commands) . '"');
    }

    /**
     * Setup WordPress
     */
    private function setupWordPress(Server $server, Site $site, string $basePath): void
    {
        Log::info("Setting up WordPress at {$basePath}");

        // WordPress will be manually uploaded or we can download latest
        $commands = [
            "cd {$basePath}",
            "wget https://wordpress.org/latest.tar.gz",
            "tar -xzf latest.tar.gz --strip-components=1",
            "rm latest.tar.gz",
            "chown -R www-data:www-data {$basePath}",
            "chmod -R 755 {$basePath}",
        ];

        $this->sshService->execute($server, 'sudo bash -c "' . implode(' && ', $commands) . '"');

        // Create wp-config.php if database is linked
        if ($site->linked_database_id) {
            $database = $site->linkedDatabase;
            // WP-CLI can handle this, or we generate the config
        }
    }

    /**
     * Setup Node.js application
     */
    private function setupNodeApp(Server $server, Site $site, string $basePath): void
    {
        Log::info("Setting up Node.js application at {$basePath}");

        $packageManager = $site->package_manager ?? 'npm';

        $commands = [
            "cd {$basePath}",
            "source ~/.nvm/nvm.sh",
            "nvm use {$site->node_version}",
            "{$packageManager} install",
        ];

        // If has build script
        if (isset($config['build_command'])) {
            $commands[] = $config['build_command'];
        }

        $this->sshService->execute($server, implode(' && ', $commands));

        // Start with PM2
        if ($site->process_manager === 'pm2') {
            $this->sshService->execute($server, "cd {$basePath} && pm2 start ecosystem.config.json");
            $this->sshService->execute($server, "pm2 save");
        }
    }

    /**
     * Setup SPA (React, Vue, Angular)
     */
    private function setupSPA(Server $server, Site $site, string $basePath): void
    {
        Log::info("Setting up SPA at {$basePath}");

        $packageManager = $site->package_manager ?? 'npm';

        $commands = [
            "cd {$basePath}",
            "source ~/.nvm/nvm.sh",
            "nvm use {$site->node_version}",
            "{$packageManager} install",
            "{$packageManager} run build",
        ];

        $this->sshService->execute($server, implode(' && ', $commands));
    }

    /**
     * Setup SSR Framework (Next.js, Nuxt.js)
     */
    private function setupSSRFramework(Server $server, Site $site, string $basePath): void
    {
        Log::info("Setting up SSR framework at {$basePath}");

        $packageManager = $site->package_manager ?? 'npm';

        $commands = [
            "cd {$basePath}",
            "source ~/.nvm/nvm.sh",
            "nvm use {$site->node_version}",
            "{$packageManager} install",
            "{$packageManager} run build",
        ];

        $this->sshService->execute($server, implode(' && ', $commands));

        // Start with PM2
        if ($site->process_manager === 'pm2') {
            $appName = str_replace('.', '-', $site->domain);
            $this->sshService->execute($server, "cd {$basePath} && pm2 start {$packageManager} --name {$appName} -- start");
            $this->sshService->execute($server, "pm2 save");
        }
    }

    /**
     * Setup Python application (Django, Flask)
     */
    private function setupPythonApp(Server $server, Site $site, string $basePath): void
    {
        Log::info("Setting up Python application at {$basePath}");

        $commands = [
            "cd {$basePath}",
            "python3 -m venv venv",
            "source venv/bin/activate",
            "pip install -r requirements.txt",
        ];

        if ($site->application_type === ApplicationType::DJANGO->value) {
            $commands[] = "python manage.py migrate";
            $commands[] = "python manage.py collectstatic --noinput";
        }

        $this->sshService->execute($server, implode(' && ', $commands));
    }

    /**
     * Setup Ruby on Rails
     */
    private function setupRails(Server $server, Site $site, string $basePath): void
    {
        Log::info("Setting up Rails application at {$basePath}");

        $commands = [
            "cd {$basePath}",
            "bundle install",
            "rails db:migrate RAILS_ENV=production",
            "rails assets:precompile RAILS_ENV=production",
        ];

        $this->sshService->execute($server, implode(' && ', $commands));
    }

    /**
     * Add environment variables to .env file
     */
    private function addEnvVariables(Server $server, Site $site, array $variables): void
    {
        $basePath = "/var/www/{$site->domain}";
        $envFile = "{$basePath}/.env";

        $envContent = '';
        foreach ($variables as $key => $value) {
            $envContent .= "{$key}={$value}\n";
        }

        $command = "sudo bash -c 'echo \"$envContent\" >> {$envFile}'";
        $this->sshService->execute($server, $command);
    }

    /**
     * Check if application type requires PHP
     */
    private function requiresPhp(string $applicationType): bool
    {
        return ApplicationType::from($applicationType)->requiresPhp();
    }

    /**
     * Check if application type requires Node.js
     */
    private function requiresNode(string $applicationType): bool
    {
        return ApplicationType::from($applicationType)->requiresNode();
    }

    /**
     * Get default root directory for application type
     */
    private function getDefaultRootDirectory(string $applicationType): string
    {
        return ApplicationType::from($applicationType)->defaultRootDirectory();
    }
}
