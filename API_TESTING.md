# API Testing Guide - Enhanced Features

Este guia fornece exemplos de como testar todos os novos endpoints da API.

## Setup

Primeiro, obtenha um token de autenticação:

```bash
# Login e obter token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "seu-email@example.com",
    "password": "sua-senha"
  }'

# Use o token retornado nas próximas requisições
export API_TOKEN="seu-token-aqui"
export BASE_URL="http://localhost:8000/api"
```

## 1. Firewall Management

### Listar regras do firewall
```bash
curl -X GET "$BASE_URL/servers/1/firewall/rules" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Adicionar regra de firewall
```bash
curl -X POST "$BASE_URL/servers/1/firewall/rules" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "port": "3306",
    "source": "192.168.1.0/24",
    "protocol": "tcp",
    "action": "allow"
  }'
```

### Bloquear IP
```bash
curl -X POST "$BASE_URL/servers/1/firewall/block-ip" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "ip_address": "192.168.1.100",
    "reason": "Multiple failed login attempts"
  }'
```

### Desbloquear IP
```bash
curl -X POST "$BASE_URL/servers/1/firewall/unblock-ip" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "ip_address": "192.168.1.100"
  }'
```

### Habilitar Fail2ban
```bash
curl -X POST "$BASE_URL/servers/1/firewall/fail2ban/enable" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Listar IPs banidos
```bash
curl -X GET "$BASE_URL/servers/1/firewall/fail2ban/banned" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Scan de segurança (rootkit)
```bash
curl -X POST "$BASE_URL/servers/1/firewall/scan/rootkit" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Scan de malware
```bash
curl -X POST "$BASE_URL/servers/1/firewall/scan/malware" \
  -H "Authorization: Bearer $API_TOKEN"
```

## 2. Performance Monitoring (APM)

### Métricas em tempo real
```bash
curl -X GET "$BASE_URL/sites/1/performance/metrics" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Análise de performance
```bash
curl -X POST "$BASE_URL/sites/1/performance/analyze" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Slow queries
```bash
curl -X GET "$BASE_URL/sites/1/performance/slow-queries" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Detecção N+1
```bash
curl -X GET "$BASE_URL/sites/1/performance/n-plus-one" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Health check
```bash
curl -X POST "$BASE_URL/sites/1/performance/health-check" \
  -H "Authorization: Bearer $API_TOKEN"
```

## 3. AI Features

### Predição de carga do servidor
```bash
curl -X POST "$BASE_URL/servers/1/ai/predict-load" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Otimizar recursos
```bash
curl -X POST "$BASE_URL/servers/1/ai/optimize" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Detectar ameaças de segurança
```bash
curl -X POST "$BASE_URL/servers/1/ai/detect-threats" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Recomendar upgrades
```bash
curl -X POST "$BASE_URL/servers/1/ai/recommend-upgrade" \
  -H "Authorization: Bearer $API_TOKEN"
```

## 4. Cache Management

### Habilitar OPcache
```bash
curl -X POST "$BASE_URL/servers/1/cache/opcache/enable" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Configurar Redis
```bash
curl -X POST "$BASE_URL/servers/1/cache/redis/configure" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "max_memory": "256mb",
    "eviction_policy": "allkeys-lru"
  }'
```

### Limpar cache
```bash
curl -X POST "$BASE_URL/servers/1/cache/clear" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Estatísticas do cache
```bash
curl -X GET "$BASE_URL/servers/1/cache/stats" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Habilitar compressão Brotli
```bash
curl -X POST "$BASE_URL/servers/1/cache/brotli/enable" \
  -H "Authorization: Bearer $API_TOKEN"
```

## 5. Artisan Commands

### Executar comando customizado
```bash
curl -X POST "$BASE_URL/sites/1/artisan/run" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "command": "cache:clear"
  }'
```

