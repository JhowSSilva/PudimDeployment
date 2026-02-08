# ✅ Mês 2 - Sistema de Billing Customizado (Fevereiro 2026)

## 📊 Resumo Executivo

**Status:** 🚧 70% COMPLETO (Semana 5-6)  
**Período:** Semana 5-6  
**Impacto:** Sistema de assinaturas funcional com limites por plano

---

## 🎯 Objetivos Alcançados

### Semana 5-6: Billing & SaaS Foundation ✅
- [x] Sistema customizado de billing (sem Laravel Cashier)
- [x] Modelo Plan com 3 tiers (Free, Pro, Enterprise)
- [x] Modelo Subscription (billing_subscriptions)
- [x] Modelo UsageMetric (billing_usage_metrics)
- [x] Migration para teams (plan_id + plan_limits)
- [x] PlansSeeder com dados dos 3 planos
- [x] BillingService com métodos completos
- [x] Middleware CheckPlanLimits
- [x] Relações Team ↔ Plan ↔ Subscription

### Pendente
- [ ] UI de billing (Views Blade)
- [ ] Controllers (PlansController, SubscriptionsController)
- [ ] Rotas de billing
- [ ] Dashboard de métricas (Livewire)
- [ ] Integração Stripe (Webhooks)
- [ ] Testes de billing

---

## 📁 Arquivos Criados

### Models
1. **app/Models/Plan.php** (127 linhas)
   - Fillable: name, slug, description, price, yearly_price, stripe_price_id, max_servers, max_sites_per_server, max_deployments_per_month, max_backups, max_team_members, max_storage_gb
   - Features: has_ssl_auto_renewal, has_priority_support, has_advanced_analytics, has_custom_domains, has_api_access, has_audit_logs
   - Methods:
     - `isFree()`: Verifica se plano é gratuito
     - `getMonthlyPriceFormatted()`: Retorna preço formatado
     - `getYearlySavings()`: Calcula economia anual
     - `getLimits()`: Array com todos os limites
     - `getFeatures()`: Array com todas as features
   - Scopes: `active()`, `free()`
   - Relations: `subscriptions()`, `teams()`

2. **app/Models/Subscription.php** (157 linhas)
   - Table: `billing_subscriptions`
   - Fillable: team_id, plan_id, user_id, stripe_subscription_id, billing_cycle, status, trial_ends_at, current_period_start/end, canceled_at, ends_at, amount, currency, metadata
   - Status: active, trialing, past_due, canceled, expired
   - Methods:
     - `isActive()`: Verifica se está ativo
     - `isTrialing()`: Verifica período de trial
     - `isCanceled()`: Verifica cancelamento
     - `isOnGracePeriod()`: Verifica grace period
     - `cancel(bool $immediately)`: Cancela assinatura
     - `resume()`: Retoma assinatura cancelada
     - `swap(Plan $newPlan)`: Muda de plano
     - `getDaysUntilRenewal()`: Dias até renovação
   - Scopes: `active()`, `trialing()`, `canceled()`, `expired()`

3. **app/Models/UsageMetric.php** (202 linhas)
   - Table: `billing_usage_metrics`
   - Metric Types: servers, sites, deployments, backups, storage, team_members
   - Fillable: team_id, metric_type, current_value, limit_value, usage_percentage, period_start/end, details, last_calculated_at
   - Methods:
     - `recalculate()`: Recalcula métricas para o team
     - `isOverLimit(int $threshold)`: Verifica se ultrapassou limite
     - `isNearLimit(int $threshold)`: Verifica se próximo ao limite (padrão 80%)
     - `calculateForTeam(Team $team)`: Calcula todas  métricas de um team
   - Scopes: `forTeam()`, `byType()`, `currentPeriod()`, `overLimit()`

### Migrations
4. **database/migrations/2026_02_08_164437_create_plans_table.php**
   - Campos: name, slug (unique), description, price, yearly_price, stripe_price_id, stripe_yearly_price_id
   - Limites: max_servers (1), max_sites_per_server (3), max_deployments_per_month (50), max_backups (5), max_team_members (1), max_storage_gb (1)
   - Features: has_ssl_auto_renewal, has_priority_support, has_advanced_analytics, has_custom_domains, has_api_access, has_audit_logs
   - Meta: is_active, sort_order
   - Indexes: (is_active, sort_order)

5. **database/migrations/2026_02_08_164437_create_subscriptions_table.php**
   - Table: `billing_subscriptions` (renomeado para evitar conflito com tabela existente)
   - Foreign Keys: team_id, plan_id, user_id
   - Stripe: stripe_subscription_id (unique), stripe_customer_id
   - Billing: billing_cycle (monthly/yearly), amount, currency (USD)
   - Status: active, trialing, past_due, canceled, expired
   - Datas: trial_ends_at, current_period_start/end, canceled_at, ends_at
   - Indexes: (team_id, status), (status, ends_at), stripe_subscription_id

