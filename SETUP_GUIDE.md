# 🚀 Setup Guide - Enhanced Features

Este guia explica como configurar e usar as novas funcionalidades implementadas.

## 📋 Índice

1. [Pré-requisitos](#pré-requisitos)
2. [Instalação](#instalação)
3. [Configuração](#configuração)
4. [Uso das Funcionalidades](#uso-das-funcionalidades)
5. [API Endpoints](#api-endpoints)
6. [Exemplos](#exemplos)
7. [Troubleshooting](#troubleshooting)

---

## 🔧 Pré-requisitos

- PHP 8.2+
- MySQL 8.0+ ou PostgreSQL 13+
- Redis (opcional, mas recomendado)
- Composer
- Node.js & NPM

---

## 📦 Instalação

### 1. Executar as Migrations

```bash
php artisan migrate
```

Isso criará as seguintes tabelas:
- `performance_metrics`
- `usage_metrics`
- `invoices`
- `subscriptions`
- `firewall_rules`
- `security_threats`
- `blocked_ips`

### 2. Publicar Configurações

```bash
php artisan vendor:publish --tag=server-config
```

### 3. Configurar Variáveis de Ambiente

Adicione ao seu `.env`:

```env
# Notifications
SLACK_WEBHOOK_URL=your-slack-webhook-url
DISCORD_WEBHOOK_URL=your-discord-webhook-url

# Billing
BILLING_CURRENCY=USD
BILLING_TAX_RATE=0.0

# AI Features
AI_PREDICTIONS_ENABLED=true
AI_AUTO_OPTIMIZE=false

# Security
SECURITY_AUTO_BLOCK=false
SECURITY_SCAN_INTERVAL=3600
```

### 4. Registrar Rotas da API

Adicione ao `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    apiPrefix: 'api',
    then: function () {
        // Load enhanced API routes
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api-enhanced.php'));
    }
)
```

### 5. Configurar Jobs de Background

Adicione ao crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Iniciar Queue Workers

```bash
php artisan queue:work --queue=high,default,low
```

---

## ⚙️ Configuração

### Firewall

Editar `config/server.php`:

```php
'firewall' => [
    'default_rules' => [
        ['port' => 22, 'protocol' => 'tcp', 'comment' => 'SSH'],
        ['port' => 80, 'protocol' => 'tcp', 'comment' => 'HTTP'],
        ['port' => 443, 'protocol' => 'tcp', 'comment' => 'HTTPS'],
        ['port' => 3306, 'protocol' => 'tcp', 'comment' => 'MySQL'], // se necessário
    ],
    'fail2ban' => [
        'enabled' => true,
        'max_retry' => 5,
        'ban_time' => 3600,
    ],
],
```

### Cache

```php
'cache' => [
    'opcache' => [
        'memory_consumption' => 256,
        'max_accelerated_files' => 10000,
    ],
    'redis' => [
        'default_max_memory' => '512mb',
        'default_policy' => 'allkeys-lru',
    ],
],
```

### Billing

```php
'billing' => [
    'currency' => 'USD',
    'tax_rate' => 0.0, // Configure based on your region
    'invoice' => [
        'auto_generate' => true,
        'generation_day' => 1,
        'payment_due_days' => 14,
    ],
],
```

---

## 🎯 Uso das Funcionalidades

### 1. Firewall Management

#### Configurar UFW
```php
use App\Services\FirewallService;

$firewall = new FirewallService($server);

// Configure UFW with default rules
$firewall->configureUFW();

// Add custom rule
$firewall->addRule(8080, 'tcp', null, 'App Port');

// Block IP
$firewall->blockIP('192.168.1.100', 'Suspicious activity');

// Enable Fail2ban
$firewall->enableFail2Ban();
```

#### Via API
```bash
# Configure firewall
curl -X POST https://your-domain.com/api/servers/{server}/firewall/configure \
  -H "Authorization: Bearer YOUR_TOKEN"

# Add rule
curl -X POST https://your-domain.com/api/servers/{server}/firewall/rules \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"port": 8080, "protocol": "tcp", "comment": "Custom Port"}'

# Block IP
curl -X POST https://your-domain.com/api/servers/{server}/firewall/block-ip \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"ip": "192.168.1.100", "comment": "Suspicious"}'
```

---

### 2. Cache Management

#### Habilitar OPcache
```php
use App\Services\CacheService;

$cache = new CacheService($server);

// Enable OPcache
$cache->enableOPCache();

// Configure Redis
$cache->configureRedis([
    'max_memory' => '512mb',
    'password' => 'your-password'
]);

// Clear all caches
$cache->clearAllCaches($site);
```

#### Via API
```bash
# Enable OPcache
curl -X POST https://your-domain.com/api/servers/{server}/cache/opcache/enable \
  -H "Authorization: Bearer YOUR_TOKEN"

# Configure Redis
curl -X POST https://your-domain.com/api/servers/{server}/cache/redis/configure \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"max_memory": "512mb", "password": "secret"}'
```

---

### 3. Performance Monitoring (APM)

#### Analisar Performance
```php
use App\Services\APMService;

$apm = new APMService($server);

// Track response times
$responseTimes = $apm->trackResponseTimes($site, 100);

// Monitor slow queries
$slowQueries = $apm->monitorDatabaseQueries($site, 60);

// Complete analysis
$analysis = $apm->analyzePerformance($site);

// Real-time metrics
$metrics = $apm->getRealTimeMetrics($site);
```

#### Via API
```bash
# Analyze performance
curl https://your-domain.com/api/sites/{site}/performance/analyze \
  -H "Authorization: Bearer YOUR_TOKEN"

# Real-time metrics
curl https://your-domain.com/api/sites/{site}/performance/realtime \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 4. AI & Predictions

#### Prever Carga do Servidor
```php
use App\Services\AIService;

$ai = new AIService($server);

// Predict load for next 24 hours
$prediction = $ai->predictServerLoad(24);

// Detect security threats
$threats = $ai->detectSecurityThreats();

// Auto-optimize resources
$optimizations = $ai->optimizeResources();

// Get upgrade recommendations
$recommendations = $ai->recommendUpgrades();
```

#### Via API
```bash
# Predict server load
curl https://your-domain.com/api/servers/{server}/ai/predict-load?hours_ahead=24 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Detect threats
curl https://your-domain.com/api/servers/{server}/ai/detect-threats \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get recommendations
curl https://your-domain.com/api/servers/{server}/ai/recommendations \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 5. Deployment Pipeline

#### Deploy Automático
```php
use App\Services\DeploymentPipeline;

$pipeline = new DeploymentPipeline($site);

// Execute full deployment
$result = $pipeline->execute();

// Run health check
$health = $pipeline->healthCheck();

// Create backup
$backup = $pipeline->backupBeforeDeploy();
```

#### Via API
```bash
# Deploy site
curl -X POST https://your-domain.com/api/sites/{site}/deployments \
  -H "Authorization: Bearer YOUR_TOKEN"

# Health check
curl https://your-domain.com/api/sites/{site}/deployments/health-check \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 6. Laravel Artisan

#### Executar Comandos
```php
use App\Services\ArtisanService;

$artisan = new ArtisanService($server);

// Run migrations
$artisan->runMigrations($site);

// Clear caches
$artisan->clearCache($site, ['config', 'route', 'view']);

// Optimize
$artisan->optimize($site);

// Enable maintenance mode
$artisan->enableMaintenanceMode($site);
```

#### Via API
```bash
# Run migrations
curl -X POST https://your-domain.com/api/sites/{site}/artisan/migrate \
  -H "Authorization: Bearer YOUR_TOKEN"

# Clear cache
curl -X POST https://your-domain.com/api/sites/{site}/artisan/cache/clear \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"types": ["config", "route", "view"]}'

# Optimize
curl -X POST https://your-domain.com/api/sites/{site}/artisan/optimize \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 7. Database Management

#### Backup e Restore
```php
use App\Services\DatabaseService;

$db = new DatabaseService($server);

// Create backup
$backup = $db->createBackup($database);

// Restore backup
$db->restoreBackup($database, '/var/backups/databases/mydb_backup.sql.gz');

// Setup automated backups
$db->setupAutomatedBackups($database, 'daily', 7);

// Setup replication
$db->setupReplication($primaryDb, $replicaServer);
```

---

### 8. Billing & Usage

#### Calcular Custos
```php
use App\Services\BillingService;

$billing = new BillingService();

// Calculate server costs
$costs = $billing->calculateServerCosts($server, $startDate, $endDate);

// Generate invoice
$invoice = $billing->generateInvoice($team, Carbon::now());

// Track usage
$billing->trackUsage($server);

// Forecast costs
$forecast = $billing->forecastCosts($team);
```

#### Via API
```bash
# Get server costs
curl "https://your-domain.com/api/billing/servers/{server}/costs?start=2026-02-01&end=2026-02-28" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Generate invoice
curl https://your-domain.com/api/billing/teams/{team}/invoice \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get forecast
curl https://your-domain.com/api/billing/teams/{team}/forecast \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🔄 Automação

### Configurar Jobs Automatizados

Criar `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Track usage every hour
    $schedule->command('usage:track')->hourly();
    
    // Generate monthly invoices
    $schedule->command('invoices:generate')->monthlyOn(1, '00:00');
    
    // Security scan every 6 hours
    $schedule->command('security:scan')->everySixHours();
    
    // Optimize resources daily
    $schedule->command('ai:optimize')->daily();
    
    // Backup databases
    $schedule->command('databases:backup')->daily();
}
```

### Criar Commands

```bash
php artisan make:command TrackUsage
php artisan make:command GenerateInvoices
php artisan make:command SecurityScan
php artisan make:command AIOptimize
```

---

## 📊 Dashboard Integration

### Livewire Components

Criar componentes para o dashboard:

```bash
php artisan make:livewire ServerMetrics
php artisan make:livewire PerformanceChart
php artisan make:livewire SecurityAlerts
php artisan make:livewire CostForecast
```

---

## 🐛 Troubleshooting

### Firewall não está funcionando
```bash
# Check UFW status
sudo ufw status

# Check logs
tail -f /var/log/ufw.log
```

### OPcache não está habilitado
```bash
# Check PHP version
php -v

# Check OPcache config
php -i | grep opcache
```

### Redis connection failed
```bash
# Check Redis status
sudo systemctl status redis-server

# Test connection
redis-cli ping
```

### Deployment failed
```bash
# Check deployment logs
tail -f storage/logs/laravel.log

# Check deployment table
php artisan tinker
>>> App\Models\Deployment::latest()->first()
```

---

## 📚 Recursos Adicionais

- [IMPROVEMENTS_IMPLEMENTED.md](IMPROVEMENTS_IMPLEMENTED.md) - Lista completa de funcionalidades
- [config/server.php](config/server.php) - Configurações
- [routes/api-enhanced.php](routes/api-enhanced.php) - Rotas da API

---

## 🆘 Suporte

Se encontrar problemas:

1. Verifique os logs: `tail -f storage/logs/laravel.log`
2. Execute migrations: `php artisan migrate`
3. Limpe caches: `php artisan config:clear && php artisan cache:clear`
4. Verifique permissões: `chmod -R 775 storage bootstrap/cache`

---

## 🎉 Próximos Passos

1. ✅ Implementar autenticação se ainda não tiver
2. ✅ Criar testes automatizados
3. ✅ Configurar CI/CD
4. ✅ Deploy em produção
5. ✅ Monitorar performance

---

**Versão:** 2.0.0  
**Data:** 5 de Fevereiro de 2026  
**Status:** ✅ Pronto para Produção
