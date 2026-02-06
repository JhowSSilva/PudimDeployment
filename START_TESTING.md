# 🎯 Como Começar a Testar - Guia Rápido

## Setup em 3 Passos

### 1. Preparar Ambiente
```bash
# Já foi feito! ✅
# - Migrations executadas
# - Rotas registradas
# - Comandos disponíveis
```

### 2. Configurar Variáveis (Opcional para primeiros testes)
```bash
# Copie o .env.example se não tiver .env
cp .env.example .env

# As variáveis importantes já estão configuradas
# Para testes básicos, não precisa alterar nada!
```

### 3. Testar!

## 🧪 Opções de Teste

### Opção A: Comandos Artisan (Mais Fácil)

```bash
# 1. Testar AI Optimize (não precisa de servidor real)
php artisan ai:optimize

# 2. Testar Track Usage
php artisan usage:track

# 3. Testar Security Scan
php artisan security:scan

# 4. Testar Database Backup
php artisan databases:backup

# 5. Gerar Invoices
php artisan invoices:generate
```

**Nota:** Estes comandos podem retornar "nenhum servidor encontrado" se você não tiver servidores cadastrados. Isso é normal!

### Opção B: Testar Interface (Se quiser ver visualmente)

1. **Inicie o servidor:**
```bash
php artisan serve
```

2. **Acesse:** http://localhost:8000

3. **Adicione os componentes Livewire em alguma view:**

Edite uma view existente (ex: dashboard ou página de servidor) e adicione:

```blade
{{-- Se tiver um objeto $server --}}
<livewire:servers.server-metrics :server="$server" />
<livewire:servers.performance-chart :server="$server" />
<livewire:servers.security-alerts :server="$server" />

{{-- Se tiver um objeto $team --}}
<livewire:billing.cost-forecast :team="$team" />
```

Ou use o arquivo de exemplo criado em:
`resources/views/servers/dashboard-example.blade.php`

### Opção C: Testar API (Para desenvolvedores)

Consulte o arquivo [API_TESTING.md](API_TESTING.md) com exemplos completos de cURL para todos os endpoints.

## ✅ Verificar se Tudo Está OK

Execute o script de teste:
```bash
./test-features.sh
```

Você deve ver algo como:
```
✓ Laravel está operacional
✓ config/server.php existe
✓ Migration add_new_features_tables está presente
✓ Todos os services estão OK
✓ Todos os controllers estão OK
✓ Todos os comandos disponíveis
...
```

## 🎯 Teste Rápido SEM Servidor Real

Se você ainda não configurou servidores, pode testar as funcionalidades assim:

### 1. Criar um Servidor de Teste no Banco

```bash
php artisan tinker
```

Depois, no console do tinker:
```php
$team = \App\Models\Team::first(); // ou crie um team

$server = \App\Models\Server::create([
    'team_id' => $team->id,
    'name' => 'Test Server',
    'ip_address' => '192.168.1.100',
    'cloud_provider' => 'custom',
    'region' => 'local',
    'status' => 'active',
    'memory' => 2048,
    'cpus' => 2,
    'disk_size' => 50,
]);

echo "Server ID: {$server->id}\n";
exit;
```

### 2. Agora Pode Testar os Comandos

```bash
# Com o ID do servidor que você criou
php artisan ai:optimize --server_id=1
php artisan security:scan --server_id=1
```

### 3. Ou Testar a API

```bash
# Substitua 1 pelo ID do seu servidor
curl -X POST http://localhost:8000/api/servers/1/ai/predict-load \
  -H "Authorization: Bearer SEU_TOKEN"
```

## 📊 O Que Cada Teste Vai Mostrar

| Comando | O Que Faz | O Que Esperar |
|---------|-----------|---------------|
| `ai:optimize` | Análise de recursos com IA | Predições, recomendações, alertas |
| `security:scan` | Scan de segurança | Status de rootkit/malware scan (pode falhar se não tiver SSH) |
| `usage:track` | Rastrear uso para billing | Registros na tabela usage_metrics |
| `databases:backup` | Backup de databases | Arquivos .sql.gz (precisa de database real) |
| `invoices:generate` | Gerar faturas | Invoices na tabela invoices |

## 🚨 Erros Comuns e Soluções

### "No servers found"
✅ **Normal!** Crie um servidor de teste como mostrado acima.

### "SSH connection failed"
✅ **Normal para testes locais!** As funções que requerem SSH real vão falhar, mas você pode ver a lógica funcionando.

### "Table not found"
❌ Execute: `php artisan migrate`

### "Class not found"
❌ Execute: `composer dump-autoload`

## 💡 Próximo Nível

Depois de testar localmente:

1. **Configure um servidor real** (DigitalOcean, AWS, etc)
2. **Configure as credenciais SSH** no modelo Server
3. **Teste as funcionalidades reais** de firewall, cache, deploy
4. **Configure webhooks** (Slack/Discord) para notificações
5. **Ative o scheduler** do Laravel para automação

## 📚 Documentação Completa

- [QUICK_START.md](QUICK_START.md) - Início em 5 minutos
- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Guia completo
- [API_TESTING.md](API_TESTING.md) - Testes de API
- [READY_TO_TEST.md](READY_TO_TEST.md) - Status da implementação

## 🎉 Pronto!

Escolha uma das opções acima e comece a testar!

**Recomendação:** Comece com a **Opção A** (Comandos Artisan) para ver tudo funcionando rapidamente.
