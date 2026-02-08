<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Run #{{ $run->id }} - {{ $run->pipeline->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
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
                    <span class="ml-3">{{ $run->created_at->format('d/m/Y H:i:s') }}</span>
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('cicd.runs.index', $run->pipeline) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Voltar
                </a>
                @if($run->isRunning() || $run->isPending())
                    <form action="{{ route('cicd.runs.cancel', $run) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Cancelar Execução
                        </button>
                    </form>
                @elseif($run->isFinished())
                    <form action="{{ route('cicd.runs.retry', $run) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Reexecutar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Informações Gerais -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informações da Execução</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <span class="text-sm text-gray-600">Origem do Trigger</span>
                        <p class="mt-1 text-lg font-medium">{{ ucfirst(str_replace('_', ' ', $run->trigger_source)) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Acionado por</span>
                        <p class="mt-1 text-lg font-medium">{{ $run->triggeredBy->name }}</p>
                    </div>
                    @if($run->git_branch)
                        <div>
                            <span class="text-sm text-gray-600">Branch</span>
                            <p class="mt-1 text-lg font-medium font-mono">{{ $run->git_branch }}</p>
                        </div>
                    @endif
                    @if($run->git_commit_hash)
                        <div>
                            <span class="text-sm text-gray-600">Commit</span>
                            <p class="mt-1 text-lg font-medium font-mono text-sm">{{ $run->git_commit_hash }}</p>
                        </div>
                    @endif
                    @if($run->started_at)
                        <div>
                            <span class="text-sm text-gray-600">Iniciado em</span>
                            <p class="mt-1 text-lg font-medium">{{ $run->started_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    @endif
                    @if($run->finished_at)
                        <div>
                            <span class="text-sm text-gray-600">Finalizado em</span>
                            <p class="mt-1 text-lg font-medium">{{ $run->finished_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    @endif
                    @if($run->isFinished())
                        <div>
                            <span class="text-sm text-gray-600">Duração Total</span>
                            <p class="mt-1 text-lg font-medium text-blue-600">{{ $run->getDurationFormatted() }}</p>
                        </div>
                    @endif
                    @if($run->deployment_id)
                        <div>
                            <span class="text-sm text-gray-600">Deployment</span>
                            <p class="mt-1 text-lg font-medium">
                                <a href="{{ route('deployments.show', $run->deployment_id) }}" class="text-blue-600 hover:underline">
                                    #{{ $run->deployment_id }}
                                </a>
                            </p>
                        </div>
                    @endif
                </div>

                @if($run->git_commit_message)
                    <div class="mt-4 p-3 bg-gray-50 rounded">
                        <span class="text-sm text-gray-600">Mensagem do Commit:</span>
                        <p class="mt-1 text-sm font-mono">{{ $run->git_commit_message }}</p>
                    </div>
                @endif
            </div>

            <!-- Stages -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Stages Executados</h3>
                
                @if($run->stage_results && count($run->stage_results) > 0)
                    <div class="space-y-4">
                        @foreach($run->stage_results as $stageId => $result)
                            @php
                                $stage = $run->pipeline->stages->firstWhere('id', $stageId);
                                $status = $result['status'] ?? 'pending';
                            @endphp
                            @if($stage)
                                <div class="border rounded-lg p-4 {{ 
                                    $status === 'success' ? 'border-green-300 bg-green-50' : 
                                    ($status === 'failed' ? 'border-red-300 bg-red-50' : 
                                    ($status === 'running' ? 'border-blue-300 bg-blue-50' : 'border-gray-300'))
                                }}">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="text-lg font-medium">
                                                <span class="text-gray-500">#{{ $stage->order }}</span>
                                                {{ $stage->name }}
                                            </h4>
                                            <div class="flex gap-2 mt-1">
                                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                                                    {{ ucfirst($stage->type) }}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-medium rounded {{ 
                                                    $status === 'success' ? 'bg-green-100 text-green-800' : 
                                                    ($status === 'failed' ? 'bg-red-100 text-red-800' : 
                                                    ($status === 'running' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))
                                                }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                                @if(isset($result['duration']))
                                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">
                                                        {{ gmdate('i:s', $result['duration']) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        @if(isset($result['exit_code']))
                                            <span class="text-sm font-mono">
                                                Exit Code: {{ $result['exit_code'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(isset($result['output']) && !empty($result['output']))
                                        <div class="mt-3">
                                            <details class="cursor-pointer">
                                                <summary class="text-sm font-medium text-gray-700 hover:text-gray-900">
                                                    Ver Output
                                                </summary>
                                                <pre class="mt-2 p-3 bg-gray-900 text-green-400 rounded text-xs overflow-x-auto">{{ $result['output'] }}</pre>
                                            </details>
                                        </div>
                                    @endif

                                    @if(isset($result['error']) && !empty($result['error']))
                                        <div class="mt-3">
                                            <p class="text-sm font-medium text-red-700 mb-2">Erro:</p>
                                            <pre class="p-3 bg-red-900 text-red-100 rounded text-xs overflow-x-auto">{{ $result['error'] }}</pre>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">Nenhum stage foi executado ainda.</p>
                @endif
            </div>

            <!-- Logs Completos -->
            @if($run->output_log || $run->error_log)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Logs Completos</h3>
                    
                    @if($run->output_log)
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Output</h4>
                            <pre class="p-4 bg-gray-900 text-green-400 rounded text-xs overflow-x-auto max-h-96 overflow-y-auto">{{ $run->output_log }}</pre>
                        </div>
                    @endif

                    @if($run->error_log)
                        <div>
                            <h4 class="text-sm font-medium text-red-700 mb-2">Errors</h4>
                            <pre class="p-4 bg-red-900 text-red-100 rounded text-xs overflow-x-auto max-h-96 overflow-y-auto">{{ $run->error_log }}</pre>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Approval -->
            @if($run->approval)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Aprovação Necessária</h3>
                    <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4">
                        <p class="text-sm text-yellow-800">
                            Esta execução requer aprovação antes de continuar.
                        </p>
                        <a href="{{ route('cicd.approvals.show', $run->approval) }}" class="mt-2 inline-block text-sm font-medium text-yellow-900 underline">
                            Ver detalhes da aprovação →
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($run->isRunning())
        <script>
            // Auto-refresh para runs em execução
            setTimeout(() => location.reload(), 5000);
        </script>
    @endif
</x-app-layout>
