# 📡 API Reference - Server Management Platform

## Autenticação

Todas as rotas requerem autenticação via Laravel Sanctum:

```bash
Authorization: Bearer {token}
```

---

## Site Management

### Criar Site
```http
POST /api/site-management/sites
Content-Type: application/json

{
  "server_id": 1,
  "name": "My Application",
  "domain": "example.com",
  "application_type": "laravel|wordpress|react_spa|nodejs_express|...",
  "custom_type": "string (opcional)",
  "php_version": "8.3|8.2|8.1|8.0|7.4",
  "node_version": "21.x|20.x|18.x|16.x|14.x",
  "package_manager": "npm|yarn|pnpm",
  "node_port": 3000,
  "git_repository": "https://github.com/user/repo.git",
  "git_branch": "main",
  "git_provider": "github|gitlab|bitbucket",
  "git_token": "github_pat_xxx",
  "auto_ssl": true,
  "force_https": false,
  "auto_create_database": true,
  "auto_deploy": false,
  "dedicated_php_pool": false,
  "php_memory_limit": "256M",
  "root_directory": "/public"
}

Response 201:
{
  "success": true,
  "message": "Site created successfully",
  "site": { ...site_object }
}
```

### Listar Tipos de Aplicação
```http
GET /api/site-management/application-types

Response 200:
{
  "success": true,
  "types": [
    {
      "value": "laravel",
      "label": "Laravel",
      "requires_php": true,
      "requires_node": false,
      "requires_database": true,
      "default_root_directory": "/public"
    },
    ...
  ]
}
```

### Detalhes do Site
```http
GET /api/site-management/sites/{site}

Response 200:
{
  "success": true,
  "site": {
    "id": 1,
    "server_id": 1,
    "name": "My App",
    "domain": "example.com",
    "application_type": "laravel",
    "status": "active",
    "created_at": "2026-02-06T10:00:00.000000Z",
    "server": { ...server_object },
    "linked_database": { ...database_object },
    "docker_containers": [ ...containers ]
  }
}
```

### Atualizar Site
```http
PUT /api/site-management/sites/{site}
Content-Type: application/json

{
  "name": "Updated Name",
  "php_version": "8.3",
  "auto_ssl": true,
  "force_https": true
}

Response 200:
{
  "success": true,
  "message": "Site updated successfully",
  "site": { ...updated_site }
}
```

### Deletar Site
```http
DELETE /api/site-management/sites/{site}

Response 200:
{
  "success": true,
  "message": "Site deleted successfully"
}
```

### Deploy Site
```http
POST /api/site-management/sites/{site}/deploy

Response 200:
{
  "success": true,
  "message": "Deployment started"
}
```

### Environment Variables
```http
# Get
GET /api/site-management/sites/{site}/env

Response 200:
{
  "success": true,
  "env_variables": {
    "APP_ENV": "production",
    "DB_HOST": "localhost",
    ...
  }
}

# Update
PUT /api/site-management/sites/{site}/env
Content-Type: application/json

{
  "variables": {
    "APP_DEBUG": "false",
    "MAIL_FROM_ADDRESS": "no-reply@example.com"
  }
}

Response 200:
{
  "success": true,
  "message": "Environment variables updated"
}
```

---

## Docker Management

### Containers

#### Listar Containers
```http
GET /api/servers/{server}/docker/containers?all=true

Response 200:
{
  "success": true,
  "containers": [
    {
      "id": "abc123",
      "name": "nginx-proxy",
      "image": "nginx:alpine",
      "status": "Up 2 hours",
      "state": "running",
      "ports": "80/tcp, 443/tcp"
    }
  ]
}
```

#### Containers Tracked (DB)
```http
GET /api/servers/{server}/docker/containers/tracked

Response 200:
{
  "success": true,
  "containers": [
    {
      "id": 1,
      "server_id": 1,
      "site_id": null,
      "container_id": "abc123",
      "name": "nginx-proxy",
      "image": "nginx",
      "image_tag": "alpine",
      "status": "running",
      "ports": [...],
      "volumes": [...],
      "stats": {
        "cpu_percentage": 2.5,
        "memory_usage": 52428800,
        "memory_percentage": 1.2
      }
    }
  ]
}
```

#### Sincronizar Containers
```http
POST /api/servers/{server}/docker/containers/sync

Response 200:
{
  "success": true,
  "message": "Synced 12 containers",
  "count": 12
}
```

