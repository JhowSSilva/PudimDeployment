<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Minha Assinatura</h1>
                <p class="mt-1 text-sm text-neutral-400">Gerencie seu plano e assinatura</p>
            </div>
            <x-button href="{{ route('billing.plans') }}" variant="secondary">
                Ver Todos os Planos
            </x-button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-success-900/20 border border-success-500/30 text-success-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-error-900/20 border border-error-500/30 text-error-400 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Current Plan -->
        <div class="lg:col-span-2">
            <x-card>
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-neutral-100">Plano Atual</h2>
                        <p class="text-sm text-neutral-400 mt-1">Informações sobre seu plano ativo</p>
                    </div>
                    @if($subscription)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        @if($subscription->status === 'active') bg-success-900/20 text-success-400 ring-1 ring-success-500/30
                        @elseif($subscription->status === 'trialing') bg-primary-900/20 text-primary-400 ring-1 ring-primary-500/30
                        @elseif($subscription->status === 'canceled') bg-warning-900/20 text-warning-400 ring-1 ring-warning-500/30
                        @else bg-error-900/20 text-error-400 ring-1 ring-error-500/30
                        @endif">
                        @if($subscription->status === 'active') Ativa
                        @elseif($subscription->status === 'trialing') Em Teste
                        @elseif($subscription->status === 'canceled') Cancelada
                        @elseif($subscription->status === 'past_due') Pagamento Pendente
                        @else Expirada
                        @endif
                    </span>
                    @endif
                </div>

                @if($team->plan)
                <div class="bg-neutral-800/50 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-neutral-100">{{ $team->plan->name }}</h3>
                            <p class="text-sm text-neutral-400 mt-1">{{ $team->plan->description }}</p>
                        </div>
                        <div class="text-right">
                            @if($team->plan->isFree())
                                <div class="text-3xl font-bold text-neutral-100">Grátis</div>
                            @else
                                <div class="text-3xl font-bold text-neutral-100">
                                    R$ {{ number_format($team->plan->price, 2, ',', '.') }}
                                </div>
                                <div class="text-sm text-neutral-400">por mês</div>
                            @endif
                        </div>
                    </div>

                    @if($subscription)
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-700">
                        <div>
                            <div class="text-xs text-neutral-400 mb-1">Ciclo de Cobrança</div>
                            <div class="text-sm font-medium text-neutral-200">
                                {{ $subscription->billing_cycle === 'monthly' ? 'Mensal' : 'Anual' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-400 mb-1">Próxima Renovação</div>
                            <div class="text-sm font-medium text-neutral-200">
                                {{ $subscription->current_period_end ? $subscription->current_period_end->format('d/m/Y') : 'N/A' }}
                            </div>
                        </div>
                        @if($subscription->isTrialing())
                        <div>
                            <div class="text-xs text-neutral-400 mb-1">Período de Teste</div>
                            <div class="text-sm font-medium text-primary-400">
                                Termina em {{ $subscription->trial_ends_at->format('d/m/Y') }}
                            </div>
                        </div>
                        @endif
                        @if($subscription->isCanceled() && $subscription->isOnGracePeriod())
                        <div class="col-span-2">
                            <div class="bg-warning-900/20 border border-warning-500/30 rounded-lg p-3">
                                <div class="text-xs text-warning-400 mb-1">Cancelamento Agendado</div>
                                <div class="text-sm font-medium text-warning-300">
                                    Acesso até {{ $subscription->ends_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Plan Limits -->
                <div>
                    <h3 class="text-lg font-semibold text-neutral-100 mb-4">Limites do Plano</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-neutral-800/30 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-neutral-400">Servidores</span>
                                <span class="text-lg font-bold text-neutral-100">{{ $team->plan->max_servers }}</span>
                            </div>
                        </div>
                        <div class="bg-neutral-800/30 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-neutral-400">Sites/Servidor</span>
                                <span class="text-lg font-bold text-neutral-100">{{ $team->plan->max_sites_per_server }}</span>
                            </div>
                        </div>
                        <div class="bg-neutral-800/30 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-neutral-400">Deployments/Mês</span>
                                <span class="text-lg font-bold text-neutral-100">{{ number_format($team->plan->max_deployments_per_month, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="bg-neutral-800/30 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-neutral-400">Backups</span>
                                <span class="text-lg font-bold text-neutral-100">{{ $team->plan->max_backups }}</span>
                            </div>
                        </div>
                        <div class="bg-neutral-800/30 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-neutral-400">Membros do Time</span>
                                <span class="text-lg font-bold text-neutral-100">{{ $team->plan->max_team_members }}</span>
                            </div>
                        </div>
                        <div class="bg-neutral-800/30 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-neutral-400">Armazenamento</span>
                                <span class="text-lg font-bold text-neutral-100">{{ $team->plan->max_storage_gb }}GB</span>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-neutral-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-neutral-300 mb-2">Nenhum plano ativo</h3>
                    <p class="text-neutral-400 mb-6">Escolha um plano para começar a usar o Pudim</p>
                    <x-button href="{{ route('billing.plans') }}" variant="primary">
                        Ver Planos Disponíveis
                    </x-button>
                </div>
                @endif
            </x-card>
        </div>

        <!-- Actions -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">Ações Rápidas</h3>
                <div class="space-y-3">
                    <x-button href="{{ route('billing.usage') }}" variant="secondary" class="w-full justify-start">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Ver Uso de Recursos
                    </x-button>

                    @if($subscription && !$subscription->isCanceled())
                    <x-button href="{{ route('billing.plans') }}" variant="secondary" class="w-full justify-start">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                        Trocar de Plano
                    </x-button>
                    @endif
                </div>
            </x-card>

            <!-- Cancel/Resume Subscription -->
            @if($subscription)
            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">Gerenciar Assinatura</h3>
                
                @if($subscription->isCanceled() && $subscription->isOnGracePeriod())
                    <!-- Resume subscription -->
                    <form method="POST" action="{{ route('billing.subscription.resume') }}">
                        @csrf
                        <x-button type="submit" variant="primary" class="w-full">
                            Reativar Assinatura
                        </x-button>
                    </form>
                    <p class="text-xs text-neutral-400 mt-2 text-center">
                        Você ainda tem acesso até {{ $subscription->ends_at->format('d/m/Y') }}
                    </p>
                @elseif(!$subscription->isCanceled())
                    <!-- Cancel subscription -->
                    <form method="POST" action="{{ route('billing.subscription.cancel') }}" 
                          onsubmit="return confirm('Tem certeza que deseja cancelar sua assinatura? Você ainda terá acesso até o fim do período de cobrança.');">
                        @csrf
                        <x-button type="submit" variant="danger" class="w-full">
                            Cancelar Assinatura
                        </x-button>
                    </form>
                    <p class="text-xs text-neutral-400 mt-2 text-center">
                        Você manterá acesso até {{ $subscription->current_period_end->format('d/m/Y') }}
                    </p>
                @endif
            </x-card>
            @endif

            <!-- Billing History (placeholder) -->
            <x-card>
                <h3 class="text-lg font-semibold text-neutral-100 mb-4">Histórico de Pagamentos</h3>
                <div class="text-center py-6">
                    <svg class="mx-auto h-10 w-10 text-neutral-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-sm text-neutral-400">Nenhum histórico disponível</p>
                </div>
            </x-card>
        </div>
    </div>
</x-layout>
