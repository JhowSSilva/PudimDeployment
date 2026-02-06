# Exemplo de Uso da API

Este arquivo contém exemplos práticos de uso da API.

## Autenticação

Primeiro, você precisa de um token de autenticação. Use Laravel Sanctum para gerar tokens de usuário.

```bash
# No tinker
php artisan tinker

$user = User::first();
$token = $user->createToken('api-token')->plainTextToken;
echo $token;
```

Use este token em todas as requisições:
```bash
Authorization: Bearer {seu-token}
```

## 1. Criar um Servidor

```bash
curl -X POST http://localhost:8000/api/servers \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Servidor Producao",
    "ip_address": "192.168.1.100",
    "ssh_port": 22,
    "ssh_user": "root",
    "auth_type": "key",
    "ssh_key": "-----BEGIN RSA PRIVATE KEY-----\nMIIEpAI..."
  }'
```

**Resposta:**
```json
{
  "message": "Server created successfully",
  "server": {
    "id": 1,
    "name": "Servidor Producao",
    "ip_address": "192.168.1.100",
    "status": "offline",
    "created_at": "2024-01-01T10:00:00.000000Z"
  }
}
```

## 2. Testar Conexão SSH

```bash
curl -X POST http://localhost:8000/api/servers/1/test-connection \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "message": "Connection successful",
  "connected": true,
  "os_info": {
    "os_type": "Ubuntu",
    "os_version": "22.04"
  }
}
```

## 3. Coletar Métricas em Tempo Real

```bash
curl -X GET http://localhost:8000/api/servers/1/metrics \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "metrics": {
    "cpu_usage": 15.5,
    "memory": {
      "used": 2048,
      "total": 4096
    },
    "disk": {
      "used": 50,
      "total": 100
    },
    "uptime_seconds": 86400,
    "processes": {
      "nginx": {
        "status": "running",
        "pid": 1234
      },
      "php8.3-fpm": {
        "status": "running",
        "pid": 1235
      }
    }
  }
}
```

## 4. Criar um Site

```bash
curl -X POST http://localhost:8000/api/sites \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "server_id": 1,
    "name": "Meu App Laravel",
    "domain": "app.example.com",
    "php_version": "8.3",
    "git_repository": "https://github.com/usuario/meu-app.git",
    "git_branch": "main",
    "git_token": "github_pat_11ABCDEFGH...",
    "document_root": "/public"
  }'
```

**Resposta:**
```json
{
  "message": "Site created successfully",
  "site": {
    "id": 1,
    "server_id": 1,
    "name": "Meu App Laravel",
    "domain": "app.example.com",
    "php_version": "8.3",
    "status": "inactive"
  }
}
```

## 5. Preview da Configuração Nginx

```bash
curl -X GET "http://localhost:8000/api/sites/1/nginx/preview?ssl=false" \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "config": "server {\n    listen 80;\n    server_name app.example.com www.app.example.com;\n    root /var/www/app.example.com/public;\n    ..."
}
```

## 6. Deploy da Configuração Nginx

```bash
curl -X POST http://localhost:8000/api/sites/1/nginx/deploy \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "ssl": false
  }'
```

**Resposta:**
```json
{
  "message": "Nginx configuration deployed successfully",
  "config_path": "/etc/nginx/sites-available/app.example.com"
}
```

## 7. Testar Configuração de Deployment

```bash
curl -X GET http://localhost:8000/api/sites/1/test-deployment \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "tests": {
    "ssh_connection": true,
    "git_repository": true,
    "composer_installed": true,
    "php_version": "PHP 8.3.0 (cli)"
  }
}
```

## 8. Executar Deployment

```bash
curl -X POST http://localhost:8000/api/deployments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "site_id": 1
  }'
```

**Resposta:**
```json
{
  "message": "Deployment job dispatched"
}
```
> **Nota:** O deployment é executado em background via Horizon. Status 202 Accepted.

## 9. Visualizar Status de um Deployment

```bash
curl -X GET http://localhost:8000/api/deployments/1 \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "deployment": {
    "id": 1,
    "site_id": 1,
    "user_id": 1,
    "status": "success",
    "trigger": "manual",
    "commit_hash": "abc123",
    "commit_message": "Fix critical bug",
    "output_log": "[2024-01-01 10:00:00] Deployment started by Admin\n[2024-01-01 10:00:01] Connecting to server...\n...",
    "started_at": "2024-01-01T10:00:00.000000Z",
    "finished_at": "2024-01-01T10:02:30.000000Z",
    "duration_seconds": 150
  }
}
```

