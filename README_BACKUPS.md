# Sistema de Backups Automáticos

Sistema completo de backups automáticos para múltiplos tipos de databases com suporte a múltiplos cloud storage providers, similar ao Ploi, RunCloud e Laravel Forge.

## 📋 Índice

- [Features](#features)
- [Database Support](#database-support)
- [Storage Providers](#storage-providers)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Cloud Providers Setup](#cloud-providers-setup)
- [Usage](#usage)
- [Artisan Commands](#artisan-commands)
- [Notificações](#notificações)
- [Scheduler](#scheduler)
- [Troubleshooting](#troubleshooting)

## ✨ Features

- ✅ **Multi-Database Support**: PostgreSQL, MySQL, MongoDB, Redis
- ✅ **Multi-Cloud Storage**: AWS S3, Azure Blob Storage, Google Cloud Storage, DigitalOcean Spaces, Backblaze B2, Wasabi, MinIO, Local
- ✅ **Compressão**: ZIP (AES-256), TAR, TAR.GZ, TAR.BZ2, None
- ✅ **Criptografia**: AES-256 para compressão ZIP
- ✅ **Scheduler**: Automação com cron (minutely, hourly, daily, weekly, monthly)
- ✅ **Retention Policies**: Limpeza automática de backups antigos
- ✅ **Notificações**: Email, Webhooks, Slack, Discord
- ✅ **Web UI**: Interface completa de gerenciamento
- ✅ **Artisan Commands**: Controle via CLI
- ✅ **Queue Support**: Processamento assíncrono com Laravel Queues
- ✅ **Event-Driven**: Sistema de eventos para extensibilidade
- ✅ **Multi-Tenant**: Suporte para times/teams
- ✅ **Metrics & Monitoring**: Success rate, file sizes, durations
- ✅ **File Integrity**: Checksums SHA-256

## 🗄️ Database Support

### PostgreSQL
- ✅ Totalmente implementado
- ✅ pg_dump com compressão nativa
- ✅ Suporte para exclusões (schemas, tables)
- ✅ Password via PGPASSWORD env

### MySQL
- ✅ Totalmente implementado
- ✅ mysqldump com routines, triggers, eventos
- ✅ Single transaction para consistência
- ✅ Suporte para exclusões

### MongoDB
- ⚠️ Stub implementado (requer mongodump)
- 📝 TODO: Implementar integração completa

### Redis
- ⚠️ Stub implementado (requer redis-cli)
- 📝 TODO: Implementar integração completa

## ☁️ Storage Providers

| Provider | Status | Driver | Configuração |
|----------|--------|--------|--------------|
| AWS S3 | ✅ | s3 | AWS_* vars |
| Azure Blob | ✅ | azure | AZURE_* vars |
| Google Cloud Storage | ✅ | gcs | GCS_* vars |
| DigitalOcean Spaces | ✅ | s3 | DO_* vars |
| Backblaze B2 | ✅ | s3 | B2_* vars |
| Wasabi | ✅ | s3 | WASABI_* vars |
| MinIO | ✅ | s3 | MINIO_* vars |
| Local | ✅ | local | - |

## 📦 Instalação

### 1. Instalar Dependências

```bash
# Composer packages
composer require aws/aws-sdk-php:^3.0
composer require league/flysystem-aws-s3-v3:^3.0
composer require league/flysystem-azure-blob-storage:^3.0
composer require google/cloud-storage:^1.30
composer require microsoft/azure-storage-blob:^1.5

# Para PostgreSQL
sudo apt-get install postgresql-client

# Para MySQL
sudo apt-get install mysql-client

# Para MongoDB (opcional)
# sudo apt-get install mongodb-database-tools

# Para Redis (opcional)
# sudo apt-get install redis-tools
```

### 2. Rodar Migrations

```bash
php artisan migrate
```

### 3. Registrar Event Listeners

Adicione no `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \App\Events\BackupCompleted::class => [
        \App\Listeners\SendBackupCompletedNotification::class,
    ],
    \App\Events\BackupFailed::class => [
        \App\Listeners\SendBackupFailedNotification::class,
    ],
];
```

### 4. Configurar Scheduler

Adicione no `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Check for due backups every minute
    $schedule->command('backups:schedule')->everyMinute();
    
    // Cleanup old backups daily at 3 AM
    $schedule->command('backups:cleanup')->daily()->at('03:00');
    
    // Cleanup old job records weekly
    $schedule->command('backups:cleanup-records')->weekly();
}
```

### 5. Configurar Queue Worker

```bash
# Supervisor config em /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600

# Recarregar supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 6. Configurar Cron

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## ⚙️ Configuração

### .env Configuration

```env
# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration (para notificações)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="backup@yourapp.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 🔧 Cloud Providers Setup

### AWS S3

1. **Criar IAM User com política:**

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:GetObject",
                "s3:DeleteObject",
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::your-bucket-name",
                "arn:aws:s3:::your-bucket-name/*"
            ]
        }
    ]
}
```

2. **Adicionar ao .env:**

```env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_USE_PATH_STYLE_ENDPOINT=false
```

3. **Na UI**: Selecione "AWS S3" e preencha:
   - Bucket name
   - Region
   - Access key
   - Secret key
   - Path (opcional)

### Azure Blob Storage

1. **Criar Storage Account e Container**

2. **Obter Connection String** em "Access Keys"

3. **Adicionar ao .env:**

```env
AZURE_STORAGE_NAME=your-account-name
AZURE_STORAGE_KEY=your-account-key
AZURE_STORAGE_CONTAINER=backups
```

4. **Na UI**: Selecione "Azure Blob Storage" e preencha:
   - Container name
   - Account name
   - Account key
   - Path (opcional)

### Google Cloud Storage

1. **Criar Bucket no GCS**

2. **Criar Service Account com role "Storage Object Admin"**

3. **Download JSON key file**

4. **Adicionar ao .env:**

```env
GCS_PROJECT_ID=your-project-id
GCS_KEY_FILE=/path/to/service-account.json
GCS_BUCKET=your-bucket-name
```

5. **Na UI**: Selecione "Google Cloud Storage" e preencha:
   - Bucket name
   - Project ID
   - Service account JSON (conteúdo completo)
   - Path (opcional)

### DigitalOcean Spaces

1. **Criar Space e API Key**

2. **Adicionar ao .env:**

```env
DO_SPACES_KEY=your-key
DO_SPACES_SECRET=your-secret
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=your-space-name
```

3. **Na UI**: Selecione "DigitalOcean Spaces" e preencha:
   - Space name
   - Region (nyc3, ams3, sgp1, sfo3)
   - Access key
   - Secret key
   - Endpoint (opcional)

### Backblaze B2

1. **Criar Bucket e Application Key**

2. **Configurar na UI**: Selecione "Backblaze B2" e preencha:
   - Bucket name
   - Key ID
   - Application key
   - Endpoint: `https://s3.us-west-002.backblazeb2.com` (altere região)

### Wasabi

1. **Criar Bucket e Access Keys**

2. **Configurar na UI**: Selecione "Wasabi" e preencha:
   - Bucket name
   - Region (us-east-1, us-west-1, eu-central-1, ap-northeast-1)
   - Access key
   - Secret key
   - Endpoint: `https://s3.wasabisys.com` ou `https://s3.us-west-1.wasabisys.com`

### MinIO

1. **Instalar MinIO:**

```bash
docker run -d \
  -p 9000:9000 \
  -p 9001:9001 \
  --name minio \
  -e "MINIO_ROOT_USER=minioadmin" \
  -e "MINIO_ROOT_PASSWORD=minioadmin" \
  -v /data:/data \
  minio/minio server /data --console-address ":9001"
```

2. **Criar Bucket via console (http://localhost:9001)**

3. **Configurar na UI**: Selecione "MinIO" e preencha:
   - Bucket name
   - Endpoint: `http://localhost:9000`
   - Access key: minioadmin
   - Secret key: minioadmin

## 🚀 Usage

### Via Web UI

1. **Navegar para** `/backups`
2. **Criar novo backup** → Botão "Create Backup"
3. **Preencher formulário**:
   - **Database Tab**: Servidor, tipo, credenciais
   - **Storage Tab**: Provider, credenciais, bucket
   - **Schedule Tab**: Frequência, timezone, retention
   - **Advanced Tab**: Compressão, criptografia, exclusões
   - **Notifications Tab**: Email, webhooks, Slack, Discord
4. **Salvar** → Backup será agendado automaticamente

### Via API

```bash
# Listar backups
curl -X GET http://yourapp.test/api/backups \
  -H "Authorization: Bearer YOUR_TOKEN"

# Criar backup
curl -X POST http://yourapp.test/api/backups \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Production DB Backup",
    "database_id": 1,
    "storage_provider": "s3",
    "storage_config": {
      "bucket": "my-backups",
      "region": "us-east-1",
      "key": "AKIA...",
      "secret": "secret..."
    },
    "frequency": "daily",
    "time": "02:00",
    "timezone": "America/New_York",
    "retention_days": 30
  }'

# Executar backup manualmente
curl -X POST http://yourapp.test/api/backups/1/run \
  -H "Authorization: Bearer YOUR_TOKEN"

# Listar arquivos de backup
curl -X GET http://yourapp.test/api/backups/1/files \
  -H "Authorization: Bearer YOUR_TOKEN"

# Download de backup
curl -X GET http://yourapp.test/api/backups/files/1/download \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output backup.zip
```

## 🔧 Artisan Commands

### backups:schedule

Verifica backups agendados e dispara jobs (roda via cron a cada minuto).

```bash
php artisan backups:schedule
```

### backups:run

Executa um backup manualmente.

```bash
# Executar backup específico
php artisan backups:run 1

# Executar de forma síncrona (sem queue)
php artisan backups:run 1 --sync

# Output
┌─────────────────────────┬──────────────────────────────────┐
│ Configuration           │ Production DB Backup             │
│ Database                │ PostgreSQL (production_db)       │
│ Storage                 │ AWS S3 (my-backups)              │
│ Status                  │ ✅ Success                       │
│ Duration                │ 45.2 seconds                     │
│ File Size               │ 125.4 MB                         │
│ Backup File             │ backup_20240206_123045.zip       │
└─────────────────────────┴──────────────────────────────────┘
```

### backups:list

Lista todas as configurações de backup.

```bash
# Listar todos
php artisan backups:list

# Filtrar por status
php artisan backups:list --status=active

# Filtrar por team
php artisan backups:list --team=1

# Output
┌────┬──────────────────────┬──────────┬──────────┬────────────────┬──────────┐
│ ID │ Name                 │ Database │ Provider │ Frequency      │ Status   │
├────┼──────────────────────┼──────────┼──────────┼────────────────┼──────────┤
│ 1  │ Production DB        │ postgres │ s3       │ daily @ 02:00  │ active   │
│ 2  │ Analytics DB         │ mysql    │ azure    │ weekly @ 03:00 │ active   │
│ 3  │ Redis Cache          │ redis    │ spaces   │ hourly         │ paused   │
└────┴──────────────────────┴──────────┴──────────┴────────────────┴──────────┘
```

### backups:cleanup

Limpa backups antigos baseado nas retention policies.

```bash
# Limpar todos os backups expirados
php artisan backups:cleanup

# Limpar de uma configuração específica
php artisan backups:cleanup --config=1
```

### backups:cleanup-records

Limpa registros de jobs de backup com mais de 90 dias.

```bash
php artisan backups:cleanup-records
```

## 🔔 Notificações

### Email

Configuração no formulário:

```php
[
    'email' => [
        'enabled' => true,
        'recipients' => ['admin@example.com', 'devops@example.com'],
        'on_success' => true,
        'on_failure' => true
    ]
]
```

### Webhook

Envia POST request com JSON:

```json
{
    "event": "backup.completed",
    "backup": {
        "id": 1,
        "name": "Production DB Backup",
        "status": "success"
    },
    "job": {
        "id": 123,
        "size": 125400000,
        "duration": 45,
        "file": "backup_20240206_123045.zip"
    }
}
```

Configuração:

```php
[
    'webhook' => [
        'enabled' => true,
        'url' => 'https://yourapp.com/webhooks/backup',
        'secret' => 'webhook-secret-key',
        'on_success' => true,
        'on_failure' => true
    ]
]
```

### Slack

```php
[
    'slack' => [
        'enabled' => true,
        'webhook_url' => 'https://hooks.slack.com/services/YOUR/WEBHOOK/URL',
        'channel' => '#backups',
        'on_success' => false,  // Apenas falhas
        'on_failure' => true
    ]
]
```

### Discord

```php
[
    'discord' => [
        'enabled' => true,
        'webhook_url' => 'https://discord.com/api/webhooks/YOUR/WEBHOOK',
        'on_success' => false,
        'on_failure' => true
    ]
]
```

## ⏱️ Scheduler

### Frequências Suportadas

- **Minutely**: A cada minuto
- **Hourly**: A cada hora (especificar minuto)
- **Daily**: Diariamente (especificar hora)
- **Weekly**: Semanalmente (especificar dia e hora)
- **Monthly**: Mensalmente (especificar dia e hora)

### Timezone Support

Todos os backups respeitam o timezone configurado:

```php
'timezone' => 'America/New_York',  // EST/EDT
'timezone' => 'America/Sao_Paulo', // BRT/BRST
'timezone' => 'Europe/London',     // GMT/BST
'timezone' => 'Asia/Tokyo',        // JST
```

### Retention Policies

Backups antigos são deletados automaticamente:

- `retention_days`: Número de dias para manter backups
- Cleanup automático diariamente às 3 AM
- Deleta do storage provider E da database

## 🐛 Troubleshooting

### Backup não executa

1. **Verificar queue worker:**
```bash
sudo supervisorctl status laravel-worker
```

2. **Verificar logs:**
```bash
tail -f storage/logs/laravel.log
```

3. **Verificar scheduler:**
```bash
php artisan schedule:list
```

### Connection refused (Database)

- Verificar se servidor está acessível
- Verificar firewall/security groups
- Testar com: `php artisan backups:test-connection {id}`

### Upload falha (Storage)

1. **Verificar credenciais:**
```bash
php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'test');
```

2. **Verificar permissões IAM/Bucket**

3. **Aumentar timeout** em `config/filesystems.php`:
```php
'options' => [
    'timeout' => 300, // 5 minutos
],
```

### Out of memory

Para databases grandes, aumentar memory limit:

```bash
php -d memory_limit=4G artisan backups:run 1 --sync
```

Ou no código (`BackupService.php`):
```php
ini_set('memory_limit', '4G');
```

### Slow backups

1. **Use compressão nativa do database** (postgres custom format)
2. **Exclua tabelas grandes desnecessárias**
3. **Aumente workers do queue**
4. **Use SSD para storage temporário**

## 📊 Monitoring

### Metrics Disponíveis

Cada backup configuration rastreia:

- `last_backup_at`: Timestamp do último backup
- `next_backup_at`: Timestamp do próximo backup agendado
- `last_backup_size`: Tamanho do último backup em bytes
- `total_backups`: Total de backups realizados
- `successful_backups`: Backups bem-sucedidos
- `failed_backups`: Backups com falha
- `success_rate`: Porcentagem de sucesso (calculated attribute)

### Queries Úteis

```php
// Backups que falharam na última execução
BackupConfiguration::where('status', 'failed')->get();

// Backups com success rate baixo
BackupConfiguration::all()->filter(fn($b) => $b->success_rate < 90);

// Total de storage usado
BackupFile::sum('file_size');

// Backups mais executados
BackupConfiguration::orderBy('total_backups', 'desc')->get();
```

## 🔐 Security

- ✅ Credenciais de database criptografadas no DB (`encrypted` cast)
- ✅ Compressão ZIP com AES-256 encryption
- ✅ Team-based authorization (BackupConfigurationPolicy)
- ✅ Senha de criptografia nunca logada
- ✅ SSH keys nunca expostas via API
- ✅ Webhook secrets para verificação de origem

## 📝 License

Este sistema é parte da aplicação PudimDeployment.

## 🤝 Contributing

Para adicionar novo storage provider:

1. Adicionar config em `config/backup-providers.php`
2. Criar disk config em `config/filesystems.php`
3. Instalar adapter necessário via composer
4. Adicionar case no `StorageManager::getDisk()`

Para adicionar novo database type:

1. Criar classe em `app/Services/Backup/DatabaseBackup/`
2. Implementar `DatabaseBackupInterface`
3. Adicionar case no `DatabaseBackupFactory`
4. Adicionar em `config/backup-providers.php`
