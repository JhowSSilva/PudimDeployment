<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\SSHService;
use App\Services\StackInstallationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InstallServerStackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 3;

    public function __construct(
        public Server $server,
        public array $config = []
    ) {}

    public function handle(
        SSHService $ssh,
        StackInstallationService $stackService
    ): void {
        try {
            Log::info("Starting stack installation for server {$this->server->id}");
            
            $this->server->update(['status' => 'installing']);
            
            // 1. Wait for server to be ready
            $this->waitForServerReady($ssh);
            
            // 2. Run base system installation
            $this->runBaseInstallation($ssh);
            
            // 3. Install programming language stack
            $this->installProgrammingStack($stackService);
            
            // 4. Install webserver if configured
            if ($this->server->webserver) {
                $this->installWebserver($ssh);
            }
            
            // 5. Install database if configured
            if ($this->server->database_type && $this->server->database_type !== 'none') {
                $this->installDatabase($ssh);
            }
            
            // 6. Install cache service if configured  
            if ($this->server->cache_service && $this->server->cache_service !== 'none') {
                $this->installCache($ssh);
            }
            
            // 7. Configure firewall
            $this->configureFirewall($ssh);
            
            // 8. Validate installation
            $this->validateInstallation($stackService);
            
            // 9. Mark as active
            $this->server->update([
                'status' => 'active',
                'provision_status' => 'active',
                'provisioned_at' => now(),
                'provision_completed_at' => now(),
            ]);
            
            Log::info("Server {$this->server->id} provisioned successfully!");
            
        } catch (\Exception $e) {
            Log::error("Error provisioning server {$this->server->id}: {$e->getMessage()}");
            
            $this->server->update([
                'status' => 'error',
                'provision_status' => 'failed'
            ]);
            
            $this->server->provisionLogs()->create([
                'step' => 'installation_failed',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'started_at' => now(),
                'completed_at' => now(),
            ]);
            
            throw $e;
        }
    }
    
    protected function waitForServerReady(SSHService $ssh): void
    {
        $maxAttempts = 30;
        $attempt = 0;
        
        $this->server->provisionLogs()->create([
            'step' => 'wait_server_ready',
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        while ($attempt < $maxAttempts) {
            try {
                $result = $ssh->testConnection($this->server);
                
                if ($result) {
                    $this->server->provisionLogs()
                        ->where('step', 'wait_server_ready')
                        ->where('status', 'running')
                        ->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    return;
                }
            } catch (\Exception $e) {
                // Continue trying
            }
            
            $attempt++;
            sleep(10);
        }
        
        throw new \Exception('Timeout waiting for server to be ready');
    }
    
    protected function runBaseInstallation(SSHService $ssh): void
    {
        $this->server->provisionLogs()->create([
            'step' => 'base_installation',
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        // Base installation script
        $script = $this->getBaseInstallationScript();
        
        $result = $ssh->executeScript($this->server, $script);
        
        $this->server->provisionLogs()
            ->where('step', 'base_installation')
            ->where('status', 'running')
            ->update([
                'status' => $result['success'] ? 'completed' : 'failed',
                'output' => $result['output'] ?? '',
                'error_message' => !$result['success'] ? $result['output'] : null,
                'completed_at' => now(),
            ]);
        
        if (!$result['success']) {
            throw new \Exception('Base installation failed');
        }
    }
    
    protected function installProgrammingStack(StackInstallationService $stackService): void
    {
        if (!$this->server->programming_language) {
            Log::info("No programming language configured for server {$this->server->id}");
            return;
        }
        
        $config = array_merge(
            $this->server->stack_config ?? [],
            $this->config,
            [
                'version' => $this->server->language_version,
            ]
        );
        
        $stackService->install(
            $this->server,
            $this->server->programming_language,
            $config
        );
    }
    
    protected function installWebserver(SSHService $ssh): void
    {
        $this->server->provisionLogs()->create([
            'step' => 'install_webserver',
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        $webserver = $this->server->webserver;
        $version = $this->server->webserver_version;
        
        $script = $this->getWebserverInstallScript($webserver, $version);
        $result = $ssh->executeScript($this->server, $script);
        
        $this->server->provisionLogs()
            ->where('step', 'install_webserver')
            ->where('status', 'running')
            ->update([
                'status' => $result['success'] ? 'completed' : 'failed',
                'output' => $result['output'] ?? '',
                'error_message' => !$result['success'] ? $result['output'] : null,
                'completed_at' => now(),
            ]);
        
        if (!$result['success']) {
            throw new \Exception('Webserver installation failed');
        }
    }
    
    protected function installDatabase(SSHService $ssh): void
    {
        $this->server->provisionLogs()->create([
            'step' => 'install_database',
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        $dbType = $this->server->database_type;
        $dbVersion = $this->server->database_version_new ?? $this->server->database_version;
        
        $script = $this->getDatabaseInstallScript($dbType, $dbVersion);
        $result = $ssh->executeScript($this->server, $script);
        
        $this->server->provisionLogs()
            ->where('step', 'install_database')
            ->where('status', 'running')
            ->update([
                'status' => $result['success'] ? 'completed' : 'failed',
                'output' => $result['output'] ?? '',
                'error_message' => !$result['success'] ? $result['output'] : null,
                'completed_at' => now(),
            ]);
        
        if (!$result['success']) {
            throw new \Exception('Database installation failed');
        }
        
        // Update installed tools
        $this->server->update([
            'installed_tools' => array_merge(
                $this->server->installed_tools ?? [],
                [$dbType]
            ),
        ]);
    }
    
    protected function installCache(SSHService $ssh): void
    {
        $this->server->provisionLogs()->create([
            'step' => 'install_cache',
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        $cacheService = $this->server->cache_service;
        $script = $this->getCacheInstallScript($cacheService);
        
        $result = $ssh->executeScript($this->server, $script);
        
        $this->server->provisionLogs()
            ->where('step', 'install_cache')
            ->where('status', 'running')
            ->update([
                'status' => $result['success'] ? 'completed' : 'failed',
                'output' => $result['output'] ?? '',
                'error_message' => !$result['success'] ? $result['output'] : null,
                'completed_at' => now(),
            ]);
        
        if (!$result['success']) {
            throw new \Exception('Cache service installation failed');
        }
        
        // Update installed tools
        $this->server->update([
            'installed_tools' => array_merge(
                $this->server->installed_tools ?? [],
                [$cacheService]
            ),
        ]);
    }
    
    protected function configureFirewall(SSHService $ssh): void
    {
        $this->server->provisionLogs()->create([
            'step' => 'configure_firewall',  
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        $rules = [
            'ufw --force reset',
            'ufw default deny incoming',
            'ufw default allow outgoing',
            'ufw allow 22/tcp comment "SSH"',
            'ufw allow 80/tcp comment "HTTP"',
            'ufw allow 443/tcp comment "HTTPS"',
        ];
        
        // Add language-specific ports
        $languagePorts = $this->getLanguageSpecificPorts();
        foreach ($languagePorts as $port => $description) {
            $rules[] = "ufw allow {$port} comment \"{$description}\"";
        }
        
        $rules[] = 'ufw --force enable';
        
        foreach ($rules as $rule) {
            $ssh->execute($this->server, $rule);
        }
        
        // Save firewall rules to database
        $this->saveFirewallRules();
        
        $this->server->provisionLogs()
            ->where('step', 'configure_firewall')
            ->where('status', 'running')
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
    }
    
    protected function validateInstallation(StackInstallationService $stackService): void
    {
        $this->server->provisionLogs()->create([
            'step' => 'validate_installation',
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        $isValid = $stackService->validate($this->server);
        
        $this->server->provisionLogs()
            ->where('step', 'validate_installation')
            ->where('status', 'running')
            ->update([
                'status' => $isValid ? 'completed' : 'failed',
                'completed_at' => now(),
                'error_message' => !$isValid ? 'Stack validation failed' : null,
            ]);
        
        if (!$isValid) {
            throw new \Exception('Installation validation failed');
        }
    }
    
    protected function getBaseInstallationScript(): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Starting base system installation..."

# Update system
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get upgrade -y

# Install essential packages
apt-get install -y \
    software-properties-common \
    curl \
    wget \
    git \
    unzip \
    zip \
    ca-certificates \
    apt-transport-https \
    gnupg \
    lsb-release \
    ufw \
    fail2ban \
    htop \
    nano \
    vim \
    supervisor

# Configure timezone
timedatectl set-timezone UTC

# Configure fail2ban
systemctl enable fail2ban
systemctl start fail2ban

# Create deploy user if not exists
if ! id "deploy" &>/dev/null; then
    useradd -m -s /bin/bash deploy
    usermod -aG sudo deploy
    mkdir -p /home/deploy/.ssh
    chmod 700 /home/deploy/.ssh
    chown deploy:deploy /home/deploy/.ssh
fi

echo "Base installation completed successfully!"
SCRIPT;
    }
    
    protected function getWebserverInstallScript(string $webserver, ?string $version = null): string
    {
        switch (strtolower($webserver)) {
            case 'nginx':
                return $this->getNginxInstallScript($version);
            case 'apache':
                return $this->getApacheInstallScript($version);
            case 'caddy':
                return $this->getCaddyInstallScript($version);
            default:
                throw new \Exception("Unsupported webserver: {$webserver}");
        }
    }
    
    protected function getNginxInstallScript(?string $version = null): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Installing Nginx..."

# Install Nginx
apt-get update
apt-get install -y nginx

# Enable and start Nginx
systemctl enable nginx
systemctl start nginx

# Create basic configuration
mkdir -p /etc/nginx/sites-available
mkdir -p /etc/nginx/sites-enabled

echo "Nginx installed successfully!"
SCRIPT;
    }
    
    protected function getApacheInstallScript(?string $version = null): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Installing Apache..."

# Install Apache
apt-get update
apt-get install -y apache2

# Enable necessary modules
a2enmod rewrite
a2enmod ssl
a2enmod headers

# Enable and start Apache
systemctl enable apache2
systemctl start apache2

echo "Apache installed successfully!"
SCRIPT;
    }
    
    protected function getCaddyInstallScript(?string $version = null): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Installing Caddy..."

# Add Caddy repository
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list

# Install Caddy
apt-get update
apt-get install -y caddy

# Enable and start Caddy
systemctl enable caddy
systemctl start caddy

echo "Caddy installed successfully!"
SCRIPT;
    }
    
    protected function getDatabaseInstallScript(string $dbType, ?string $version = null): string
    {
        switch (strtolower($dbType)) {
            case 'mysql':
            case 'mariadb':
                return $this->getMySQLInstallScript($dbType, $version);
            case 'postgresql':
                return $this->getPostgreSQLInstallScript($version);
            case 'mongodb':
                return $this->getMongoDBInstallScript($version);
            default:
                throw new \Exception("Unsupported database: {$dbType}");
        }
    }
    
    protected function getMySQLInstallScript(string $type, ?string $version = null): string
    {
        $packageName = $type === 'mariadb' ? 'mariadb-server' : 'mysql-server';
        $serviceName = $type === 'mariadb' ? 'mariadb' : 'mysql';
        
        return <<<SCRIPT
#!/bin/bash
set -e

echo "Installing {$type}..."

# Install {$type}
apt-get update
apt-get install -y {$packageName}

# Enable and start service
systemctl enable {$serviceName}
systemctl start {$serviceName}

# Secure installation (basic)
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

echo "{$type} installed successfully!"
SCRIPT;
    }
    
    protected function getPostgreSQLInstallScript(?string $version = null): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Installing PostgreSQL..."

# Install PostgreSQL
apt-get update
apt-get install -y postgresql postgresql-contrib

# Enable and start PostgreSQL
systemctl enable postgresql
systemctl start postgresql

echo "PostgreSQL installed successfully!"
SCRIPT;
    }
    
    protected function getMongoDBInstallScript(?string $version = null): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Installing MongoDB..."

# Add MongoDB repository
curl -fsSL https://pgp.mongodb.com/server-7.0.asc | gpg -o /usr/share/keyrings/mongodb-server-7.0.gpg --dearmor
echo "deb [ arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-7.0.gpg ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/7.0 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-7.0.list

# Install MongoDB
apt-get update
apt-get install -y mongodb-org

# Enable and start MongoDB
systemctl enable mongod
systemctl start mongod

echo "MongoDB installed successfully!"
SCRIPT;
    }
    
    protected function getCacheInstallScript(string $cacheService): string
    {
        switch (strtolower($cacheService)) {
            case 'redis':
                return $this->getRedisInstallScript();
            case 'memcached':
                return $this->getMemcachedInstallScript();
            default:
                throw new \Exception("Unsupported cache service: {$cacheService}");
        }
    }
    
    protected function getRedisInstallScript(): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Installing Redis..."

# Install Redis
apt-get update
apt-get install -y redis-server

# Configure Redis
sed -i 's/^supervised no/supervised systemd/' /etc/redis/redis.conf

# Enable and start Redis
systemctl enable redis-server
systemctl restart redis-server

echo "Redis installed successfully!"
SCRIPT;
    }
    
    protected function getMemcachedInstallScript(): string
    {
        return <<<'SCRIPT'
#!/bin/bash
set -e

echo "Installing Memcached..."

# Install Memcached
apt-get update
apt-get install -y memcached libmemcached-tools

# Enable and start Memcached
systemctl enable memcached
systemctl start memcached

echo "Memcached installed successfully!"
SCRIPT;
    }
    
    protected function getLanguageSpecificPorts(): array
    {
        $ports = [];
        
        switch ($this->server->programming_language) {
            case 'nodejs':
                $ports[3000] = 'Node.js default port';
                $ports[8080] = 'Node.js alternative port';
                break;
            case 'python':
                $ports[8000] = 'Python Django default port';
                $ports[5000] = 'Python Flask default port';
                break;
            case 'ruby':
                $ports[3000] = 'Ruby Rails default port';
                break;
            case 'go':
                $ports[8080] = 'Go default port';
                break;
        }
        
        return $ports;
    }
    
    protected function saveFirewallRules(): void
    {
        $rules = [
            ['port' => 22, 'protocol' => 'tcp', 'name' => 'SSH'],
            ['port' => 80, 'protocol' => 'tcp', 'name' => 'HTTP'],
            ['port' => 443, 'protocol' => 'tcp', 'name' => 'HTTPS'],
        ];
        
        // Add language-specific ports
        foreach ($this->getLanguageSpecificPorts() as $port => $description) {
            $rules[] = [
                'port' => $port,
                'protocol' => 'tcp',
                'name' => $description,
            ];
        }
        
        foreach ($rules as $rule) {
            $this->server->firewallRules()->create($rule);
        }
    }
}