## 10. Listar Deployments

```bash
curl -X GET "http://localhost:8000/api/deployments?site_id=1&status=success" \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 3,
      "site_id": 1,
      "status": "success",
      "commit_hash": "xyz789",
      "created_at": "2024-01-01T12:00:00.000000Z"
    },
    {
      "id": 1,
      "site_id": 1,
      "status": "success",
      "commit_hash": "abc123",
      "created_at": "2024-01-01T10:00:00.000000Z"
    }
  ],
  "per_page": 20,
  "total": 2
}
```

## 11. Rollback

```bash
curl -X POST http://localhost:8000/api/sites/1/rollback \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "message": "Rollback initiated",
  "deployment": {
    "id": 4,
    "site_id": 1,
    "status": "success",
    "commit_hash": "abc123",
    "commit_message": "Rollback to: Fix critical bug"
  }
}
```

## 12. Atualizar Site (Deploy Script Customizado)

```bash
curl -X PUT http://localhost:8000/api/sites/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "deploy_script": "composer install --no-dev\nphp artisan migrate --force\nphp artisan config:cache\nnpm run build\nphp artisan queue:restart"
  }'
```

## 13. Atualizar Variáveis de Ambiente

```bash
curl -X PUT http://localhost:8000/api/sites/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "env_variables": {
      "APP_ENV": "production",
      "APP_DEBUG": "false",
      "DB_HOST": "localhost",
      "DB_DATABASE": "meu_app",
      "DB_USERNAME": "usuario",
      "DB_PASSWORD": "senha123"
    }
  }'
```

## 14. Listar Todos os Servidores com Métricas

```bash
curl -X GET http://localhost:8000/api/servers \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "servers": [
    {
      "id": 1,
      "name": "Servidor Producao",
      "ip_address": "192.168.1.100",
      "status": "online",
      "os_type": "Ubuntu",
      "os_version": "22.04",
      "last_ping_at": "2024-01-01T12:00:00.000000Z",
      "metrics": [
        {
          "cpu_usage": 25.5,
          "memory_used_mb": 2048,
          "memory_total_mb": 4096,
          "disk_used_gb": 50,
          "disk_total_gb": 100,
          "created_at": "2024-01-01T12:00:00.000000Z"
        }
      ]
    }
  ]
}
```

## 15. Disparar Coleta de Métricas Manualmente

```bash
curl -X POST http://localhost:8000/api/servers/1/collect-metrics \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "message": "Metrics collection job dispatched"
}
```

## Fluxo Completo de Deploy

### 1. Criar servidor e testar conexão
```bash
# Criar servidor
curl -X POST http://localhost:8000/api/servers -H "Authorization: Bearer {token}" -d '{"name":"Prod","ip_address":"192.168.1.100",...}'

# Testar conexão
curl -X POST http://localhost:8000/api/servers/1/test-connection -H "Authorization: Bearer {token}"
```

### 2. Criar site
```bash
curl -X POST http://localhost:8000/api/sites -H "Authorization: Bearer {token}" -d '{"server_id":1,"domain":"app.com",...}'
```

### 3. Deploy Nginx
```bash
curl -X POST http://localhost:8000/api/sites/1/nginx/deploy -H "Authorization: Bearer {token}" -d '{"ssl":false}'
```

### 4. Testar deployment
```bash
curl -X GET http://localhost:8000/api/sites/1/test-deployment -H "Authorization: Bearer {token}"
```

### 5. Executar deployment
```bash
curl -X POST http://localhost:8000/api/deployments -H "Authorization: Bearer {token}" -d '{"site_id":1}'
```

### 6. Monitorar deployment
```bash
# Aguardar alguns segundos, então verificar
curl -X GET http://localhost:8000/api/deployments/1 -H "Authorization: Bearer {token}"
```

## Comandos Úteis

### Criar usuário e token (Tinker)
```php
php artisan tinker

$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);

$token = $user->createToken('api-token')->plainTextToken;
echo $token;
```

### Verificar Jobs no Horizon
```bash
# Acessar dashboard
open http://localhost:8000/horizon

# Verificar failed jobs
php artisan horizon:failed

# Retry failed jobs
php artisan queue:retry all
```

### Verificar Scheduler
```bash
# Listar tarefas agendadas
php artisan schedule:list

# Executar manualmente
php artisan schedule:run
```
