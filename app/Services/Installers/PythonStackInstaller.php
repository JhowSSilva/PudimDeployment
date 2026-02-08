<?php

namespace App\Services\Installers;

use App\Models\Server;

class PythonStackInstaller extends AbstractStackInstaller
{
    public function getName(): string
    {
        return 'Python';
    }
    
    public function getSupportedVersions(): array
    {
        return ['3.8', '3.9', '3.10', '3.11', '3.12'];
    }
    
    public function getRequiredPackages(): array
    {
        return [
            'build-essential',
            'libssl-dev',
            'libffi-dev',
            'python3-dev',
            'libbz2-dev',
            'libreadline-dev',
            'libsqlite3-dev',
            'libncurses5-dev',
            'libncursesw5-dev',
            'xz-utils',
            'tk-dev',
        ];
    }
    
    public function getDefaultConfig(): array
    {
        return [
            'install_pip' => true,
            'install_virtualenv' => true,
            'install_poetry' => false,
            'install_pipenv' => false,
            'install_gunicorn' => true,
            'install_uvicorn' => true,
            'global_packages' => [
                'pip',
                'setuptools',
                'wheel',
                'virtualenv',
                'gunicorn',
                'uvicorn[standard]',
                'supervisor',
            ],
            'venv_path' => '/var/www/venvs',
        ];
    }
    
