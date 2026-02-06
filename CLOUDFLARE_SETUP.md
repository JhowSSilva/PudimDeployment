# Cloudflare API Token - Guia de Configuração

## 📋 Como Obter o Cloudflare API Token

### Passo 1: Acessar o Dashboard Cloudflare
1. Acesse https://dash.cloudflare.com
2. Faça login na sua conta Cloudflare

### Passo 2: Criar API Token
1. No canto superior direito, clique no seu **perfil** (ícone de usuário)
2. Selecione **"My Profile"**
3. No menu lateral, clique em **"API Tokens"**
4. Clique no botão **"Create Token"**

### Passo 3: Configurar Permissões do Token

#### Opção A: Usar Template "Edit zone DNS" (Recomendado)
1. Procure o template **"Edit zone DNS"**
2. Clique em **"Use template"**
3. Configure as permissões:
   - **Zone → DNS → Edit**
   - **Zone → Zone → Read**
   - **Zone → SSL and Certificates → Edit** (para Origin Certificates)

#### Opção B: Criar Token Personalizado
1. Clique em **"Create Custom Token"**
2. Configure as seguintes permissões:

**Permissions:**
```
Zone → Zone → Read
Zone → DNS → Edit
Zone → SSL and Certificates → Edit
Account → Account Settings → Read
```

**Zone Resources:**
```
Include → All zones from an account → (Selecione sua conta)
```
OU
```
Include → Specific zone → exemplo.com.br
```

**Client IP Address Filtering (Opcional):**
- Deixe em branco para permitir de qualquer IP
- OU adicione o IP do seu servidor para maior segurança

**TTL (Time to Live):**
- Deixe em branco para token permanente
- OU defina uma data de expiração

### Passo 4: Gerar e Copiar Token
1. Clique em **"Continue to summary"**
2. Revise as permissões
3. Clique em **"Create Token"**
4. **IMPORTANTE:** Copie o token imediatamente (ele só será exibido uma vez!)

Exemplo de token:
```
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Passo 5: Configurar no Laravel

Adicione o token no arquivo `.env`:

```env
CLOUDFLARE_API_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
CLOUDFLARE_ACCOUNT_ID=1234567890abcdef (opcional)
CLOUDFLARE_ZONE_ID=abcdef1234567890 (opcional)
```

#### Como encontrar Account ID e Zone ID:

**Account ID:**
1. Dashboard Cloudflare
2. Clique em qualquer domínio
3. No menu direito, role para baixo
4. Encontre **"Account ID"**

**Zone ID:**
1. Dashboard Cloudflare
2. Clique no domínio desejado
3. Na aba **"Overview"**, role para baixo
4. Lado direito, encontre **"Zone ID"**

### Passo 6: Testar a Conexão

Execute o comando artisan para testar:

```bash
php artisan tinker

# No tinker:
$cf = app(\App\Services\CloudflareService::class);
$cf->verifyToken(); // Deve retornar true

# Listar zonas:
$cf->listZones();
```

## 🔒 Segurança do Token

### ✅ Boas Práticas:

1. **Nunca** commite o token no Git
2. Use `.env` para armazenar (já está no `.gitignore`)
3. No servidor de produção, configure variáveis de ambiente
4. Considere criar tokens diferentes para dev/staging/production
5. Adicione restrição de IP quando possível
6. Defina TTL (expiração) para tokens não-produção

### 🔄 Rotação de Tokens:

Recomenda-se rotacionar (trocar) tokens periodicamente:

1. Crie um novo token
2. Atualize `.env` com o novo token
3. Teste a aplicação
4. Revogue o token antigo no dashboard Cloudflare

## 📊 Permissões Explicadas

### Zone → DNS → Edit
- **Permite:** Criar, atualizar e deletar registros DNS
- **Usado para:** Configuração automática de DNS ao criar sites

### Zone → SSL and Certificates → Edit
- **Permite:** Criar Origin Certificates
- **Usado para:** Gerar certificados SSL Cloudflare (15 anos)

### Zone → Zone → Read
- **Permite:** Ler informações da zona (domínio)
- **Usado para:** Listar zonas e obter IDs

## 🚀 Funcionalidades Disponíveis

Com o token configurado, o sistema pode:

✅ **DNS Automático:**
- Criar registro A apontando para IP do servidor
- Ativar/desativar proxy Cloudflare
- Atualizar registros DNS automaticamente

✅ **SSL Automático:**
- Gerar Cloudflare Origin Certificate (15 anos)
- Gerar Let's Encrypt (90 dias, renovação automática)
- Configurar Nginx com SSL/TLS

✅ **Gerenciamento:**
- Listar todos os domínios da conta
- Purgar cache do Cloudflare
- Ver analytics das zonas

## 🐛 Troubleshooting

### Erro: "Invalid API Token"
- Verifique se o token foi copiado corretamente
- Confirme que o token não expirou
- Verifique as permissões do token

### Erro: "Zone not found"
- Certifique-se que o domínio está adicionado na Cloudflare
- Verifique se o token tem permissão para acessar a zona
- Confirme que os nameservers do domínio apontam para Cloudflare

### Erro: "Rate limit exceeded"
- Aguarde alguns minutos
- Cloudflare tem limite de 1200 requests por 5 minutos

## 📝 Exemplo de Uso

```php
// No controller ao criar site:
$site = Site::create([
    'domain' => 'exemplo.com.br',
    'server_id' => $server->id,
    'auto_dns' => true,
    'cloudflare_proxy' => true,
    'ssl_type' => 'cloudflare',
    // ... outros campos
]);

// O sistema automaticamente:
// 1. Encontra a zona do domínio
// 2. Cria registro A apontando para IP do servidor
// 3. Aguarda propagação DNS
// 4. Gera certificado SSL Cloudflare
// 5. Instala certificado no servidor
// 6. Configura Nginx com HTTPS
// 7. Recarrega Nginx
```

## 📞 Recursos Adicionais

- **Documentação API:** https://developers.cloudflare.com/api/
- **SDK PHP:** https://github.com/cloudflare/cloudflare-php
- **Limits:** https://developers.cloudflare.com/fundamentals/api/reference/limits/
- **Community:** https://community.cloudflare.com/
