<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Server;
use App\Models\Site;
use App\Models\Deployment;
use App\Models\ServerMetric;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário de teste
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        // Criar servidores de teste
        $server1 = Server::create([
            'user_id' => $user->id,
            'name' => 'Servidor Web Produção',
            'ip_address' => '192.168.1.10',
            'ssh_port' => 22,
            'ssh_user' => 'ubuntu',
            'auth_type' => 'password',
            'ssh_password' => 'demo123',
            'os_type' => 'Ubuntu',
            'os_version' => '22.04 LTS',
            'status' => 'online',
            'last_ping_at' => now(),
        ]);

        $server2 = Server::create([
            'user_id' => $user->id,
            'name' => 'Servidor API Staging',
            'ip_address' => '192.168.1.20',
            'ssh_port' => 22,
            'ssh_user' => 'root',
            'auth_type' => 'key',
            'ssh_key' => '-----BEGIN RSA PRIVATE KEY-----\nDEMO\n-----END RSA PRIVATE KEY-----',
            'os_type' => 'Debian',
            'os_version' => '12',
            'status' => 'online',
            'last_ping_at' => now(),
        ]);

        $server3 = Server::create([
            'user_id' => $user->id,
            'name' => 'Servidor DB Backup',
            'ip_address' => '192.168.1.30',
            'ssh_port' => 2222,
            'ssh_user' => 'admin',
            'auth_type' => 'password',
            'ssh_password' => 'backup456',
            'os_type' => 'Ubuntu',
            'os_version' => '24.04 LTS',
            'status' => 'offline',
            'last_ping_at' => now()->subHours(2),
        ]);

        // Criar sites de teste
        $site1 = Site::create([
            'server_id' => $server1->id,
            'name' => 'Aplicação Principal',
            'domain' => 'app.exemplo.com.br',
            'git_repository' => 'https://github.com/example/app.git',
            'git_branch' => 'main',
            'git_token' => 'ghp_demo_token_12345',
            'document_root' => '/public',
            'php_version' => '8.3',
            'status' => 'active',
        ]);

        $site2 = Site::create([
            'server_id' => $server2->id,
            'name' => 'API Staging',
            'domain' => 'api.staging.exemplo.com',
            'git_repository' => 'git@github.com:example/api.git',
            'git_branch' => 'develop',
            'document_root' => '/public',
            'php_version' => '8.2',
            'status' => 'active',
        ]);

        // Criar métricas simuladas dos últimos 60 minutos
        foreach ([$server1, $server2] as $server) {
            for ($i = 60; $i >= 0; $i--) {
                $time = now()->subMinutes($i);
                
                // Simular variação de métricas
                $cpuUsage = rand(10, 85) + sin($i / 10) * 10;
                $memoryUsed = rand(2000, 6000);
                $memoryTotal = 8192;
                $diskUsed = rand(20, 50);
                $diskTotal = 100;
                
                ServerMetric::create([
                    'server_id' => $server->id,
                    'cpu_usage' => min(100, max(0, $cpuUsage)),
                    'memory_used_mb' => $memoryUsed,
                    'memory_total_mb' => $memoryTotal,
                    'disk_used_gb' => $diskUsed,
                    'disk_total_gb' => $diskTotal,
                    'uptime_seconds' => 86400 * 30 + $i * 60,
                    'processes' => json_encode([
                        'nginx' => 'active',
                        'php-fpm' => 'active',
                        'mysql' => 'active',
                    ]),
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }

        // Criar deployments de teste
        $deployments = [
            [
                'site_id' => $site1->id,
                'user_id' => $user->id,
                'status' => 'success',
                'commit_hash' => 'a1b2c3d4e5f6',
                'commit_message' => 'feat: Adicionar autenticação de dois fatores',
                'duration_seconds' => 45,
                'started_at' => now()->subHours(2),
                'finished_at' => now()->subHours(2)->addSeconds(45),
            ],
            [
                'site_id' => $site1->id,
                'user_id' => $user->id,
                'status' => 'success',
                'commit_hash' => 'b2c3d4e5f6a1',
                'commit_message' => 'fix: Corrigir validação de formulário',
                'duration_seconds' => 32,
                'started_at' => now()->subHours(5),
                'finished_at' => now()->subHours(5)->addSeconds(32),
            ],
            [
                'site_id' => $site2->id,
                'user_id' => $user->id,
                'status' => 'failed',
                'commit_hash' => 'c3d4e5f6a1b2',
                'commit_message' => 'refactor: Reestruturar módulo de pagamentos',
                'duration_seconds' => 15,
                'started_at' => now()->subHours(8),
                'finished_at' => now()->subHours(8)->addSeconds(15),
            ],
            [
                'site_id' => $site1->id,
                'user_id' => $user->id,
                'status' => 'success',
                'commit_hash' => 'd4e5f6a1b2c3',
                'commit_message' => 'chore: Atualizar dependências',
                'duration_seconds' => 67,
                'started_at' => now()->subHours(12),
                'finished_at' => now()->subHours(12)->addSeconds(67),
            ],
            [
                'site_id' => $site2->id,
                'user_id' => $user->id,
                'status' => 'running',
                'commit_hash' => 'e5f6a1b2c3d4',
                'commit_message' => 'feat: Implementar cache de API',
                'started_at' => now()->subMinutes(5),
            ],
        ];

        foreach ($deployments as $deployment) {
            Deployment::create($deployment);
        }

        $this->command->info('✅ Dados de demonstração criados com sucesso!');
        $this->command->info('📧 Email: admin@example.com');
        $this->command->info('🔑 Senha: password');
        $this->command->info('🌐 Acesse: http://127.0.0.1:8000/dashboard');
    }
}
