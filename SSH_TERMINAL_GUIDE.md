# Terminal SSH - Guia de Instalação e Uso

## 📋 Visão Geral

Sistema completo de Terminal SSH integrado à aplicação web, permitindo acesso remoto a servidores via navegador com gerenciamento de chaves SSH.

## 🚀 Instalação

### 1. Instalar Dependências PHP

```bash
composer install
```

Isso instalará:
- `phpseclib/phpseclib` (v3.0) - Biblioteca SSH para PHP
- `cboden/ratchet` (v0.4.4) - WebSocket server

### 2. Executar Migrations

```bash
php artisan migrate
```

Isso criará as tabelas:
- `ssh_keys` - Armazena chaves SSH
- `servers` - Informações de servidores (adiciona campo `default_key_id`)
- `ssh_connection_logs` - Logs de conexões SSH

### 3. Configurar Variáveis de Ambiente

Adicione ao seu arquivo `.env`:

```env
# WebSocket Configuration
WEBSOCKET_HOST=localhost
WEBSOCKET_PORT=8080

# A chave APP_KEY já existente será usada para criptografia
APP_KEY=base64:sua_chave_aqui
```

### 4. Gerar Chave de Aplicação (se ainda não tiver)

```bash
php artisan key:generate
```

## 🖥️ Executar WebSocket Server

### Desenvolvimento

Execute o comando:

```bash
php websocket-server.php
```

Você verá:
```
========================================
SSH Terminal WebSocket Server
========================================
Server running on port 8080
Press Ctrl+C to stop
========================================
```

### Produção (com Supervisor)

1. Copie o arquivo de configuração do Supervisor:

```bash
sudo cp ssh-terminal-websocket.conf /etc/supervisor/conf.d/
```

2. Atualize o caminho no arquivo se necessário:

```bash
sudo nano /etc/supervisor/conf.d/ssh-terminal-websocket.conf
```

Altere:
- `command` - Caminho completo para o projeto
- `directory` - Diretório do projeto
- `user` - Usuário que executará o processo

3. Recarregue e inicie o Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ssh-terminal-websocket
```

4. Verificar status:

```bash
sudo supervisorctl status ssh-terminal-websocket
```

## 📱 Uso da Aplicação

### 1. Gerenciar Chaves SSH

Acesse: `http://seu-dominio/ssh/keys`

**Gerar Nova Chave:**
- Clique em "Gerar Nova Chave"
- Preencha:
  - Nome da chave (ex: "servidor_producao")
  - Tipo: RSA 4096 ou ED25519
  - Passphrase (opcional, mas recomendado)
  - Comentário/Email
- Clique em "Gerar Chave"
- A chave pública será exibida automaticamente

**Importar Chave Existente:**
- Clique em "Importar Chave"
- Cole sua chave privada
- Forneça a passphrase se a chave for protegida
- Clique em "Importar"

**Visualizar Chave Pública:**
- Clique em "Ver Chave Pública" em qualquer chave
- Copie a chave para adicionar ao servidor remoto

### 2. Configurar Servidores

(Use a interface existente de servidores)

Certifique-se de ter:
- Nome do servidor
- IP/Host
- Porta SSH (padrão: 22)
- Usuário SSH
- Chave SSH padrão (opcional)

### 3. Usar Terminal SSH

Acesse: `http://seu-dominio/ssh/terminal`

**Conectar:**
1. Selecione um servidor no dropdown
2. Selecione uma chave SSH
3. Clique em "Conectar"
4. Aguarde a conexão ser estabelecida

**Usar Terminal:**
- Digite comandos normalmente
- Use setas ↑↓ para histórico
- Ctrl+C para interromper comandos
- Suporte completo a cores ANSI
- Redimensionamento automático

**Desconectar:**
- Clique em "Desconectar"
- Ou feche a aba (conexão será encerrada automaticamente)

## 🔐 Segurança

### Chaves SSH

- **Criptografia:** Chaves privadas são criptografadas com AES-256-GCM antes de serem armazenadas
- **Chave de Criptografia:** Usa `APP_KEY` do Laravel
- **Passphrase:** Suporte a chaves protegidas por passphrase
- **Fingerprint:** SHA256 para identificação única

