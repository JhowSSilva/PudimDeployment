<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Uso de Recursos</h1>
                <p class="mt-1 text-sm text-neutral-400">Acompanhe seu consumo em relação aos limites do plano</p>
            </div>
            <div class="flex items-center space-x-3">
                <x-button href="{{ route('billing.subscription') }}" variant="secondary">
                    Ver Assinatura
                </x-button>
                @if($team->plan && !$team->plan->isFree())
                <x-button href="{{ route('billing.plans') }}" variant="primary">
                    Fazer Upgrade
                </x-button>
                @endif
            </div>
        </div>
    </div>

    @if($subscription && $subscription->current_period_end)
    <div class="mb-6 bg-primary-900/20 border border-primary-500/30 rounded-lg px-4 py-3">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-primary-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm text-primary-300">
                Período atual: até <strong>{{ $subscription->current_period_end->format('d/m/Y') }}</strong>
                ({{ $subscription->getDaysUntilRenewal() }} dias restantes)
            </span>
        </div>
    </div>
    @endif

    <!-- Usage Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @php
            $metricTypes = [
                'servers' => ['name' => 'Servidores', 'icon' => 'server', 'limit_field' => 'max_servers'],
                'sites' => ['name' => 'Sites', 'icon' => 'globe', 'limit_field' => 'max_sites_per_server'],
                'deployments' => ['name' => 'Deployments', 'icon' => 'rocket', 'limit_field' => 'max_deployments_per_month'],
                'backups' => ['name' => 'Backups', 'icon' => 'database', 'limit_field' => 'max_backups'],
                'team_members' => ['name' => 'Membros do Time', 'icon' => 'users', 'limit_field' => 'max_team_members'],
                'storage' => ['name' => 'Armazenamento', 'icon' => 'hard-drive', 'limit_field' => 'max_storage_gb'],
            ];
        @endphp

        @foreach($metricTypes as $type => $config)
            @php
                $metric = $metrics->get($type);
                $limitValue = $team->plan->{$config['limit_field']} ?? 0;
                $currentValue = $metric ? $metric->current_value : 0;
                $percentage = $limitValue > 0 ? min(($currentValue / $limitValue) * 100, 100) : 0;
                
                // Color based on usage
                if ($percentage >= 90) {
                    $color = 'error';
                    $bgColor = 'bg-error-900/20';
                    $ringColor = 'ring-error-500/30';
                    $textColor = 'text-error-400';
                    $barColor = 'bg-error-500';
                } elseif ($percentage >= 75) {
                    $color = 'warning';
                    $bgColor = 'bg-warning-900/20';
                    $ringColor = 'ring-warning-500/30';
                    $textColor = 'text-warning-400';
                    $barColor = 'bg-warning-500';
                } else {
                    $color = 'success';
                    $bgColor = 'bg-success-900/20';
                    $ringColor = 'ring-success-500/30';
                    $textColor = 'text-success-400';
                    $barColor = 'bg-success-500';
                }
            @endphp

            <x-card padding="false" class="group hover:scale-[1.02] transition-transform">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 {{ $bgColor }} rounded-lg flex items-center justify-center mr-3 ring-1 {{ $ringColor }}">
                                @if($config['icon'] === 'server')
                                <svg class="w-6 h-6 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                                @elseif($config['icon'] === 'globe')
                                <svg class="w-6 h-6 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                @elseif($config['icon'] === 'rocket')
                                <svg class="w-6 h-6 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                @elseif($config['icon'] === 'database')
                                <svg class="w-6 h-6 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                </svg>
                                @elseif($config['icon'] === 'users')
                                <svg class="w-6 h-6 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                @elseif($config['icon'] === 'hard-drive')
                                <svg class="w-6 h-6 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-neutral-400">{{ $config['name'] }}</h3>
                                <p class="text-2xl font-bold text-neutral-100 mt-1">
                                    {{ number_format($currentValue, $type === 'storage' ? 2 : 0, ',', '.') }}
                                    @if($type === 'storage')
                                        <span class="text-sm font-normal text-neutral-400">GB</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="flex items-center justify-between text-xs text-neutral-400 mb-1">
                            <span>Uso</span>
                            <span>{{ number_format($percentage, 1) }}%</span>
                        </div>
                        <div class="w-full bg-neutral-700 rounded-full h-2 overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full transition-all duration-500" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <!-- Limit Info -->
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-400">
                            Limite: <strong class="text-neutral-200">{{ number_format($limitValue, 0, ',', '.') }}</strong>
                            @if($type === 'storage') GB @endif
                        </span>
                        @if($percentage >= 90)
                        <span class="text-xs px-2 py-1 rounded-full {{ $bgColor }} {{ $textColor }} font-medium">
                            Limite Atingido
                        </span>
                        @elseif($percentage >= 75)
                        <span class="text-xs px-2 py-1 rounded-full {{ $bgColor }} {{ $textColor }} font-medium">
                            Quase no Limite
                        </span>
                        @endif
                    </div>

                    @if($metric && $metric->details)
                    <div class="mt-4 pt-4 border-t border-neutral-700">
                        <details class="text-xs text-neutral-400">
                            <summary class="cursor-pointer hover:text-neutral-300">Detalhes</summary>
                            <div class="mt-2 space-y-1">
                                @foreach($metric->details as $key => $value)
                                <div class="flex justify-between">
                                    <span>{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                    <span class="font-medium text-neutral-300">{{ $value }}</span>
                                </div>
                                @endforeach
                            </div>
                        </details>
                    </div>
                    @endif
                </div>
            </x-card>
        @endforeach
    </div>

    <!-- Usage Warnings -->
    @php
        $warnings = $metrics->filter(fn($m) => $m->isNearLimit(75));
    @endphp

    @if($warnings->isNotEmpty())
    <x-card class="mb-8">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-warning-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-neutral-100 mb-2">Avisos de Uso</h3>
                <p class="text-sm text-neutral-300 mb-3">
                    Você está próximo ou excedeu os limites em alguns recursos. Considere fazer upgrade do seu plano.
                </p>
                <div class="space-y-2">
                    @foreach($warnings as $warning)
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 rounded-full {{ $warning->isOverLimit() ? 'bg-error-500' : 'bg-warning-500' }} mr-2"></span>
                        <span class="text-neutral-300">
                            <strong>{{ ucfirst(str_replace('_', ' ', $warning->metric_type)) }}</strong>: 
                            {{ number_format($warning->usage_percentage, 1) }}% usado
                            ({{ number_format($warning->current_value, 0, ',', '.') }} de {{ number_format($warning->limit_value, 0, ',', '.') }})
                        </span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <x-button href="{{ route('billing.plans') }}" variant="warning" size="sm">
                        Ver Planos com Mais Recursos
                    </x-button>
                </div>
            </div>
        </div>
    </x-card>
    @endif

    <!-- Plan Comparison -->
    <x-card>
        <h3 class="text-lg font-semibold text-neutral-100 mb-4">Seu Plano Atual: {{ $team->plan->name }}</h3>
        <p class="text-sm text-neutral-400 mb-6">
            @if($team->plan->isFree())
                Você está no plano gratuito. Faça upgrade para desbloquear mais recursos e capacidade.
            @else
                Você está no plano {{ $team->plan->name }}. 
                @if($team->plan->slug !== 'enterprise')
                    Faça upgrade para aumentar seus limites.
                @else
                    Você está no nosso melhor plano!
                @endif
            @endif
        </p>
        <x-button href="{{ route('billing.plans') }}" variant="secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
            Comparar Planos
        </x-button>
    </x-card>
</x-layout>
