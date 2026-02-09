<x-layout title="Server Pools">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Server Pools') }}
        </h2>
        <a href="{{ route('scaling.pools.create') }}" class="px-4 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700 transition-colors">
            Novo Server Pool
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-success-900/50 border border-green-500 text-success-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @forelse($pools as $pool)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-white">{{ $pool->name }}</h3>
                                <span class="px-3 py-1 rounded text-xs font-semibold {{ $pool->status === 'active' ? 'bg-success-900 text-success-300' : 'bg-error-900 text-error-300' }}">
                                    {{ ucfirst($pool->status) }}
                                </span>
                            </div>
                            
                            @if($pool->description)
                                <p class="text-sm text-neutral-400 mb-2">{{ $pool->description }}</p>
                            @endif
                            
                            <div class="space-y-1 text-sm text-neutral-400">
                                <div>
                                    <span>Servidores:</span>
                                    <span class="text-white ml-1">{{ $pool->servers->count() }}</span>
                                </div>
                                <div>
                                    <span>Mínimo:</span>
                                    <span class="text-white ml-1">{{ $pool->min_servers }}</span>
                                </div>
                                <div>
                                    <span>Máximo:</span>
                                    <span class="text-white ml-1">{{ $pool->max_servers }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ml-4 flex gap-2">
                            <a href="{{ route('scaling.pools.show', $pool) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-7000">
                                Ver
                            </a>
                            <a href="{{ route('scaling.pools.edit', $pool) }}" class="px-3 py-1 bg-info-600 text-white rounded text-sm hover:bg-info-700">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    <p>Nenhum server pool configurado.</p>
                </div>
            @endforelse

            @if($pools->hasPages())
                <div class="mt-4">
                    {{ $pools->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
