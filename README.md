# Pudim Deployment

Um painel completo de gerenciamento de servidores web e deploy automatizado, construído com Laravel 11 + PostgreSQL, Redis e Horizon.

## 🚀 Características

### ✅ Funcionalidades Implementadas (Fase 1)

#### Gestão de Servidores
- ✅ CRUD completo de servidores
- ✅ Conexão SSH via chave privada ou senha (criptografadas)
- ✅ Teste de conectividade e handshake SSH
- ✅ Detecção automática de SO (Ubuntu 22.04/24.04, Debian)
- ✅ Lista de servidores com status (online/offline)
- ✅ Suporte a servidores ARM64 (AWS Graviton)

#### Monitoramento Básico
- ✅ CPU usage (%)
- ✅ RAM usage (usado/total em MB)
- ✅ Disco usage (GB para partição /)
- ✅ Uptime do servidor
- ✅ Status de processos (nginx, php-fpm, mysql, redis, postgresql)
- ✅ Coleta automática a cada 60 segundos (via Laravel Scheduler + Horizon)

#### Deployment
- ✅ Conexão com repositórios Git (GitHub/GitLab via token)
- ✅ Configuração de branch de deploy
- ✅ Script de deploy customizável
- ✅ Deploy manual via API
- ✅ Webhook para deploy automático (preparado)
- ✅ Histórico de deploys com status (success/failed)
- ✅ Rollback para deploy anterior
- ✅ Logs detalhados de cada deployment

#### Gestão de Sites/Apps
- ✅ Múltiplos sites por servidor
- ✅ Configuração de domínio
- ✅ Geração automática de configuração Nginx
- ✅ Seletor de versão PHP (8.1, 8.2, 8.3)
- ✅ Document root customizável
- ✅ Variáveis de ambiente (.env) criptografadas
- ✅ Deploy e remoção de configurações Nginx

### ✅ Novas Funcionalidades (Fase 2) - **IMPLEMENTADAS!** 🎉

> **Tudo está pronto para teste!** Consulte [INDEX.md](INDEX.md) para começar.

#### 🔒 Segurança Avançada
- ✅ **Firewall Management** - UFW com regras customizáveis
- ✅ **Fail2ban** - Detecção automática de intrusões e banimento
- ✅ **Security Scanning** - Scan de rootkits e malware
- ✅ **IP Blocking** - Bloqueio/desbloqueio manual e automático
- ✅ **Threat Tracking** - Registro e análise de ameaças

#### ⚡ Performance & Cache
- ✅ **OPcache** - Otimização de PHP com cache de bytecode
- ✅ **Redis Caching** - Cache de aplicação configurável
- ✅ **Memcached** - Suporte alternativo de cache
- ✅ **Brotli Compression** - Compressão avançada de assets
- ✅ **APM (Application Performance Monitoring)** - Monitoramento em tempo real
- ✅ **Slow Query Detection** - Identificação de queries lentas
- ✅ **N+1 Detection** - Detecção de problemas N+1 queries

#### 🤖 Inteligência Artificial
- ✅ **Load Prediction** - Predição de carga do servidor com IA
- ✅ **Resource Optimization** - Otimização automática de recursos
- ✅ **Anomaly Detection** - Detecção de comportamentos anormais
- ✅ **Upgrade Recommendations** - Recomendações inteligentes de upgrade
- ✅ **Trend Analysis** - Análise de tendências de uso

#### 🚀 Deployment Avançado
- ✅ **Deployment Pipeline** - Pipeline completo de 12 passos
- ✅ **Auto Backup** - Backup automático antes do deploy
- ✅ **Health Checks** - Verificação automática pós-deploy
- ✅ **Auto Rollback** - Rollback automático em caso de falha
- ✅ **Zero Downtime** - Deploy sem interrupção do serviço

#### 💾 Database Management
- ✅ **Automated Backups** - Backups automáticos agendáveis
- ✅ **Backup Restore** - Restore de backups com um clique
- ✅ **Replication** - Setup de replicação master-slave
- ✅ **Database Optimization** - Otimização de tabelas
- ✅ **Size Analysis** - Análise de tamanho e performance

#### 💰 Billing & Cost Management
- ✅ **Cost Tracking** - Rastreamento automático de custos
- ✅ **Invoice Generation** - Geração automática de faturas
- ✅ **Usage Metrics** - Métricas detalhadas de uso
- ✅ **Cost Forecasting** - Previsão de custos com IA
- ✅ **Multi-cloud Pricing** - Suporte para múltiplos provedores

