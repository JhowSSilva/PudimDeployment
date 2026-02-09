<x-layout title="Load Balancers">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Load Balancers') }}
        </h2>
        <a href="{{ route('scaling.load-balancers.create') }}" class="px-4 py-2 bg-info-600 text-white rounded-lg hover:bg-info-700 transition-colors">
            Novo Load Balancer
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-success-900/50 border border-green-500 text-success-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @forelse($loadBalancers as $lb)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-white">{{ $lb->name }}</h3>
                                <span class="px-3 py-1 rounded text-xs font-semibold {{ $lb->status === 'active' ? 'bg-success-900 text-success-300' : 'bg-error-900 text-error-300' }}">
                                    {{ ucfirst($lb->status) }}
                                </span>
                            </div>
                            
                            <div class="space-y-1 text-sm text-neutral-400">
                                <div>
                                    <span>Algoritmo:</span>
                                    <span class="text-white ml-1">{{ $lb->algorithm }}</span>
                                </div>
                                <div>
                                    <span>Server Pool:</span>
                                    <span class="text-white ml-1">{{ $lb->serverPool->name ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span>IP:</span>
                                    <span class="text-white ml-1">{{ $lb->ip_address }}</span>
                                </div>
                                <div>
                                    <span>Portas:</span>
                                    <span class="text-white ml-1">{{ implode(', ', $lb->ports ?? []) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ml-4 flex gap-2">
                            <a href="{{ route('scaling.load-balancers.show', $lb) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-7000">
                                Ver
                            </a>
                            <a href="{{ route('scaling.load-balancers.edit', $lb) }}" class="px-3 py-1 bg-info-600 text-white rounded text-sm hover:bg-info-700">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                    <p>Nenhum load balancer configurado.</p>
                </div>
            @endforelse

            @if($loadBalancers->hasPages())
                <div class="mt-4">
                    {{ $loadBalancers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
