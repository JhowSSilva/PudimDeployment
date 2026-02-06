# GitHub Integration - Pudim Deployment

## 📋 Visão Geral

Sistema completo de gerenciamento de deploys PHP com integração ao GitHub, similar ao Ploi.io. Permite gerenciar repositórios, workflows, GitHub Pages e webhooks diretamente da interface do Pudim Deployment.

## ✨ Funcionalidades

### 1. **Autenticação GitHub**
- ✅ OAuth App do GitHub para autenticação
- ✅ Suporte a Personal Access Tokens
- ✅ Tokens armazenados criptografados no banco de dados
- ✅ Middleware de validação de token

### 2. **Gerenciamento de Repositórios**
- ✅ Listar todos os repositórios do usuário
- ✅ Sincronização automática de repositórios
- ✅ Filtros por linguagem e busca
- ✅ Visualizar informações detalhadas
- ✅ Configuração automática de webhooks

### 3. **GitHub Actions (Workflows)**
- ✅ Listar workflows de repositórios
- ✅ Visualizar runs de workflows
- ✅ Disparar workflows manualmente
- ✅ Cancelar workflows em execução
- ✅ Reexecutar workflows
- ✅ Gerenciar secrets do repositório
- ✅ Templates pré-configurados:
  - Laravel Deploy
  - Static Site (GitHub Pages)
  - Node.js Application
  - Docker Build & Push

### 4. **GitHub Pages**
- ✅ Ativar/Desativar GitHub Pages
- ✅ Configurar branch e path de deploy
- ✅ Suporte a domínios customizados
- ✅ Verificar status de builds
- ✅ Requisitar builds manualmente
- ✅ Logs de builds

### 5. **Webhook Handler**
- ✅ Endpoint para receber webhooks do GitHub
- ✅ Validação de assinatura HMAC SHA256
- ✅ Processamento assíncrono via Queue
- ✅ Suporte a eventos: push, workflow_run, page_build, deployment
- ✅ Logs de todos os eventos

## 🛠️ Instalação e Configuração

### 1. Instalar Dependências

```bash
composer require knplabs/github-api php-http/guzzle7-adapter
```

### 2. Rodar Migrations

```bash
php artisan migrate
```

As migrations criam as seguintes tabelas:
- `github_repositories` - Repositórios do GitHub
- `github_workflows` - Workflows/Actions
- `github_workflow_runs` - Execuções de workflows
- `github_webhook_events` - Eventos de webhooks
- `github_pages` - Configuração do GitHub Pages
- Adiciona campos GitHub na tabela `users`

### 3. Configurar GitHub OAuth App

1. Acesse: https://github.com/settings/developers
2. Clique em "New OAuth App"
3. Preencha:
   - **Application name:** Pudim Deployment
   - **Homepage URL:** `https://seu-dominio.com`
   - **Authorization callback URL:** `https://seu-dominio.com/github/callback`
4. Copie `Client ID` e `Client Secret`

### 4. Configurar Variáveis de Ambiente

Adicione no seu `.env`:

```env
GITHUB_CLIENT_ID=seu_client_id_aqui
GITHUB_CLIENT_SECRET=seu_client_secret_aqui
GITHUB_REDIRECT_URI="${APP_URL}/github/callback"
GITHUB_WEBHOOK_SECRET=seu_secret_aleatório_aqui
```

Gere um webhook secret:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### 5. Configurar Queue Worker

O processamento de webhooks usa filas. Configure no `.env`:

```env
QUEUE_CONNECTION=redis  # ou database
```

Rode o worker:
```bash
php artisan queue:work --tries=3
```

Ou use o Horizon (já incluído):
```bash
php artisan horizon
```

### 6. Registrar Políticas

Adicione em `App\Providers\AuthServiceProvider`:

```php
protected $policies = [
    GitHubRepository::class => GitHubRepositoryPolicy::class,
];
```

## 📡 Rotas Disponíveis

### Autenticação
- `GET /github/connect` - Redirecionar para OAuth GitHub
- `GET /github/callback` - Callback do OAuth
- `POST /github/disconnect` - Desconectar GitHub
- `POST /github/personal-token` - Salvar Personal Access Token

### Repositórios
- `GET /github/repositories` - Listar repositórios
- `POST /github/repositories/sync` - Sincronizar repositórios
- `GET /github/repositories/{id}` - Ver detalhes do repositório
- `POST /github/repositories/{id}/webhook` - Configurar webhook