#### 🎨 Dashboard & UI
- ✅ **Server Metrics Component** - Dashboard de métricas em tempo real
- ✅ **Performance Charts** - Gráficos interativos com predições
- ✅ **Security Alerts** - Alertas visuais de segurança
- ✅ **Cost Forecast Widget** - Widget de previsão de custos

#### 🛠️ Comandos Artisan
- ✅ `php artisan usage:track` - Rastrear uso para billing
- ✅ `php artisan invoices:generate` - Gerar faturas mensais
- ✅ `php artisan security:scan` - Scan de segurança completo
- ✅ `php artisan ai:optimize` - Otimização com IA
- ✅ `php artisan databases:backup` - Backup de databases

#### 📡 API REST (50+ endpoints)
- ✅ Firewall Management (8 endpoints)
- ✅ Performance Monitoring (5 endpoints)
- ✅ AI Features (4 endpoints)
- ✅ Cache Management (6 endpoints)
- ✅ Artisan Commands (6 endpoints)
- ✅ Billing System (6 endpoints)
- ✅ Database Management (4 endpoints)

**📚 Documentação Completa:**
- [INDEX.md](INDEX.md) - Índice de toda documentação
- [START_TESTING.md](START_TESTING.md) - Como começar a testar agora
- [QUICK_START.md](QUICK_START.md) - Setup em 5 minutos
- [API_TESTING.md](API_TESTING.md) - Exemplos de API
- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Guia completo

**🧪 Testar Agora:**
```bash
./test-features.sh  # Script de teste automatizado
# OU
php artisan ai:optimize  # Teste individual
```

### ✅ Novas Funcionalidades (Fase 3) - **IMPLEMENTADAS!** 🎉

> **Status: CONCLUÍDA** - Todas as funcionalidades prioritárias da Fase 3 implementadas!

#### 🔗 Webhooks Automáticos
- ✅ **GitHub Webhooks** - Deploy automático em push
- ✅ **GitLab Webhooks** - Integração completa  
- ✅ **Bitbucket Webhooks** - Suporte total
- ✅ **Validação de Assinatura** - Segurança HMAC SHA256
- ✅ **Auto-Deploy** - Enable/disable por site
- ✅ **Setup Wizard** - Instruções passo a passo
- ✅ **Secret Management** - Regeneração de secrets

#### 💻 Terminal Web Integrado
- ✅ **XTerm.js** - Terminal profissional no navegador
- ✅ **SSH Connection** - Via chave privada ou senha
- ✅ **Syntax Highlighting** - Tema customizado com cores
- ✅ **Command History** - Setas ↑ ↓ para histórico
- ✅ **Quick Commands** - Botões para comandos comuns
- ✅ **Auto-Resize** - Responsivo e adaptável
- ✅ **Multiple Sessions** - Várias sessões simultâneas
- ✅ **Ctrl+C Support** - Controle completo do terminal

#### 🔔 Notificações em Tempo Real
- ✅ **Notification Bell** - Componente Livewire no header
- ✅ **Auto-Polling** - Atualização a cada 30 segundos
- ✅ **Badge Counter** - Contador de não lidas
- ✅ **Typed Notifications** - 6 tipos (deployment, security, error, warning, success, info)
- ✅ **Action Links** - Links diretos para ações
- ✅ **Mark as Read** - Individual ou todas
- ✅ **Notification Page** - Página completa de histórico
- ✅ **Emoji Icons** - Ícones visuais por tipo

**📖 Documentação Fase 3:**
- [PHASE3_COMPLETE.md](PHASE3_COMPLETE.md) - Documentação completa da Fase 3

**🌐 Novas Rotas:**
```bash
# Webhooks
POST /webhooks/receive/{siteId}/{token}  # Endpoint público
GET  /sites/{site}/webhooks/config
POST /sites/{site}/webhooks/enable

# Terminal
GET  /servers/{server}/terminal
POST /servers/{server}/terminal/execute

# Notificações
GET  /notifications
POST /notifications/{id}/read
POST /notifications/read-all
```

## 📋 Requisitos

- PHP 8.2+
- PostgreSQL 14+
- Redis 7+
- Composer
- Node.js & NPM (para frontend)

## 🔧 Instalação

### 1. Clone o repositório
```bash
git clone <repository-url>
cd server-manager
```

### 2. Instale as dependências
```bash
composer install
```

### 3. Configure o ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure o banco de dados no `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=server_manager
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