#### Criar Container
```http
POST /api/servers/{server}/docker/containers
Content-Type: application/json

{
  "site_id": 1,
  "name": "mysql-production",
  "image": "mysql:8.0",
  "ports": {
    "3306": "3306"
  },
  "volumes": {
    "/var/lib/mysql-data": "/var/lib/mysql"
  },
  "environment": {
    "MYSQL_ROOT_PASSWORD": "secret",
    "MYSQL_DATABASE": "myapp"
  },
  "network": "app-network",
  "restart": "always|unless-stopped|no|on-failure",
  "memory": "2g",
  "cpus": "1.5",
  "privileged": false,
  "working_dir": "/app",
  "command": "mysqld",
  "labels": {
    "app": "production"
  }
}

Response 201:
{
  "success": true,
  "message": "Container created successfully",
  "container": { ...container_object }
}
```

#### Detalhes do Container
```http
GET /api/docker/containers/{container}

Response 200:
{
  "success": true,
  "container": {
    "id": 1,
    "container_id": "abc123",
    "name": "mysql-production",
    "status": "running",
    "server": { ...server },
    "site": { ...site }
  }
}
```

#### Iniciar Container
```http
POST /api/docker/containers/{container}/start

Response 200:
{
  "success": true,
  "message": "Container started",
  "container": { ...updated_container }
}
```

#### Parar Container
```http
POST /api/docker/containers/{container}/stop
Content-Type: application/json

{
  "timeout": 10
}

Response 200:
{
  "success": true,
  "message": "Container stopped"
}
```

#### Reiniciar Container
```http
POST /api/docker/containers/{container}/restart
Content-Type: application/json

{
  "timeout": 10
}

Response 200:
{
  "success": true,
  "message": "Container restarted"
}
```

#### Remover Container
```http
DELETE /api/docker/containers/{container}?force=true&volumes=true

Response 200:
{
  "success": true,
  "message": "Container removed"
}
```

#### Logs do Container
```http
GET /api/docker/containers/{container}/logs?lines=100

Response 200:
{
  "success": true,
  "logs": "2026-02-06 10:00:00 [INFO] Server started\n..."
}
```

#### Stats do Container
```http
GET /api/docker/containers/{container}/stats

Response 200:
{
  "success": true,
  "stats": {
    "cpu_percentage": 2.5,
    "memory_usage": 524288000,
    "memory_percentage": 12.5,
    "network_io": "1.2MB / 850KB",
    "block_io": "500KB / 200KB"
  }
}
```

#### Executar Comando
```http
POST /api/docker/containers/{container}/exec
Content-Type: application/json

{
  "command": "mysql -u root -p'secret' -e 'SHOW DATABASES;'",
  "interactive": false
}

Response 200:
{
  "success": true,
  "output": "Database\ninformation_schema\nmyapp\n..."
}
```

---

### Images

#### Listar Imagens
```http
GET /api/servers/{server}/docker/images

Response 200:
{
  "success": true,
  "images": [
    {
      "id": "sha256:abc123",
      "repository": "nginx",
      "tag": "alpine",
      "size": "23.5MB",
      "created": "2 weeks ago"
    }
  ]
}
```

#### Pull Imagem
```http
POST /api/servers/{server}/docker/images/pull
Content-Type: application/json

{
  "image": "postgres",
  "tag": "15-alpine"
}

Response 200:
{
  "success": true,
  "message": "Image pulled successfully"
}
```

#### Remover Imagem
```http
DELETE /api/servers/{server}/docker/images
Content-Type: application/json

{
  "image_id": "sha256:abc123",
  "force": false
}

Response 200:
{
  "success": true,
  "message": "Image removed successfully"
}
```

---

### Volumes

#### Listar Volumes
```http
GET /api/servers/{server}/docker/volumes

Response 200:
{
  "success": true,
  "volumes": [
    {
      "name": "mysql-data",
      "driver": "local"
    }
  ]
}
```

#### Criar Volume
```http
POST /api/servers/{server}/docker/volumes
Content-Type: application/json

{
  "name": "postgres-data",
  "driver": "local"
}

Response 200:
{
  "success": true,
  "message": "Volume created successfully"
}
```

#### Remover Volume
```http
DELETE /api/servers/{server}/docker/volumes
Content-Type: application/json

{
  "name": "old-data",
  "force": false
}

Response 200:
{
  "success": true,
  "message": "Volume removed successfully"
}
```

