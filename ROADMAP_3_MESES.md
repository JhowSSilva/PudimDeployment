# Roadmap de Melhorias - Pudim Deployment

## 📋 Visão Geral
Plano de 3 meses para transformar o Pudim Deployment em uma plataforma SaaS robusta, segura e escalável.

---

## MÊS 1: Estabilização e Segurança 🔒

### Semana 1-2: Segurança Crítica

#### ✅ **1. Revisar e corrigir Policies**
**Objetivo:** Garantir que todos os acessos validem corretamente o `team_id`

**Arquivos a revisar:**
- `app/Policies/ServerPolicy.php`
- `app/Policies/SitePolicy.php`
- `app/Policies/DatabasePolicy.php`
- `app/Policies/BackupPolicy.php`
- `app/Policies/GitHubRepositoryPolicy.php`

**Implementação:**
```php
// Padrão em todas as Policies
public function view(User $user, Model $model): bool
{
    return $user->currentTeam->id === $model->team_id;
}

public function update(User $user, Model $model): bool
{
    return $user->currentTeam->id === $model->team_id;
}

public function delete(User $user, Model $model): bool
{
    return $user->currentTeam->id === $model->team_id 
        && $user->ownsTeam($user->currentTeam);
}
```

#### ✅ **2. Implementar rate limiting global**
**Objetivo:** Proteção contra abuso de APIs e endpoints críticos

**Arquivo:** `app/Http/Kernel.php` ou `bootstrap/app.php` (Laravel 11)

```php
// config/app.php ou AppServiceProvider
RateLimiter::for('deployments', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('github', function (Request $request) {
    return Limit::perMinute(30)->by($request->user()?->id);
});

RateLimiter::for('ssh-commands', function (Request $request) {
    return Limit::perMinute(20)->by($request->user()?->id);
});
```

**Rotas:**
```php
Route::middleware(['auth', 'throttle:deployments'])->group(function () {
    Route::post('/sites/{site}/deploy', ...);
});
```

#### ✅ **3. Webhook signature verification**
**Objetivo:** Validar webhooks do GitHub/Cloudflare

**Arquivo:** `app/Http/Middleware/VerifyWebhookSignature.php`

```php
<?php

namespace App\Http\Middleware;

class VerifyWebhookSignature
{
    public function handle($request, Closure $next, string $provider)
    {
        if ($provider === 'github') {
            $signature = $request->header('X-Hub-Signature-256');
            $payload = $request->getContent();
            $secret = config('services.github.webhook_secret');
            
            $computed = 'sha256=' . hash_hmac('sha256', $payload, $secret);
            
            if (!hash_equals($computed, $signature)) {
                abort(403, 'Invalid signature');
            }
        }
        
        return $next($request);
    }
}
```

#### ✅ **4. Audit logging completo**
**Objetivo:** Registrar todas as ações críticas

**Criar Migration:**
```bash
php artisan make:migration create_audit_logs_table
```

**Modelo:**
```php
// app/Models/AuditLog.php
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'action',
        'model_type',
        'model_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];
}
```

**Observer Pattern:**
```php
// Registrar em todos os modelos críticos
Server::observe(ServerObserver::class);
Site::observe(SiteObserver::class);

// ServerObserver
public function updated(Server $server)
{
    AuditLog::create([
        'user_id' => auth()->id(),
        'team_id' => $server->team_id,
        'action' => 'updated',
        'model_type' => Server::class,
        'model_id' => $server->id,
        'changes' => $server->getChanges(),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
}
```

#### ✅ **5. Security scan com OWASP ZAP**
**Objetivo:** Identificar vulnerabilidades

**Passos:**
1. Instalar OWASP ZAP
2. Rodar scan automatizado
3. Rodar scan manual nas rotas críticas
4. Gerar relatório
5. Corrigir vulnerabilidades encontradas

---

### Semana 3-4: Testes e Qualidade 🧪

#### ✅ **1. Criar testes unitários (50% coverage)**
**Objetivo:** Services devem ter cobertura de testes

**Arquivos:**
- `tests/Unit/Services/DeploymentServiceTest.php`
- `tests/Unit/Services/SSHServiceTest.php`
- `tests/Unit/Services/GitHubServiceTest.php`
- `tests/Unit/Services/CloudflareServiceTest.php`

**Exemplo:**
```php
class DeploymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_deploy_site()
    {
        $site = Site::factory()->create();
        $service = new DeploymentService($site);
        
        $result = $service->deploy();
        
        $this->assertTrue($result->success);
        $this->assertDatabaseHas('deployments', [
            'site_id' => $site->id,
            'status' => 'success',
        ]);
    }

    public function test_handles_deployment_failure()
    {
        $site = Site::factory()->create(['git_repository' => 'invalid']);
        $service = new DeploymentService($site);
        
        $this->expectException(DeploymentException::class);
        $service->deploy();
    }
}
```

