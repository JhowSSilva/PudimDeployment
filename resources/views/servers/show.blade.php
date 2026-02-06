<x-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('servers.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Voltar para servidores
            </a>
            <div class="mt-2 md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $server->name }}</h1>
                    <p class="mt-2 text-sm text-gray-700">{{ $server->ip_address }}</p>
                </div>
                <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                    <a href="{{ route('servers.sites.create', $server) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Novo Site
                    </a>
                    <a href="{{ route('servers.edit', $server) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <!-- Server Info & Metrics -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
            <div class="lg:col-span-2">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Informações do Servidor</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Endereço IP</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $server->ip_address }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($server->status === 'online') bg-green-100 text-green-800
                                        @elseif($server->status === 'offline') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst($server->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Porta SSH</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $server->ssh_port }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Usuário SSH</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $server->ssh_user }}</dd>
                            </div>
                            @if($server->os_type)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Sistema Operacional</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $server->os_type }} {{ $server->os_version }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Autenticação</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $server->auth_type === 'password' ? 'Senha' : 'Chave SSH' }}</dd>
                            </div>
                            @if($server->last_ping_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Último Ping</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $server->last_ping_at->diffForHumans() }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Criado em</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $server->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Latest Metrics -->
            <div>
                @php
                    $metric = $server->latestMetric();
                @endphp
                @if($metric)
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Métricas Atuais</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">CPU</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($metric->cpu_usage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $metric->cpu_usage > 80 ? 'bg-red-600' : ($metric->cpu_usage > 60 ? 'bg-yellow-600' : 'bg-green-600') }}" 
                                             style="width: {{ min($metric->cpu_usage, 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">RAM</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($metric->memory_usage_percentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $metric->memory_usage_percentage > 80 ? 'bg-red-600' : ($metric->memory_usage_percentage > 60 ? 'bg-yellow-600' : 'bg-green-600') }}" 
                                             style="width: {{ min($metric->memory_usage_percentage, 100) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Disco</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($metric->disk_usage_percentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $metric->disk_usage_percentage > 80 ? 'bg-red-600' : ($metric->disk_usage_percentage > 60 ? 'bg-yellow-600' : 'bg-green-600') }}" 
                                             style="width: {{ min($metric->disk_usage_percentage, 100) }}%"></div>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-gray-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">Uptime</span>
                                        <span class="font-medium text-gray-900">{{ $metric->uptime_human }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6 text-center">
                            <p class="text-sm text-gray-500">Nenhuma métrica disponível</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sites -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Sites</h3>
                    <a href="{{ route('servers.sites.create', $server) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        + Adicionar Site
                    </a>
                </div>

                @if($server->sites->count() > 0)
                    <div class="overflow-hidden">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($server->sites as $site)
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $site->name }}</p>
                                            <p class="text-sm text-gray-500 truncate">{{ $site->domain }}</p>
                                        </div>
                                        <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($site->status) }}
                                            </span>
                                            <a href="{{ route('sites.show', $site) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                                Ver →
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhum site configurado neste servidor.</p>
                @endif
            </div>
        </div>
    </div>
</x-layout>