---

### Networks

#### Listar Networks
```http
GET /api/servers/{server}/docker/networks

Response 200:
{
  "success": true,
  "networks": [
    {
      "id": "abc123",
      "name": "bridge",
      "driver": "bridge",
      "scope": "local"
    }
  ]
}
```

#### Criar Network
```http
POST /api/servers/{server}/docker/networks
Content-Type: application/json

{
  "name": "app-network",
  "driver": "bridge"
}

Response 200:
{
  "success": true,
  "message": "Network created successfully"
}
```

#### Remover Network
```http
DELETE /api/servers/{server}/docker/networks
Content-Type: application/json

{
  "name": "old-network"
}

Response 200:
{
  "success": true,
  "message": "Network removed successfully"
}
```

---

## Laravel Tools

### Artisan

#### Executar Comando
```http
POST /api/sites/{site}/laravel/artisan
Content-Type: application/json

{
  "command": "migrate --seed"
}

Response 200:
{
  "success": true,
  "command": "migrate --seed",
  "output": "Migrating: 2026_02_06_create_users_table\nMigrated:  2026_02_06_create_users_table (45.23ms)"
}
```

#### Listar Comandos
```http
GET /api/sites/{site}/laravel/artisan/commands

Response 200:
{
  "success": true,
  "commands": [
    {
      "name": "migrate",
      "description": "Run database migrations"
    },
    ...
  ]
}
```

### Migrations
```http
POST /api/sites/{site}/laravel/migrate
Content-Type: application/json

{
  "force": true,
  "seed": false
}

Response 200:
{
  "success": true,
  "output": "Migration table created successfully.\n..."
}
```

### Cache

#### Limpar Cache
```http
POST /api/sites/{site}/laravel/cache/clear

Response 200:
{
  "success": true,
  "message": "Cache cleared successfully",
  "outputs": {
    "config:clear": "Configuration cache cleared!",
    "cache:clear": "Application cache cleared!",
    "route:clear": "Route cache cleared!",
    "view:clear": "Compiled views cleared!"
  }
}
```

#### Otimizar
```http
POST /api/sites/{site}/laravel/optimize

Response 200:
{
  "success": true,
  "message": "Application optimized successfully",
  "outputs": {
    "config:cache": "Configuration cached successfully!",
    "route:cache": "Routes cached successfully!",
    "view:cache": "Blade templates cached successfully!"
  }
}
```

### Logs
```http
GET /api/sites/{site}/laravel/logs?type=laravel&lines=100

Types: laravel | nginx-access | nginx-error | php

Response 200:
{
  "success": true,
  "logs": "[2026-02-06 10:00:00] production.ERROR: ...",
  "type": "laravel"
}
```

### Composer
```http
POST /api/sites/{site}/laravel/composer
Content-Type: application/json

{
  "command": "install|update|require|remove",
  "packages": ["laravel/sanctum", "spatie/laravel-permission"],
  "dev": false
}

Response 200:
{
  "success": true,
  "output": "Installing dependencies from lock file\n..."
}
```

### Queue Status
```http
GET /api/sites/{site}/laravel/queue/status

Response 200:
{
  "success": true,
  "failed_jobs": "No failed jobs found."
}
```

### Environment
```http
GET /api/sites/{site}/laravel/environment

Response 200:
{
  "success": true,
  "environment": "production"
}
```

---

## Códigos de Status HTTP

- `200 OK` - Requisição bem-sucedida
- `201 Created` - Recurso criado com sucesso
- `400 Bad Request` - Dados inválidos
- `401 Unauthorized` - Token inválido ou ausente
- `403 Forbidden` - Sem permissão para acessar
- `404 Not Found` - Recurso não encontrado
- `422 Unprocessable Entity` - Validação falhou
- `500 Internal Server Error` - Erro no servidor

---

## Rate Limiting

As rotas API têm limite de:
- **60 requisições por minuto** para usuários autenticados
- **10 requisições por minuto** para não autenticados

Headers de resposta:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1638360000
```

---

## Webhooks (Futuro)

Configurar webhooks para eventos:
- Site criado
- Deploy concluído
- Container iniciado/parado
- SSL renovado
- Erro crítico

---

** Documentação gerada automaticamente** | Versão 2.0 | 2026-02-06
