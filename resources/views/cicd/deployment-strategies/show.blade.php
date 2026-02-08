<x-layout title="Estratégia: {{ $deploymentStrategy->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            Estratégia: {{ $deploymentStrategy->name }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('cicd.deployment-strategies.edit', $deploymentStrategy) }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                Editar
            </a>
            <a href="{{ route('cicd.deployment-strategies.index') }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Informações</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-neutral-400">Tipo:</span>
                    <span class="ml-2 px-3 py-1 rounded text-xs font-semibold
                        {{ $deploymentStrategy->type === 'blue_green' ? 'bg-blue-900 text-blue-300' : '' }}
                        {{ $deploymentStrategy->type === 'canary' ? 'bg-yellow-900 text-yellow-300' : '' }}
                        {{ $deploymentStrategy->type === 'rolling' ? 'bg-green-900 text-green-300' : '' }}
                        {{ $deploymentStrategy->type === 'recreate' ? 'bg-neutral-600 text-neutral-300' : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $deploymentStrategy->type)) }}
                    </span>
                </div>
                <div>
                    <span class="text-neutral-400">Status:</span>
                    <span class="ml-2 text-white">{{ $deploymentStrategy->is_default ? 'Padrão' : 'Disponível' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-neutral-400">Descrição:</span>
                    <p class="text-white mt-1">{{ $deploymentStrategy->description }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Configurações</h3>
            
            @if($deploymentStrategy->isBlueGreen())
                <div class="p-4 bg-blue-900/20 border border-blue-700 rounded-lg">
                    <h4 class="text-blue-300 font-semibold mb-3">Blue-Green Deployment</h4>
                    <div>
                        <span class="text-neutral-400">Tempo de espera:</span>
                        <span class="text-white ml-2">{{ $deploymentStrategy->config['blue_green']['wait_time'] ?? 30 }}s</span>
                    </div>
                </div>
            @elseif($deploymentStrategy->isCanary())
                <div class="p-4 bg-yellow-900/20 border border-yellow-700 rounded-lg">
                    <h4 class="text-yellow-300 font-semibold mb-3">Canary Deployment</h4>
                    <div class="space-y-2">
                        <div>
                            <span class="text-neutral-400">Percentage inicial:</span>
                            <span class="text-white ml-2">{{ $deploymentStrategy->config['canary']['initial_percentage'] ?? 10 }}%</span>
                        </div>
                        <div>
                            <span class="text-neutral-400">Incremento:</span>
                            <span class="text-white ml-2">{{ $deploymentStrategy->config['canary']['increment'] ?? 25 }}%</span>
                        </div>
                        <div>
                            <span class="text-neutral-400">Intervalo:</span>
                            <span class="text-white ml-2">{{ $deploymentStrategy->config['canary']['interval_minutes'] ?? 5 }} minutos</span>
                        </div>
                    </div>
                </div>
            @elseif($deploymentStrategy->isRolling())
                <div class="p-4 bg-green-900/20 border border-green-700 rounded-lg">
                    <h4 class="text-green-300 font-semibold mb-3">Rolling Deployment</h4>
                    <div>
                        <span class="text-neutral-400">Tamanho do lote:</span>
                        <span class="text-white ml-2">{{ $deploymentStrategy->config['rolling']['max_batch_size'] ?? 1 }}</span>
                    </div>
                </div>
            @else
                <div class="p-4 bg-neutral-700 border border-neutral-600 rounded-lg">
                    <h4 class="text-neutral-300 font-semibold mb-3">Recreate Deployment</h4>
                    <p class="text-neutral-400 text-sm">Estratégia simples que para a aplicação antiga antes de iniciar a nova.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Health Check</h3>
            @if($deploymentStrategy->health_check)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-neutral-700 rounded-lg">
                        <div class="text-neutral-400 text-sm">URL</div>
                        <div class="text-white mt-1 break-all">{{ $deploymentStrategy->getHealthCheckUrl() }}</div>
                    </div>
                    <div class="p-4 bg-neutral-700 rounded-lg">
                        <div class="text-neutral-400 text-sm">Tentativas</div>
                        <div class="text-white mt-1">{{ $deploymentStrategy->getHealthCheckRetries() }}</div>
                    </div>
                    <div class="p-4 bg-neutral-700 rounded-lg">
                        <div class="text-neutral-400 text-sm">Timeout</div>
                        <div class="text-white mt-1">{{ $deploymentStrategy->getHealthCheckTimeout() }}s</div>
                    </div>
                </div>
            @else
                <p class="text-neutral-400">Nenhum health check configurado.</p>
            @endif
        </div>
    </div>
</x-layout>
