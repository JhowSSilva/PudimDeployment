#!/bin/bash
# Quick CLI Reference Commands

cat << 'EOF'
╔══════════════════════════════════════════════════════════════════════════════╗
║                      CLI REFERENCE COMMANDS                                  ║
╚══════════════════════════════════════════════════════════════════════════════╝

📋 ARTISAN COMMANDS
═══════════════════════════════════════════════════════════════════════════════

🤖 AI & Optimization:
  php artisan ai:optimize                    # Otimizar todos os servidores
  php artisan ai:optimize --server_id=1      # Otimizar servidor específico

🔒 Security:
  php artisan security:scan                  # Scan de segurança em todos
  php artisan security:scan --server_id=1    # Scan em servidor específico

💰 Billing:
  php artisan usage:track                    # Rastrear uso de todos
  php artisan invoices:generate              # Gerar invoices para todos
  php artisan invoices:generate --team_id=1  # Gerar para time específico

💾 Database:
  php artisan databases:backup               # Backup de todos os databases
  php artisan databases:backup --database_id=1  # Backup específico

═══════════════════════════════════════════════════════════════════════════════
🔧 MAINTENANCE COMMANDS
═══════════════════════════════════════════════════════════════════════════════

🧹 Cache:
  php artisan config:cache                   # Cache de configuração
  php artisan route:cache                    # Cache de rotas
  php artisan view:cache                     # Cache de views
  php artisan optimize                       # Otimizar tudo
  
  php artisan config:clear                   # Limpar cache de config
  php artisan route:clear                    # Limpar cache de rotas
  php artisan view:clear                     # Limpar cache de views
  php artisan optimize:clear                 # Limpar todos os caches

📊 Database:
  php artisan migrate                        # Executar migrations
  php artisan migrate:status                 # Status das migrations
  php artisan migrate:rollback               # Reverter última migration
  php artisan migrate:fresh                  # Recriar database (CUIDADO!)

🔄 Queue:
  php artisan queue:work                     # Processar jobs da queue
  php artisan queue:work --verbose           # Com output detalhado
  php artisan queue:listen                   # Escutar por novos jobs
  php artisan queue:restart                  # Reiniciar workers
  php artisan queue:failed                   # Listar jobs falhados
  php artisan queue:retry all                # Retentar jobs falhados

⏰ Schedule:
  php artisan schedule:list                  # Listar agendamentos
  php artisan schedule:run                   # Executar agendamentos agora
  php artisan schedule:work                  # Rodar scheduler (development)

═══════════════════════════════════════════════════════════════════════════════
📡 API TESTING (CURL)
═══════════════════════════════════════════════════════════════════════════════

🔐 Setup:
  export API_TOKEN="seu-token-aqui"
  export BASE_URL="http://localhost:8000/api"

🔥 Firewall:
  # Listar regras
  curl -X GET "$BASE_URL/servers/1/firewall/rules" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Adicionar regra
  curl -X POST "$BASE_URL/servers/1/firewall/rules" \
    -H "Authorization: Bearer $API_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"port":"3306","source":"0.0.0.0/0","protocol":"tcp","action":"allow"}'
  
  # Bloquear IP
  curl -X POST "$BASE_URL/servers/1/firewall/block-ip" \
    -H "Authorization: Bearer $API_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"ip_address":"192.168.1.100","reason":"Suspicious activity"}'
  
  # Scan rootkit
  curl -X POST "$BASE_URL/servers/1/firewall/scan/rootkit" \
    -H "Authorization: Bearer $API_TOKEN"

📊 Performance:
  # Métricas em tempo real
  curl -X GET "$BASE_URL/sites/1/performance/metrics" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Análise de performance
  curl -X POST "$BASE_URL/sites/1/performance/analyze" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Slow queries
  curl -X GET "$BASE_URL/sites/1/performance/slow-queries" \
    -H "Authorization: Bearer $API_TOKEN"

