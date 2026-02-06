# 🧪 Guia de Testes - Expansão do Sistema

## Verificação Rápida

### 1. Verificar Migrations
```bash
cd /home/jhow/server-manager
php artisan migrate:status
```

Deve mostrar as migrations executadas:
- `2026_02_06_130513_add_extended_fields_to_sites_table`
- `2026_02_06_130514_create_docker_containers_table`

### 2. Verificar Schema do Banco
```bash
# PostgreSQL
psql -d server_manager -c "\d sites"
psql -d server_manager -c "\d docker_containers"
```

Verificar novos campos:
- `sites.application_type`
- `sites.node_version`
- `sites.linked_database_id`
- `docker_containers.container_id`

### 3. Testar Enums
```bash
php artisan tinker
```

```php
// Testar ApplicationType
use App\Enums\ApplicationType;
ApplicationType::LARAVEL->label();           // "Laravel"
ApplicationType::REACT_SPA->requiresNode();  // true

// Testar PhpVersion
use App\Enums\PhpVersion;
PhpVersion::PHP_83->fpmService();  // "php8.3-fpm"

// Testar NodeVersion
use App\Enums\NodeVersion;
NodeVersion::NODE_20->installCommand();  // "nvm install 20.x"
```

### 4. Testar Rotas API
```bash
# Listar application types
curl http://localhost/api/site-management/application-types \
  -H "Authorization: Bearer SEU_TOKEN"

# Listar containers Docker em um servidor
curl http://localhost/api/servers/1/docker/containers \
  -H "Authorization: Bearer SEU_TOKEN"

# Listar comandos Artisan
curl http://localhost/api/sites/1/laravel/artisan/commands \
  -H "Authorization: Bearer SEU_TOKEN"
```

### 5. Testar Criação de Site (Laravel)

```bash
curl -X POST http://localhost/api/site-management/sites \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "server_id": 1,
    "name": "Test Laravel Site",
    "domain": "test.local",
    "application_type": "laravel",
    "php_version": "8.3",
    "auto_create_database": true,
    "auto_ssl": false
  }'
```

**Nota:** Certifique-se de ter um servidor ativo no banco antes de testar.

### 6. Testar Docker Manager

```bash
# Criar container de teste (Redis)
curl -X POST http://localhost/api/servers/1/docker/containers \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "redis-test",
    "image": "redis:7-alpine",
    "ports": {"6379": "6379"}
  }'

# Listar containers tracked
curl http://localhost/api/servers/1/docker/containers/tracked \
  -H "Authorization: Bearer SEU_TOKEN"

# Ver stats de um container
curl http://localhost/api/docker/containers/1/stats \
  -H "Authorization: Bearer SEU_TOKEN"
```

### 7. Testar Laravel Tools

```bash
# Execute um comando Artisan
curl -X POST http://localhost/api/sites/1/laravel/artisan \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"command": "route:list"}'

# Limpar cache
curl -X POST http://localhost/api/sites/1/laravel/cache/clear \
  -H "Authorization: Bearer SEU_TOKEN"

# Ver logs
curl "http://localhost/api/sites/1/laravel/logs?type=laravel&lines=50" \
  -H "Authorization: Bearer SEU_TOKEN"
```

---

## Testes Manuais no Servidor

### Setup de Site Laravel (SSH)

```bash
ssh usuario@seu-servidor

# Verificar estrutura criada
ls -la /var/www/test.local/

# Verificar Nginx config
cat /etc/nginx/sites-available/test.local

# Verificar PHP-FPM pool (se dedicated_php_pool=true)
cat /etc/php/8.3/fpm/pool.d/test-local.conf

# Ver logs Nginx
tail -f /var/www/test.local/logs/access.log
tail -f /var/www/test.local/logs/error.log
```

### Verificar Node.js Setup

```bash
# Verificar Node instalado via NVM
nvm list

# Verificar PM2
pm2 list
pm2 logs nome-do-app

# Ver ecosystem config
cat /var/www/seu-site/ecosystem.config.json
```

### Verificar Docker

