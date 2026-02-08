<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $pipeline->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Pipeline CI/CD</p>
            </div>
            <div class="flex gap-2">
                @if($pipeline->canRun())
                    <form action="{{ route('cicd.pipelines.run', $pipeline) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Executar Agora
                        </button>
                    </form>
                @endif
                <a href="{{ route('cicd.pipelines.edit', $pipeline) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Visão Geral</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="mt-1">
                            @if($pipeline->status === 'active')
                                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800">● Ativo</span>
                            @elseif($pipeline->status === 'paused')
                                <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">● Pausado</span>
                            @else
                                <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-800">● Desabilitado</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Trigger</div>
                        <div class="mt-1 font-medium">{{ ucfirst($pipeline->trigger_type) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Site</div>
                        <div class="mt-1 font-medium">{{ $pipeline->site?->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Auto Deploy</div>
                        <div class="mt-1 font-medium">{{ $pipeline->auto_deploy ? 'Sim' : 'Não' }}</div>
                    </div>
                </div>

                @if($pipeline->description)
                    <div class="mt-4 pt-4 border-t">
                        <div class="text-sm text-gray-500">Descrição</div>
                        <div class="mt-1">{{ $pipeline->description }}</div>
                    </div>
                @endif
            </div>

            <!-- Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Total de Execuções</div>
                    <div class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_runs'] }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Taxa de Sucesso</div>
                    <div class="text-3xl font-bold text-green-600 mt-2">{{ number_format($stats['success_rate'], 1) }}%</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Duração Média</div>
                    <div class="text-3xl font-bold text-blue-600 mt-2">
                        @if($stats['avg_duration'])
                            {{ gmdate('i:s', $stats['avg_duration']) }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Último Sucesso</div>
                    <div class="text-sm font-medium text-gray-900 mt-2">
                        @if($stats['last_success'])
                            {{ $stats['last_success']->finished_at->diffForHumans() }}
                        @else
                            Nunca
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stages -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Stages</h3>
                    <span class="text-sm text-gray-500">{{ $pipeline->stages->count() }} stages</span>
                </div>

                @if($pipeline->stages->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <p>Nenhum stage configurado ainda.</p>
                        <p class="text-sm mt-2">Adicione stages para executar comandos no pipeline.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($pipeline->stages as $stage)
                            <div class="border rounded-lg p-4 {{ $stage->allow_failure ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200' }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{ $stage->order }}. {{ $stage->name }}</span>
                                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">{{ $stage->type }}</span>
                                            @if($stage->parallel)
                                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-600">Paralelo</span>
                                            @endif
                                            @if($stage->allow_failure)
                                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-600">Permite falha</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            Timeout: {{ $stage->timeout_minutes }}min | When: {{ $stage->when }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Execuções Recentes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Execuções Recentes</h3>
                    <a href="{{ route('cicd.pipeline-runs.index', $pipeline) }}" class="text-sm text-blue-600 hover:text-blue-800">
                        Ver todas →
                    </a>
                </div>

                @if($pipeline->runs->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        Nenhuma execução ainda.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($pipeline->runs as $run)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">#{{ $run->id }}</span>
                                            @if($run->status === 'success')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">✓ Sucesso</span>
                                            @elseif($run->status === 'failed')
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">✗ Falhou</span>
                                            @elseif($run->status === 'running')
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">● Executando</span>
                                            @elseif($run->status === 'cancelled')
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">○ Cancelado</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">⏸ Pendente</span>
                                            @endif
                                            <span class="text-sm text-gray-500">
                                                {{ $run->trigger_source }} por {{ $run->triggeredBy?->name }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            @if($run->git_branch)
                                                Branch: {{ $run->git_branch }}
                                            @endif
                                            @if($run->git_commit_hash)
                                                | Commit: {{ substr($run->git_commit_hash, 0, 7) }}
                                            @endif
                                            @if($run->duration_seconds)
                                                | Duração: {{ $run->getDurationFormatted() }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $run->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <a href="{{ route('cicd.pipeline-runs.show', $run) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Ver detalhes →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Ações -->
            <div class="flex justify-between">
                <form action="{{ route('cicd.pipelines.destroy', $pipeline) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este pipeline?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800">
                        Excluir Pipeline
                    </button>
                </form>

                @if($pipeline->isActive())
                    <form action="{{ route('cicd.pipelines.pause', $pipeline) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                            Pausar Pipeline
                        </button>
                    </form>
                @elseif($pipeline->isPaused())
                    <form action="{{ route('cicd.pipelines.activate', $pipeline) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-800">
                            Ativar Pipeline
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