#### ✅ **2. Criar testes Feature (40% coverage)**
**Objetivo:** Controllers e flows críticos

**Arquivos:**
- `tests/Feature/DeploymentFlowTest.php`
- `tests/Feature/ServerManagementTest.php`
- `tests/Feature/GitHubIntegrationTest.php`

```php
class DeploymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_deploy_site()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['team_id' => $user->currentTeam->id]);

        $response = $this->actingAs($user)
            ->post(route('sites.deploy', $site));

        $response->assertStatus(200);
        $this->assertDatabaseHas('deployments', [
            'site_id' => $site->id,
        ]);
    }

    public function test_user_cannot_deploy_other_team_site()
    {
        $user = User::factory()->create();
        $otherSite = Site::factory()->create(); // Different team

        $response = $this->actingAs($user)
            ->post(route('sites.deploy', $otherSite));

        $response->assertStatus(403);
    }
}
```

#### ✅ **3. Configurar CI com GitHub Actions**
**Arquivo:** `.github/workflows/tests.yml`

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: pudim_test
          MYSQL_ROOT_PASSWORD: password
        options: --health-cmd="mysqladmin ping" --health-interval=10s
      
      redis:
        image: redis:alpine
        options: --health-cmd="redis-cli ping" --health-interval=10s
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, pdo, pdo_mysql, redis
        coverage: xdebug
    
    - name: Install Dependencies
      run: composer install --prefer-dist --no-interaction
    
    - name: Copy .env
      run: cp .env.example .env
    
    - name: Generate key
      run: php artisan key:generate
    
    - name: Run Migrations
      run: php artisan migrate --seed
      env:
        DB_CONNECTION: mysql
        DB_HOST: 127.0.0.1
        DB_PORT: 3306
        DB_DATABASE: pudim_test
        DB_USERNAME: root
        DB_PASSWORD: password
    
    - name: Run Tests
      run: php artisan test --coverage --min=50
      env:
        DB_CONNECTION: mysql
        DB_HOST: 127.0.0.1
    
    - name: Upload coverage
      uses: codecov/codecov-action@v3
```

#### ✅ **4. Migrar database indexes**
**Objetivo:** Performance em queries pesadas

```bash
php artisan make:migration add_performance_indexes
```

```php
Schema::table('deployments', function (Blueprint $table) {
    $table->index(['site_id', 'status', 'created_at']);
    $table->index(['status', 'started_at']);
});

Schema::table('sites', function (Blueprint $table) {
    $table->index(['team_id', 'server_id']);
    $table->index(['domain']);
});

Schema::table('servers', function (Blueprint $table) {
    $table->index(['team_id', 'status']);
});

Schema::table('audit_logs', function (Blueprint $table) {
    $table->index(['user_id', 'created_at']);
    $table->index(['team_id', 'action', 'created_at']);
});
```

#### ✅ **5. Health checks robustos**
**Arquivo:** `routes/api.php`

```php
Route::get('/health', function () {
    $checks = [
        'database' => DB::connection()->getPdo() !== null,
        'redis' => Redis::connection()->ping(),
        'storage' => Storage::disk('local')->exists('test.txt') || Storage::disk('local')->put('test.txt', 'test'),
        'queue' => Queue::size() !== null,
    ];

    $healthy = !in_array(false, $checks, true);

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $healthy ? 200 : 503);
});
```

---

## MÊS 2: Funcionalidades Core SaaS 💳

### Semana 5-6: Billing System

#### ✅ **1. Integrar Laravel Cashier (Stripe)**
```bash
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

**Configuração:**
```php
// config/services.php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
],
```

#### ✅ **2. Criar modelos Subscription, Plan, Invoice**
```bash
php artisan make:model Plan -m
php artisan make:model Subscription -m
```

**Migration Plans:**
```php
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('stripe_plan_id')->unique();
    $table->decimal('price', 8, 2);
    $table->string('interval'); // month, year
    $table->integer('max_servers')->default(5);
    $table->integer('max_sites')->default(10);
    $table->integer('max_deployments_per_month')->default(100);
    $table->boolean('priority_support')->default(false);
    $table->json('features')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**Model User:**
```php
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Billable;

    public function canCreateServer(): bool
    {
        $plan = $this->currentTeam->plan;
        $currentServers = $this->currentTeam->servers()->count();
        
        return $currentServers < $plan->max_servers;
    }
}
```

#### ✅ **3. Implementar 3 planos com limites**
**Seeder:**
```php
Plan::create([
    'name' => 'Starter',
    'stripe_plan_id' => 'price_starter',
    'price' => 29.99,
    'interval' => 'month',
    'max_servers' => 3,
    'max_sites' => 10,
    'max_deployments_per_month' => 50,
    'features' => ['DNS Management', 'SSL Certificates', 'Basic Support'],
]);