    public function install(Server $server, array $config): bool
    {
        $version = $config['version'] ?? '3.11';
        $installPoetry = $config['install_poetry'] ?? false;
        $installPipenv = $config['install_pipenv'] ?? false;
        $globalPackages = $config['global_packages'] ?? $this->getDefaultConfig()['global_packages'];
        
        try {
            // 1. Install prerequisites
            $this->logProgress($server, 'python_prerequisites', 'running');
            
            if (!$this->installPackages($server, $this->getRequiredPackages(), 'Installing Python prerequisites')) {
                throw new \Exception('Failed to install prerequisites');
            }
            
            $this->logProgress($server, 'python_prerequisites', 'completed');
            
            // 2. Add deadsnakes repository
            $this->logProgress($server, 'python_add_repository', 'running');
            
            if (!$this->addRepository($server, 'ppa:deadsnakes/ppa', 'Python (deadsnakes)')) {
                throw new \Exception('Failed to add Python repository');
            }
            
            $this->logProgress($server, 'python_add_repository', 'completed');
            
            // 3. Install Python
            $this->installPython($server, $version);
            
            // 4. Install pip and global packages
            $this->installGlobalPackages($server, $version, $globalPackages);
            
            // 5. Install Poetry if requested
            if ($installPoetry) {
                $this->installPoetry($server);
            }
            
            // 6. Install Pipenv if requested
            if ($installPipenv) {
                $this->installPipenv($server);
            }
            
            // 7. Setup virtual environment directory
            $this->setupVenvDirectory($server, $config);
            
            // 8. Update server record
            $tools = ['pip', 'virtualenv'];
            
            if (in_array('gunicorn', $globalPackages)) {
                $tools[] = 'gunicorn';
            }
            if (in_array('uvicorn[standard]', $globalPackages)) {
                $tools[] = 'uvicorn';
            }
            if ($installPoetry) {
                $tools[] = 'poetry';
            }
            if ($installPipenv) {
                $tools[] = 'pipenv';
            }
            
            $server->update([
                'language_version' => $version,
                'process_manager' => 'gunicorn',
                'installed_tools' => array_merge(
                    $server->installed_tools ?? [],
                    $tools
                ),
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logProgress($server, 'python_install', 'failed', null, $e->getMessage());
            throw $e;
        }
    }
    
    protected function installPython(Server $server, string $version): void
    {
        $this->logProgress($server, 'python_install', 'running');
        
        $packages = [
            "python{$version}",
            "python{$version}-dev",
            "python{$version}-venv",
            "python{$version}-distutils",
            "python3-pip",
        ];
        
        if (!$this->installPackages($server, $packages, 'Installing Python packages')) {
            throw new \Exception('Failed to install Python packages');
        }
        
        // Set Python alternative
        $result = $this->executeCommand(
            $server,
            "update-alternatives --install /usr/bin/python3 python3 /usr/bin/python{$version} 1",
            'Setting Python alternative'
        );
        
        if ($result['exit_code'] !== 0) {
            throw new \Exception('Failed to set Python alternative');
        }
        
        $this->logProgress($server, 'python_install', 'completed');
    }
    
    protected function installGlobalPackages(Server $server, string $version, array $packages): void
    {
        $this->logProgress($server, 'python_global_packages', 'running');
        
        // Upgrade pip first
        $result = $this->executeCommand(
            $server,
            'python3 -m pip install --upgrade pip setuptools wheel',
            'Upgrading pip and basic tools'
        );
        
        if ($result['exit_code'] !== 0) {
            throw new \Exception('Failed to upgrade pip');
        }
        
        // Install each package
        foreach ($packages as $package) {
            if ($package === 'pip' || $package === 'setuptools' || $package === 'wheel') {
                continue; // Already installed
            }
            
            $result = $this->executeCommand(
                $server,
                "python3 -m pip install '{$package}'",
                "Installing {$package}"
            );
            
            if ($result['exit_code'] !== 0) {
                // Log warning but continue
                Log::warning("Failed to install Python package: {$package}");
            }
        }
        
        $this->logProgress($server, 'python_global_packages', 'completed');
    }
    
    protected function installPoetry(Server $server): void
    {
        $this->logProgress($server, 'poetry_install', 'running');
        
        $result = $this->executeCommand(
            $server,
            'curl -sSL https://install.python-poetry.org | python3 -',
            'Installing Poetry'
        );
        
        if ($result['exit_code'] !== 0) {
            throw new \Exception('Failed to install Poetry');
        }
        
        // Create symlink
        $this->executeCommand(
            $server,
            'ln -sf /root/.local/bin/poetry /usr/local/bin/poetry',
            'Creating Poetry symlink'
        );
        
        $this->logProgress($server, 'poetry_install', 'completed');
    }
    
    protected function installPipenv(Server $server): void
    {
        $this->logProgress($server, 'pipenv_install', 'running');
        
        $result = $this->executeCommand(
            $server,
            'python3 -m pip install pipenv',
            'Installing Pipenv'
        );
        
        if ($result['exit_code'] !== 0) {
            throw new \Exception('Failed to install Pipenv');
        }
        
        $this->logProgress($server, 'pipenv_install', 'completed');
    }
    
    protected function setupVenvDirectory(Server $server, array $config): void
    {
        $venvPath = $config['venv_path'] ?? $this->getDefaultConfig()['venv_path'];
        
        $this->executeCommand(
            $server,
            "mkdir -p {$venvPath} && chmod 755 {$venvPath}",
            'Creating virtual environment directory'
        );
        
        // Create a default virtual environment
        $this->executeCommand(
            $server,
            "python3 -m venv {$venvPath}/default",
            'Creating default virtual environment'
        );
    }
    
    public function validate(Server $server): bool
    {
        // Check Python
        $pythonCheck = $this->executeCommand($server, 'python3 --version', 'Validating Python');
        if ($pythonCheck['exit_code'] !== 0) {
            return false;
        }
        
        // Check pip
        $pipCheck = $this->executeCommand($server, 'python3 -m pip --version', 'Validating pip');
        if ($pipCheck['exit_code'] !== 0) {
            return false;
        }
        
        // Check virtual environment creation
        $venvCheck = $this->executeCommand(
            $server,
            'python3 -m venv /tmp/test_venv && rm -rf /tmp/test_venv',
            'Testing virtual environment creation'
        );
        if ($venvCheck['exit_code'] !== 0) {
            return false;
        }
        
        // Check installed tools
        $tools = $server->getInstalledToolsList();
        foreach (['gunicorn', 'uvicorn', 'poetry', 'pipenv'] as $tool) {
            if (in_array($tool, $tools)) {
                $toolCheck = $this->executeCommand($server, "{$tool} --version", "Validating {$tool}");
                if ($toolCheck['exit_code'] !== 0) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public function generateInstallScript(Server $server, array $config): string
    {
        $version = $config['version'] ?? '3.11';
        $globalPackages = implode("' '", $config['global_packages'] ?? $this->getDefaultConfig()['global_packages']);
        $venvPath = $config['venv_path'] ?? $this->getDefaultConfig()['venv_path'];
        
        return <<<SCRIPT
#!/bin/bash
set -e

echo "Installing Python {$version}..."

# Install prerequisites
apt-get update
apt-get install -y build-essential libssl-dev libffi-dev python3-dev libbz2-dev libreadline-dev libsqlite3-dev libncurses5-dev libncursesw5-dev xz-utils tk-dev

# Add deadsnakes repository
add-apt-repository ppa:deadsnakes/ppa -y
apt-get update

# Install Python
apt-get install -y python{$version} python{$version}-dev python{$version}-venv python{$version}-distutils python3-pip

# Set Python alternative
update-alternatives --install /usr/bin/python3 python3 /usr/bin/python{$version} 1

# Upgrade pip
python3 -m pip install --upgrade pip setuptools wheel

# Install global packages
python3 -m pip install '{$globalPackages}'

# Setup virtual environment directory
mkdir -p {$venvPath}
chmod 755 {$venvPath}
python3 -m venv {$venvPath}/default

echo "Python {$version} installed successfully!"
python3 --version
python3 -m pip --version
SCRIPT;
    }
}