### Workflows
- `GET /github/repositories/{id}/workflows` - Listar workflows
- `POST /github/repositories/{id}/workflows/sync` - Sincronizar workflows
- `POST /github/repositories/{id}/workflows/{workflowId}/dispatch` - Disparar workflow
- `POST /github/repositories/{id}/workflows/runs/{runId}/cancel` - Cancelar execução
- `POST /github/repositories/{id}/workflows/runs/{runId}/rerun` - Reexecutar workflow

### GitHub Pages
- `GET /github/repositories/{id}/pages` - Ver configuração do Pages
- `POST /github/repositories/{id}/pages/enable` - Ativar Pages
- `POST /github/repositories/{id}/pages/disable` - Desativar Pages
- `PUT /github/repositories/{id}/pages/update` - Atualizar configuração
- `POST /github/repositories/{id}/pages/build` - Requisitar build

### Webhooks
- `POST /webhook/github` - Endpoint para webhooks do GitHub

## 🔐 Segurança

### Criptografia de Tokens
Os tokens do GitHub são armazenados criptografados usando o Laravel Encryption:

```php
$user->setGitHubToken($token);  // Criptografa automaticamente
$token = $user->getGitHubToken();  // Descriptografa automaticamente
```

### Validação de Webhooks
Todos os webhooks são validados usando HMAC SHA256:

```php
GitHubService::verifyWebhookSignature($payload, $signature, $secret);
```

### Middleware de Autenticação
O middleware `EnsureGitHubTokenValid` garante que o usuário tem um token do GitHub válido antes de acessar recursos protegidos.

## 🎨 Templates de Workflow

### Laravel Deploy
```yaml
name: Laravel Deploy
# Executa testes e faz deploy via SSH
# Includes: composer install, migrations, cache clear
```

### Static Site (GitHub Pages)
```yaml
name: Deploy to GitHub Pages
# Build com Node.js e deploy automático
# Ideal para sites estáticos, Vue, React, etc
```

### Node.js Application
```yaml
name: Node.js CI
# Testes em múltiplas versões do Node
# Matrix strategy: 16.x, 18.x
```

### Docker Build & Push
```yaml
name: Docker Build and Push
# Build de imagem Docker
# Push para Docker Hub
```

## 📊 Models e Relacionamentos

```php
User
├── githubRepositories() -> GitHubRepository[]
└── hasGitHubConnected() -> bool

GitHubRepository
├── user() -> User
├── workflows() -> GitHubWorkflow[]
├── workflowRuns() -> GitHubWorkflowRun[]
├── pages() -> GitHubPages
└── webhookEvents() -> GitHubWebhookEvent[]

GitHubWorkflow
├── repository() -> GitHubRepository
└── runs() -> GitHubWorkflowRun[]

GitHubWorkflowRun
├── workflow() -> GitHubWorkflow
├── repository() -> GitHubRepository
├── isSuccess() -> bool
├── isFailed() -> bool
└── isRunning() -> bool

GitHubPages
├── repository() -> GitHubRepository
├── isBuilding() -> bool
└── getPublicUrl() -> string

GitHubWebhookEvent
├── repository() -> GitHubRepository
├── isPending() -> bool
└── isProcessed() -> bool
```

## 🚀 Uso Básico

### 1. Conectar ao GitHub

```php
// Via OAuth
return redirect()->route('github.connect');

// Ou via Personal Access Token
// Na UI: Settings -> GitHub -> Add Personal Token
```

### 2. Sincronizar Repositórios

```php
$service = new RepositoryService($user);
$repositories = $service->syncRepositories();
```

### 3. Configurar Webhook

```php
$service = new RepositoryService($user);
$service->setupWebhook($repository, route('github.webhook'), $secret);
```

### 4. Disparar Workflow

```php
$service = new WorkflowService($user);
$service->dispatchWorkflow($repository, $workflow, 'main', [
    'environment' => 'production'
]);
```

### 5. Ativar GitHub Pages

```php
$service = new GitHubPagesService($user);
$service->enablePages($repository, 'gh-pages', '/');
```

## 🧪 Testes

Execute os testes (quando implementados):

```bash
php artisan test --filter=GitHub
```

## 📝 Licença

Este módulo faz parte do Pudim Deployment e segue a mesma licença do projeto principal.

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, siga o padrão de código do projeto.

## 🐛 Reportar Bugs

Abra uma issue no repositório com:
- Descrição do problema
- Passos para reproduzir
- Comportamento esperado vs atual
- Logs relevantes

---

**Desenvolvido com** 🐾 **para Pudim Deployment**
