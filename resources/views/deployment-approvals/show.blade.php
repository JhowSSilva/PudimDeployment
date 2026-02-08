<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Aprovação #{{ $approval->id }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="px-3 py-1 text-xs font-medium rounded-full {{ 
                        $approval->isPending() ? 'bg-yellow-100 text-yellow-800' : 
                        ($approval->isApproved() ? 'bg-green-100 text-green-800' : 
                        ($approval->isRejected() ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))
                    }}">
                        @if($approval->isPending())
                            ⏸ Pendente
                        @elseif($approval->isApproved())
                            ✓ Aprovado
                        @elseif($approval->isRejected())
                            ✗ Rejeitado
                        @else
                            ○ Expirado
                        @endif
                    </span>
                    @if($approval->isPending() && $approval->getTimeRemaining())
                        <span class="ml-2 text-red-600">
                            ⏰ Expira em: {{ $approval->getTimeRemaining() }}
                        </span>
                    @endif
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('cicd.approvals.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Informações Gerais -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informações da Solicitação</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <span class="text-sm text-gray-600">Pipeline Run</span>
                        <p class="mt-1 text-lg font-medium">
                            <a href="{{ route('cicd.runs.show', $approval->pipelineRun) }}" class="text-blue-600 hover:underline">
                                Run #{{ $approval->pipeline_run_id }}
                            </a>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Pipeline: {{ $approval->pipelineRun->pipeline->name }}
                        </p>
                    </div>

                    <div>
                        <span class="text-sm text-gray-600">Estratégia de Deployment</span>
                        <p class="mt-1 text-lg font-medium">
                            <a href="{{ route('cicd.deployment-strategies.show', $approval->deploymentStrategy) }}" class="text-blue-600 hover:underline">
                                {{ $approval->deploymentStrategy->name }}
                            </a>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Tipo: {{ ucfirst(str_replace('_', ' ', $approval->deploymentStrategy->type)) }}
                        </p>
                    </div>

                    <div>
                        <span class="text-sm text-gray-600">Solicitado por</span>
                        <p class="mt-1 text-lg font-medium">{{ $approval->requestedBy->name }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $approval->requestedBy->email }}</p>
                    </div>

                    <div>
                        <span class="text-sm text-gray-600">Data da Solicitação</span>
                        <p class="mt-1 text-lg font-medium">{{ $approval->requested_at->format('d/m/Y H:i:s') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $approval->requested_at->diffForHumans() }}</p>
                    </div>

                    @if($approval->expires_at)
                        <div>
                            <span class="text-sm text-gray-600">Data de Expiração</span>
                            <p class="mt-1 text-lg font-medium">{{ $approval->expires_at->format('d/m/Y H:i:s') }}</p>
                            @if($approval->isPending())
                                <p class="text-sm {{ $approval->expires_at->isPast() ? 'text-red-600' : 'text-yellow-600' }} mt-1">
                                    {{ $approval->getTimeRemaining() ?? 'Expirado' }}
                                </p>
                            @endif
                        </div>
                    @endif

                    @if($approval->reviewed_at)
                        <div>
                            <span class="text-sm text-gray-600">Revisado por</span>
                            <p class="mt-1 text-lg font-medium">{{ $approval->reviewedBy->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $approval->reviewed_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    @endif
                </div>

                @if($approval->request_message)
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <span class="text-sm font-medium text-blue-900">Mensagem da Solicitação:</span>
                        <p class="mt-2 text-gray-800">{{ $approval->request_message }}</p>
                    </div>
                @endif

                @if($approval->review_comment)
                    <div class="mt-6 p-4 {{ $approval->isApproved() ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} rounded-lg border">
                        <span class="text-sm font-medium {{ $approval->isApproved() ? 'text-green-900' : 'text-red-900' }}">
                            Comentário da Revisão:
                        </span>
                        <p class="mt-2 text-gray-800">{{ $approval->review_comment }}</p>
                    </div>
                @endif
            </div>

            <!-- Aprovações Necessárias -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status de Aprovações</h3>
                
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Necessárias</span>
                        <p class="text-3xl font-bold text-gray-900">{{ $approval->required_approvals }}</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Recebidas</span>
                        <p class="text-3xl font-bold text-green-600">{{ $approval->getApprovalCount() }}</p>
                    </div>
                    <div class="text-center p-4 {{ $approval->getRemainingApprovals() > 0 ? 'bg-yellow-50' : 'bg-green-50' }} rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Faltam</span>
                        <p class="text-3xl font-bold {{ $approval->getRemainingApprovals() > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                            {{ $approval->getRemainingApprovals() }}
                        </p>
                    </div>
                </div>

                @if($approval->required_approvers && count($approval->required_approvers) > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Aprovadores Autorizados:</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($approval->required_approvers as $approverId)
                                @php
                                    $user = \App\Models\User::find($approverId);
                                @endphp
                                @if($user)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                        {{ $user->name }}
                                        @if($approval->hasUserApproved($user))
                                            <span class="ml-1 text-green-600">✓</span>
                                        @endif
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($approval->approval_history && count($approval->approval_history) > 0)
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Histórico de Aprovações:</h4>
                        <div class="space-y-2">
                            @foreach($approval->approval_history as $history)
                                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded">
                                    <span class="text-green-600 mt-1">✓</span>
                                    <div class="flex-1">
                                        <p class="font-medium">{{ $history['user_name'] ?? 'Usuário' }}</p>
                                        @if(isset($history['comment']))
                                            <p class="text-sm text-gray-600 mt-1">{{ $history['comment'] }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ \Carbon\Carbon::parse($history['approved_at'])->format('d/m/Y H:i:s') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Ações -->
            @if($approval->canBeReviewed() && $approval->canUserApprove(auth()->user()))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Revisar Solicitação</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Aprovar -->
                        <form action="{{ route('cicd.approvals.approve', $approval) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="3" placeholder="Comentário (opcional)"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                            <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                                ✓ Aprovar
                            </button>
                        </form>

                        <!-- Rejeitar -->
                        <form action="{{ route('cicd.approvals.reject', $approval) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="3" placeholder="Motivo da rejeição (opcional)"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium"
                                onclick="return confirm('Tem certeza que deseja rejeitar esta aprovação?')">
                                ✗ Rejeitar
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($approval->isPending() && !$approval->canUserApprove(auth()->user()))
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <p class="text-yellow-800">
                        Você não está autorizado a revisar esta aprovação.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
