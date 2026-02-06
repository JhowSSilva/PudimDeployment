# Sistema Multi-Tenant SaaS - Arquitetura de Dados

## Princípio Fundamental

**Esta é uma aplicação SaaS multi-tenant onde:**
- ✅ **Admin/DevOps** configura infraestrutura (`.env`)
- ✅ **Usuários finais** cadastram dados via interface web
- ❌ **Usuários NUNCA** acessam arquivos de configuração

## Separação de Responsabilidades

### 1. Configuração do Admin (`.env`)

Configurações gerais da **infraestrutura** da aplicação:

```env
# Banco de dados da aplicação (não dos usuários!)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=pudim_deployment

# WebSocket Server (compartilhado por todos os usuários)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=pudim-app
REVERB_APP_KEY=local-key-12345

# Cache, Queue, Mail (serviços da aplicação)
CACHE_STORE=redis
QUEUE_CONNECTION=database
MAIL_MAILER=smtp

# AWS/Cloud (credenciais do ADMIN para provisionar recursos)
AWS_ACCESS_KEY_ID=AKIAXXXXX
AWS_SECRET_ACCESS_KEY=xxxxx
```

### 2. Dados dos Usuários (Banco de Dados)

Cada usuário cadastra **seus próprios recursos** através da interface:

#### Servidores SSH (`servers` table)
```php
[
    'user_id' => 123,              // Dono do servidor
    'name' => 'Prod Server',
    'ip_address' => '192.168.1.10',
    'ssh_user' => 'ubuntu',
    'ssh_key' => 'encrypted_private_key',  // Criptografado!
    'ssh_password' => 'encrypted_pass',    // Criptografado!
]
```

#### Credenciais AWS (`aws_credentials` table)
```php
[
    'user_id' => 123,
    'name' => 'My AWS Account',
    'access_key_id' => 'AKIAUSER123',      // Do usuário, não do admin!
    'secret_access_key' => 'encrypted',    // Criptografado!
    'region' => 'us-east-1'
]
```

#### Backups (`backup_configurations` table)
```php
[
    'user_id' => 123,
    'server_id' => 456,           // FK para server do USUÁRIO
    'storage_provider' => 's3',
    'storage_config' => [
        'bucket' => 'user-backups-bucket',
        'key' => 'encrypted_access_key',
        'secret' => 'encrypted_secret_key'
    ]
]
```

#### GitHub Tokens (`github_personal_access_tokens` table)
```php
[
    'user_id' => 123,
    'token' => 'ghp_encrypted_token',  // Token do usuário
    'scopes' => ['repo', 'workflow']
]
```

## Fluxo de Trabalho

### Exemplo: Terminal SSH

1. **Admin** configura `.env` (uma vez):
```env
REVERB_APP_KEY=xyz
REVERB_HOST=localhost
```

2. **Usuário** cadastra servidor (interface web):
- Nome: "Production Server"
- IP: 203.0.113.45
- Usuário SSH: ubuntu
- Upload da chave privada

3. **Sistema** salva no banco:
```php
Server::create([
    'user_id' => auth()->id(),      // Isola por usuário!
    'ip_address' => '203.0.113.45',
    'ssh_key' => encrypt($privateKey)
]);
```

4. **Conexão** usa dados do banco:
```php
// app/Services/TerminalService.php
public function connect(): bool {
    $server = Server::where('user_id', auth()->id())
                    ->findOrFail($id);
    
    $ssh = new SSH2($server->ip_address);
    $key = decrypt($server->ssh_key);  // Descriptografa
    
    return $ssh->login($server->ssh_user, $key);
}
```

### Exemplo: Backups

1. **Admin**: Nada a configurar (jobs já estão no código)

2. **Usuário A** cadastra backup:
- Servidor: "Prod DB"
- Destino: AWS S3 + suas próprias credenciais
- Agendamento: diário às 3h

3. **Usuário B** cadastra backup:
- Servidor: "Staging API"
- Destino: Google Cloud Storage + suas credenciais
- Agendamento: semanal aos domingos

4. **Sistema** executa isoladamente:
```php
// Jobs rodam para cada usuário separadamente
BackupConfiguration::where('user_id', $userId)
    ->where('is_active', true)
    ->each(fn($config) => dispatch(new ExecuteBackupJob($config)));
```

## Segurança (Isolamento por Tenant)

### Queries sempre filtradas por `user_id`

```php
// ❌ ERRADO - Sem filtro de usuário
Server::all();

// ✅ CORRETO - Filtra por usuário autenticado
Server::where('user_id', auth()->id())->get();

// ✅ MELHOR - Usa global scope (auto-filtra)
class Server extends Model {
    protected static function booted() {
        static::addGlobalScope('user', function ($query) {
            $query->where('user_id', auth()->id());
        });
    }
}
```

### Policies verificam ownership

```php
// app/Policies/ServerPolicy.php
public function view(User $user, Server $server) {
    return $user->id === $server->user_id;
}
```

### Credenciais criptografadas

```php
// app/Models/Server.php
protected $casts = [
    'ssh_key' => 'encrypted',
    'ssh_password' => 'encrypted',
];

// Salva criptografado automaticamente
$server->ssh_key = $privateKey;
$server->save();

// Lê descriptografado automaticamente
$key = $server->ssh_key;
```

### WebSocket Channels privados

```php
// routes/channels.php
Broadcast::channel('terminal.{serverId}', function ($user, $serverId) {
    $server = Server::findOrFail($serverId);
    return $user->id === $server->user_id;  // Autorização!
});
```

## Resumo Geral

| Recurso | Quem Configura | Onde | Isolamento |
|---------|----------------|------|-----------|
| **Infraestrutura** | Admin | `.env` | Global |
| **Servidores SSH** | Cada usuário | Interface → DB | `user_id` |
| **Credenciais Cloud** | Cada usuário | Interface → DB | `user_id` + encryption |
| **Backups** | Cada usuário | Interface → DB | `user_id` + policies |
| **GitHub Tokens** | Cada usuário | Interface → DB | `user_id` + encryption |
| **WebSocket** | Admin (infra) | `.env` | Channels privados |

## Benefícios

✅ **Escalável**: Cada usuário opera independentemente  
✅ **Seguro**: Criptografia + isolamento + policies  
✅ **Self-service**: Usuários não precisam de admin  
✅ **Multi-tenant**: Mesma aplicação, dados isolados  
✅ **Auditável**: Todos os dados têm `user_id`  

## Anti-Padrões (Evitar!)

❌ Credenciais de usuários no `.env`  
❌ Queries sem filtro de `user_id`  
❌ Senhas sem criptografia  
❌ Recursos compartilhados entre usuários  
❌ Usuários editando arquivos de configuração  

**Regra de ouro: Se é dado do usuário, vai no banco. Se é configuração da aplicação, vai no `.env`.**
