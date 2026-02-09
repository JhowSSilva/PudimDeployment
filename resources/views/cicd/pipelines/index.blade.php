<x-layout title="Pipelines CI/CD">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Pipelines CI/CD') }}
        </h2>
        <a href="{{ route('cicd.pipelines.create') }}" class="inline-flex items-center px-4 py-2 bg-info-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-info-700 active:bg-info-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Criar Pipeline
        </a>
    </div>

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            @if(session('success'))
                <div class="mb-4 p-4 bg-success-900/50 border border-green-500 text-success-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($pipelines as $pipeline)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white mb-2">{{ $pipeline->name }}</h3>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span class="px-2 py-1 rounded text-xs bg-neutral-600 text-neutral-300">
                                    Trigger: {{ ucfirst(str_replace('_', ' ', $pipeline->trigger_type)) }}
                                </span>
                                @if($pipeline->status === 'active')
                                    <span class="px-2 py-1 rounded text-xs bg-success-900 text-success-300">Ativo</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-neutral-600 text-neutral-400">Pausado</span>
                                @endif
                                @if($pipeline->auto_deploy)
                                    <span class="px-2 py-1 rounded text-xs bg-info-900 text-info-300">Auto Deploy</span>
                                @endif
                            </div>
                            <p class="text-sm text-neutral-400">{{ $pipeline->description }}</p>
                        </div>
                        <div class="ml-4 flex gap-2">
                            @if($pipeline->status === 'paused')
                                <form action="{{ route('cicd.pipelines.activate', $pipeline) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-success-500 text-white rounded text-sm hover:bg-success-700">
                                        Ativar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('cicd.pipelines.pause', $pipeline) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-warning-500 text-white rounded text-sm hover:bg-warning-700">
                                        Pausar
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('cicd.pipelines.run', $pipeline) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-info-600 text-white rounded text-sm hover:bg-info-700">
                                    Executar
                                </button>
                            </form>
                            <a href="{{ route('cicd.pipelines.show', $pipeline) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-7000">
                                Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>Nenhum pipeline criado ainda.</p>
                    <a href="{{ route('cicd.pipelines.create') }}" class="text-info-400 hover:text-info-300 mt-2 inline-block">
                        Criar seu primeiro pipeline →
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
