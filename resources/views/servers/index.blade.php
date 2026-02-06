<x-layout>
    <div class="py-8">
        <!-- Breadcrumbs -->
        <x-breadcrumbs :items="[
            ['label' => 'Servidores', 'url' => '#']
        ]" />
        
        <div x-data="{ 
            search: '', 
            statusFilter: 'all',
            viewMode: 'grid'
        }">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900">Servidores</h1>
                    <p class="mt-1 text-sm text-neutral-600">Gerencie e monitore todos os seus servidores</p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- View Mode Toggle -->
                    <div class="flex items-center bg-white border border-neutral-300 rounded-lg p-1">
                        <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:text-neutral-900'" class="px-3 py-1.5 rounded-md transition-all duration-150">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </button>
                        <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-neutral-100 text-neutral-900' : 'text-neutral-600 hover:text-neutral-900'" class="px-3 py-1.5 rounded-md transition-all duration-150">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <x-button href="{{ route('servers.create') }}" variant="primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Novo Servidor
                    </x-button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <x-card class="mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="search" class="block text-sm font-medium text-neutral-700 mb-2">Buscar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            x-model="search" 
                            type="text" 
                            id="search" 
                            placeholder="Nome ou IP..." 
                            class="block w-full pl-10 pr-3 py-2.5 border border-neutral-300 rounded-lg text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-neutral-700 mb-2">Status</label>
                    <select 
                        x-model="statusFilter" 
                        id="status" 
                        class="block w-full px-3 py-2.5 border border-neutral-300 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                    >
                        <option value="all">Todos</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <option value="provisioning">Provisionando</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <x-button variant="secondary" class="w-full" @click="search = ''; statusFilter = 'all'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Limpar Filtros
                    </x-button>
                </div>
            </div>
        </x-card>

        @if($servers->count() > 0)
            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($servers as $server)
                    @php
                        $metric = $server->latestMetric();
                    @endphp
                    <x-card padding="false" class="group hover:ring-2 hover:ring-primary-500 hover:ring-offset-2 transition-all duration-200">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-neutral-200">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0 w-12 h-12 bg-neutral-100 group-hover:bg-primary-50 rounded-xl flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-primary-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-semibold text-neutral-900 truncate">{{ $server->name }}</h3>
                                        <p class="text-sm text-neutral-500 truncate">{{ $server->ip_address }}</p>
                                    </div>
                                </div>
                                @if($server->status === 'online')
                                    <x-badge variant="success" :dot="true" :pulse="true">Online</x-badge>
                                @elseif($server->status === 'offline')
                                    <x-badge variant="error" :dot="true">Offline</x-badge>
                                @elseif($server->status === 'provisioning')
                                    <x-badge variant="warning" :dot="true" :pulse="true">Provisionando</x-badge>
                                @else
                                    <x-badge variant="neutral" :dot="true">{{ ucfirst($server->status) }}</x-badge>
                                @endif
                            </div>
                            
                            <!-- Server Info -->
                            <div class="space-y-2">
                                <div class="flex items-center text-sm text-neutral-600">
                                    <svg class="w-4 h-4 mr-2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                    </svg>
                                    {{ $server->os_type ?? 'N/A' }} {{ $server->os_version }}
                                </div>
                                <div class="flex items-center text-sm text-neutral-600">
                                    <svg class="w-4 h-4 mr-2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $server->ssh_user }} @ porta {{ $server->ssh_port }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Metrics -->
                        @if($metric)
                            <div class="p-6 bg-neutral-50 space-y-4">
                                <!-- CPU -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-medium text-neutral-600">CPU</span>
                                        <span class="text-xs font-semibold {{ $metric->cpu_usage > 80 ? 'text-error-600' : ($metric->cpu_usage > 60 ? 'text-warning-600' : 'text-success-600') }}">
                                            {{ number_format($metric->cpu_usage, 1) }}%
                                        </span>
                                    </div>
                                    <div class="w-full h-2 bg-neutral-200 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-500 {{ $metric->cpu_usage > 80 ? 'bg-error-500' : ($metric->cpu_usage > 60 ? 'bg-warning-500' : 'bg-success-500') }}" style="width: {{ min($metric->cpu_usage, 100) }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Memory -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-medium text-neutral-600">Memória</span>
                                        <span class="text-xs font-semibold {{ $metric->memory_usage_percentage > 80 ? 'text-error-600' : ($metric->memory_usage_percentage > 60 ? 'text-warning-600' : 'text-primary-600') }}">
                                            {{ number_format($metric->memory_usage_percentage, 1) }}%
                                        </span>
                                    </div>
                                    <div class="w-full h-2 bg-neutral-200 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-500 {{ $metric->memory_usage_percentage > 80 ? 'bg-error-500' : ($metric->memory_usage_percentage > 60 ? 'bg-warning-500' : 'bg-primary-500') }}" style="width: {{ min($metric->memory_usage_percentage, 100) }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Disk -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-medium text-neutral-600">Disco</span>
                                        <span class="text-xs font-semibold text-neutral-600">
                                            {{ number_format($metric->disk_used_gb, 1) }}GB / {{ number_format($metric->disk_total_gb, 1) }}GB
                                        </span>
                                    </div>
                                    <div class="w-full h-2 bg-neutral-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-info-500 transition-all duration-500" style="width: {{ $metric->disk_total_gb > 0 ? min(($metric->disk_used_gb / $metric->disk_total_gb) * 100, 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-6 bg-neutral-50">
                                <p class="text-xs text-neutral-500 text-center">Métricas não disponíveis</p>
                            </div>
                        @endif
                        
                        <!-- Actions -->
                        <div class="p-4 bg-white border-t border-neutral-200">
                            <div class="flex items-center justify-between space-x-2">
                                <x-button href="{{ route('servers.show', $server) }}" variant="secondary" size="sm" class="flex-1">
                                    Ver Detalhes
                                </x-button>
                                <button class="p-2 text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 rounded-lg transition-all duration-150" title="Mais ações">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" x-cloak>
                <x-card padding="false">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200">
                            <thead class="bg-neutral-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Servidor</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">IP / Porta</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Sistema</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Métricas</th>
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
                                                    <p class="text-sm text-neutral-500">{{ $server->ssh_user }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm text-neutral-900">{{ $server->ip_address }}</p>
                                            <p class="text-sm text-neutral-500">Porta {{ $server->ssh_port }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                                            {{ $server->os_type ?? 'N/A' }} {{ $server->os_version }}
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($metric)
                                                <div class="space-y-1.5">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs text-neutral-600 w-12">CPU:</span>
                                                        <div class="w-20 h-1.5 bg-neutral-200 rounded-full overflow-hidden">
                                                            <div class="h-full {{ $metric->cpu_usage > 80 ? 'bg-error-500' : 'bg-success-500' }}" style="width: {{ min($metric->cpu_usage, 100) }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-medium">{{ number_format($metric->cpu_usage, 0) }}%</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs text-neutral-600 w-12">RAM:</span>
                                                        <div class="w-20 h-1.5 bg-neutral-200 rounded-full overflow-hidden">
                                                            <div class="h-full bg-primary-500" style="width: {{ min($metric->memory_usage_percentage, 100) }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-medium">{{ number_format($metric->memory_usage_percentage, 0) }}%</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-neutral-400 text-xs">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <x-button href="{{ route('servers.show', $server) }}" variant="ghost" size="sm">
                                                Ver Detalhes
                                            </x-button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        @else
            <x-empty-state 
                title="Nenhum servidor cadastrado" 
                description="Crie seu primeiro servidor para começar a gerenciar sua infraestrutura. Conecte-se a AWS, DigitalOcean ou qualquer provedor VPS."
                :action="route('servers.create')"
                actionLabel="Criar Primeiro Servidor"
            >
                <x-slot:icon>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        @endif
    </div>
    </div>
</x-layout>
