# Configuração do WebSocket Terminal (Real-time)

## Arquitetura da Aplicação

Esta é uma **aplicação SaaS multi-tenant**:

- **Admin/DevOps**: Configura infraestrutura via `.env` (banco de dados, Reverb, cache, etc.)
- **Usuários finais**: Cadastram seus próprios servidores através da **interface web**
- **Sem acesso ao .env**: Usuários nunca tocam em arquivos de configuração

## Como Funciona o Terminal WebSocket

### 1. Configuração do Servidor (Admin - Uma vez)

O Reverb (WebSocket server) roda na infraestrutura da aplicação. Adicione ao `.env`:

```env
# Broadcasting (Reverb WebSocket)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=pudim-app
REVERB_APP_KEY=local-key-12345
REVERB_APP_SECRET=local-secret-67890
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Frontend (Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Inicie o servidor WebSocket:**
```bash
php artisan reverb:start
```

Para produção, use supervisor:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 2. Uso pelos Usuários (Interface Web)

Os usuários cadastram servidores através da interface:

1. **Navegar**: Dashboard → Servidores → Adicionar Servidor
2. **Preencher formulário**:
   - Nome do servidor
   - IP/hostname
   - Porta SSH (padrão: 22)
   - Usuário SSH
   - Autenticação: Chave SSH ou Senha
   - Upload da chave privada (se usar chave)

3. **Dados salvos no banco**: `servers` table
   - `ip_address` → IP do servidor do usuário
   - `ssh_user` → usuário SSH (ex: ubuntu, root)
   - `ssh_key` → chave privada (criptografada)
   - `ssh_password` → senha (criptografada)
   - `user_id` → dono do servidor

### 3. Conexão Terminal

Quando o usuário abre o terminal:

1. **Frontend**: Conecta ao Reverb via Laravel Echo
2. **Backend**: 
   - Busca credenciais SSH do banco de dados (`Server` model)
   - Conecta ao servidor SSH do usuário usando `phpseclib`
   - Executa comandos
   - Envia output via WebSocket (evento `TerminalOutput`)
3. **Real-time**: Output aparece instantaneamente no navegador

## Fluxo de Dados

```
┌─────────────┐          ┌──────────────┐          ┌─────────────────┐
│   Browser   │          │   Laravel    │          │  Servidor SSH   │
│  (usuário)  │          │   + Reverb   │          │   do Usuário    │
└──────┬──────┘          └──────┬───────┘          └────────┬────────┘
       │                        │                           │
       │  1. WebSocket Connect  │                           │
       ├───────────────────────>│                           │
       │                        │                           │
       │  2. Comando: "ls -la"  │                           │
       ├───────────────────────>│  3. SSH Connect          │
       │                        ├──────────────────────────>│
       │                        │  (IP/user/key do banco)  │
       │                        │                           │
       │                        │  4. Executa "ls -la"     │
       │                        ├──────────────────────────>│
       │                        │                           │
       │                        │  5. Output do comando    │
       │  6. Broadcast output   │<──────────────────────────┤
       │<───────────────────────┤                           │
       │  (tempo real via WS)   │                           │
       └────────────────────────┴───────────────────────────┘
```

## Segurança

✅ **Cada usuário vê apenas seus servidores**
```php
// routes/channels.php
Broadcast::channel('terminal.{serverId}', function ($user, $serverId) {
    $server = Server::findOrFail($serverId);
    return $user->id === $server->user_id; // Autorização
});
```

✅ **Credenciais criptografadas no banco**
```php
// app/Models/Server.php
protected $casts = [
    'ssh_key' => 'encrypted',
    'ssh_password' => 'encrypted',
];
```

✅ **Isolamento por tenant**
- `user_id` e `team_id` em todas as queries
- Policies para autorização
- WebSocket channels privados

## Produção

### Supervisor (Reverb)

Crie `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
command=php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/html
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/reverb.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### Nginx (Proxy WebSocket)

```nginx
location /app {
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_pass http://127.0.0.1:8080;
}
```

## Resumo

| Configuração | Quem configura | Onde |
|-------------|----------------|------|
| **Reverb** (WebSocket server) | Admin/DevOps | `.env` (uma vez) |
| **Servidores SSH** | Cada usuário | Interface web (quantos quiser) |
| **Credenciais SSH** | Cada usuário | Formulário de servidor |
| **Conexões** | Automático | Backend busca do banco |

**Nada de .env para usuários finais!** Tudo pela interface web. 🎯