```bash
# Containers rodando
docker ps

# Inspecionar container criado
docker inspect redis-test

# Logs do container
docker logs redis-test

# Stats de recursos
docker stats --no-stream
```

---

## Checklist de Validação

### ✅ Database
- [ ] Migrations executadas sem erros
- [ ] Tabela `sites` com 25+ novos campos
- [ ] Tabela `docker_containers` criada
- [ ] Foreign keys funcionando (`linked_database_id`, `server_id`)

### ✅ Enums
- [ ] `ApplicationType` com 17 valores
- [ ] Métodos helper funcionando (`requiresPhp()`, `nginxTemplate()`)
- [ ] `PhpVersion`, `NodeVersion`, `DatabaseType`, `PackageManager` retornando valores corretos

### ✅ Services
- [ ] `SiteManager` instanciável sem erros
- [ ] `NginxConfigGenerator` gerando templates válidos
- [ ] `DockerManager` executando comandos Docker via SSH

### ✅ Controllers
- [ ] `SiteManagementController` respondendo em todas as rotas
- [ ] `DockerController` listando containers
- [ ] `LaravelToolsController` executando Artisan

### ✅ Models
- [ ] `Site` model com novos campos no `$fillable`
- [ ] `DockerContainer` model com relacionamentos
- [ ] Casts funcionando (JSON, boolean, datetime)

### ✅ API Routes
- [ ] 41 novas rotas registradas
- [ ] Middleware `auth:api` aplicado
- [ ] Model binding funcionando (`{site}`, `{container}`, `{server}`)

---

## Resolução de Problemas Comuns

### Erro: "Class 'App\Enums\ApplicationType' not found"
```bash
composer dump-autoload
```

### Erro: "Connection refused" ao Docker
```bash
# Instalar Docker no servidor
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
sudo systemctl start docker
```

### Erro: "NVM command not found"
```bash
# Instalar NVM no servidor
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
```

### Erro: "pm2 command not found"
```bash
npm install -g pm2
```

### Site criado mas não acessível
```bash
# Verificar status Nginx
sudo systemctl status nginx

# Testar config Nginx
sudo nginx -t

# Recarregar Nginx
sudo systemctl reload nginx

# Verificar DNS
ping test.local

# Adicionar ao /etc/hosts se local
echo "127.0.0.1 test.local" | sudo tee -a /etc/hosts
```

---

## Performance Testing

### Teste de Carga - Criar Múltiplos Sites
```bash
# Use um loop para criar 10 sites rapidamente
for i in {1..10}; do
  curl -X POST http://localhost/api/site-management/sites \
    -H "Authorization: Bearer TOKEN" \
    -H "Content-Type: application/json" \
    -d "{
      \"server_id\": 1,
      \"name\": \"Site $i\",
      \"domain\": \"site$i.local\",
      \"application_type\": \"laravel\",
      \"auto_ssl\": false
    }"
done
```

### Monitorar Uso de Recursos
```bash
# CPU e Memória
htop

# Docker stats
watch -n 1 docker stats

# Logs do Laravel
tail -f storage/logs/laravel.log
```

---

## Métricas de Sucesso

✅ **Implementação Completa** se:
- [x] Migrations rodaram sem erros
- [x] Todos os endpoints API respondem 200/201
- [x] Site Laravel criado e acessível
- [x] Container Docker iniciado e visível em `docker ps`
- [x] Comando Artisan executado com sucesso
- [x] Nginx servindo conteúdo corretamente
- [x] Logs não apresentam erros críticos

---

## Próximos Testes Avançados

1. **Criar site de cada um dos 17 tipos**
2. **Deploy automático com webhook GitHub**
3. **SSL Let's Encrypt completo**
4. **PM2 auto-restart após reboot**
5. **Backup automático de banco**
6. **Rollback de deployment**
7. **Firewall rules aplicadas**
8. **CDN Cloudflare integration**

---

**Boa sorte nos testes!** 🚀

Se encontrar bugs, verifique os logs em:
- `storage/logs/laravel.log`
- `/var/www/*/logs/error.log`
- `docker logs <container>`
