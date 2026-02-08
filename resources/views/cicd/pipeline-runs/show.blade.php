<x-layout title="Execução #{{ $pipelineRun->id }} - {{ $pipelineRun->pipeline->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            Execução #{{ $pipelineRun->id }} - {{ $pipelineRun->pipeline->name }}
        </h2>
        <a href="{{ route('cicd.pipeline-runs.index', $pipelineRun->pipeline) }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
            Voltar às Execuções
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Run Status Card -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="px-3 py-1 rounded text-sm
                        {{ $pipelineRun->status === 'success' ? 'bg-green-900 text-green-300' : '' }}
                        {{ $pipelineRun->status === 'failed' ? 'bg-red-900 text-red-300' : '' }}
                        {{ $pipelineRun->status === 'running' ? 'bg-blue-900 text-blue-300 animate-pulse' : '' }}
                        {{ $pipelineRun->status === 'pending' ? 'bg-neutral-600 text-neutral-300' : '' }}
                        {{ $pipelineRun->status === 'canceled' ? 'bg-yellow-900 text-yellow-300' : '' }}">
                        {{ ucfirst($pipelineRun->status) }}
                    </span>
                </div>
                <div class="flex gap-2">
                    @if($pipelineRun->status === 'running')
                        <form action="{{ route('cicd.pipeline-runs.cancel', $pipelineRun) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                Cancelar Execução
                            </button>
                        </form>
                    @elseif($pipelineRun->status === 'failed')
                        <form action="{{ route('cicd.pipeline-runs.retry', $pipelineRun) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Tentar Novamente
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-neutral-400 text-sm">Iniciado por</div>
                    <div class="text-white">{{ $pipelineRun->triggeredBy->name ?? 'Sistema' }}</div>
                </div>
                <div>
                    <div class="text-neutral-400 text-sm">Iniciado em</div>
                    <div class="text-white">{{ $pipelineRun->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div>
                    <div class="text-neutral-400 text-sm">Duração</div>
                    <div class="text-white">{{ $pipelineRun->getDurationFormatted() }}</div>
                </div>
                @if($pipelineRun->git_branch)
                    <div>
                        <div class="text-neutral-400 text-sm">Branch</div>
                        <div class="text-white">{{ $pipelineRun->git_branch }}</div>
                    </div>
                @endif
                @if($pipelineRun->git_commit)
                    <div>
                        <div class="text-neutral-400 text-sm">Commit</div>
                        <div class="text-white font-mono text-sm">{{ substr($pipelineRun->git_commit, 0, 8) }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stage Results -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Estágios</h3>
            @foreach($pipelineRun->stage_results ?? [] as $stageResult)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold text-white">{{ $stageResult['name'] }}</h4>
                        <span class="px-2 py-1 rounded text-xs
                            {{ $stageResult['status'] === 'success' ? 'bg-green-900 text-green-300' : '' }}
                            {{ $stageResult['status'] === 'failed' ? 'bg-red-900 text-red-300' : '' }}
                            {{ $stageResult['status'] === 'running' ? 'bg-blue-900 text-blue-300' : '' }}
                            {{ $stageResult['status'] === 'skipped' ? 'bg-neutral-600 text-neutral-400' : '' }}">
                            {{ ucfirst($stageResult['status']) }}
                        </span>
                    </div>
                    
                    @if(isset($stageResult['duration']))
                        <div class="text-sm text-neutral-400 mb-2">
                            Duração: {{ round($stageResult['duration'], 2) }}s
                        </div>
                    @endif

                    @if(isset($stageResult['output']))
                        <details class="mt-2">
                            <summary class="cursor-pointer text-blue-400 hover:text-blue-300 text-sm">Ver logs</summary>
                            <pre class="mt-2 p-3 bg-neutral-900 rounded text-xs text-neutral-300 overflow-x-auto">{{ $stageResult['output'] }}</pre>
                        </details>
                    @endif

                    @if(isset($stageResult['error']))
                        <div class="mt-2 p-3 bg-red-900/20 border border-red-700 rounded">
                            <div class="text-red-300 text-sm font-semibold mb-1">Erro:</div>
                            <pre class="text-red-200 text-xs overflow-x-auto">{{ $stageResult['error'] }}</pre>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if($pipelineRun->status === 'running')
        <script>
            // Auto-refresh para execuções em andamento
            setTimeout(() => window.location.reload(), 5000);
        </script>
    @endif
</x-layout>
