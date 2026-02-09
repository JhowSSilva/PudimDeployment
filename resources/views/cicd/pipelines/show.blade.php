<x-layout title="Pipeline: {{ $pipeline->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            Pipeline: {{ $pipeline->name }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('cicd.pipelines.edit', $pipeline) }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-info-600 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                Editar
            </a>
            <a href="{{ route('cicd.pipelines.index') }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-info-600 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-success-900/50 border border-green-500 text-success-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Total Execuções</div>
            <div class="text-2xl font-bold text-white">{{ $pipeline->runs()->count() }}</div>
        </div>
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Taxa de Sucesso</div>
            <div class="text-2xl font-bold text-success-400">{{ number_format($pipeline->getSuccessRate(), 1) }}%</div>
        </div>
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Status</div>
            <div class="text-lg font-semibold {{ $pipeline->status === 'active' ? 'text-success-400' : 'text-neutral-400' }}">
                {{ ucfirst($pipeline->status) }}
            </div>
        </div>
        <div class="bg-neutral-800 p-4 rounded-lg border border-neutral-700">
            <div class="text-neutral-400 text-sm">Última Execução</div>
            <div class="text-sm text-white">
                {{ $pipeline->runs()->latest()->first()?->created_at?->diffForHumans() ?? 'Nunca' }}
            </div>
        </div>
    </div>

    <!-- Pipeline Info -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Informações</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-neutral-400">Trigger:</span>
                    <span class="text-white ml-2">{{ ucfirst(str_replace('_', ' ', $pipeline->trigger_type)) }}</span>
                </div>
                <div>
                    <span class="text-neutral-400">Auto Deploy:</span>
                    <span class="text-white ml-2">{{ $pipeline->auto_deploy ? 'Sim' : 'Não' }}</span>
                </div>
                <div>
                    <span class="text-neutral-400">Timeout:</span>
                    <span class="text-white ml-2">{{ $pipeline->timeout }}s</span>
                </div>
                <div>
                    <span class="text-neutral-400">Retenção:</span>
                    <span class="text-white ml-2">{{ $pipeline->retention_days }} dias</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stages -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Estágios</h3>
            @foreach($pipeline->stages()->orderBy('order')->get() as $stage)
                <div class="mb-3 p-4 bg-neutral-700 rounded-lg border border-neutral-600">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-semibold text-white">{{ $stage->order }}. {{ $stage->name }}</h4>
                            <p class="text-sm text-neutral-400">Tipo: {{ ucfirst($stage->type) }}</p>
                            @if($stage->parallel)
                                <span class="text-xs text-info-400">Paralelo</span>
                            @endif
                            @if($stage->allow_failure)
                                <span class="text-xs text-yellow-400">Permite Falha</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Runs -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">Execuções Recentes</h3>
                <a href="{{ route('cicd.pipeline-runs.index', $pipeline) }}" class="text-info-400 hover:text-info-300 text-sm">
                    Ver todas →
                </a>
            </div>
            @foreach($pipeline->runs()->latest()->take(5)->get() as $run)
                <div class="mb-3 p-4 bg-neutral-700 rounded-lg border border-neutral-600">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-white font-semibold">#{{ $run->id }}</span>
                            <span class="ml-2 px-2 py-1 rounded text-xs
                                {{ $run->status === 'success' ? 'bg-success-900 text-success-300' : '' }}
                                {{ $run->status === 'failed' ? 'bg-error-900 text-error-300' : '' }}
                                {{ $run->status === 'running' ? 'bg-info-900 text-info-300 animate-pulse' : '' }}
                                {{ $run->status === 'pending' ? 'bg-neutral-600 text-neutral-300' : '' }}">
                                {{ ucfirst($run->status) }}
                            </span>
                            <span class="ml-2 text-neutral-400 text-sm">{{ $run->created_at->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('cicd.pipeline-runs.show', $run) }}" class="text-info-400 hover:text-info-300 text-sm">
                            Detalhes
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layout>
