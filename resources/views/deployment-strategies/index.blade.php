<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Estratégias de Deployment
            </h2>
            <a href="{{ route('cicd.deployment-strategies.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Nova Estratégia
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if($strategies->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhuma estratégia configurada</h3>
                    <p class="text-gray-600 mb-4">Crie estratégias de deployment para controlar como seus sites são atualizados.</p>
                    <a href="{{ route('cicd.deployment-strategies.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Criar Primeira Estratégia
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($strategies as $strategy)
                        <div class="bg-white rounded-lg shadow-sm {{ $strategy->is_default ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $strategy->name }}</h3>
                                            @if($strategy->is_default)
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                    Padrão
                                                </span>
                                            @endif
                                        </div>
                                        <span class="inline-block mt-1 px-2 py-1 text-xs font-medium rounded {{ 
                                            $strategy->isBlueGreen() ? 'bg-green-100 text-green-800' : 
                                            ($strategy->isCanary() ? 'bg-yellow-100 text-yellow-800' : 
                                            ($strategy->isRolling() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $strategy->type)) }}
                                        </span>
                                    </div>
                                </div>

                                @if($strategy->description)
                                    <p class="text-gray-600 mb-4">{{ Str::limit($strategy->description, 100) }}</p>
                                @else
                                    <p class="text-gray-600 mb-4">{{ $strategy->getDescription() }}</p>
                                @endif

                                <div class="border-t pt-4 space-y-2 text-sm">
                                    @if($strategy->requires_approval)
                                        <div class="flex items-center text-yellow-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Requer aprovação
                                        </div>
                                    @endif

                                    @if($strategy->isCanary())
                                        <div class="text-gray-600">
                                            <span class="font-medium">Etapas:</span> {{ implode(', ', $strategy->getCanarySteps()) }}%
                                        </div>
                                    @endif

                                    @if($strategy->isRolling())
                                        <div class="text-gray-600">
                                            <span class="font-medium">Tamanho do batch:</span> {{ $strategy->getRollingBatchSize() }}
                                        </div>
                                    @endif

                                    @if($strategy->site_id)
                                        <div class="text-gray-600">
                                            <span class="font-medium">Site:</span> {{ $strategy->site->name ?? 'N/A' }}
                                        </div>
                                    @else
                                        <div class="text-gray-500 italic">
                                            Disponível para todos os sites
                                        </div>
                                    @endif
                                </div>

                                <div class="flex gap-2 mt-6">
                                    <a href="{{ route('cicd.deployment-strategies.show', $strategy) }}" class="flex-1 px-4 py-2 text-center bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                        Ver Detalhes
                                    </a>
                                    
                                    @if(!$strategy->is_default)
                                        <form action="{{ route('cicd.deployment-strategies.make-default', $strategy) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 text-sm">
                                                Tornar Padrão
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $strategies->links() }}
                </div>
            @endif

            <!-- Info Box -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <h4 class="font-medium text-blue-900 mb-2">Sobre Estratégias de Deployment</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><strong>Blue-Green:</strong> Cria ambiente completo novo, troca após validação</li>
                    <li><strong>Canary:</strong> Atualiza gradualmente (10%, 25%, 50%, 100%)</li>
                    <li><strong>Rolling:</strong> Atualiza instâncias em lotes sequenciais</li>
                    <li><strong>Recreate:</strong> Para tudo, atualiza, reinicia (downtime)</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
