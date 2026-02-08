<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Aprovações de Deployment
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <a href="?tab=pending" class="px-3 py-2 {{ request('tab', 'pending') === 'pending' ? 'border-b-2 border-blue-500 text-blue-600 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            Pendentes 
                            @if($pendingCount > 0)
                                <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </a>
                        <a href="?tab=history" class="px-3 py-2 {{ request('tab') === 'history' ? 'border-b-2 border-blue-500 text-blue-600 font-medium' : 'text-gray-500 hover:text-gray-700' }}">
                            Histórico
                        </a>
                    </nav>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(request('tab', 'pending') === 'pending')
                <!-- Aprovações Pendentes -->
                @if($approvals->isEmpty())
                    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhuma aprovação pendente</h3>
                        <p class="text-gray-600">Todas as aprovações foram processadas.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($approvals as $approval)
                            <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                Aprovação #{{ $approval->id }}
                                            </h3>
                                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                ⏸ Pendente
                                            </span>
                                            @if($approval->getTimeRemaining())
                                                <span class="text-sm text-red-600">
                                                    ⏰ {{ $approval->getTimeRemaining() }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-600">Pipeline Run:</span>
                                                <a href="{{ route('cicd.runs.show', $approval->pipelineRun) }}" 
                                                    class="ml-1 font-medium text-blue-600 hover:underline">
                                                    #{{ $approval->pipeline_run_id }}
                                                </a>
                                            </div>

                                            <div>
                                                <span class="text-gray-600">Estratégia:</span>
                                                <span class="ml-1 font-medium">{{ $approval->deploymentStrategy->name }}</span>
                                            </div>

                                            <div>
                                                <span class="text-gray-600">Solicitado por:</span>
                                                <span class="ml-1 font-medium">{{ $approval->requestedBy->name }}</span>
                                            </div>

                                            <div>
                                                <span class="text-gray-600">Solicitado em:</span>
                                                <span class="ml-1 font-medium">{{ $approval->requested_at->diffForHumans() }}</span>
                                            </div>

                                            <div>
                                                <span class="text-gray-600">Aprovações necessárias:</span>
                                                <span class="ml-1 font-medium">
                                                    {{ $approval->getApprovalCount() }} / {{ $approval->required_approvals }}
                                                </span>
                                            </div>

                                            @if($approval->getRemainingApprovals() > 0)
                                                <div>
                                                    <span class="text-gray-600">Faltam:</span>
                                                    <span class="ml-1 font-medium text-yellow-600">
                                                        {{ $approval->getRemainingApprovals() }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($approval->request_message)
                                            <div class="mt-3 p-3 bg-gray-50 rounded">
                                                <span class="text-sm text-gray-600">Mensagem:</span>
                                                <p class="mt-1 text-sm">{{ $approval->request_message }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex gap-2 ml-4">
                                        <a href="{{ route('cicd.approvals.show', $approval) }}" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <!-- Histórico -->
                @if($approvals->isEmpty())
                    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhum histórico</h3>
                        <p class="text-gray-600">Nenhuma aprovação foi processada ainda.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($approvals as $approval)
                            <div class="bg-white rounded-lg shadow-sm border-l-4 {{ 
                                $approval->isApproved() ? 'border-green-500' : 
                                ($approval->isRejected() ? 'border-red-500' : 'border-gray-400')
                            }} p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                Aprovação #{{ $approval->id }}
                                            </h3>
                                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ 
                                                $approval->isApproved() ? 'bg-green-100 text-green-800' : 
                                                ($approval->isRejected() ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')
                                            }}">
                                                @if($approval->isApproved())
                                                    ✓ Aprovado
                                                @elseif($approval->isRejected())
                                                    ✗ Rejeitado
                                                @else
                                                    ○ Expirado
                                                @endif
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-600">Pipeline Run:</span>
                                                <a href="{{ route('cicd.runs.show', $approval->pipelineRun) }}" 
                                                    class="ml-1 font-medium text-blue-600 hover:underline">
                                                    #{{ $approval->pipeline_run_id }}
                                                </a>
                                            </div>

                                            <div>
                                                <span class="text-gray-600">Solicitado por:</span>
                                                <span class="ml-1 font-medium">{{ $approval->requestedBy->name }}</span>
                                            </div>

                                            @if($approval->reviewedBy)
                                                <div>
                                                    <span class="text-gray-600">Revisado por:</span>
                                                    <span class="ml-1 font-medium">{{ $approval->reviewedBy->name }}</span>
                                                </div>
                                            @endif

                                            @if($approval->reviewed_at)
                                                <div>
                                                    <span class="text-gray-600">Revisado em:</span>
                                                    <span class="ml-1 font-medium">{{ $approval->reviewed_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($approval->review_comment)
                                            <div class="mt-3 p-3 bg-gray-50 rounded">
                                                <span class="text-sm text-gray-600">Comentário:</span>
                                                <p class="mt-1 text-sm">{{ $approval->review_comment }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ml-4">
                                        <a href="{{ route('cicd.approvals.show', $approval) }}" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            @if($approvals->hasPages())
                <div class="mt-6">
                    {{ $approvals->appends(['tab' => request('tab', 'pending')])->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
