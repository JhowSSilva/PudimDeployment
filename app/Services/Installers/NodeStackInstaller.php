<?php

namespace App\Services\Installers;

use App\Models\Server;

class NodeStackInstaller extends AbstractStackInstaller
{
    public function getName(): string
    {
        return 'Node.js';
    }
    
    public function getSupportedVersions(): array
    {
        return ['16', '18', '20', '21'];
    }
    
    public function getRequiredPackages(): array
    {
        return ['curl', 'build-essential'];
    }
    
    public function getDefaultConfig(): array
    {
        return [
            'install_yarn' => true,
            'install_pm2' => true,
            'global_packages' => ['yarn', 'pm2', 'nodemon', 'typescript'],
            'pm2_setup_startup' => true,
        ];
    }
    
    public function install(Server $server, array $config): bool
    {
        $version = $config['version'] ?? '20';
        $installYarn = $config['install_yarn'] ?? true;
        $installPm2 = $config['install_pm2'] ?? true;
        $globalPackages = $config['global_packages'] ?? $this->getDefaultConfig()['global_packages'];
        
        try {
            // 1. Install required packages first
            $this->logProgress($server, 'nodejs_prerequisites', 'running');
            
            if (!$this->installPackages($server, $this->getRequiredPackages(), 'Installing prerequisites')) {
                throw new \Exception('Failed to install prerequisites');
            }
            
            $this->logProgress($server, 'nodejs_prerequisites', 'completed');
            
            // 2. Install NVM
            $this->installNvm($server);
            
            // 3. Install Node.js
            $this->installNode($server, $version);
            
            // 4. Install global packages
            $this->installGlobalPackages($server, $globalPackages);
            
            // 5. Setup PM2 if requested
            if ($installPm2 && in_array('pm2', $globalPackages)) {
                $this->setupPm2($server, $config);
            }
            
            // 6. Update server record
            $tools = ['npm'];
            if ($installYarn && in_array('yarn', $globalPackages)) {
                $tools[] = 'yarn';
            }
            if ($installPm2 && in_array('pm2', $globalPackages)) {
                $tools[] = 'pm2';
            }
            $tools = array_merge($tools, array_diff($globalPackages, ['yarn', 'pm2']));
            
            $server->update([
                'language_version' => $version,
                'process_manager' => $installPm2 ? 'pm2' : null,
                'installed_tools' => array_merge(
                    $server->installed_tools ?? [],
                    $tools
                ),
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logProgress($server, 'nodejs_install', 'failed', null, $e->getMessage());
            throw $e;
        }
    }
    
    protected function installNvm(Server $server): void
    {
        $this->logProgress($server, 'nvm_install', 'running');
        
        $nvmInstallScript = 'curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash';
        
        // Install for root
        $result1 = $this->executeCommand($server, $nvmInstallScript, 'Installing NVM (root)');
        
        // Install for deploy user if exists
        $result2 = $this->executeCommand(
            $server,
            "su - deploy -c '{$nvmInstallScript}' || true",
            'Installing NVM (deploy user)'
        );
        
        if ($result1['exit_code'] !== 0) {
            throw new \Exception('Failed to install NVM');
        }
        
        $this->logProgress($server, 'nvm_install', 'completed');
    }
    
    protected function installNode(Server $server, string $version): void
    {
        $this->logProgress($server, 'nodejs_install', 'running');
        
        $commands = [
            'export NVM_DIR="$HOME/.nvm"',
            '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"',
            "nvm install {$version}",
            "nvm use {$version}",
            "nvm alias default {$version}",
        ];
        
        $fullCommand = implode(' && ', $commands);
        $result = $this->executeCommand($server, $fullCommand, 'Installing Node.js');
        
        if ($result['exit_code'] !== 0) {
            throw new \Exception('Failed to install Node.js');
        }
        
        // Create global symlinks
        $symlinkCommands = [
            'export NVM_DIR="$HOME/.nvm"',
            '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"',
            'ln -sf $(which node) /usr/local/bin/node',
            'ln -sf $(which npm) /usr/local/bin/npm',
        ];
        
        $this->executeCommand(
            $server,
            implode(' && ', $symlinkCommands),
            'Creating Node.js symlinks'
        );
        
        $this->logProgress($server, 'nodejs_install', 'completed');
    }
    
    protected function installGlobalPackages(Server $server, array $packages): void
    {
        if (empty($packages)) {
            return;
        }
        
        $this->logProgress($server, 'npm_global_packages', 'running');
        
        foreach ($packages as $package) {
            $commands = [
                'export NVM_DIR="$HOME/.nvm"',
                '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"',
                "npm install -g {$package}",
            ];
            
            $result = $this->executeCommand(
                $server,
                implode(' && ', $commands),
                "Installing {$package} globally"
            );
            
            if ($result['exit_code'] === 0) {
                // Create symlink for global availability
                $symlinkCommands = [
                    'export NVM_DIR="$HOME/.nvm"',
                    '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"',
                    "ln -sf \$(which {$package}) /usr/local/bin/{$package} || true",
                ];
                
                $this->executeCommand(
                    $server,
                    implode(' && ', $symlinkCommands),
                    "Creating {$package} symlink"
                );
            }
        }
        
        $this->logProgress($server, 'npm_global_packages', 'completed');
    }
    
    protected function setupPm2(Server $server, array $config): void
    {
        $this->logProgress($server, 'pm2_setup', 'running');
        
        $setupStartup = $config['pm2_setup_startup'] ?? true;
        
        if ($setupStartup) {
            // Setup PM2 startup script
            $commands = [
                'export NVM_DIR="$HOME/.nvm"',
                '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"',
                'pm2 startup systemd -u deploy --hp /home/deploy || true',
            ];
            
            $this->executeCommand(
                $server,
                implode(' && ', $commands),
                'Setting up PM2 startup'
            );
        }
        
        // Create PM2 log directory
        $this->executeCommand(
            $server,
            'mkdir -p /var/log/pm2 && chown -R deploy:deploy /var/log/pm2 || true',
            'Creating PM2 log directory'
        );
        
        $this->logProgress($server, 'pm2_setup', 'completed');
    }
    
    public function validate(Server $server): bool
    {
        // Check Node.js
        $nodeCheck = $this->executeCommand($server, 'node --version', 'Validating Node.js');
        if ($nodeCheck['exit_code'] !== 0) {
            return false;
        }
        
        // Check npm
        $npmCheck = $this->executeCommand($server, 'npm --version', 'Validating npm');
        if ($npmCheck['exit_code'] !== 0) {
            return false;
        }
        
        // Check installed tools
        $tools = $server->getInstalledToolsList();
        foreach (['yarn', 'pm2', 'nodemon', 'typescript'] as $tool) {
            if (in_array($tool, $tools)) {
                $toolCheck = $this->executeCommand($server, "{$tool} --version || {$tool} -v", "Validating {$tool}");
                if ($toolCheck['exit_code'] !== 0) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public function generateInstallScript(Server $server, array $config): string
    {
        $version = $config['version'] ?? '20';
        $globalPackages = implode(' ', $config['global_packages'] ?? ['yarn', 'pm2']);
        
        return <<<SCRIPT
#!/bin/bash
set -e

echo "Installing Node.js {$version}..."

# Install prerequisites
apt-get update
apt-get install -y curl build-essential

# Install NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash

# Load NVM and install Node.js
export NVM_DIR="\$HOME/.nvm"
[ -s "\$NVM_DIR/nvm.sh" ] && \\. "\$NVM_DIR/nvm.sh"

nvm install {$version}
nvm use {$version}
nvm alias default {$version}

# Create symlinks
ln -sf \$(which node) /usr/local/bin/node
ln -sf \$(which npm) /usr/local/bin/npm

# Install global packages
npm install -g {$globalPackages}

# Create symlinks for global packages
for package in {$globalPackages}; do
    ln -sf \$(which \$package) /usr/local/bin/\$package || true
done

echo "Node.js {$version} installed successfully!"
node --version
npm --version
SCRIPT;
    }
}