### Executar migrations
```bash
curl -X POST "$BASE_URL/sites/1/artisan/migrate" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Limpar cache
```bash
curl -X POST "$BASE_URL/sites/1/artisan/cache/clear" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Otimizar aplicação
```bash
curl -X POST "$BASE_URL/sites/1/artisan/optimize" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Agendar cron job
```bash
curl -X POST "$BASE_URL/sites/1/artisan/schedule" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "command": "backup:run",
    "schedule": "0 2 * * *"
  }'
```

### Reiniciar queue workers
```bash
curl -X POST "$BASE_URL/sites/1/artisan/queue/restart" \
  -H "Authorization: Bearer $API_TOKEN"
```

## 6. Billing

### Calcular custos do servidor
```bash
curl -X GET "$BASE_URL/billing/servers/1/costs" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Gerar invoice
```bash
curl -X POST "$BASE_URL/billing/invoices" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Listar invoices
```bash
curl -X GET "$BASE_URL/billing/invoices" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Obter invoice específico
```bash
curl -X GET "$BASE_URL/billing/invoices/1" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Previsão de custos
```bash
curl -X GET "$BASE_URL/billing/forecast" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Rastrear uso
```bash
curl -X POST "$BASE_URL/billing/usage/track" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "server_id": 1
  }'
```

## 7. Database Management

### Criar backup de banco de dados
```bash
curl -X POST "$BASE_URL/databases/1/backup" \
  -H "Authorization: Bearer $API_TOKEN"
```

### Restaurar backup
```bash
curl -X POST "$BASE_URL/databases/1/restore" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "backup_file": "database_2026-02-05_02-00-00.sql.gz"
  }'
```

### Configurar backups automáticos
```bash
curl -X POST "$BASE_URL/databases/1/auto-backup" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "schedule": "0 2 * * *",
    "retention_days": 30
  }'
```

### Configurar replicação
```bash
curl -X POST "$BASE_URL/databases/1/replication" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "replica_host": "replica.example.com",
    "replica_port": 3306
  }'
```

## Usando os Comandos Artisan

Você também pode testar as funcionalidades via linha de comando:

```bash
# Rastrear uso para billing
php artisan usage:track

# Gerar invoices
php artisan invoices:generate

# Gerar invoice para um time específico
php artisan invoices:generate --team_id=1

# Executar scan de segurança
php artisan security:scan

# Scan em servidor específico
php artisan security:scan --server_id=1

# Otimização com AI
php artisan ai:optimize

# Otimização de servidor específico
php artisan ai:optimize --server_id=1

# Backup de bancos de dados
php artisan databases:backup

# Backup de banco específico
php artisan databases:backup --database_id=1
```

## Testando os Componentes Livewire

Os componentes Livewire podem ser incluídos nas suas views:

```blade
{{-- Server Metrics --}}
<livewire:servers.server-metrics :server="$server" />

{{-- Performance Chart --}}
<livewire:servers.performance-chart :server="$server" />

{{-- Security Alerts --}}
<livewire:servers.security-alerts :server="$server" />

{{-- Cost Forecast --}}
<livewire:billing.cost-forecast :team="$team" />
```

## Testes Automatizados

Para rodar os testes automatizados (quando criados):

```bash
# Rodar todos os testes
php artisan test

# Rodar testes de feature
php artisan test --testsuite=Feature

# Rodar testes específicos
php artisan test --filter FirewallServiceTest
```

## Monitoramento

Para verificar o funcionamento do sistema:

```bash
# Ver logs
tail -f storage/logs/laravel.log

# Ver queue workers
php artisan queue:work --verbose

# Ver agendamentos
php artisan schedule:list

# Rodar scheduler manualmente
php artisan schedule:run
```

## Troubleshooting

Se algo não funcionar:

1. Verifique os logs: `tail -f storage/logs/laravel.log`
2. Verifique se as migrations rodaram: `php artisan migrate:status`
3. Limpe o cache: `php artisan config:clear && php artisan cache:clear`
4. Verifique as permissões SSH nos servidores
5. Confirme que as credenciais de cloud providers estão configuradas
