<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $strategy->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="px-2 py-1 text-xs font-medium rounded {{ 
                        $strategy->isBlueGreen() ? 'bg-green-100 text-green-800' : 
                        ($strategy->isCanary() ? 'bg-yellow-100 text-yellow-800' : 
                        ($strategy->isRolling() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))
                    }}">
                        {{ ucfirst(str_replace('_', ' ', $strategy->type)) }}
                    </span>
                    @if($strategy->is_default)
                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            Estratégia Padrão
                        </span>
                    @endif
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('cicd.deployment-strategies.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Voltar
                </a>
                <a href="{{ route('cicd.deployment-strategies.edit', $strategy) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Editar
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

            <!-- Informações Gerais -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Gerais</h3>
                <div class="space-y-3">
                    @if($strategy->description)
                        <div>
                            <span class="text-sm text-gray-600">Descrição:</span>
                            <p class="mt-1 text-gray-900">{{ $strategy->description }}</p>
                        </div>
                    @else
                        <div>
                            <p class="text-gray-700">{{ $strategy->getDescription() }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4 pt-4">
                        <div>
                            <span class="text-sm text-gray-600">Site:</span>
                            <p class="mt-1 text-gray-900 font-medium">
                                @if($strategy->site_id)
                                    {{ $strategy->site->name ?? 'N/A' }}
                                @else
                                    <span class="text-gray-500">Todos os sites</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <span class="text-sm text-gray-600">Requer Aprovação:</span>
                            <p class="mt-1">
                                @if($strategy->requires_approval)
                                    <span class="text-yellow-600 font-medium">✓ Sim</span>
                                @else
                                    <span class="text-gray-500">Não</span>
                                @endif
                            </p>
                        </div>

                        @if($strategy->rollback_on_failure_percentage)
                            <div>
                                <span class="text-sm text-gray-600">Auto-rollback:</span>
                                <p class="mt-1 text-gray-900 font-medium">
                                    Se erro > {{ $strategy->rollback_on_failure_percentage }}%
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Configurações Específicas -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações da Estratégia</h3>

                @if($strategy->isCanary())
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-600">Percentual Inicial:</span>
                            <p class="mt-1 text-2xl font-bold text-yellow-600">{{ $strategy->getCanaryPercentage() }}%</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Etapas de Deployment:</span>
                            <div class="mt-2 flex gap-2">
                                @foreach($strategy->getCanarySteps() as $step)
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full font-medium">
                                        {{ $step }}%
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif($strategy->isRolling())
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-600">Tamanho do Batch:</span>
                            <p class="mt-1 text-2xl font-bold text-blue-600">{{ $strategy->getRollingBatchSize() }}</p>
                            <p class="text-xs text-gray-500">instâncias por vez</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Delay entre Batches:</span>
                            <p class="mt-1 text-2xl font-bold text-blue-600">{{ $strategy->getRollingBatchDelay() }}s</p>
                        </div>
                    </div>
                @elseif($strategy->isBlueGreen())
                    <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-sm text-green-800">
                            <strong>Blue-Green Deployment:</strong> Cria um ambiente completo novo (Green) em paralelo 
                            ao atual (Blue). Após validação e health checks, o tráfego é redirecionado do Blue para o Green. 
                            O ambiente Blue é mantido para rollback rápido se necessário.
                        </p>
                    </div>
                @else
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-800">
                            <strong>Recreate Deployment:</strong> Para todas as instâncias existentes, faz o deployment 
                            da nova versão e reinicia. Este método causa downtime mas é o mais simples e rápido.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Health Check -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Health Check</h3>
                <div class="grid grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Intervalo</span>
                        <p class="text-2xl font-bold text-gray-900">{{ $strategy->getHealthCheckInterval() }}s</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Timeout</span>
                        <p class="text-2xl font-bold text-gray-900">{{ $strategy->getHealthCheckTimeout() }}s</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Threshold</span>
                        <p class="text-2xl font-bold text-gray-900">{{ $strategy->getHealthCheckThreshold() }}</p>
                        <p class="text-xs text-gray-500 mt-1">falhas consecutivas</p>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center">
                    @if(!$strategy->is_default)
                        <form action="{{ route('cicd.deployment-strategies.make-default', $strategy) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Tornar Estratégia Padrão
                            </button>
                        </form>
                    @else
                        <div class="text-blue-600 font-medium">
                            ✓ Esta é a estratégia padrão do time
                        </div>
                    @endif

                    <form action="{{ route('cicd.deployment-strategies.destroy', $strategy) }}" method="POST" 
                        onsubmit="return confirm('Tem certeza que deseja excluir esta estratégia?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                            Excluir Estratégia
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
