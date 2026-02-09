<x-layout title="Política: {{ $policy->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ $policy->name }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('scaling.policies.edit', $policy) }}" class="px-4 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700">
                Editar
            </a>
            <a href="{{ route('scaling.policies.index') }}" class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-7000">
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
            <h3 class="text-lg font-semibold text-white mb-4">Configuração</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-neutral-400">Status:</span>
                    <span class="px-3 py-1 rounded text-xs font-semibold {{ $policy->is_active ? 'bg-success-900 text-success-300' : 'bg-neutral-600 text-neutral-300' }}">
                        {{ $policy->is_active ? 'Ativa' : 'Inativa' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Tipo:</span>
                    <span class="text-white">{{ ucfirst($policy->type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Threshold Up:</span>
                    <span class="text-white">{{ $policy->threshold_up }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Threshold Down:</span>
                    <span class="text-white">{{ $policy->threshold_down }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Scale Up:</span>
                    <span class="text-white">+{{ $policy->scale_up_by }} servidor(es)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Scale Down:</span>
                    <span class="text-white">-{{ $policy->scale_down_by }} servidor(es)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Mín/Máx Servidores:</span>
                    <span class="text-white">{{ $policy->min_servers }} / {{ $policy->max_servers }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Cooldown:</span>
                    <span class="text-white">{{ $policy->cooldown_minutes }} min</span>
                </div>
            </div>

            @if($policy->description)
                <div class="mt-4 pt-4 border-t border-neutral-700">
                    <p class="text-sm text-neutral-400">{{ $policy->description }}</p>
                </div>
            @endif
        </div>

        <div class="bg-neutral-800 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-4">Server Pool</h3>
            @if($policy->serverPool)
                <div class="p-4 bg-neutral-700 rounded-lg">
                    <h4 class="text-white font-medium mb-2">{{ $policy->serverPool->name }}</h4>
                    <div class="space-y-1 text-sm text-neutral-400">
                        <div>
                            <span>Status:</span>
                            <span class="ml-1 px-2 py-0.5 rounded text-xs {{ $policy->serverPool->status === 'active' ? 'bg-success-900 text-success-300' : 'bg-error-900 text-error-300' }}">
                                {{ ucfirst($policy->serverPool->status) }}
                            </span>
                        </div>
                        <div>
                            <span>Servidores:</span>
                            <span class="text-white ml-1">{{ $policy->serverPool->servers->count() }}</span>
                        </div>
                        <div>
                            <span>Min/Max:</span>
                            <span class="text-white ml-1">{{ $policy->serverPool->min_servers }} / {{ $policy->serverPool->max_servers }}</span>
                        </div>
                    </div>
                    <a href="{{ route('scaling.pools.show', $policy->serverPool) }}" class="mt-3 inline-block text-info-400 hover:text-info-300 text-sm">
                        Ver Pool →
                    </a>
                </div>
            @else
                <p class="text-neutral-500">Nenhum pool associado</p>
            @endif
        </div>
    </div>

    <div class="bg-neutral-800 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-white mb-4">Histórico de Execuções</h3>
        <div class="space-y-2">
            @forelse($policy->executions()->latest()->limit(10)->get() as $execution)
                <div class="p-3 bg-neutral-700 rounded flex justify-between items-center">
                    <div>
                        <span class="text-white text-sm">{{ $execution->action }}</span>
                        <p class="text-xs text-neutral-400">{{ $execution->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="px-3 py-1 rounded text-xs font-semibold {{ $execution->status === 'success' ? 'bg-success-900 text-success-300' : 'bg-error-900 text-error-300' }}">
                        {{ ucfirst($execution->status) }}
                    </span>
                </div>
            @empty
                <p class="text-neutral-500 text-center py-4">Nenhuma execução registrada</p>
            @endforelse
        </div>
    </div>
</x-layout>
