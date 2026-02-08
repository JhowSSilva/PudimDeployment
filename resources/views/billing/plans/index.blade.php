<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-100">Planos e Preços</h1>
                <p class="mt-1 text-sm text-neutral-400">Escolha o plano perfeito para o seu time</p>
            </div>
        </div>
    </div>

    <!-- Billing Cycle Toggle (if needed) -->
    <div class="flex justify-center mb-12">
        <div class="inline-flex items-center bg-neutral-800/50 rounded-lg p-1">
            <button id="monthly-toggle" class="px-6 py-2 text-sm font-medium rounded-md transition-all duration-200 bg-primary-600 text-white">
                Mensal
            </button>
            <button id="yearly-toggle" class="px-6 py-2 text-sm font-medium rounded-md transition-all duration-200 text-neutral-400 hover:text-neutral-200">
                Anual <span class="ml-1 text-xs text-success-400">(economize ~17%)</span>
            </button>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 mb-12">
        @foreach($plans as $plan)
        <x-card padding="false" class="relative overflow-hidden group hover:scale-[1.02] transition-transform {{ $plan->slug === 'pro' ? 'ring-2 ring-primary-500' : '' }}">
            @if($plan->slug === 'pro')
            <div class="absolute top-4 right-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-500/20 text-primary-400 ring-1 ring-primary-500/30">
                    Popular
                </span>
            </div>
            @endif

            <div class="p-8">
                <!-- Plan Name -->
                <h3 class="text-2xl font-bold text-neutral-100 mb-2">{{ $plan->name }}</h3>
                <p class="text-sm text-neutral-400 mb-6">{{ $plan->description }}</p>

                <!-- Price -->
                <div class="mb-8">
                    <div class="flex items-baseline monthly-price">
                        @if($plan->isFree())
                            <span class="text-5xl font-bold text-neutral-100">Grátis</span>
                        @else
                            <span class="text-lg text-neutral-400">R$</span>
                            <span class="text-5xl font-bold text-neutral-100">{{ number_format($plan->price, 0, ',', '.') }}</span>
                            <span class="ml-2 text-neutral-400">/mês</span>
                        @endif
                    </div>
                    @if(!$plan->isFree())
                    <div class="yearly-price hidden">
                        <div class="flex items-baseline">
                            <span class="text-lg text-neutral-400">R$</span>
                            <span class="text-5xl font-bold text-neutral-100">{{ number_format($plan->yearly_price / 12, 0, ',', '.') }}</span>
                            <span class="ml-2 text-neutral-400">/mês</span>
                        </div>
                        <p class="text-xs text-success-400 mt-1">
                            Economize R${{ number_format($plan->getYearlySavings(), 0, ',', '.') }}/ano
                        </p>
                    </div>
                    @endif
                </div>

                <!-- CTA Button -->
                @auth
                    @if(auth()->user()->currentTeam->plan_id === $plan->id)
                        <x-button class="w-full mb-6" variant="ghost" disabled>
                            Plano Atual
                        </x-button>
                    @elseif(auth()->user()->currentTeam->subscribed())
                        <form method="POST" action="{{ route('billing.subscription.swap', $plan) }}">
                            @csrf
                            <x-button type="submit" class="w-full mb-6" variant="{{ $plan->slug === 'pro' ? 'primary' : 'secondary' }}">
                                {{ $plan->price > auth()->user()->currentTeam->plan->price ? 'Fazer Upgrade' : 'Fazer Downgrade' }}
                            </x-button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('billing.subscribe', $plan) }}" id="subscribe-form-{{ $plan->id }}">
                            @csrf
                            <input type="hidden" name="billing_cycle" value="monthly" class="billing-cycle-input">
                            <x-button type="submit" class="w-full mb-6" variant="{{ $plan->slug === 'pro' ? 'primary' : 'secondary' }}">
                                {{ $plan->isFree() ? 'Começar Grátis' : 'Assinar Agora' }}
                            </x-button>
                        </form>
                    @endif
                @else
                    <x-button href="{{ route('register') }}" class="w-full mb-6" variant="{{ $plan->slug === 'pro' ? 'primary' : 'secondary' }}">
                        Começar
                    </x-button>
                @endauth

                <!-- Features -->
                <div class="space-y-4">
                    <div class="text-sm font-medium text-neutral-300 mb-3">Recursos incluídos:</div>
                    
                    <!-- Limits -->
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center text-neutral-300">
                            <svg class="w-5 h-5 text-primary-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>{{ $plan->max_servers }}</strong> servidor{{ $plan->max_servers > 1 ? 'es' : '' }}</span>
                        </div>
                        <div class="flex items-center text-neutral-300">
                            <svg class="w-5 h-5 text-primary-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>{{ $plan->max_sites_per_server }}</strong> sites por servidor</span>
                        </div>
                        <div class="flex items-center text-neutral-300">
                            <svg class="w-5 h-5 text-primary-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>{{ number_format($plan->max_deployments_per_month, 0, ',', '.') }}</strong> deployments/mês</span>
                        </div>
                        <div class="flex items-center text-neutral-300">
                            <svg class="w-5 h-5 text-primary-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>{{ $plan->max_backups }}</strong> backups</span>
                        </div>
                        <div class="flex items-center text-neutral-300">
                            <svg class="w-5 h-5 text-primary-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>{{ $plan->max_team_members }}</strong> membro{{ $plan->max_team_members > 1 ? 's' : '' }} no time</span>
                        </div>
                        <div class="flex items-center text-neutral-300">
                            <svg class="w-5 h-5 text-primary-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>{{ $plan->max_storage_gb }}GB</strong> de armazenamento</span>
                        </div>
                    </div>

                    @php
                        $features = $plan->getFeatures();
                    @endphp

                    @if(count(array_filter($features)) > 0)
                    <div class="pt-4 mt-4 border-t border-neutral-700">
                        <div class="text-sm font-medium text-neutral-300 mb-3">Recursos premium:</div>
                        <div class="space-y-3 text-sm">
                            @if($plan->has_ssl_auto_renewal)
                            <div class="flex items-center text-neutral-300">
                                <svg class="w-5 h-5 text-success-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>SSL auto-renewal</span>
                            </div>
                            @endif
                            @if($plan->has_priority_support)
                            <div class="flex items-center text-neutral-300">
                                <svg class="w-5 h-5 text-success-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Suporte prioritário</span>
                            </div>
                            @endif
                            @if($plan->has_advanced_analytics)
                            <div class="flex items-center text-neutral-300">
                                <svg class="w-5 h-5 text-success-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Analytics avançado</span>
                            </div>
                            @endif
                            @if($plan->has_custom_domains)
                            <div class="flex items-center text-neutral-300">
                                <svg class="w-5 h-5 text-success-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Domínios personalizados</span>
                            </div>
                            @endif
                            @if($plan->has_api_access)
                            <div class="flex items-center text-neutral-300">
                                <svg class="w-5 h-5 text-success-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Acesso à API</span>
                            </div>
                            @endif
                            @if($plan->has_audit_logs)
                            <div class="flex items-center text-neutral-300">
                                <svg class="w-5 h-5 text-success-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Logs de auditoria</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </x-card>
        @endforeach
    </div>

    <!-- FAQ or Additional Info -->
    <div class="mt-16">
        <x-card>
            <div class="text-center">
                <h3 class="text-xl font-bold text-neutral-100 mb-2">Precisa de algo personalizado?</h3>
                <p class="text-neutral-400 mb-4">Entre em contato para planos empresariais customizados</p>
                <x-button variant="secondary" href="mailto:suporte@pudim.io">
                    Falar com Vendas
                </x-button>
            </div>
        </x-card>
    </div>

    @push('scripts')
    <script>
        // Toggle between monthly and yearly pricing
        const monthlyToggle = document.getElementById('monthly-toggle');
        const yearlyToggle = document.getElementById('yearly-toggle');
        const monthlyPrices = document.querySelectorAll('.monthly-price');
        const yearlyPrices = document.querySelectorAll('.yearly-price');
        const billingCycleInputs = document.querySelectorAll('.billing-cycle-input');

        monthlyToggle.addEventListener('click', () => {
            monthlyToggle.classList.add('bg-primary-600', 'text-white');
            monthlyToggle.classList.remove('text-neutral-400');
            yearlyToggle.classList.remove('bg-primary-600', 'text-white');
            yearlyToggle.classList.add('text-neutral-400');
            
            monthlyPrices.forEach(el => el.classList.remove('hidden'));
            yearlyPrices.forEach(el => el.classList.add('hidden'));
            billingCycleInputs.forEach(input => input.value = 'monthly');
        });

        yearlyToggle.addEventListener('click', () => {
            yearlyToggle.classList.add('bg-primary-600', 'text-white');
            yearlyToggle.classList.remove('text-neutral-400');
            monthlyToggle.classList.remove('bg-primary-600', 'text-white');
            monthlyToggle.classList.add('text-neutral-400');
            
            monthlyPrices.forEach(el => el.classList.add('hidden'));
            yearlyPrices.forEach(el => el.classList.remove('hidden'));
            billingCycleInputs.forEach(input => input.value = 'yearly');
        });
    </script>
    @endpush
</x-layout>