Plan::create([
    'name' => 'Professional',
    'stripe_plan_id' => 'price_pro',
    'price' => 79.99,
    'interval' => 'month',
    'max_servers' => 10,
    'max_sites' => 50,
    'max_deployments_per_month' => 500,
    'priority_support' => true,
    'features' => ['Everything in Starter', 'Priority Support', 'Advanced Monitoring'],
]);

Plan::create([
    'name' => 'Enterprise',
    'stripe_plan_id' => 'price_enterprise',
    'price' => 299.99,
    'interval' => 'month',
    'max_servers' => 999,
    'max_sites' => 999,
    'max_deployments_per_month' => 9999,
    'priority_support' => true,
    'features' => ['Unlimited Everything', '24/7 Support', 'Dedicated Account Manager'],
]);
```

#### ✅ **4. Webhooks Stripe**
```php
// routes/api.php
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handleWebhook']);

// StripeWebhookController
public function handleWebhook(Request $request)
{
    $payload = $request->getContent();
    $signature = $request->header('Stripe-Signature');

    try {
        $event = Webhook::constructEvent(
            $payload, $signature, config('services.stripe.webhook.secret')
        );
    } catch(\Exception $e) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    match ($event->type) {
        'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event),
        'invoice.payment_failed' => $this->handlePaymentFailed($event),
        'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
        default => null,
    };

    return response()->json(['status' => 'success']);
}
```

#### ✅ **5. Dashboard de billing**
**View:** `resources/views/billing/index.blade.php`

Exibir:
- Plano atual
- Uso de recursos (servers/sites/deployments)
- Próxima fatura
- Histórico de faturas
- Opção para trocar/cancelar plano

---

### Semana 7-8: Observabilidade 📊

#### ✅ **1. Integrar Sentry**
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=YOUR_DSN
```

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'sentry'],
    ],
    'sentry' => [
        'driver' => 'sentry',
    ],
],
```

#### ✅ **2. Structured logging**
```php
// AppServiceProvider
Log::shareContext([
    'team_id' => fn() => auth()->user()?->current_team_id,
    'user_id' => fn() => auth()->id(),
]);

// Em Services
Log::info('Deployment started', [
    'site_id' => $site->id,
    'server_id' => $site->server_id,
    'git_branch' => $site->git_branch,
]);
```

#### ✅ **3. APM básico**
**Middleware:** `RecordResponseTime`

```php
public function handle($request, Closure $next)
{
    $start = microtime(true);
    $response = $next($request);
    $duration = (microtime(true) - $start) * 1000;

    Metric::record('api.response_time', $duration, [
        'route' => $request->route()?->getName(),
        'method' => $request->method(),
        'status' => $response->status(),
    ]);

    return $response;
}
```

#### ✅ **4. Alerting**
**Criar Observer para eventos críticos:**

```php
// DeploymentObserver
public function failed(Deployment $deployment)
{
    if ($deployment->consecutive_failures >= 3) {
        Notification::route('mail', $deployment->site->team->owner->email)
            ->notify(new DeploymentFailureAlert($deployment));
        
        Log::channel('slack')->critical('Multiple deployment failures', [
            'deployment_id' => $deployment->id,
            'site' => $deployment->site->name,
            'failures' => $deployment->consecutive_failures,
        ]);
    }
}
```

#### ✅ **5. Dashboards de métricas**
**Criar modelo Metric:**

```php
class Metric extends Model
{
    public static function record(string $name, $value, array $tags = [])
    {
        static::create([
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'recorded_at' => now(),
        ]);
    }

    public static function mrr(): float
    {
        return Subscription::where('status', 'active')
            ->sum('amount');
    }

    public static function churn(): float
    {
        $activeLastMonth = Subscription::where('created_at', '<', now()->subMonth())
            ->count();
        
        $cancelled = Subscription::where('cancelled_at', '>=', now()->subMonth())
            ->count();

        return $activeLastMonth > 0 ? ($cancelled / $activeLastMonth) * 100 : 0;
    }
}
```

---

## MÊS 3: Escalabilidade e DevOps 🚀

### Semana 9-10: Infraestrutura

#### ✅ **1. Dockerizar aplicação**
**Dockerfile:**
```dockerfile
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo pdo_mysql gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
```

**docker-compose.yml:**
```yaml
version: '3.8'
services:
  app:
    build: .
    volumes:
      - ./storage:/var/www/storage
    environment:
      - DB_HOST=db
      - REDIS_HOST=redis

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./public:/var/www/public

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: pudim
      MYSQL_ROOT_PASSWORD: secret

  redis:
    image: redis:alpine

  queue:
    build: .
    command: php artisan queue:work --tries=3
