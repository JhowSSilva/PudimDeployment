<x-layout title="Estratégias de Deployment">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Estratégias de Deployment') }}
        </h2>
        <a href="{{ route('cicd.deployment-strategies.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Criar Estratégia
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Info Box -->
    <div class="mb-6 p-4 bg-blue-900/30 border border-blue-700 rounded-lg text-blue-200">
        <h3 class="font-semibold mb-2">Sobre Estratégias de Deployment</h3>
        <p class="text-sm">Estratégias definem como seus deploys são realizados. Escolha entre Blue-Green (zero downtime), Canary (gradual), Rolling (sequencial) ou Recreate (simples).</p>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            @forelse($strategies as $strategy)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white mb-2">{{ $strategy->name }}</h3>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span class="px-3 py-1 rounded text-xs font-semibold
                                    {{ $strategy->type === 'blue_green' ? 'bg-blue-900 text-blue-300' : '' }}
                                    {{ $strategy->type === 'canary' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                    {{ $strategy->type === 'rolling' ? 'bg-green-900 text-green-300' : '' }}
                                    {{ $strategy->type === 'recreate' ? 'bg-neutral-600 text-neutral-300' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $strategy->type)) }}
                                </span>
                                @if($strategy->is_default)
                                    <span class="px-2 py-1 rounded text-xs bg-amber-900 text-amber-300">Padrão</span>
                                @endif
                            </div>
                            <p class="text-sm text-neutral-400">{{ $strategy->description }}</p>
                        </div>
                        <div class="ml-4 flex gap-2">
                            @if(!$strategy->is_default)
                                <form action="{{ route('cicd.deployment-strategies.make-default', $strategy) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-amber-600 text-white rounded text-sm hover:bg-amber-700">
                                        Tornar Padrão
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('cicd.deployment-strategies.show', $strategy) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-500">
                                Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                    <p>Nenhuma estratégia criada ainda.</p>
                    <a href="{{ route('cicd.deployment-strategies.create') }}" class="text-blue-400 hover:text-blue-300 mt-2 inline-block">
                        Criar sua primeira estratégia →
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
