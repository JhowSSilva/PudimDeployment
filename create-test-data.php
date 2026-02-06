<?php

// Script para criar dados de teste
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Team;
use App\Models\Server;
use App\Models\Site;
use App\Models\Database;

DB::beginTransaction();

try {
    echo "🔧 Criando dados de teste...\n\n";
    
    // Criar ou pegar usuário
    $user = User::firstOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]
    );
    echo "✓ Usuário: {$user->email} (ID: {$user->id})\n";
    
    // Criar ou pegar team
    $team = Team::firstOrCreate(
        ['name' => 'Test Team'],
        ['user_id' => $user->id]
    );
    echo "✓ Team: {$team->name} (ID: {$team->id})\n";
    
    // Criar servidor de teste
    $server = Server::firstOrCreate(
        ['name' => 'Test Server'],
        [
            'user_id' => $user->id,
            'team_id' => $team->id,
            'ip_address' => '192.168.1.100',
            'cloud_provider' => 'custom',
            'region' => 'local',
            'status' => 'online',
            'memory' => 2048,
            'cpus' => 2,
            'disk_size' => 50,
            'custom_hourly_rate' => 0.05,
            'firewall_enabled' => true,
            'fail2ban_enabled' => true,
        ]
    );
    echo "✓ Servidor: {$server->name} (ID: {$server->id})\n";
    
    // Criar site de teste
    $site = Site::firstOrCreate(
        ['domain' => 'test.local'],
        [
            'name' => 'Test Site',
            'server_id' => $server->id,
            'php_version' => '8.2',
            'document_root' => '/var/www/test.local',
            'auto_migrate' => true,
            'maintenance_mode' => false,
            'framework' => 'laravel',
        ]
    );
    echo "✓ Site: {$site->domain} (ID: {$site->id})\n";
    
    // Criar database de teste
    $database = Database::firstOrCreate(
        ['name' => 'test_database'],
        [
            'server_id' => $server->id,
            'type' => 'mysql',
        ]
    );
    echo "✓ Database: {$database->name} (ID: {$database->id})\n";
    
    // Criar algumas métricas de uso (para servers)
    for ($i = 0; $i < 10; $i++) {
        DB::table('usage_metrics')->insert([
            'server_id' => $server->id,
            'cpu_usage' => rand(20, 80) / 1.0,
            'memory_used_mb' => rand(500, 1500),
            'disk_used_gb' => rand(10, 40),
            'bandwidth_in' => rand(100000, 1000000),
            'bandwidth_out' => rand(100000, 1000000),
            'recorded_at' => now()->subHours(10 - $i),
            'created_at' => now()->subHours(10 - $i),
            'updated_at' => now()->subHours(10 - $i),
        ]);
    }
    echo "✓ Criadas 10 métricas de uso\n";
    
    // Criar métricas de performance (para sites)
    for ($i = 0; $i < 5; $i++) {
        DB::table('performance_metrics')->insert([
            'site_id' => $site->id,
            'type' => 'response_time',
            'metrics' => json_encode([
                'response_time' => rand(50, 500),
                'requests' => rand(100, 1000),
                'cpu_usage' => rand(20, 80),
            ]),
            'created_at' => now()->subHours(5 - $i),
            'updated_at' => now()->subHours(5 - $i),
        ]);
    }
    echo "✓ Criadas 5 métricas de performance\n";
    
    // Criar métrica de billing
    DB::table('usage_metrics')->insert([
        'server_id' => $server->id,
        'cpu_usage' => 65.5,
        'memory_used_mb' => 1200,
        'disk_used_gb' => 25,
        'bandwidth_in' => 500000,
        'bandwidth_out' => 300000,
        'recorded_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Criada métrica de billing atual\n";
    
    DB::commit();
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "✅ DADOS DE TESTE CRIADOS COM SUCESSO!\n";
    echo str_repeat("=", 70) . "\n\n";
    
    echo "📋 IDs para uso nos testes:\n\n";
    echo "  User ID:     {$user->id}\n";
    echo "  Team ID:     {$team->id}\n";
    echo "  Server ID:   {$server->id}\n";
    echo "  Site ID:     {$site->id}\n";
    echo "  Database ID: {$database->id}\n";
    echo "\n";
    
    echo "🧪 COMANDOS PARA TESTAR:\n\n";
    echo "  # Testar AI com servidor específico:\n";
    echo "  php artisan ai:optimize --server_id={$server->id}\n\n";
    
    echo "  # Testar security scan:\n";
    echo "  php artisan security:scan --server_id={$server->id}\n\n";
    
    echo "  # Testar usage tracking:\n";
    echo "  php artisan usage:track\n\n";
    
    echo "  # Testar invoice generation:\n";
    echo "  php artisan invoices:generate --team_id={$team->id}\n\n";
    
    echo "  # Testar database backup:\n";
    echo "  php artisan databases:backup --database_id={$database->id}\n\n";
    
    echo str_repeat("=", 70) . "\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERRO: {$e->getMessage()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
