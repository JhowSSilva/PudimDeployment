<x-layout title="Load Balancer: {{ $loadBalancer->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ $loadBalancer->name }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('scaling.load-balancers.edit', $loadBalancer) }}" class="px-4 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700">
                Editar
            </a>
            <a href="{{ route('scaling.load-balancers.index') }}" class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-7000">
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-success-900/50 border border-green-500 text-success-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-neutral-800 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-4">Informações</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-neutral-400">Status:</span>
                    <span class="px-3 py-1 rounded text-xs font-semibold {{ $loadBalancer->status === 'active' ? 'bg-success-900 text-success-300' : 'bg-error-900 text-error-300' }}">
                        {{ ucfirst($loadBalancer->status) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Algoritmo:</span>
                    <span class="text-white">{{ $loadBalancer->algorithm }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">IP:</span>
                    <span class="text-white">{{ $loadBalancer->ip_address }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Portas:</span>
                    <span class="text-white">{{ implode(', ', $loadBalancer->ports ?? []) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">SSL:</span>
                    <span class="text-white">{{ $loadBalancer->ssl_enabled ? 'Sim' : 'Não' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Health Check:</span>
                    <span class="text-white">{{ $loadBalancer->health_check_enabled ? 'Sim' : 'Não' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-neutral-800 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-4">Server Pool</h3>
            @if($loadBalancer->serverPool)
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Nome:</span>
                        <span class="text-white">{{ $loadBalancer->serverPool->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Servidores:</span>
                        <span class="text-white">{{ $loadBalancer->serverPool->servers->count() }}</span>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('scaling.pools.show', $loadBalancer->serverPool) }}" class="text-info-400 hover:text-info-300 text-sm">
                            Ver detalhes do pool →
                        </a>
                    </div>
                </div>
            @else
                <p class="text-neutral-500">Nenhum pool associado</p>
            @endif
        </div>
    </div>

    <div class="bg-neutral-800 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-white mb-4">Configurações Avançadas</h3>
        <div class="bg-neutral-700 p-4 rounded">
            <pre class="text-sm text-neutral-300 overflow-x-auto">{{ json_encode($loadBalancer->config ?? [], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</x-layout>
