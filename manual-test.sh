#!/bin/bash

# Script de Teste Manual - Server Manager
# Este script cria dados de teste e executa comandos para você testar manualmente

echo "════════════════════════════════════════════════════════════════"
echo "   🧪 TESTE MANUAL - SERVER MANAGER"
echo "════════════════════════════════════════════════════════════════""
echo ""

# Verificar dados existentes
echo "📊 Verificando dados existentes..."
echo ""

php artisan db:show 2>/dev/null | grep -E "(Tables|Connections)" || echo "Database conectado"

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "   OPÇÕES DE TESTE MANUAL"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "1️⃣  TESTAR COMANDOS ARTISAN (Recomendado para começar)"
echo "   → Não precisa de servidor real"
echo "   → Testa a lógica das funcionalidades"
echo ""
echo "2️⃣  CRIAR DADOS DE TESTE"
echo "   → Cria um team, servidor e site de teste"
echo "   → Permite testar com dados mockados"
echo ""
echo "3️⃣  TESTAR API (CURL)"
echo "   → Precisa de autenticação"
echo "   → Testa os endpoints REST"
echo ""
echo "4️⃣  VER LOGS EM TEMPO REAL"
echo "   → Acompanha execução dos comandos"
echo ""
echo "════════════════════════════════════════════════════════════════"
echo ""

# Função para criar dados de teste
criar_dados_teste() {
    echo "📝 Criando dados de teste..."
    
    cat > /tmp/create_test_data.php << 'PHPEOF'
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Team;
use App\Models\Server;
use App\Models\Site;

DB::beginTransaction();
try {
    // Criar ou pegar usuário
    $user = User::first();
    if (!$user) {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        echo "✓ Usuário criado: {$user->email}\n";
    } else {
        echo "✓ Usando usuário existente: {$user->email}\n";
    }

    // Criar ou pegar team
    $team = Team::first();
    if (!$team) {
        $team = Team::create([
            'user_id' => $user->id,
            'name' => 'Test Team',
        ]);
        echo "✓ Team criado: {$team->name}\n";
    } else {
        echo "✓ Usando team existente: {$team->name}\n";
    }

    // Criar servidor de teste
    $server = Server::where('name', 'Test Server')->first();
    if (!$server) {
        $server = Server::create([
            'team_id' => $team->id,
            'name' => 'Test Server',
            'ip_address' => '192.168.1.100',
            'cloud_provider' => 'custom',
            'region' => 'local',
            'status' => 'active',
            'memory' => 2048,
            'cpus' => 2,
            'disk_size' => 50,
            'custom_hourly_rate' => 0.05,
        ]);
        echo "✓ Servidor criado: {$server->name} (ID: {$server->id})\n";
    } else {
        echo "✓ Usando servidor existente: {$server->name} (ID: {$server->id})\n";
    }

    // Criar site de teste
    $site = Site::where('server_id', $server->id)->first();
    if (!$site) {
        $site = Site::create([
            'server_id' => $server->id,
            'domain' => 'test.local',
            'php_version' => '8.2',
            'document_root' => '/var/www/test.local',
        ]);
        echo "✓ Site criado: {$site->domain} (ID: {$site->id})\n";
    } else {
        echo "✓ Usando site existente: {$site->domain} (ID: {$site->id})\n";
    }

    DB::commit();
    
    echo "\n";
    echo "════════════════════════════════════════════════════════════════\n";
    echo "✅ DADOS DE TESTE CRIADOS COM SUCESSO!\n";
    echo "════════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "📋 IDs para uso nos testes:\n";
    echo "   User ID:   {$user->id}\n";
    echo "   Team ID:   {$team->id}\n";
    echo "   Server ID: {$server->id}\n";
    echo "   Site ID:   {$site->id}\n";
    echo "\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Erro: {$e->getMessage()}\n";
    exit(1);
}
PHPEOF

    php /tmp/create_test_data.php
    rm /tmp/create_test_data.php
}