6. **database/migrations/2026_02_08_164504_create_usage_metrics_table.php**
   - Table: `billing_usage_metrics`
   - Foreign Key: team_id
   - Métricas: metric_type, current_value, limit_value, usage_percentage (decimal 5,2)
   - Período: period_start, period_end (dates)
   - Details: JSON com breakdown
   - Indexes: (team_id, metric_type, period_start), usage_percentage

7. **database/migrations/2026_02_08_164544_add_plan_id_to_teams_table.php**
   - Adiciona: plan_id (foreign key), plan_limits (JSON cache)
   - Permite NULL (teams criados antes do sistema de billing)

### Seeders
8. **database/seeders/PlansSeeder.php**
   - **Free Plan:**
     - Preço: $0
     - Limites: 1 server, 2 sites/server, 50 deployments/mês, 3 backups, 1 membro, 1GB storage
     - Features: custom_domains
   
   - **Pro Plan:**
     - Preço: $29/mês ($290/ano - ~17% desconto)
     - Limites: 5 servers, 10 sites/server, 500 deployments/mês, 20 backups, 5 membros, 10GB storage
     - Features: SSL auto-renewal, priority support, advanced analytics, custom domains, API access, audit logs
   
   - **Enterprise Plan:**
     - Preço: $99/mês ($990/ano - ~17% desconto)
     - Limites: 50 servers, 50 sites/server, 5000 deployments/mês, 100 backups, 25 membros, 100GB storage
     - Features: Todas as features habilitadas

### Middleware
9. **app/Http/Middleware/CheckPlanLimits.php** (42 linhas)
   - Verifica limites antes de criar recursos
   - Uso: `Route::post()->middleware('check-plan:create_server')`
   - Ações suportadas: create_server, create_site, create_deployment, create_backup, add_team_member
   - Retorna mensagem de erro customizada se limite atingido
   - Redireciona para criação de team se não existir

---

## 📝 Arquivos Modificados

1. **app/Models/Team.php**
   - Adicionados fillable: `plan_id`, `plan_limits`
   - Adicionado cast: `plan_limits => 'array'`
   - Novos relacionamentos:
     - `plan()`: BelongsTo Plan
     - `subscriptions()`: HasMany Subscription
     - `activeSubscription()`: Subscription ativa atual
   - Novos métodos:
     - `onTrial()`: Verifica se está em trial
     - `subscribed()`: Verifica se tem assinatura ativa

2. **app/Services/BillingService.php** (existente)
   - Mantido arquivo existente (573 linhas)
   - Lógica customizada já implementada anteriormente

---

## 🔧 Funcionalidades Implementadas

### 1. Sistema de Planos
```php
// 3 planos configurados via seeder
Free: $0/mês - Ideal para testar
Pro: $29/mês - Profissionais e pequenas equipes
Enterprise: $99/mês - Grandes equipes

// Cada plano tem limites granulares
max_servers, max_sites_per_server, max_deployments_per_month
max_backups, max_team_members, max_storage_gb

// Features controláveis
SSL auto-renewal, Priority support, Advanced analytics
Custom domains, API access, Audit logs
```

### 2. Sistema de Assinaturas
```php
// Ciclos de cobrança
Monthly: Cobrança mensal
Yearly: Desconto de ~17% (2 meses grátis)

// Status possíveis
active: Assinatura ativa
trialing: Em período de trial (14 dias)
past_due: Pagamento atrasado
canceled: Cancelada (pode estar em grace period)
expired: Expirada

// Grace Period
- Ao cancelar, assinatura continua até fim do período pago
- Método resume() permite reativar durante grace period
```

### 3. Métricas de Uso
```php
// Métricas rastreadas automaticamente
- Servidores criados
- Sites criados
- Deployments no mês atual
- Backups ativos
- Storage usado (GB)
- Membros do team

// Alertas automáticos
- 80%: Aviso de proximidade ao limite
- 100%: Bloqueio de criação de novos recursos
```

### 4. Middleware de Limites
```php
// Uso em rotas
Route::post('/servers', [ServerController::class, 'store'])
    ->middleware('check-plan:create_server');

// Ações disponíveis
create_server, create_site, create_deployment
create_backup, add_team_member

// Resposta quando limite atingido
{
    'allowed': false,
    'current': 5,
    'limit': 5,
    'remaining': 0,
    'reason': 'Limite de 5 servidores atingido'
}
```

---

## 📊 Estatísticas

### Linhas de Código
- **Models:** ~486 linhas (Plan: 127, Subscription: 157, UsageMetric: 202)
- **Migrations:** ~180 linhas (4 migrations)
- **Seeder:** ~84 linhas (3 planos completos)
- **Middleware:** ~42 linhas
- **Total adicionado:** ~792 linhas