### 5. Configure Redis
```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

### 6. Execute as migrations
```bash
php artisan migrate
```

### 7. Publique os assets do Horizon
```bash
php artisan horizon:install
```

### 8. Inicie os serviços

**Terminal 1 - Servidor Web:**
```bash
php artisan serve
```

**Terminal 2 - Horizon (Queue Worker):**
```bash
php artisan horizon
```

**Terminal 3 - Scheduler (Coleta de Métricas):**
```bash
php artisan schedule:work
```

## 📚 Arquitetura

### Models Principais

#### Server
Representa um servidor gerenciado. Armazena credenciais SSH criptografadas.

**Relacionamentos:**
- `belongsTo(User)`
- `hasMany(Site)`
- `hasMany(ServerMetric)`

#### Site
Representa uma aplicação/site hospedada em um servidor.

**Relacionamentos:**
- `belongsTo(Server)`
- `hasMany(Deployment)`

#### Deployment
Histórico de deploys de um site.

**Status possíveis:**
- `pending`, `running`, `success`, `failed`, `rolled_back`

#### ServerMetric
Métricas coletadas de um servidor.

### Services

#### SSHConnectionService
Gerencia conexões SSH usando `phpseclib3`.

**Exemplo de uso:**
```php
use App\Services\SSHConnectionService;
use App\Models\Server;

$server = Server::find(1);
$ssh = new SSHConnectionService($server);

// Executar comando
$result = $ssh->execute('ls -la /var/www');
echo $result['output'];
echo $result['exit_code'];

// Detectar SO
$osInfo = $ssh->detectOS();
// ['os_type' => 'Ubuntu', 'os_version' => '22.04']
```

#### MetricsCollectorService
Coleta métricas do servidor via SSH.

**Comandos SSH utilizados:**
```bash
# CPU
top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1

# Memória (MB)
free -m | grep Mem | awk '{print $3, $2}'

# Disco (GB)
df -BG / | tail -1 | awk '{print $3, $2}' | sed 's/G//g'

# Uptime (segundos)
cat /proc/uptime | awk '{print $1}' | cut -d'.' -f1

# Status de serviço
systemctl is-active nginx
```

#### DeploymentService
Executa deploys de sites Laravel.

**Script de Deploy Padrão:**
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
php artisan cache:clear
php artisan queue:restart
```

#### NginxConfigService
Gera e gerencia configurações Nginx.

**Exemplo de configuração gerada:**
```nginx
server {
    listen 80;
    server_name example.com www.example.com;
    root /var/www/example.com/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 📡 API Endpoints

### Autenticação
Todas as rotas requerem autenticação via Laravel Sanctum:
```
Authorization: Bearer {token}
```

### Servers

```http
GET    /api/servers              # Listar
POST   /api/servers              # Criar
GET    /api/servers/{id}         # Visualizar
PUT    /api/servers/{id}         # Atualizar
DELETE /api/servers/{id}         # Deletar
POST   /api/servers/{id}/test-connection
GET    /api/servers/{id}/metrics
POST   /api/servers/{id}/collect-metrics
```

### Sites

```http
GET    /api/sites                # Listar
POST   /api/sites                # Criar
GET    /api/sites/{id}           # Visualizar
PUT    /api/sites/{id}           # Atualizar
DELETE /api/sites/{id}           # Deletar
POST   /api/sites/{id}/nginx/deploy
GET    /api/sites/{id}/nginx/preview
```

### Deployments

```http
GET    /api/deployments          # Listar
GET    /api/deployments/{id}     # Visualizar
POST   /api/deployments          # Triggerar deploy
POST   /api/sites/{id}/rollback  # Rollback
GET    /api/sites/{id}/test-deployment
```

## 🔐 Segurança

- Chaves SSH armazenadas com `Crypt::encryptString()`
- Senhas SSH criptografadas
- Git tokens criptografados
- Variáveis de ambiente criptografadas
- Validação de comandos SSH com whitelist
- Policies implementadas para autorização

## ⚙️ Configuração do Horizon

**Acessar Dashboard:**
```
http://localhost:8000/horizon
```

## 📊 Scheduler (Cron)

Para produção, adicione ao crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🎯 Próximos Passos (Fase 2)

- [ ] Interface Web com Livewire 3 ou Inertia.js + Vue 3
- [ ] Webhooks para deploy automático (GitHub/GitLab)
- [ ] SSL automático com Let's Encrypt
- [ ] Backup automático de bancos de dados
- [ ] Firewall management (UFW)
- [ ] Cron jobs management
- [ ] Logs viewer em tempo real
- [ ] Notificações (email, Slack, Discord)
- [ ] 2FA para usuários

## 📝 Licença

MIT
