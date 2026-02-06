<x-layout>
    <div class="py-8">
        <!-- Breadcrumbs -->
        <x-breadcrumbs :items="[
            ['label' => 'Sites', 'url' => '#']
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
                    <h1 class="text-3xl font-bold text-neutral-900">Sites & Aplicações</h1>
                    <p class="mt-1 text-sm text-neutral-600">Gerencie deployments, domínios e configurações</p>
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
                    
                    <x-button href="{{ route('sites.create') }}" variant="primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Novo Site
                    </x-button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <x-card class="mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="search-sites" class="block text-sm font-medium text-neutral-700 mb-2">Buscar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            x-model="search" 
                            type="text" 
                            id="search-sites" 
                            placeholder="Nome ou domínio..." 
                            class="block w-full pl-10 pr-3 py-2.5 border border-neutral-300 rounded-lg text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                </div>
                
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-neutral-700 mb-2">Status</label>
                    <select 
                        x-model="statusFilter" 
                        id="status-filter" 
                        class="block w-full px-3 py-2.5 border border-neutral-300 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                    >
                        <option value="all">Todos</option>
                        <option value="active">Ativos</option>
                        <option value="inactive">Inativos</option>
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

        @if($sites->count() > 0)
            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($sites as $site)
                    <x-card padding="false" class="group hover:ring-2 hover:ring-primary-500 hover:ring-offset-2 transition-all duration-200">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-neutral-200">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0 w-12 h-12 bg-neutral-100 group-hover:bg-primary-50 rounded-xl flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-primary-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-semibold text-neutral-900 truncate">{{ $site->name }}</h3>
                                        <a href="http://{{ $site->domain }}" target="_blank" class="text-sm text-primary-600 hover:text-primary-700 truncate block">
                                            {{ $site->domain }}
                                        </a>
                                    </div>
                                </div>
                                @if($site->status === 'active')
                                    <x-badge variant="success" :dot="true" :pulse="true">Ativo</x-badge>
                                @elseif($site->status === 'inactive')
                                    <x-badge variant="neutral" :dot="true">Inativo</x-badge>
                                @else
                                    <x-badge variant="warning" :dot="true">{{ ucfirst($site->status) }}</x-badge>
                                @endif
                            </div>
                            
                            <!-- Site Info -->
                            <div class="space-y-2">
                                <div class="flex items-center text-sm text-neutral-600">
                                    <svg class="w-4 h-4 mr-2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                    </svg>
                                    {{ $site->server->name }}
                                </div>
                                
                                @if($site->git_repository)
                                    <div class="flex items-center text-sm text-neutral-600">
                                        <svg class="w-4 h-4 mr-2 text-neutral-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="truncate">{{ Str::limit($site->git_repository, 35) }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-badge variant="info" size="sm">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $site->git_branch ?? 'main' }}
                                        </x-badge>
                                        @if($site->auto_deploy)
                                            <x-badge variant="primary" size="sm">Auto Deploy</x-badge>
                                        @endif
                                    </div>
                                @endif
                                
                                <div class="flex items-center text-sm text-neutral-600">
                                    <svg class="w-4 h-4 mr-2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                    </svg>
                                    PHP {{ $site->php_version }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- SSL & Deployment Status -->
                        <div class="p-6 bg-neutral-50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-medium text-neutral-600">SSL Certificate</span>
                                @if($site->ssl_enabled ?? false)
                                    <x-badge variant="success" size="sm">Ativo</x-badge>
                                @else
                                    <x-badge variant="neutral" size="sm">Não configurado</x-badge>
                                @endif
                            </div>
                            
                            @if($site->latest_deployment_at)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-neutral-600">Último Deploy</span>
                                    <span class="text-xs text-neutral-500">{{ $site->latest_deployment_at->diffForHumans() }}</span>
                                </div>
                            @else
                                <div class="text-center py-2">
                                    <p class="text-xs text-neutral-500">Nenhum deployment realizado</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Actions -->
                        <div class="p-4 bg-white border-t border-neutral-200">
                            <div class="flex items-center justify-between space-x-2">
                                <x-button href="{{ route('sites.show', $site) }}" variant="secondary" size="sm" class="flex-1">
                                    Ver Detalhes
                                </x-button>
                                @if($site->git_repository)
                                    <x-button variant="primary" size="sm" title="Deploy">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </x-button>
                                @endif
                                <button class="p-2 text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 rounded-lg transition-all duration-150" title="Mais ações">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </x-card>
                @empty
                @endforelse
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" x-cloak>
                <x-card padding="false">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200">
                            <thead class="bg-neutral-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Site</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Servidor</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Git</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">SSL</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-semibold text-neutral-600 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 bg-white">
                                @foreach($sites as $site)
                                    <tr class="hover:bg-neutral-50 transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10 bg-neutral-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3 max-w-xs">
                                                    <p class="text-sm font-semibold text-neutral-900 truncate">{{ $site->name }}</p>
                                                    <a href="http://{{ $site->domain }}" target="_blank" class="text-sm text-primary-600 hover:text-primary-700 truncate block">
                                                        {{ $site->domain }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                                            {{ $site->server->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($site->git_repository)
                                                <div class="flex items-center space-x-1">
                                                    <x-badge variant="info" size="sm">{{ $site->git_branch ?? 'main' }}</x-badge>
                                                    @if($site->auto_deploy)
                                                        <x-badge variant="primary" size="sm">Auto</x-badge>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-neutral-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($site->status === 'active')
                                                <x-badge variant="success" :dot="true" :pulse="true">Ativo</x-badge>
                                            @elseif($site->status === 'inactive')
                                                <x-badge variant="neutral" :dot="true">Inativo</x-badge>
                                            @else
                                                <x-badge variant="warning" :dot="true">{{ ucfirst($site->status) }}</x-badge>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($site->ssl_enabled ?? false)
                                                <x-badge variant="success" size="sm">Ativo</x-badge>
                                            @else
                                                <x-badge variant="neutral" size="sm">-</x-badge>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                @if($site->git_repository)
                                                    <x-button variant="ghost" size="sm" title="Deploy">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg>
                                                    </x-button>
                                                @endif
                                                <x-button href="{{ route('sites.show', $site) }}" variant="ghost" size="sm">
                                                    Ver Detalhes
                                                </x-button>
                                            </div>
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
                title="Nenhum site cadastrado" 
                description="Crie seu primeiro site para começar a fazer deployments. Configure domínios, SSL, Git integration e muito mais."
                :action="route('sites.create')"
                actionLabel="Criar Primeiro Site"
            >
                <x-slot:icon>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        @endif

        <!-- Pagination -->
        @if($sites->hasPages())
            <div class="mt-6">
                {{ $sites->links() }}
            </div>
        @endif
    </div>
</x-layout>

