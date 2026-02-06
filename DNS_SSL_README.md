# 🔒 Sistema de DNS e SSL Automático

## ✨ Funcionalidades Implementadas

### 🌐 Gerenciamento Automático de DNS (Cloudflare)
- ✅ Criação automática de registros DNS tipo A
- ✅ Suporte a proxy Cloudflare (CDN + DDoS Protection)
- ✅ Verificação de propagação DNS
- ✅ Atualização e remoção de registros
- ✅ Cache de zonas para performance

### 🔐 Certificados SSL Automáticos
- ✅ **Cloudflare Origin Certificate** (Recomendado)
  - Validade: 15 anos
  - Sem necessidade de renovação
  - Geração instantânea via API
  - Funciona apenas com proxy ativo

- ✅ **Let's Encrypt**
  - Validade: 90 dias
  - Renovação automática
  - Funciona sem proxy
  - Via certbot/nginx

### ⚙️ Configuração Nginx Automática
- ✅ Geração de config HTTP (porta 80)
- ✅ Geração de config HTTPS (porta 443)
- ✅ Redirect HTTP → HTTPS
- ✅ Headers de segurança (HSTS, XSS, etc)
- ✅ TLS 1.2 e 1.3
- ✅ Configuração Laravel otimizada

## 📁 Estrutura de Arquivos

```
app/
├── Services/
│   ├── CloudflareService.php    # Integração API Cloudflare
│   ├── SSLService.php            # Gerenciamento SSL
│   └── NginxConfigService.php    # Configuração Nginx (atualizado)
├── Jobs/
│   ├── ConfigureDNSJob.php       # Configura DNS na Cloudflare
│   ├── VerifyDNSPropagationJob.php # Verifica propagação DNS
│   ├── GenerateSSLJob.php        # Gera certificado SSL
│   └── RenewSSLCertificatesJob.php # Renovação automática
├── Console/Commands/
│   └── RenewSSLCommand.php       # Comando manual de renovação
└── Models/
    └── Site.php                  # Model atualizado com campos SSL/DNS

database/migrations/
└── xxxx_add_dns_ssl_fields_to_sites_table.php

resources/views/sites/
├── create.blade.php              # Form com opções DNS/SSL
└── edit.blade.php                # Atualizado

CLOUDFLARE_SETUP.md               # Guia de configuração
```

## 🚀 Como Usar

### 1. Configurar Cloudflare API Token

**Obter token:**
1. Acesse https://dash.cloudflare.com
2. Profile → API Tokens → Create Token
3. Use template "Edit zone DNS"
4. Adicione permissão "SSL and Certificates → Edit"
5. Copie o token gerado

**Configurar no Laravel:**

```env
# .env
CLOUDFLARE_API_TOKEN=seu_token_aqui
CLOUDFLARE_ACCOUNT_ID=seu_account_id (opcional)
```

Ver guia completo: [CLOUDFLARE_SETUP.md](CLOUDFLARE_SETUP.md)

### 2. Executar Migrations

```bash
php artisan migrate
```

### 3. Configurar Queue Worker

```bash
# Desenvolvimento
php artisan queue:work

# Produção (com Supervisor)
php artisan horizon
```

### 4. Criar Site com DNS e SSL Automático