# Menu principal
read -p "Escolha uma opção (1-4): " opcao

case $opcao in
    1)
        echo ""
        echo "════════════════════════════════════════════════════════════════"
        echo "   🧪 TESTANDO COMANDOS ARTISAN"
        echo "════════════════════════════════════════════════════════════════"
        echo ""
        
        echo "1. Testando AI Optimize..."
        echo "   Comando: php artisan ai:optimize"
        echo ""
        php artisan ai:optimize
        echo ""
        
        echo "─────────────────────────────────────────────────────────────────"
        read -p "Pressione ENTER para próximo teste..."
        echo ""
        
        echo "2. Testando Usage Track..."
        echo "   Comando: php artisan usage:track"
        echo ""
        php artisan usage:track
        echo ""
        
        echo "─────────────────────────────────────────────────────────────────"
        read -p "Pressione ENTER para próximo teste..."
        echo ""
        
        echo "3. Testando Security Scan..."
        echo "   Comando: php artisan security:scan"
        echo ""
        php artisan security:scan
        echo ""
        
        echo "─────────────────────────────────────────────────────────────────"
        read -p "Pressione ENTER para próximo teste..."
        echo ""
        
        echo "4. Testando Database Backup..."
        echo "   Comando: php artisan databases:backup"
        echo ""
        php artisan databases:backup
        echo ""
        
        echo "════════════════════════════════════════════════════════════════"
        echo "✅ Testes de comandos concluídos!"
        echo ""
        echo "💡 Dica: Execute './manual-test.sh' novamente para outras opções"
        echo "════════════════════════════════════════════════════════════════"
        ;;
        
    2)
        criar_dados_teste
        
        echo "════════════════════════════════════════════════════════════════"
        echo "   PRÓXIMOS PASSOS"
        echo "════════════════════════════════════════════════════════════════"
        echo ""
        echo "Agora você pode:"
        echo ""
        echo "1. Testar comandos com servidor específico:"
        echo "   php artisan ai:optimize --server_id=1"
        echo "   php artisan security:scan --server_id=1"
        echo ""
        echo "2. Testar via API (precisa de token):"
        echo "   curl http://localhost:8000/api/servers/1/ai/predict-load"
        echo ""
        echo "3. Ver os componentes Livewire funcionando"
        echo ""
        echo "════════════════════════════════════════════════════════════════"
        ;;
        
    3)
        echo ""
        echo "════════════════════════════════════════════════════════════════"
        echo "   📡 TESTE DE API"
        echo "════════════════════════════════════════════════════════════════"
        echo ""
        echo "Para testar a API, você precisa:"
        echo ""
        echo "1. Criar um token de autenticação (Sanctum)"
        echo "2. Usar o token nas requisições"
        echo ""
        echo "Consulte API_TESTING.md para exemplos completos!"
        echo ""
        echo "Exemplo rápido:"
        echo '  export API_TOKEN="seu-token"'
        echo '  curl -X GET http://localhost:8000/api/servers/1/firewall/rules \'
        echo '    -H "Authorization: Bearer $API_TOKEN"'
        echo ""
        echo "════════════════════════════════════════════════════════════════"
        
        read -p "Abrir API_TESTING.md? (y/n): " abrir
        if [ "$abrir" = "y" ]; then
            if command -v code &> /dev/null; then
                code API_TESTING.md
            elif command -v nano &> /dev/null; then
                nano API_TESTING.md
            else
                cat API_TESTING.md | less
            fi
        fi
        ;;
        
    4)
        echo ""
        echo "════════════════════════════════════════════════════════════════"
        echo "   📋 LOGS EM TEMPO REAL"
        echo "════════════════════════════════════════════════════════════════"
        echo ""
        echo "Abrindo logs... (Ctrl+C para sair)"
        echo ""
        sleep 2
        tail -f storage/logs/laravel.log
        ;;
        
    *)
        echo "Opção inválida!"
        exit 1
        ;;
esac
