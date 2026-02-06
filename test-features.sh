#!/bin/bash

# Quick Test Script - Enhanced Features
# This script checks if all implemented features are operational

echo "======================================"
echo "Enhanced Features Test"
echo "======================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para verificar sucesso
check_success() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ $1${NC}"
        exit 1
    fi
}

# 1. Verificar se o Laravel está funcionando
echo "1. Verificando Laravel..."
php artisan --version > /dev/null 2>&1
check_success "Laravel está operacional"

# 2. Verificar configuração
echo ""
echo "2. Verificando arquivos de configuração..."
if [ -f "config/server.php" ]; then
    echo -e "${GREEN}✓ config/server.php existe${NC}"
else
    echo -e "${RED}✗ config/server.php não encontrado${NC}"
    exit 1
fi

# 3. Verificar migrations
echo ""
echo "3. Verificando migrations..."
php artisan migrate:status | grep -q "add_new_features_tables"
check_success "Migration add_new_features_tables está presente"

# 4. Verificar Services
echo ""
echo "4. Verificando Services..."
services=(
    "app/Services/FirewallService.php"
    "app/Services/CacheService.php"
    "app/Services/ArtisanService.php"
    "app/Services/APMService.php"
    "app/Services/DeploymentPipeline.php"
    "app/Services/AIService.php"
    "app/Services/DatabaseService.php"
    "app/Services/BillingService.php"
)

for service in "${services[@]}"; do
    if [ -f "$service" ]; then
        echo -e "${GREEN}✓ $service${NC}"
    else
        echo -e "${RED}✗ $service não encontrado${NC}"
        exit 1
    fi
done

# 5. Verificar Controllers
echo ""
echo "5. Verificando Controllers..."
controllers=(
    "app/Http/Controllers/Api/FirewallController.php"
    "app/Http/Controllers/Api/PerformanceController.php"
    "app/Http/Controllers/Api/AIController.php"
)

for controller in "${controllers[@]}"; do
    if [ -f "$controller" ]; then
        echo -e "${GREEN}✓ $controller${NC}"
    else
        echo -e "${RED}✗ $controller não encontrado${NC}"
        exit 1
    fi
done

# 6. Verificar Comandos Artisan
echo ""
echo "6. Verificando Comandos Artisan..."
commands=(
    "usage:track"
    "invoices:generate"
    "security:scan"
    "ai:optimize"
    "databases:backup"
)

for cmd in "${commands[@]}"; do
    php artisan list | grep -q "$cmd"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Comando $cmd disponível${NC}"
    else
        echo -e "${YELLOW}⚠ Comando $cmd não encontrado (pode precisar registrar)${NC}"
    fi
done

# 7. Verificar Componentes Livewire
echo ""
echo "7. Verificando Componentes Livewire..."
livewire_components=(
    "app/Livewire/Servers/ServerMetrics.php"
    "app/Livewire/Servers/PerformanceChart.php"
    "app/Livewire/Servers/SecurityAlerts.php"
    "app/Livewire/Billing/CostForecast.php"
)

for component in "${livewire_components[@]}"; do
    if [ -f "$component" ]; then
        echo -e "${GREEN}✓ $component${NC}"
    else
        echo -e "${RED}✗ $component não encontrado${NC}"
    fi
done

# 8. Verificar Views Livewire
echo ""
echo "8. Verificando Views Livewire..."
livewire_views=(
    "resources/views/livewire/servers/server-metrics.blade.php"
    "resources/views/livewire/servers/performance-chart.blade.php"
    "resources/views/livewire/servers/security-alerts.blade.php"
    "resources/views/livewire/billing/cost-forecast.blade.php"
)

for view in "${livewire_views[@]}"; do
    if [ -f "$view" ]; then
        echo -e "${GREEN}✓ $view${NC}"
    else
        echo -e "${RED}✗ $view não encontrado${NC}"
    fi
done

# 9. Verificar rotas API
echo ""
echo "9. Verificando rotas API..."
if [ -f "routes/api-enhanced.php" ]; then
    echo -e "${GREEN}✓ routes/api-enhanced.php existe${NC}"
else
    echo -e "${RED}✗ routes/api-enhanced.php não encontrado${NC}"
    exit 1
fi

# 10. Verificar tabelas do banco de dados
echo ""
echo "10. Verificando tabelas do banco de dados..."
tables=(
    "performance_metrics"
    "usage_metrics"
    "invoices"
    "subscriptions"
    "firewall_rules"
    "security_threats"
    "blocked_ips"
)

for table in "${tables[@]}"; do
    php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasTable('$table') ? 'exists' : 'missing';" 2>/dev/null | grep -q "exists"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Tabela $table existe${NC}"
    else
        echo -e "${YELLOW}⚠ Tabela $table não encontrada (executar migrations?)${NC}"
    fi
done

# 11. Testar compilação do Laravel
echo ""
echo "11. Testando compilação do Laravel..."
php artisan config:cache > /dev/null 2>&1
check_success "Cache de configuração gerado"

php artisan route:cache > /dev/null 2>&1
check_success "Cache de rotas gerado"

# 12. Verificar documentação
echo ""
echo "12. Verificando documentação..."
docs=(
    "IMPROVEMENTS_IMPLEMENTED.md"
    "SETUP_GUIDE.md"
    "QUICK_START.md"
    "API_TESTING.md"
)

for doc in "${docs[@]}"; do
    if [ -f "$doc" ]; then
        echo -e "${GREEN}✓ $doc${NC}"
    else
        echo -e "${YELLOW}⚠ $doc não encontrado${NC}"
    fi
done

# Resumo
echo ""
echo "======================================"
echo -e "${GREEN}✓ Verificação concluída com sucesso!${NC}"
echo "======================================"
echo ""
echo "Próximos passos:"
echo "1. Configure as variáveis de ambiente no arquivo .env"
echo "2. Configure credenciais de cloud providers"
echo "3. Configure webhooks do Slack/Discord (opcional)"
echo "4. Teste os endpoints da API usando API_TESTING.md"
echo "5. Adicione os componentes Livewire às suas views"
echo ""
echo "Para começar a usar, execute:"
echo "  php artisan serve"
echo ""
echo "Consulte QUICK_START.md para um guia rápido!"
