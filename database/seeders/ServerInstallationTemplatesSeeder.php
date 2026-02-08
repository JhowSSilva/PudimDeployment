<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServerInstallationTemplate;

class ServerInstallationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // PHP Templates
            [
                'name' => 'PHP 8.3 with Extensions',
                'language' => 'php',
                'version' => '8.3',
                'description' => 'PHP 8.3 with common extensions (FPM, CLI, MySQL, Redis, etc.)',
                'dependencies' => [
                    'software-properties-common',
                    'apt-transport-https',
                    'ca-certificates',
                ],
                'install_script' => $this->getPhpInstallScript('8.3'),
                'default_config' => $this->getPhpDefaultConfig(),
            ],
            [
                'name' => 'PHP 8.2 with Extensions',
                'language' => 'php',
                'version' => '8.2',
                'description' => 'PHP 8.2 with common extensions (FPM, CLI, MySQL, Redis, etc.)',
                'dependencies' => [
                    'software-properties-common',
                    'apt-transport-https',
                    'ca-certificates',
                ],
                'install_script' => $this->getPhpInstallScript('8.2'),
                'default_config' => $this->getPhpDefaultConfig(),
            ],
            
            // Node.js Templates
            [
                'name' => 'Node.js 20 LTS',
                'language' => 'nodejs',
                'version' => '20',
                'description' => 'Node.js 20 LTS with npm, yarn and PM2',
                'dependencies' => ['curl', 'build-essential'],
                'install_script' => $this->getNodeInstallScript('20'),
                'default_config' => $this->getNodeDefaultConfig(),
            ],
            [
                'name' => 'Node.js 18 LTS',
                'language' => 'nodejs',
                'version' => '18',
                'description' => 'Node.js 18 LTS with npm, yarn and PM2',
                'dependencies' => ['curl', 'build-essential'],
                'install_script' => $this->getNodeInstallScript('18'),
                'default_config' => $this->getNodeDefaultConfig(),
            ],
            
            // Python Templates
            [
                'name' => 'Python 3.11',
                'language' => 'python',
                'version' => '3.11',
                'description' => 'Python 3.11 with pip, virtualenv, gunicorn and common packages',
                'dependencies' => [
                    'build-essential',
                    'libssl-dev',
                    'libffi-dev',
                    'python3-dev',
                ],
                'install_script' => $this->getPythonInstallScript('3.11'),
                'default_config' => $this->getPythonDefaultConfig(),
            ],
            [
                'name' => 'Python 3.12',
                'language' => 'python',
                'version' => '3.12',
                'description' => 'Python 3.12 with pip, virtualenv, gunicorn and common packages',
                'dependencies' => [
                    'build-essential',
                    'libssl-dev',
                    'libffi-dev',
                    'python3-dev',
                ],
                'install_script' => $this->getPythonInstallScript('3.12'),
                'default_config' => $this->getPythonDefaultConfig(),
            ],
        ];

        foreach ($templates as $template) {
            ServerInstallationTemplate::create($template);
        }
    }

    private function getPhpInstallScript(string $version): string
    {
        return <<<SCRIPT
#!/bin/bash
set -e

echo "Installing PHP {$version}..."

# Add ondrej/php repository
add-apt-repository ppa:ondrej/php -y
apt-get update

# Install PHP and common extensions
apt-get install -y \\
    php{$version}-fpm \\
    php{$version}-cli \\
    php{$version}-common \\
    php{$version}-mysql \\
    php{$version}-pgsql \\
    php{$version}-mongodb \\
    php{$version}-redis \\
    php{$version}-memcached \\
    php{$version}-gd \\
    php{$version}-curl \\
    php{$version}-mbstring \\
    php{$version}-xml \\
    php{$version}-zip \\
    php{$version}-bcmath \\
    php{$version}-intl \\
    php{$version}-soap \\
    php{$version}-imagick \\
    php{$version}-opcache

# Enable and start PHP-FPM
systemctl enable php{$version}-fpm
systemctl start php{$version}-fpm

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer

echo "PHP {$version} installed successfully!"
SCRIPT;
    }

    private function getPhpDefaultConfig(): array
    {
        return [
            'memory_limit' => '512M',
            'upload_max_filesize' => '256M',
            'post_max_size' => '256M',
            'max_execution_time' => '300',
            'opcache_enabled' => true,
            'opcache_memory_consumption' => 256,
        ];
    }

    private function getNodeInstallScript(string $version): string
    {
        return <<<SCRIPT
#!/bin/bash
set -e

echo "Installing Node.js {$version}..."

# Install NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
export NVM_DIR="\$HOME/.nvm"
[ -s "\$NVM_DIR/nvm.sh" ] && \\. "\$NVM_DIR/nvm.sh"

# Install Node.js
nvm install {$version}
nvm use {$version}
nvm alias default {$version}

# Create global symlinks
ln -sf \$(which node) /usr/local/bin/node
ln -sf \$(which npm) /usr/local/bin/npm

# Install global packages
npm install -g yarn pm2

# Create symlinks for global packages
ln -sf \$(which yarn) /usr/local/bin/yarn
ln -sf \$(which pm2) /usr/local/bin/pm2

# Setup PM2 startup
pm2 startup systemd --user deploy --hp /home/deploy || true

echo "Node.js {$version} installed successfully!"
SCRIPT;
    }

    private function getNodeDefaultConfig(): array
    {
        return [
            'install_yarn' => true,
            'install_pm2' => true,
            'global_packages' => ['yarn', 'pm2'],
        ];
    }

    private function getPythonInstallScript(string $version): string
    {
        return <<<SCRIPT
#!/bin/bash
set -e

echo "Installing Python {$version}..."

# Add deadsnakes repository
add-apt-repository ppa:deadsnakes/ppa -y
apt-get update

# Install Python
apt-get install -y \\
    python{$version} \\
    python{$version}-dev \\
    python{$version}-venv \\
    python{$version}-distutils \\
    python3-pip

# Set Python alternative
update-alternatives --install /usr/bin/python3 python3 /usr/bin/python{$version} 1

# Upgrade pip
python3 -m pip install --upgrade pip setuptools wheel

# Install common packages
pip3 install \\
    virtualenv \\
    gunicorn \\
    uvicorn[standard] \\
    supervisor

echo "Python {$version} installed successfully!"
SCRIPT;
    }

    private function getPythonDefaultConfig(): array
    {
        return [
            'virtual_env_path' => '/var/www/venvs',
            'packages' => ['virtualenv', 'gunicorn', 'uvicorn'],
        ];
    }
}