🤖 AI:
  # Predição de carga
  curl -X POST "$BASE_URL/servers/1/ai/predict-load" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Otimizar recursos
  curl -X POST "$BASE_URL/servers/1/ai/optimize" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Detectar ameaças
  curl -X POST "$BASE_URL/servers/1/ai/detect-threats" \
    -H "Authorization: Bearer $API_TOKEN"

💾 Cache:
  # Habilitar OPcache
  curl -X POST "$BASE_URL/servers/1/cache/opcache/enable" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Configurar Redis
  curl -X POST "$BASE_URL/servers/1/cache/redis/configure" \
    -H "Authorization: Bearer $API_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"max_memory":"256mb","eviction_policy":"allkeys-lru"}'
  
  # Limpar todos os caches
  curl -X POST "$BASE_URL/servers/1/cache/clear" \
    -H "Authorization: Bearer $API_TOKEN"

💰 Billing:
  # Calcular custos
  curl -X GET "$BASE_URL/billing/servers/1/costs" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Listar invoices
  curl -X GET "$BASE_URL/billing/invoices" \
    -H "Authorization: Bearer $API_TOKEN"
  
  # Forecast de custos
  curl -X GET "$BASE_URL/billing/forecast" \
    -H "Authorization: Bearer $API_TOKEN"

═══════════════════════════════════════════════════════════════════════════════
🐛 DEBUGGING
═══════════════════════════════════════════════════════════════════════════════

📋 Logs:
  tail -f storage/logs/laravel.log           # Ver logs em tempo real
  tail -n 100 storage/logs/laravel.log       # Ver últimas 100 linhas
  grep ERROR storage/logs/laravel.log        # Buscar erros
  grep -A 5 "Exception" storage/logs/laravel.log  # Ver exceções com contexto

🔍 Informações:
  php artisan about                          # Info sobre a aplicação
  php artisan list                           # Listar todos os comandos
  php artisan route:list                     # Listar todas as rotas
  php artisan route:list --path=api          # Listar apenas rotas da API
  
  php artisan tinker                         # Console interativo
  # Dentro do tinker:
  # \App\Models\Server::count()              # Contar servidores
  # \App\Models\Team::all()                  # Listar times
  # DB::table('performance_metrics')->count() # Contar métricas

🧪 Testing:
  ./test-features.sh                         # Script de teste completo
  php artisan test                           # Rodar testes PHPUnit
  php artisan test --filter TestName         # Teste específico

═══════════════════════════════════════════════════════════════════════════════
🚀 DEPLOYMENT
═══════════════════════════════════════════════════════════════════════════════

📦 Preparation:
  composer install --no-dev --optimize-autoloader
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan migrate --force

🔄 Updates:
  git pull origin main
  composer install --no-dev
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan queue:restart

═══════════════════════════════════════════════════════════════════════════════
💡 DICAS ÚTEIS
═══════════════════════════════════════════════════════════════════════════════

🎯 Desenvolvimento:
  php artisan serve                          # Servidor local (porta 8000)
  php artisan serve --port=8080              # Porta customizada
  php artisan queue:work --verbose           # Queue worker com output
  
📊 Monitoramento:
  watch -n 1 php artisan queue:failed        # Monitorar jobs falhados
  watch -n 5 'tail -n 20 storage/logs/laravel.log'  # Monitorar logs

🔧 Produtividade:
  alias art='php artisan'                    # Atalho para artisan
  alias pf='php artisan test --filter'       # Atalho para testes
  alias pa='php artisan'                     # Outro atalho comum

═══════════════════════════════════════════════════════════════════════════════

Para mais informações, consulte:
  • START_TESTING.md    - Como começar a testar
  • API_TESTING.md      - Exemplos completos de API
  • SETUP_GUIDE.md      - Guia de configuração completo

═══════════════════════════════════════════════════════════════════════════════
EOF
