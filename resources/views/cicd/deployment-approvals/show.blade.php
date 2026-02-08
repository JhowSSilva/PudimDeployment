<x-layout title="Aprovação #{{ $deploymentApproval->id }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            Aprovação #{{ $deploymentApproval->id }}
        </h2>
        <a href="{{ route('cicd.deployment-approvals.index') }}" class="inline-flex items-center px-3 py-2 bg-neutral-800 border border-neutral-700 rounded-md font-semibold text-xs text-neutral-300 uppercase tracking-widest shadow-sm hover:bg-neutral-700 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
            Voltar
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-900/50 border border-red-500 text-red-200 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Status Card -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="px-4 py-2 rounded text-sm font-semibold
                        {{ $deploymentApproval->status === 'pending' ? 'bg-yellow-900 text-yellow-300' : '' }}
                        {{ $deploymentApproval->status === 'approved' ? 'bg-green-900 text-green-300' : '' }}
                        {{ $deploymentApproval->status === 'rejected' ? 'bg-red-900 text-red-300' : '' }}
                        {{ $deploymentApproval->status === 'expired' ? 'bg-neutral-600 text-neutral-400' : '' }}">
                        {{ ucfirst($deploymentApproval->status) }}
                    </span>
                </div>
                @if($deploymentApproval->status === 'pending' && $deploymentApproval->canUserApprove(auth()->user()))
                    <div class="flex gap-2">
                        <form action="{{ route('cicd.deployment-approvals.approve', $deploymentApproval) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Aprovar
                            </button>
                        </form>
                        <form action="{{ route('cicd.deployment-approvals.reject', $deploymentApproval) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Rejeitar
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-neutral-400 text-sm">Pipeline</div>
                    <div class="text-white">{{ $deploymentApproval->pipelineRun->pipeline->name }}</div>
                </div>
                <div>
                    <div class="text-neutral-400 text-sm">Execução</div>
                    <div class="text-white">#{{ $deploymentApproval->pipelineRun->id }}</div>
                </div>
                @if($deploymentApproval->deployment_strategy_id)
                    <div>
                        <div class="text-neutral-400 text-sm">Estratégia</div>
                        <div class="text-white">{{ $deploymentApproval->deploymentStrategy->name }}</div>
                    </div>
                @endif
                <div>
                    <div class="text-neutral-400 text-sm">Criado em</div>
                    <div class="text-white">{{ $deploymentApproval->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($deploymentApproval->expires_at)
                    <div>
                        <div class="text-neutral-400 text-sm">Expira em</div>
                        <div class="text-white">{{ $deploymentApproval->expires_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Progress Card -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Progresso de Aprovação</h3>
            
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-2">
                    <span>{{ count($deploymentApproval->approval_history ?? []) }} de {{ $deploymentApproval->required_approvers }} aprovações</span>
                    <span>{{ round((count($deploymentApproval->approval_history ?? []) / $deploymentApproval->required_approvers) * 100) }}%</span>
                </div>
                <div class="w-full bg-neutral-700 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ (count($deploymentApproval->approval_history ?? []) / $deploymentApproval->required_approvers) * 100 }}%"></div>
                </div>
            </div>

            @if($deploymentApproval->hasEnoughApprovals())
                <div class="p-3 bg-green-900/30 border border-green-700 rounded text-green-200 text-sm">
                    ✓ Aprovações suficientes obtidas! O deployment pode prosseguir.
                </div>
            @elseif($deploymentApproval->status === 'pending')
                <div class="p-3 bg-yellow-900/30 border border-yellow-700 rounded text-yellow-200 text-sm">
                    ⏳ Aguardando {{ $deploymentApproval->required_approvers - count($deploymentApproval->approval_history ?? []) }} aprovação(ões) adicional(is).
                </div>
            @endif
        </div>
    </div>

    <!-- Approval History -->
    @if(!empty($deploymentApproval->approval_history))
        <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6 text-neutral-300">
                <h3 class="text-lg font-semibold text-white mb-4">Histórico de Aprovações</h3>
                
                <div class="space-y-3">
                    @foreach($deploymentApproval->approval_history as $history)
                        <div class="p-4 bg-neutral-700 rounded-lg border border-neutral-600">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-semibold text-white">{{ $history['user_name'] }}</div>
                                    <div class="text-sm text-neutral-400">{{ $history['user_email'] }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $history['action'] === 'approved' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">
                                        {{ ucfirst($history['action']) }}
                                    </span>
                                    <div class="text-xs text-neutral-400 mt-1">
                                        {{ \Carbon\Carbon::parse($history['timestamp'])->format('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                            @if(isset($history['comment']))
                                <div class="mt-2 p-2 bg-neutral-800 rounded text-sm text-neutral-300">
                                    {{ $history['comment'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Pipeline Run Info -->
    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            <h3 class="text-lg font-semibold text-white mb-4">Informações da Execução</h3>
            
            <div class="space-y-2">
                <div>
                    <span class="text-neutral-400">Status:</span>
                    <span class="ml-2 px-2 py-1 rounded text-xs
                        {{ $deploymentApproval->pipelineRun->status === 'success' ? 'bg-green-900 text-green-300' : '' }}
                        {{ $deploymentApproval->pipelineRun->status === 'failed' ? 'bg-red-900 text-red-300' : '' }}
                        {{ $deploymentApproval->pipelineRun->status === 'running' ? 'bg-blue-900 text-blue-300' : '' }}
                        {{ $deploymentApproval->pipelineRun->status === 'pending' ? 'bg-neutral-600 text-neutral-300' : '' }}">
                        {{ ucfirst($deploymentApproval->pipelineRun->status) }}
                    </span>
                </div>
                @if($deploymentApproval->pipelineRun->git_branch)
                    <div>
                        <span class="text-neutral-400">Branch:</span>
                        <span class="text-white ml-2">{{ $deploymentApproval->pipelineRun->git_branch }}</span>
                    </div>
                @endif
                @if($deploymentApproval->pipelineRun->git_commit)
                    <div>
                        <span class="text-neutral-400">Commit:</span>
                        <span class="text-white ml-2 font-mono">{{ substr($deploymentApproval->pipelineRun->git_commit, 0, 8) }}</span>
                    </div>
                @endif
                <div>
                    <a href="{{ route('cicd.pipeline-runs.show', $deploymentApproval->pipelineRun) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                        Ver detalhes da execução →
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>
