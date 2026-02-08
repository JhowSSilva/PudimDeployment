<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Execuções: {{ $pipeline->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Histórico completo de execuções deste pipeline
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('cicd.pipelines.show', $pipeline) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Voltar ao Pipeline
                </a>
                @if($pipeline->canRun())
                    <form action="{{ route('cicd.pipelines.run', $pipeline) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            ▶ Executar Agora
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <form method="GET" class="flex gap-4">
                    <select name="status" class="rounded-md border-gray-300">
                        <option value="">Todos os status</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>✓ Sucesso</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>✗ Falhou</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>● Executando</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏸ Pendente</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>○ Cancelado</option>
                    </select>

                    <select name="trigger_source" class="rounded-md border-gray-300">
                        <option value="">Todas as fontes</option>
                        <option value="manual" {{ request('trigger_source') === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="git_push" {{ request('trigger_source') === 'git_push' ? 'selected' : '' }}>Git Push</option>
                        <option value="webhook" {{ request('trigger_source') === 'webhook' ? 'selected' : '' }}>Webhook</option>
                        <option value="schedule" {{ request('trigger_source') === 'schedule' ? 'selected' : '' }}>Agendado</option>
                    </select>

                    <input type="text" name="branch" placeholder="Branch..." value="{{ request('branch') }}" 
                        class="rounded-md border-gray-300">

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Filtrar
                    </button>
                    
                    @if(request()->has('status') || request()->has('trigger_source') || request()->has('branch'))
                        <a href="{{ route('cicd.runs.index', $pipeline) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Limpar
                        </a>
                    @endif
                </form>
            </div>

            <!-- Lista de Runs -->
            @if($runs->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhuma execução encontrada</h3>
                    <p class="text-gray-600">
                        @if(request()->has('status') || request()->has('trigger_source') || request()->has('branch'))
                            Nenhuma execução corresponde aos filtros selecionados.
                        @else
                            Este pipeline ainda não foi executado.
                        @endif
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($runs as $run)
                        <div class="bg-white rounded-lg shadow-sm border-l-4 {{ 
                            $run->isSuccess() ? 'border-green-500' : 
                            ($run->isFailed() ? 'border-red-500' : 
                            ($run->isRunning() ? 'border-blue-500' : 
                            ($run->isCancelled() ? 'border-gray-400' : 'border-yellow-500')))
                        }}">
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                Run #{{ $run->id }}
                                            </h3>
                                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $run->getStatusBadge() }}">
                                                @if($run->isSuccess())
                                                    ✓ Sucesso
                                                @elseif($run->isFailed())
                                                    ✗ Falhou
                                                @elseif($run->isRunning())
                                                    ● Executando
                                                @elseif($run->isCancelled())
                                                    ○ Cancelado
                                                @else
                                                    ⏸ Pendente
                                                @endif
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-600">Origem:</span>
                                                <span class="ml-1 font-medium">{{ ucfirst(str_replace('_', ' ', $run->trigger_source)) }}</span>
                                            </div>
                                            
                                            <div>
                                                <span class="text-gray-600">Acionado por:</span>
                                                <span class="ml-1 font-medium">{{ $run->triggeredBy->name }}</span>
                                            </div>

                                            @if($run->git_branch)
                                                <div>
                                                    <span class="text-gray-600">Branch:</span>
                                                    <span class="ml-1 font-medium font-mono text-xs">{{ $run->git_branch }}</span>
                                                </div>
                                            @endif

                                            @if($run->git_commit_hash)
                                                <div>
                                                    <span class="text-gray-600">Commit:</span>
                                                    <span class="ml-1 font-medium font-mono text-xs">{{ substr($run->git_commit_hash, 0, 7) }}</span>
                                                </div>
                                            @endif

                                            @if($run->isFinished())
                                                <div>
                                                    <span class="text-gray-600">Duração:</span>
                                                    <span class="ml-1 font-medium">{{ $run->getDurationFormatted() }}</span>
                                                </div>
                                            @endif

                                            <div>
                                                <span class="text-gray-600">Iniciado:</span>
                                                <span class="ml-1 font-medium">{{ $run->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        @if($run->git_commit_message)
                                            <p class="mt-3 text-sm text-gray-700 bg-gray-50 p-2 rounded font-mono">
                                                {{ Str::limit($run->git_commit_message, 100) }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex gap-2 ml-4">
                                        <a href="{{ route('cicd.runs.show', $run) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                            Ver Detalhes
                                        </a>

                                        @if($run->isRunning() || $run->isPending())
                                            <form action="{{ route('cicd.runs.cancel', $run) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                                                    Cancelar
                                                </button>
                                            </form>
                                        @elseif($run->isFinished())
                                            <form action="{{ route('cicd.runs.retry', $run) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm">
                                                    Reexecutar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $runs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
