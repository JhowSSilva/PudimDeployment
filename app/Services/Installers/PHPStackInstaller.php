<?php

namespace App\Services\Installers;

use App\Models\Server;

class PHPStackInstaller extends AbstractStackInstaller
{
    public function getName(): string
    {
        return 'PHP';
    }
    
    public function getSupportedVersions(): array
    {
        return ['8.0', '8.1', '8.2', '8.3', '8.4'];
    }
    
    public function getRequiredPackages(): array
    {
        return [
            'software-properties-common',
            'apt-transport-https',
            'ca-certificates',
        ];
    }
    
    public function getDefaultConfig(): array
    {
        return [
            'install_composer' => true,
            'install_extensions' => [
                'fpm', 'cli', 'common', 'mysql', 'pgsql', 'mongodb',
                'redis', 'memcached', 'gd', 'curl', 'mbstring',
                'xml', 'zip', 'bcmath', 'intl', 'soap', 'imagick', 'opcache'
            ],
            'php_config' => [
                'upload_max_filesize' => '256M',
                'post_max_size' => '256M',
                'memory_limit' => '512M',
                'max_execution_time' => '300',
                'date.timezone' => 'UTC',
            ],
            'opcache_config' => [
                'opcache.enable' => '1',
                'opcache.memory_consumption' => '256',
                'opcache.interned_strings_buffer' => '16',
                'opcache.max_accelerated_files' => '10000',
                'opcache.revalidate_freq' => '60',
                'opcache.fast_shutdown' => '1',
                'opcache.enable_cli' => '0',
            ],
        ];
    }
    
    public function install(Server $server, array $config): bool
    {
        $version = $config['version'] ?? '8.2';
        $installComposer = $config['install_composer'] ?? true;
        $extensions = $config['install_extensions'] ?? $this->getDefaultConfig()['install_extensions'];
        
        try {
            // 1. Add repository
            $this->logProgress($server, 'php_add_repository', 'running');
            
            if (!$this->addRepository($server, 'ppa:ondrej/php', 'PHP')) {
                throw new \Exception('Failed to add PHP repository');
            }
            
            $this->logProgress($server, 'php_add_repository', 'completed');
            
            // 2. Install PHP and extensions
            $this->logProgress($server, 'php_install', 'running');
            
            $packages = array_map(fn($ext) => "php{$version}-{$ext}", $extensions);
            
            if (!$this->installPackages($server, $packages, 'Installing PHP and extensions')) {
                throw new \Exception('Failed to install PHP packages');
            }
            
            $this->logProgress($server, 'php_install', 'completed');
            
            // 3. Enable PHP-FPM service
            if (!$this->enableService($server, "php{$version}-fpm")) {
                throw new \Exception('Failed to enable PHP-FPM service');
            }
            
            // 4. Configure PHP
            $this->configure($server, array_merge($config, ['version' => $version]));
            
            // 5. Install Composer
            if ($installComposer) {
                $this->installComposer($server);
            }
            
            // 6. Update server record
            $tools = [];
            if ($installComposer) {
                $tools[] = 'composer';
            }
            
            $server->update([
                'language_version' => $version,
                'installed_tools' => array_merge(
                    $server->installed_tools ?? [],
                    $tools
                ),
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logProgress($server, 'php_install', 'failed', null, $e->getMessage());
            throw $e;
        }
    }
    
    public function configure(Server $server, array $config): bool
    {
        $version = $config['version'] ?? '8.2';
        
        $this->logProgress($server, 'php_configure', 'running');
        
        // Configure PHP INI
        $phpIni = "/etc/php/{$version}/fpm/php.ini";
        $phpConfigs = array_merge(
            $this->getDefaultConfig()['php_config'],
            $config['php_config'] ?? []
        );
        
        foreach ($phpConfigs as $key => $value) {
            $this->executeCommand(
                $server,
                "sed -i 's/^{$key} =.*/{$key} = {$value}/' {$phpIni}",
                "Configuring {$key}"
            );
        }
        
        // Configure OPcache
        $opcacheConfigs = array_merge(
            $this->getDefaultConfig()['opcache_config'],
            $config['opcache_config'] ?? []
        );
        
        $opcacheConfig = "\n[opcache]\n";
        foreach ($opcacheConfigs as $key => $value) {
            $opcacheConfig .= "{$key}={$value}\n";
        }
        
        $this->executeCommand(
            $server,
            "echo '{$opcacheConfig}' >> {$phpIni}",
            'Configuring OPcache'
        );
        
        // Configure PHP-FPM pool
        $fpmPool = "/etc/php/{$version}/fpm/pool.d/www.conf";
        $poolConfigs = [
            'pm' => 'dynamic',
            'pm.max_children' => '50',
            'pm.start_servers' => '10',
            'pm.min_spare_servers' => '5',
            'pm.max_spare_servers' => '15',
            'pm.max_requests' => '1000',
        ];
        
        foreach ($poolConfigs as $key => $value) {
            $this->executeCommand(
                $server,
                "sed -i 's/^{$key} =.*/{$key} = {$value}/' {$fpmPool}",
                "Configuring FPM: {$key}"
            );
        }
        
        // Restart PHP-FPM
        if (!$this->restartService($server, "php{$version}-fmp")) {
            $this->logProgress($server, 'php_configure', 'failed', null, 'Failed to restart PHP-FPM');
            return false;
        }
        
        $this->logProgress($server, 'php_configure', 'completed');
        return true;
    }
    
    protected function installComposer(Server $server): void
    {
        $this->logProgress($server, 'composer_install', 'running');
        
        $result = $this->executeCommand(
            $server,
            'curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer',
            'Installing Composer'
        );
        
        if ($result['exit_code'] === 0) {
            $this->executeCommand($server, 'chmod +x /usr/local/bin/composer', 'Making Composer executable');
            $this->logProgress($server, 'composer_install', 'completed');
        } else {
            $this->logProgress($server, 'composer_install', 'failed', null, 'Failed to install Composer');
            throw new \Exception('Failed to install Composer');
        }
    }
    
    public function validate(Server $server): bool
    {
        // Check if PHP is installed and working
        $phpCheck = $this->executeCommand($server, 'php -v', 'Validating PHP installation');
        if ($phpCheck['exit_code'] !== 0) {
            return false;
        }
        
        // Check if PHP-FPM is running
        $version = $server->language_version ?? '8.2';
        if (!$this->isServiceRunning($server, "php{$version}-fpm")) {
            return false;
        }
        
        // Check Composer if installed
        if ($server->hasToolInstalled('composer')) {
            $composerCheck = $this->executeCommand($server, 'composer -V', 'Validating Composer');
            if ($composerCheck['exit_code'] !== 0) {
                return false;
            }
        }
        
        return true;
    }
    
    public function generateInstallScript(Server $server, array $config): string
    {
        $version = $config['version'] ?? '8.2';
        $extensions = implode(' ', array_map(
            fn($ext) => "php{$version}-{$ext}", 
            $config['install_extensions'] ?? $this->getDefaultConfig()['install_extensions']
        ));
        
        return <<<SCRIPT
#!/bin/bash
set -e

echo "Installing PHP {$version}..."

# Add ondrej/php repository
add-apt-repository ppa:ondrej/php -y
apt-get update

# Install PHP and extensions
apt-get install -y {$extensions}

# Enable and start PHP-FPM
systemctl enable php{$version}-fpm
systemctl start php{$version}-fpm

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer

echo "PHP {$version} installed successfully!"
php -v
composer -V
SCRIPT;
    }
}