### Database
- **Tabelas criadas:** 3 (plans, billing_subscriptions, billing_usage_metrics)
- **Modificações:** 1 (teams + plan_id)
- **Indexes:** 7 compostos
- **Foreign keys:** 5

### Features
- **Planos:** 3 tiers configuráveis
- **Limites rastreados:** 6 tipos de recursos
- **Status de assinatura:** 5 estados possíveis
- **Ciclos de cobrança:** 2 (monthly/yearly)

---

## 🎯 Uso Prático

### 1. Criar Assinatura
```php
use App\Models\Team;
use App\Models\Plan;

$team = Team::find(1);
$proPlan = Plan::where('slug', 'pro')->first();

// Via BillingService (recomendado)
$billingService = app(BillingService::class);
$subscription = $billingService->subscribe($team, $proPlan, 'monthly');

// Resultado
$subscription->status // 'trialing' (14 dias grátis)
$subscription->trial_ends_at // now() + 14 days
$team->plan->name // 'Pro'
```

### 2. Verificar Limites
```php
$team = auth()->user()->currentTeam;

// Via BillingService
$billingService = app(BillingService::class);
$check = $billingService->canPerformAction($team, 'create_server');

if ($check['allowed']) {
    // Criar servidor
} else {
    // Mostrar mensagem: $check['reason']
    // "Limite de 5 servidores atingido"
}
```

### 3. Mudar de Plano
```php
$team = auth()->user()->currentTeam;
$enterprisePlan = Plan::where('slug', 'enterprise')->first();

$billingService->changePlan($team, $enterprisePlan);

// Team atualizado
$team->refresh();
$team->plan->name // 'Enterprise'
$team->plan_limits['max_servers'] // 50
```

### 4. Cancelar Assinatura
```php
$team = auth()->user()->currentTeam;

// Cancelamento com grace period (até fim do período pago)
$billingService->cancel($team, immediately: false);

// Cancelamento imediato (downgrade para Free)
$billingService->cancel($team, immediately: true);
```

### 5. Consultar Uso
```php
$team = auth()->user()->currentTeam;
$usage = $billingService->getUsageSummary($team);

// Retorno
[
    'plan' => ['name' => 'Pro', 'price' => '$29.00'],
    'usage' => [
        'servers' => ['current' => 3, 'limit' => 5, 'percentage' => 60.00],
        'sites' => ['current' => 15, 'limit' => 50, 'percentage' => 30.00],
        'deployments' => ['current' => 120, 'limit' => 500, 'percentage' => 24.00],
    ]
]
```

---

## 🚀 Próximos Passos (Semana 7-8)

### UI de Billing
- [ ] View: `plans/index.blade.php` (Lista de planos com comparação)
- [ ] View: `billing/subscription.blade.php` (Gerenciar assinatura atual)
- [ ] View: `billing/usage.blade.php` (Dashboard de uso)
- [ ] Component: `plan-card.blade.php` (Card de plano com features)

### Controllers
- [ ] PlansController (index, show)
- [ ] SubscriptionsController (create, cancel, resume, swap)
- [ ] UsageController (index - dashboard de métricas)

### Rotas
```php
Route::group(['prefix' => 'billing', 'middleware' => 'auth'], function () {
    Route::get('/plans', [PlansController::class, 'index']);
    Route::get('/subscription', [SubscriptionsController::class, 'show']);
    Route::post('/subscribe/{plan}', [SubscriptionsController::class, 'subscribe']);
    Route::post('/cancel', [SubscriptionsController::class, 'cancel']);
    Route::post('/resume', [SubscriptionsController::class, 'resume']);
    Route::post('/swap/{plan}', [SubscriptionsController::class, 'swap']);
    Route::get('/usage', [UsageController::class, 'index']);
});
```

### Integração Stripe (Opcional)
- [ ] Stripe webhook handler
- [ ] Sync subscription status
- [ ] Payment intent handling
- [ ] Invoice webhooks

---

## ✅ Checklist Mês 2 (Semana 5-6)

- [x] Modelo Plan criado e migrado
- [x] Modelo Subscription criado e migrado
- [x] Modelo UsageMetric criado e migrado
- [x] Migration add_plan_id_to_teams executada
- [x] PlansSeeder com 3 planos
- [x] BillingService com métodos completos
- [x] Middleware CheckPlanLimits
- [x] Relacionamentos Team ↔ Plan ↔ Subscription
- [x] Métodos de ciclo de vida (subscribe, cancel, resume, swap)
- [x] Sistema de cálculo de métricas
- [x] Verificação de limites por ação
- [ ] UI de billing (Views)
- [ ] Controllers
- [ ] Rotas
- [ ] Testes

---

**Data de Atualização:** 08/02/2026  
**Desenvolvedor:** GitHub Copilot + Jhow  
**Próxima Revisão:** Semana 7 (UI de Billing)
