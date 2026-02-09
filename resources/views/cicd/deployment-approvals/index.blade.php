<x-layout title="Aprovações de Deployment">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Aprovações de Deployment') }}
        </h2>
    </div>

    <!-- Tabs -->
    <div class="mb-4 flex gap-2">
        <a href="{{ route('cicd.deployment-approvals.index') }}" class="px-4 py-2 rounded {{ request('status') === null ? 'bg-info-600 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Todas
        </a>
        <a href="{{ route('cicd.deployment-approvals.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded {{ request('status') === 'pending' ? 'bg-warning-500 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Pendentes
            @if($pendingCount > 0)
                <span class="ml-2 px-2 py-0.5 bg-warning-900 text-warning-300 rounded-full text-xs font-medium">
                    {{ $pendingCount }}
                </span>
            @endif
        </a>
        <a href="{{ route('cicd.deployment-approvals.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded {{ request('status') === 'approved' ? 'bg-success-500 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Aprovadas
        </a>
        <a href="{{ route('cicd.deployment-approvals.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded {{ request('status') === 'rejected' ? 'bg-error-500 text-white' : 'bg-neutral-800 text-neutral-300' }}">
            Rejeitadas
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-success-900/50 border border-green-500 text-success-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 text-neutral-300">
            @forelse($approvals as $approval)
                <div class="mb-4 p-4 bg-neutral-700 rounded-lg border border-neutral-600 hover:border-blue-500 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-white">Aprovação #{{ $approval->id }}</h3>
                                <span class="px-3 py-1 rounded text-xs font-semibold
                                    {{ $approval->status === 'pending' ? 'bg-warning-900 text-warning-300' : '' }}
                                    {{ $approval->status === 'approved' ? 'bg-success-900 text-success-300' : '' }}
                                    {{ $approval->status === 'rejected' ? 'bg-error-900 text-error-300' : '' }}
                                    {{ $approval->status === 'expired' ? 'bg-neutral-600 text-neutral-400' : '' }}">
                                    {{ ucfirst($approval->status) }}
                                </span>
                            </div>
                            
                            <div class="space-y-1 text-sm text-neutral-400">
                                <div>
                                    <span>Pipeline:</span>
                                    <span class="text-white ml-1">{{ $approval->pipelineRun->pipeline->name }}</span>
                                </div>
                                <div>
                                    <span>Execução:</span>
                                    <span class="text-white ml-1">#{{ $approval->pipelineRun->id }}</span>
                                </div>
                                @if($approval->deployment_strategy_id)
                                    <div>
                                        <span>Estratégia:</span>
                                        <span class="text-white ml-1">{{ $approval->deploymentStrategy->name }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span>Aprovadores:</span>
                                    <span class="text-white ml-1">{{ count($approval->approval_history ?? []) }}/{{ $approval->required_approvers }}</span>
                                </div>
                                @if($approval->status === 'pending' && $approval->expires_at)
                                    <div>
                                        <span>Expira em:</span>
                                        <span class="text-yellow-400 ml-1" x-data="countdown('{{ $approval->expires_at }}')">
                                            <span x-text="timeLeft"></span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="ml-4 flex gap-2">
                            @if($approval->status === 'pending' && $approval->canUserApprove(auth()->user()))
                                <form action="{{ route('cicd.deployment-approvals.approve', $approval) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-success-500 text-white rounded text-sm hover:bg-success-700">
                                        Aprovar
                                    </button>
                                </form>
                                <form action="{{ route('cicd.deployment-approvals.reject', $approval) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-error-500 text-white rounded text-sm hover:bg-error-700">
                                        Rejeitar
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('cicd.deployment-approvals.show', $approval) }}" class="px-3 py-1 bg-neutral-600 text-white rounded text-sm hover:bg-neutral-7000">
                                Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-neutral-500">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>Nenhuma aprovação encontrada.</p>
                </div>
            @endforelse

            @if($approvals->hasPages())
                <div class="mt-4">
                    {{ $approvals->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function countdown(expiresAt) {
            return {
                timeLeft: '',
                init() {
                    this.updateCountdown();
                    setInterval(() => this.updateCountdown(), 1000);
                },
                updateCountdown() {
                    const now = new Date().getTime();
                    const expiry = new Date(expiresAt).getTime();
                    const distance = expiry - now;
                    
                    if (distance < 0) {
                        this.timeLeft = 'Expirado';
                        return;
                    }
                    
                    const hours = Math.floor(distance / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    this.timeLeft = `${hours}h ${minutes}m ${seconds}s`;
                }
            }
        }
    </script>
    @endpush
</x-layout>
