<?php

namespace Database\Seeders;

use App\Models\ServerSoftwareCatalog;
use Illuminate\Database\Seeder;

class ServerSoftwareCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $software = [
            // Essential Tools
            [
                'key' => 'git',
                'name' => 'Git',
                'category' => 'essential',
                'description' => 'Distributed version control system',
                'is_default' => true,
                'dependencies' => null,
                'install_commands' => ['apt-get install -y git'],
                'verify_commands' => ['git --version'],
                'install_order' => 10,
                'is_active' => true,
            ],
            [
                'key' => 'curl',
                'name' => 'cURL',
                'category' => 'essential',
                'description' => 'Command line tool for transferring data',
                'is_default' => true,
                'dependencies' => null,
                'install_commands' => ['apt-get install -y curl'],
                'verify_commands' => ['curl --version'],
                'install_order' => 5,
                'is_active' => true,
            ],
            [
                'key' => 'unzip',
                'name' => 'Unzip',
                'category' => 'essential',
                'description' => 'Archive extraction utility',
                'is_default' => true,
                'dependencies' => null,
                'install_commands' => ['apt-get install -y unzip'],
                'verify_commands' => ['unzip -v'],
                'install_order' => 5,
                'is_active' => true,
            ],

            // Web Servers
            [
                'key' => 'nginx',
                'name' => 'NGINX',
                'category' => 'webserver',
                'description' => 'High-performance HTTP server and reverse proxy',
                'is_default' => false,
                'dependencies' => null,
                'install_commands' => [
                    'apt-get install -y nginx',
                    'systemctl enable nginx',
                    'systemctl start nginx',
                ],
                'verify_commands' => ['nginx -v'],
                'install_order' => 20,
                'is_active' => true,
            ],

            // PHP
            [
                'key' => 'php-8.3',
                'name' => 'PHP 8.3',
                'category' => 'runtime',
                'description' => 'PHP 8.3 with FPM and common extensions',
                'is_default' => false,
                'dependencies' => ['curl'],
                'install_commands' => [
                    'add-apt-repository -y ppa:ondrej/php',
                    'apt-get update',
                    'apt-get install -y php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-pgsql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath php8.3-redis',
                    'systemctl enable php8.3-fpm',
                    'systemctl start php8.3-fpm',
                ],
                'verify_commands' => ['php -v'],
                'install_order' => 30,
                'is_active' => true,
            ],
            [
                'key' => 'php-8.2',
                'name' => 'PHP 8.2',
                'category' => 'runtime',
                'description' => 'PHP 8.2 with FPM and common extensions',
                'is_default' => false,
                'dependencies' => ['curl'],
                'install_commands' => [
                    'add-apt-repository -y ppa:ondrej/php',
                    'apt-get update',
                    'apt-get install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-pgsql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis',
                    'systemctl enable php8.2-fpm',
                    'systemctl start php8.2-fpm',
                ],
                'verify_commands' => ['php8.2 -v'],
                'install_order' => 30,
                'is_active' => true,
            ],

            // Composer
            [
                'key' => 'composer',
                'name' => 'Composer',
                'category' => 'runtime',
                'description' => 'PHP dependency manager',
                'is_default' => false,
                'dependencies' => ['php-8.3', 'curl'],
                'install_commands' => [
                    'curl -sS https://getcomposer.org/installer | php',
                    'mv composer.phar /usr/local/bin/composer',
                    'chmod +x /usr/local/bin/composer',
                ],
                'verify_commands' => ['composer --version'],
                'install_order' => 35,
                'is_active' => true,
            ],

            // Databases
            [
                'key' => 'mysql-8.0',
                'name' => 'MySQL 8.0',
                'category' => 'database',
                'description' => 'MySQL 8.0 Database Server',
                'is_default' => false,
                'dependencies' => null,
                'install_commands' => [
                    'apt-get install -y mysql-server-8.0',
                    'systemctl enable mysql',
                    'systemctl start mysql',
                ],
                'verify_commands' => ['mysql --version'],
                'install_order' => 40,
                'is_active' => true,
            ],
            [
                'key' => 'postgresql-16',
                'name' => 'PostgreSQL 16',
                'category' => 'database',
                'description' => 'PostgreSQL 16 Database Server',
                'is_default' => false,
                'dependencies' => null,
                'install_commands' => [
                    'sh -c \'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list\'',
                    'wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -',
                    'apt-get update',
                    'apt-get install -y postgresql-16',
                    'systemctl enable postgresql',
                    'systemctl start postgresql',
                ],
                'verify_commands' => ['psql --version'],
                'install_order' => 40,
                'is_active' => true,
            ],

            // Redis
            [
                'key' => 'redis',
                'name' => 'Redis',
                'category' => 'cache',
                'description' => 'Redis in-memory data store',
                'is_default' => false,
                'dependencies' => null,
                'install_commands' => [
                    'apt-get install -y redis-server',
                    'systemctl enable redis-server',
                    'systemctl start redis-server',
                ],
                'verify_commands' => ['redis-cli --version'],
                'install_order' => 45,
                'is_active' => true,
            ],

            // Node.js
            [
                'key' => 'nodejs-20',
                'name' => 'Node.js 20 LTS',
                'category' => 'runtime',
                'description' => 'Node.js 20 LTS with NPM',
                'is_default' => false,
                'dependencies' => ['curl'],
                'install_commands' => [
                    'curl -fsSL https://deb.nodesource.com/setup_20.x | bash -',
                    'apt-get install -y nodejs',
                ],
                'verify_commands' => ['node --version', 'npm --version'],
                'install_order' => 50,
                'is_active' => true,
            ],

            // Docker
            [
                'key' => 'docker',
                'name' => 'Docker',
                'category' => 'container',
                'description' => 'Docker container platform',
                'is_default' => false,
                'dependencies' => ['curl'],
                'install_commands' => [
                    'curl -fsSL https://get.docker.com | sh',
                    'systemctl enable docker',
                    'systemctl start docker',
                    'usermod -aG docker admin_agile',
                ],
                'verify_commands' => ['docker --version'],
                'install_order' => 60,
                'is_active' => true,
            ],

            // Supervisor
            [
                'key' => 'supervisor',
                'name' => 'Supervisor',
                'category' => 'process-manager',
                'description' => 'Process control system',
                'is_default' => false,
                'dependencies' => null,
                'install_commands' => [
                    'apt-get install -y supervisor',
                    'systemctl enable supervisor',
                    'systemctl start supervisor',
                ],
                'verify_commands' => ['supervisorctl version'],
                'install_order' => 70,
                'is_active' => true,
            ],

            // Certbot
            [
                'key' => 'certbot',
                'name' => 'Certbot',
                'category' => 'security',
                'description' => 'Let\'s Encrypt SSL certificate manager',
                'is_default' => false,
                'dependencies' => null,
                'install_commands' => [
                    'apt-get install -y certbot python3-certbot-nginx',
                ],
                'verify_commands' => ['certbot --version'],
                'install_order' => 80,
                'is_active' => true,
            ],
        ];

        foreach ($software as $item) {
            ServerSoftwareCatalog::updateOrCreate(
                ['key' => $item['key']],
                $item
            );
        }

        $this->command->info('✅ Server Software Catalog seeded successfully!');
        $this->command->info('📦 Total software packages: ' . count($software));
    }
}
