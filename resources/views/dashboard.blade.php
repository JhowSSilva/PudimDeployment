<x-layout>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900">Dashboard</h1>
                <p class="mt-1 text-sm text-neutral-600">Visão geral do seu ambiente de deployment</p>
            </div>
            <div class="flex items-center space-x-3">
                <x-button variant="ghost" size="sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </x-button>
                <x-button href="{{ route('servers.create') }}" variant="primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo Servidor
                </x-button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Servers -->
        <x-card padding="false" class="group">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-neutral-600 mb-1">Total de Servidores</p>
                        <p class="text-3xl font-bold text-neutral-900">{{ $totalServers }}</p>
                        <p class="text-sm text-neutral-500 mt-2 flex items-center">
                            <svg class="w-4 h-4 text-success-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-success-600 font-medium">8.2%</span>
                            <span class="ml-1">vs último mês</span>
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-primary-50 group-hover:bg-primary-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Online Servers -->
        <x-card padding="false" class="group">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-neutral-600 mb-1">Servidores Online</p>
                        <p class="text-3xl font-bold text-success-600">{{ $serversOnline }}</p>
                        <p class="text-sm text-neutral-500 mt-2">
                            {{ $totalServers > 0 ? round(($serversOnline / $totalServers) * 100, 1) : 0 }}% uptime
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-success-50 group-hover:bg-success-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                        <svg class="w-7 h-7 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Offline Servers -->
        <x-card padding="false" class="group">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-neutral-600 mb-1">Servidores Offline</p>
                        <p class="text-3xl font-bold text-error-600">{{ $serversOffline }}</p>
                        <p class="text-sm text-neutral-500 mt-2">
                            {{ $serversOffline > 0 ? 'Requer atenção' : 'Tudo OK' }}
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-error-50 group-hover:bg-error-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                        <svg class="w-7 h-7 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Total Sites -->
        <x-card padding="false" class="group">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-neutral-600 mb-1">Sites Ativos</p>
                        <p class="text-3xl font-bold text-neutral-900">{{ $totalSites }}</p>
                        <p class="text-sm text-neutral-500 mt-2">
                            Distribuídos em {{ $totalServers }} {{ Str::plural('servidor', $totalServers) }}
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-info-50 group-hover:bg-info-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                        <svg class="w-7 h-7 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content - 2/3 width -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Servers List -->
            <x-card padding="false">
                <div class="p-6 border-b border-neutral-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-900">Servidores</h2>
                            <p class="text-sm text-neutral-600 mt-1">Gerenciamento e monitoramento</p>
                        </div>
                        <x-button href="{{ route('servers.create') }}" variant="secondary" size="sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Adicionar
                        </x-button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    @if($servers->count() > 0)
                        <table class="min-w-full divide-y divide-neutral-200">
                            <thead class="bg-neutral-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Servidor</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">CPU</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Memória</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-semibold text-neutral-600 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 bg-white">
                                @foreach($servers as $server)
                                    @php
                                        $metric = $server->latestMetric();
                                    @endphp
                                    <tr class="hover:bg-neutral-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-semibold text-neutral-900">{{ $server->name }}</p>
                                                    <p class="text-sm text-neutral-500">{{ $server->ip_address }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($server->status === 'online')
                                                <x-badge variant="success" :dot="true" :pulse="true">Online</x-badge>
                                            @elseif($server->status === 'offline')
                                                <x-badge variant="error" :dot="true">Offline</x-badge>
                                            @elseif($server->status === 'provisioning')
                                                <x-badge variant="warning" :dot="true" :pulse="true">Provisionando</x-badge>
                                            @else
                                                <x-badge variant="neutral" :dot="true">{{ ucfirst($server->status) }}</x-badge>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600">
                                            @if($metric)
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium">{{ number_format($metric->cpu_usage, 1) }}%</span>
                                                    <div class="w-16 h-2 bg-neutral-200 rounded-full overflow-hidden">
                                                        <div class="h-full {{ $metric->cpu_usage > 80 ? 'bg-error-500' : ($metric->cpu_usage > 60 ? 'bg-warning-500' : 'bg-success-500') }} transition-all duration-300" style="width: {{ min($metric->cpu_usage, 100) }}%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-neutral-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600">
                                            @if($metric)
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium">{{ number_format($metric->memory_usage_percentage, 1) }}%</span>
                                                    <div class="w-16 h-2 bg-neutral-200 rounded-full overflow-hidden">
                                                        <div class="h-full {{ $metric->memory_usage_percentage > 80 ? 'bg-error-500' : ($metric->memory_usage_percentage > 60 ? 'bg-warning-500' : 'bg-primary-500') }} transition-all duration-300" style="width: {{ min($metric->memory_usage_percentage, 100) }}%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-neutral-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <x-button href="{{ route('servers.show', $server) }}" variant="ghost" size="sm">
                                                Ver detalhes
                                            </x-button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <x-empty-state 
                            title="Nenhum servidor cadastrado" 
                            description="Crie seu primeiro servidor para começar a gerenciar sua infraestrutura"
                            :action="route('servers.create')"
                            actionLabel="Criar Servidor"
                        >
                            <x-slot:icon>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                            </x-slot:icon>
                        </x-empty-state>
                    @endif
                </div>
            </x-card>

            <!-- Recent Deployments -->
            @if($recentDeployments->count() > 0)
                <x-card padding="false">
                    <div class="p-6 border-b border-neutral-200">
                        <h2 class="text-lg font-semibold text-neutral-900">Deployments Recentes</h2>
                        <p class="text-sm text-neutral-600 mt-1">Últimas implantações realizadas</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($recentDeployments as $deployment)
                                <div class="flex items-center justify-between py-3 border-b border-neutral-100 last:border-0">
                                    <div class="flex items-center space-x-4 flex-1">
                                        <div class="flex-shrink-0">
                                            @if($deployment->status === 'success')
                                                <div class="w-10 h-10 bg-success-50 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @elseif($deployment->status === 'failed')
                                                <div class="w-10 h-10 bg-error-50 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-error-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 bg-info-50 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-info-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-neutral-900 truncate">{{ $deployment->site->name }}</p>
                                            <div class="flex items-center space-x-3 mt-1">
                                                <code class="text-xs text-neutral-500 font-mono bg-neutral-100 px-2 py-0.5 rounded">{{ Str::limit($deployment->commit_hash, 8, '') }}</code>
                                                <span class="text-xs text-neutral-500">{{ $deployment->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        @if($deployment->status === 'success')
                                            <x-badge variant="success">Success</x-badge>
                                        @elseif($deployment->status === 'failed')
                                            <x-badge variant="error">Failed</x-badge>
                                        @elseif($deployment->status === 'running')
                                            <x-badge variant="info" :pulse="true">Running</x-badge>
                                        @else
                                            <x-badge variant="warning">{{ ucfirst($deployment->status) }}</x-badge>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        <!-- Sidebar - 1/3 width -->
        <div class="space-y-6">
            <!-- Recent Activity -->
            <x-card padding="false">
                <div class="p-6 border-b border-neutral-200">
                    <h2 class="text-lg font-semibold text-neutral-900">Atividades Recentes</h2>
                </div>
                <div class="p-6">
                    @if($recentActivities->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentActivities as $activity)
                                <div class="flex space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-primary-50 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-neutral-900">
                                            <span class="font-semibold">{{ $activity->user->name }}</span>
                                            <span class="text-neutral-600"> {{ $activity->description }}</span>
                                        </p>
                                        <p class="text-xs text-neutral-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 text-center py-8">Nenhuma atividade recente</p>
                    @endif
                </div>
            </x-card>

            <!-- Quick Actions -->
            <x-card>
                <h3 class="text-sm font-semibold text-neutral-900 mb-4">Ações Rápidas</h3>
                <div class="space-y-2">
                    <x-button href="{{ route('servers.create') }}" variant="secondary" class="w-full justify-start">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Criar Servidor
                    </x-button>
                    <x-button href="{{ route('sites.create') }}" variant="secondary" class="w-full justify-start">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        Criar Site
                    </x-button>
                    <x-button href="{{ route('aws-provision.step1') }}" variant="secondary" class="w-full justify-start">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                        Provisionar AWS
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
</x-layout>