```

#### ✅ **2. CI/CD pipeline completo**
**.github/workflows/deploy.yml:**
```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to production
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.PROD_HOST }}
        username: ${{ secrets.PROD_USER }}
        key: ${{ secrets.PROD_SSH_KEY }}
        script: |
          cd /var/www/pudim
          git pull origin main
          composer install --no-dev
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          npm run build
          php artisan queue:restart
```

#### ✅ **3. Database read replicas**
**config/database.php:**
```php
'mysql' => [
    'read' => [
        'host' => [
            env('DB_READ_HOST_1'),
            env('DB_READ_HOST_2'),
        ],
    ],
    'write' => [
        'host' => [
            env('DB_WRITE_HOST'),
        ],
    ],
    'sticky' => true,
```

#### ✅ **4. Redis caching**
```php
// Cache queries pesadas
$servers = Cache::remember('team.' . $team->id . '.servers', 3600, function () use ($team) {
    return $team->servers()->with('sites')->get();
});

// Cache de configuração
Cache::tags(['config', 'team:' . $team->id])->remember('nginx.config', 600, function () {
    return $this->generateNginxConfig();
});
```

#### ✅ **5. CDN para assets**
**config/filesystems.php:**
```php
'cloudfront' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_CLOUDFRONT_URL'),
],
```

---

### Semana 11-12: Polimento ✨

#### ✅ **1. API documentation (OpenAPI)**
```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

**Annotations:**
```php
/**
 * @OA\Post(
 *     path="/api/sites",
 *     summary="Create new site",
 *     tags={"Sites"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","domain","server_id"},
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="domain", type="string"),
 *         )
 *     ),
 *     @OA\Response(response=201, description="Site created"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function store(Request $request) { }
```

#### ✅ **2. Onboarding flow**
**Views:**
- `onboarding/welcome.blade.php`
- `onboarding/select-plan.blade.php`
- `onboarding/add-server.blade.php`
- `onboarding/deploy-first-site.blade.php`
- `onboarding/complete.blade.php`

**Middleware:**
```php
if (!auth()->user()->onboarding_completed) {
    return redirect()->route('onboarding.welcome');
}
```

#### ✅ **3. Testes E2E**
```bash
composer require laravel/dusk --dev
php artisan dusk:install
```

**tests/Browser/DeploymentTest.php:**
```php
public function testUserCanDeploySite()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs(User::find(1))
            ->visit('/sites')
            ->click('@create-site')
            ->type('name', 'Test Site')
            ->type('domain', 'test.com')
            ->select('server_id', 1)
            ->press('Create Site')
            ->assertSee('Site created successfully');
    });
}
```

#### ✅ **4. Performance optimization**
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verificar N+1 queries
composer require barryvdh/laravel-debugbar --dev
```

**Lazy loading:**
```php
// Bad
$sites = Site::all();
foreach ($sites as $site) {
    echo $site->server->name; // N+1
}

// Good
$sites = Site::with('server')->get();
foreach ($sites as $site) {
    echo $site->server->name; // 2 queries total
}
```

#### ✅ **5. Security audit final**
- Rodar OWASP ZAP novamente
- Verificar todas as vulnerabilidades corrigidas
- Penetration testing manual
- Code review de segurança
- Atualizar todas as dependências

---

## 📊 Métricas de Sucesso

**Mês 1:**
- ✅ 0 vulnerabilidades críticas
- ✅ 50%+ cobertura de testes
- ✅ CI/CD funcionando
- ✅ Audit logs implementados

**Mês 2:**
- ✅ Billing system completo
- ✅ 3 planos ativos
- ✅ Sentry configurado
- ✅ Dashboards de métricas

**Mês 3:**
- ✅ Aplicação dockerizada
- ✅ Deploy automatizado
- ✅ Documentação API completa
- ✅ Performance otimizada

---

## 🔄 Manutenção Contínua

**Semanal:**
- Revisar logs de erro (Sentry)
- Verificar métricas de performance
- Atualizar dependências menores

**Mensal:**
- Security audit
- Performance review
- Atualizar documentação
- Treinar novos features

**Trimestral:**
- Penetration testing
- Disaster recovery drill
- Infrastructure review
- Customer feedback session
