<x-layout title="Execuções - {{ $pipeline->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            Execuções - {{ $pipeline->name }}
        </h2>
        <a href="{{ route('cicd.pipelines.show', $pipeline) }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
            Voltar ao Pipeline
        </a>
    </div>

    <!-- Status Filter -->
    <div class="mb-4 flex gap-2">
        <a href="{{ route('cicd.pipeline-runs.index', $pipeline) }}" class="px-3 py-2 rounded {{ request('status') === null ? 'bg-blue-600 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Todas
        </a>
        <a href="{{ route('cicd.pipeline-runs.index', [$pipeline, 'status' => 'running']) }}" class="px-3 py-2 rounded {{ request('status') === 'running' ? 'bg-blue-600 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Em Execução
        </a>
        <a href="{{ route('cicd.pipeline-runs.index', [$pipeline, 'status' => 'success']) }}" class="px-3 py-2 rounded {{ request('status') === 'success' ? 'bg-green-600 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Sucesso
        </a>
        <a href="{{ route('cicd.pipeline-runs.index', [$pipeline, 'status' => 'failed']) }}" class="px-3 py-2 rounded {{ request('status') === 'failed' ? 'bg-red-600 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Falhou
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Total</div>
            <div class="text-2xl font-bold text-white">{{ $runs->total() }}</div>
        </div>
        <div class="bg-green-900 p-4 rounded-lg border border-green-700">
            <div class="text-green-200 text-sm">Sucesso</div>
            <div class="text-2xl font-bold text-green-100">{{ $pipeline->runs()->where('status', 'success')->count() }}</div>
        </div>
        <div class="bg-red-900 p-4 rounded-lg border border-red-700">
            <div class="text-red-200 text-sm">Falhou</div>
            <div class="text-2xl font-bold text-red-100">{{ $pipeline->runs()->where('status', 'failed')->count() }}</div>
        </div>
        <div class="bg-blue-900 p-4 rounded-lg border border-blue-700">
            <div class="text-blue-200 text-sm">Em Execução</div>
            <div class="text-2xl font-bold text-blue-100">{{ $pipeline->runs()->where('status', 'running')->count() }}</div>
        </div>
    </div>

    <!-- Runs List -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            @foreach($runs as $run)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-white font-semibold">#{{ $run->id }}</span>
                                <span class="px-2 py-1 rounded text-xs
                                    {{ $run->status === 'success' ? 'bg-green-900 text-green-300' : '' }}
                                    {{ $run->status === 'failed' ? 'bg-red-900 text-red-300' : '' }}
                                    {{ $run->status === 'running' ? 'bg-blue-900 text-blue-300 animate-pulse' : '' }}
                                    {{ $run->status === 'pending' ? 'bg-neutral-600 text-neutral-300' : '' }}
                                    {{ $run->status === 'canceled' ? 'bg-yellow-900 text-yellow-300' : '' }}">
                                    {{ ucfirst($run->status) }}
                                </span>
                                @if($run->git_branch)
                                    <span class="px-2 py-1 rounded text-xs bg-neutral-600 text-neutral-300">
                                        Branch: {{ $run->git_branch }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-neutral-400">
                                Iniciado por: {{ $run->triggeredBy->name ?? 'Sistema' }} • {{ $run->created_at->diffForHumans() }}
                            </div>
                            @if($run->duration)
                                <div class="text-sm text-neutral-500">
                                    Duração: {{ $run->getDurationFormatted() }}
                                </div>
                            @endif
                        </div>
                        <div class="ml-4 flex gap-2">
                            @if($run->status === 'running')
                                <form action="{{ route('cicd.pipeline-runs.cancel', $run) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-yellow-600 text-white rounded text-sm hover:bg-yellow-700">
                                        Cancelar
                                    </button>
                                </form>
                            @elseif($run->status === 'failed')
                                <form action="{{ route('cicd.pipeline-runs.retry', $run) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                        Tentar Novamente
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('cicd.pipeline-runs.show', $run) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-500">
                                Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-4">
                {{ $runs->links() }}
            </div>
        </div>
    </div>
</x-layout>
