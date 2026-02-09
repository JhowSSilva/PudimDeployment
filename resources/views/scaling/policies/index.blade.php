<x-layout title="Políticas de Auto-scaling">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Políticas de Auto-scaling') }}
        </h2>
        <a href="{{ route('scaling.policies.create') }}" class="px-4 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700 transition-colors">
            Nova Política
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-success-900/50 border border-green-500 text-success-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @forelse($policies as $policy)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-white">{{ $policy->name }}</h3>
                                <span class="px-3 py-1 rounded text-xs font-semibold {{ $policy->is_active ? 'bg-success-900 text-success-300' : 'bg-neutral-600 text-neutral-300' }}">
                                    {{ $policy->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>
                            
                            <div class="space-y-1 text-sm text-neutral-400">
                                <div>
                                    <span>Tipo:</span>
                                    <span class="text-white ml-1">{{ ucfirst($policy->type) }}</span>
                                </div>
                                <div>
                                    <span>Scale Up/Down:</span>
                                    <span class="text-white ml-1">{{ $policy->threshold_up }}% / {{ $policy->threshold_down }}%</span>
                                </div>
                                <div>
                                    <span>Ação:</span>
                                    <span class="text-white ml-1">+{{ $policy->scale_up_by }} / -{{ $policy->scale_down_by }} servidores</span>
                                </div>
                                @if($policy->serverPool)
                                    <div>
                                        <span>Pool:</span>
                                        <a href="{{ route('scaling.pools.show', $policy->serverPool) }}" class="text-info-400 hover:text-info-300 ml-1">
                                            {{ $policy->serverPool->name }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="ml-4 flex gap-2">
                            <a href="{{ route('scaling.policies.show', $policy) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-7000">
                                Ver
                            </a>
                            <a href="{{ route('scaling.policies.edit', $policy) }}" class="px-3 py-1 bg-info-600 text-white rounded text-sm hover:bg-info-700">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <p>Nenhuma política de auto-scaling configurada.</p>
                </div>
            @endforelse

            @if($policies->hasPages())
                <div class="mt-4">
                    {{ $policies->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