**Via Interface Web:**
1. Acesse `/sites/create`
2. Preencha dados do site
3. Marque "Configurar DNS automaticamente"
4. Escolha tipo de SSL (Cloudflare ou Let's Encrypt)
5. Clique em "Criar Site"

**Fluxo automático:**
```
Site Criado
    ↓
ConfigureDNSJob (delay 5s)
    ↓ Cria registro A na Cloudflare
    ↓ Aguarda propagação DNS
VerifyDNSPropagationJob (delay 30s, retry 5x)
    ↓ Verifica se DNS propagou
    ↓
GenerateSSLJob (delay 10s)
    ↓ Gera certificado (Cloudflare OU Let's Encrypt)
    ↓ Instala certificado no servidor
    ↓ Atualiza configuração Nginx
    ↓ Recarrega Nginx
    ↓
✅ Site online com HTTPS!
```

### 5. Monitorar Jobs

```bash
# Ver jobs em execução
php artisan horizon:list

# Ver logs
tail -f storage/logs/laravel.log | grep -i "ssl\|dns"
```

## 🔧 Comandos Artisan

### Renovar Certificados SSL

```bash
# Ver quais certificados precisam renovação
php artisan ssl:renew --check

# Renovar certificados expirando em 30 dias
php artisan ssl:renew

# Forçar renovação de todos
php artisan ssl:renew --force

# Renovar site específico
php artisan ssl:renew --site=exemplo.com.br
```

### Agendar Renovação Automática

Já configurado em `routes/console.php`:

```php
// Roda diariamente às 2h da manhã
Schedule::job(new \App\Jobs\RenewSSLCertificatesJob)->daily()->at('02:00');
```

No servidor, adicione ao crontab:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Campos Adicionados ao Model Site

```php
// DNS
'cloudflare_zone_id'     // ID da zona Cloudflare
'cloudflare_record_id'   // ID do registro DNS
'cloudflare_proxy'       // Proxy ativo? (boolean)
'auto_dns'               // DNS automático? (boolean)

// SSL
'ssl_type'               // Tipo: none|letsencrypt|cloudflare
'ssl_enabled'            // SSL ativo? (boolean)
'ssl_expires_at'         // Data de expiração (datetime)
'ssl_last_check'         // Última verificação (datetime)
'ssl_certificate'        // Certificado (text, encrypted)
'ssl_private_key'        // Chave privada (text, encrypted)
'ssl_ca_bundle'          // CA Bundle (text, encrypted)
```

## 🔐 Segurança

### Dados Criptografados

Automaticamente criptografados no banco:
- `git_token`
- `ssl_private_key`
- `ssl_certificate`
- `ssl_ca_bundle`

### Headers de Segurança (Nginx)

```nginx
# HTTPS
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: no-referrer-when-downgrade
```

### SSL/TLS

```nginx
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:...';
ssl_prefer_server_ciphers off;
ssl_session_cache shared:SSL:10m;
ssl_stapling on;
ssl_stapling_verify on;
```

## 🧪 Testes

### Testar Cloudflare API

```bash
php artisan tinker

$cf = app(\App\Services\CloudflareService::class);

// Verificar token
$cf->verifyToken(); // true/false

// Listar zonas
$zones = $cf->listZones();
print_r($zones);

// Encontrar zona
$zone = $cf->findZone('exemplo.com.br');
print_r($zone);

// Criar registro DNS
$record = $cf->createDNSRecord(
    $zone['id'],
    'A',
    'teste.exemplo.com.br',
    '192.168.1.1',
    true // proxy
);

// Gerar certificado Cloudflare
$cert = $cf->createOriginCertificate(['exemplo.com.br', '*.exemplo.com.br']);
```

### Testar SSL Service

```bash
php artisan tinker

$site = \App\Models\Site::find(1);
$ssl = app(\App\Services\SSLService::class);

// Gerar certificado
$ssl->generateCertificate($site);

// Verificar expiração
$check = $ssl->checkExpiration($site);
print_r($check);

// Renovar
$ssl->renewCertificate($site);
```

## 📈 Monitoramento

### Ver Sites com SSL

```php
// Sites com SSL ativo
Site::where('ssl_enabled', true)->get();

// Sites expirando em 30 dias
Site::where('ssl_enabled', true)
    ->where('ssl_expires_at', '<=', now()->addDays(30))
    ->get();

// Sites com Cloudflare proxy
Site::where('cloudflare_proxy', true)->get();
```

### Logs

Todos os eventos são logados:

```bash
# Ver logs de DNS
grep "DNS" storage/logs/laravel.log

# Ver logs de SSL
grep "SSL" storage/logs/laravel.log

# Ver jobs failures
grep "FAILED" storage/logs/laravel.log
```

## 🐛 Troubleshooting

### Erro: "Cloudflare zone not found"

**Causa:** Domínio não está na Cloudflare ou token sem permissão

**Solução:**
1. Verifique se domínio está adicionado na Cloudflare
2. Confirme que nameservers apontam para Cloudflare
3. Verifique permissões do token

### Erro: "DNS propagation failed"

**Causa:** DNS ainda não propagou ou configuração incorreta

**Solução:**
1. Aguarde mais tempo (pode levar até 24h)
2. Verifique registro DNS no dashboard Cloudflare
3. Teste manualmente: `dig exemplo.com.br`

### Erro: "SSL generation failed"

**Causa:** Proxy desativado (Cloudflare) ou certbot não instalado (Let's Encrypt)

**Solução:**

**Cloudflare:**
- Ative o proxy Cloudflare
- Ou mude para Let's Encrypt

**Let's Encrypt:**
- Instale certbot no servidor: `apt install certbot python3-certbot-nginx`
- Verifique se DNS está configurado corretamente

### Job ficou travado

```bash
# Limpar failed jobs
php artisan queue:flush

# Reiniciar queue worker
php artisan queue:restart

# Horizon
php artisan horizon:terminate
php artisan horizon
```

## 📚 Recursos

- **Cloudflare API Docs:** https://developers.cloudflare.com/api/
- **Let's Encrypt:** https://letsencrypt.org/docs/
- **Laravel Queues:** https://laravel.com/docs/queues
- **Nginx SSL Config:** https://ssl-config.mozilla.org/

## 🎯 Próximos Passos Sugeridos

- [ ] Dashboard de status SSL/DNS
- [ ] Notificações por email quando certificado expira
- [ ] Suporte a wildcard certificates
- [ ] Backup automático de certificados
- [ ] API REST para gerenciamento remoto
- [ ] Webhook Cloudflare para eventos
- [ ] Métricas de performance SSL (handshake time)
- [ ] Suporte a múltiplos domínios por site

## 🤝 Contribuindo

Este é um projeto privado, mas sugestões são bem-vindas!

## 📝 Licença

Proprietário