### Conexões SSH

- **Autenticação:** Por chave SSH ou senha
- **Logs:** Todas as conexões são registradas com timestamp, IP, status
- **Timeout:** Conexões inativas são encerradas automaticamente
- **Isolamento:** Cada usuário só acessa seus próprios servidores e chaves

### WebSocket

- **Autenticação:** Requer token CSRF válido
- **Sessões:** Vinculadas ao usuário autenticado
- **Validação:** Verifica permissões antes de conectar a servidores

## 📊 Monitoramento

### Logs do WebSocket

Visualize logs em tempo real:

```bash
tail -f /var/log/ssh-terminal-websocket.log
```

### Logs de Conexão SSH

Acesse via interface web ou API:

```bash
GET /api/ssh/logs?limit=50
```

Retorna:
- Servidor conectado
- Chave SSH usada
- Timestamp de conexão/desconexão
- Status (success, failed, disconnected)
- Mensagens de erro

### Supervisor

Monitorar processo:

```bash
sudo supervisorctl status ssh-terminal-websocket
sudo supervisorctl tail ssh-terminal-websocket
```

## 🛠️ Troubleshooting

### WebSocket não conecta

1. Verifique se o servidor WebSocket está rodando:
```bash
ps aux | grep websocket-server
```

2. Verifique a porta:
```bash
netstat -tlnp | grep 8080
```

3. Teste conexão manualmente:
```bash
telnet localhost 8080
```

### Erro de autenticação SSH

1. Verifique se a chave pública está no servidor remoto:
```bash
cat ~/.ssh/authorized_keys
```

2. Adicione a chave pública ao servidor:
```bash
echo "sua_chave_publica" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

3. Verifique permissões SSH no servidor:
```bash
ls -la ~/.ssh
# .ssh deve ser 700
# authorized_keys deve ser 600
```

### Chave privada não descriptografa

1. Verifique se `APP_KEY` está configurada:
```bash
php artisan env
```

2. Regenere a chave se necessário (⚠️ isso invalidará chaves SSH armazenadas):
```bash
php artisan key:generate
```

### Terminal não exibe caracteres corretamente

1. Limpe o cache do navegador
2. Verifique se as fontes estão carregando (JetBrains Mono)
3. Teste em modo anônimo/privado

## 🔧 Comandos Úteis

### Limpar terminal
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Restart WebSocket Server
```bash
sudo supervisorctl restart ssh-terminal-websocket
```

### Ver logs em tempo real
```bash
# WebSocket
tail -f /var/log/ssh-terminal-websocket.log

# Laravel
tail -f storage/logs/laravel.log
```

## 📡 API Endpoints

### Chaves SSH

```bash
# Listar chaves
GET /api/ssh/keys

# Gerar chave
POST /api/ssh/keys/generate
{
  "name": "producao",
  "type": "rsa",
  "comment": "admin@example.com",
  "passphrase": "senha123"
}

# Importar chave
POST /api/ssh/keys/import
{
  "name": "minha_chave",
  "private_key": "-----BEGIN RSA PRIVATE KEY-----\n...",
  "passphrase": "senha123"
}

# Ver chave pública
GET /api/ssh/keys/{id}/public

# Deletar chave
DELETE /api/ssh/keys/{id}
```

### Logs

```bash
# Obter logs de conexão
GET /api/ssh/logs?limit=50
```

## 🎨 Personalização

### Tema do Terminal

Edite [public/js/ssh-terminal.js](public/js/ssh-terminal.js):

```javascript
theme: {
    background: '#000000',
    foreground: '#ffffff',
    cursor: '#D4A574',
    // ... outras cores
}
```

### Porta WebSocket

Altere `.env`:
```env
WEBSOCKET_PORT=8080
```

E atualize [public/js/ssh-terminal.js](public/js/ssh-terminal.js):
```javascript
const wsPort = 8080;
```

## 📄 Licença

Este módulo faz parte do projeto Agile Deployment.

## 🤝 Suporte

Para problemas ou dúvidas:
1. Verifique os logs
2. Consulte este guia
3. Abra uma issue no repositório
