<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $integration->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="px-2 py-1 text-xs font-medium rounded {{ 
                        $integration->isGitHub() ? 'bg-gray-900 text-white' : 
                        ($integration->isGitLab() ? 'bg-orange-100 text-orange-800' :
                        ($integration->isBitbucket() ? 'bg-blue-100 text-blue-800' :
                        ($integration->isSlack() ? 'bg-purple-100 text-purple-800' :
                        ($integration->isDiscord() ? 'bg-indigo-100 text-indigo-800' :
                        ($integration->isTelegram() ? 'bg-sky-100 text-sky-800' : 'bg-gray-100 text-gray-800')))))
                    }}">
                        {{ ucfirst($integration->provider) }}
                    </span>
                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full {{ 
                        $integration->isActive() ? 'bg-green-100 text-green-800' : 
                        ($integration->status === 'error' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')
                    }}">
                        {{ ucfirst($integration->status) }}
                    </span>
                </p>
            </div>
            <div class="flex gap-3">
                <form action="{{ route('cicd.integrations.test', $integration) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50">
                        Enviar Teste
                    </button>
                </form>

                <form action="{{ route('cicd.integrations.toggle', $integration) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 {{ 
                        $integration->isActive() ? 'border border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-green-600 text-white hover:bg-green-700'
                    }} rounded-md">
                        {{ $integration->isActive() ? 'Desativar' : 'Ativar' }}
                    </button>
                </form>

                <a href="{{ route('cicd.integrations.edit', $integration) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
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

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Estatísticas -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas</h3>
                <div class="grid grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Notificações Enviadas</span>
                        <p class="text-3xl font-bold text-gray-900">{{ $integration->trigger_count }}</p>
                    </div>

                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Status</span>
                        <p class="text-2xl font-bold {{ 
                            $integration->isActive() ? 'text-green-600' : 
                            ($integration->status === 'error' ? 'text-red-600' : 'text-gray-600')
                        }}">
                            {{ ucfirst($integration->status) }}
                        </p>
                    </div>

                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600 block mb-1">Último Uso</span>
                        <p class="text-lg font-medium text-gray-900">
                            @if($integration->last_triggered_at)
                                {{ $integration->last_triggered_at->diffForHumans() }}
                            @else
                                Nunca
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Configuração -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Configuração</h3>
                <div class="space-y-4">
                    @if($integration->isGitHub() || $integration->isGitLab() || $integration->isBitbucket())
                        @if($integration->getRepositoryUrl())
                            <div>
                                <span class="text-sm text-gray-600">Repositório:</span>
                                <p class="mt-1 font-mono text-sm">{{ $integration->getRepositoryUrl() }}</p>
                            </div>
                        @endif
                        <div>
                            <span class="text-sm text-gray-600">Access Token:</span>
                            <p class="mt-1 font-mono text-sm text-gray-500">
                                {{ str_repeat('•', 40) }}
                            </p>
                        </div>
                    @elseif($integration->isSlack())
                        <div>
                            <span class="text-sm text-gray-600">Webhook URL:</span>
                            <p class="mt-1 font-mono text-sm break-all">{{ $integration->config['webhook_url'] ?? 'N/A' }}</p>
                        </div>
                        @if(isset($integration->config['channel']))
                            <div>
                                <span class="text-sm text-gray-600">Canal:</span>
                                <p class="mt-1 font-medium">{{ $integration->config['channel'] }}</p>
                            </div>
                        @endif
                    @elseif($integration->isDiscord())
                        <div>
                            <span class="text-sm text-gray-600">Webhook URL:</span>
                            <p class="mt-1 font-mono text-sm break-all">{{ $integration->config['webhook_url'] ?? 'N/A' }}</p>
                        </div>
                    @elseif($integration->isTelegram())
                        <div>
                            <span class="text-sm text-gray-600">Bot Token:</span>
                            <p class="mt-1 font-mono text-sm text-gray-500">
                                {{ str_repeat('•', 30) }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Chat ID:</span>
                            <p class="mt-1 font-mono text-sm">{{ $integration->config['chat_id'] ?? 'N/A' }}</p>
                        </div>
                    @elseif($integration->isWebhook())
                        <div>
                            <span class="text-sm text-gray-600">Webhook URL:</span>
                            <p class="mt-1 font-mono text-sm break-all">{{ $integration->webhook_url }}</p>
                        </div>
                        @if($integration->webhook_secret)
                            <div>
                                <span class="text-sm text-gray-600">Webhook Secret:</span>
                                <p class="mt-1 font-mono text-sm text-gray-500">Configurado</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Eventos -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Eventos Monitorados</h3>
                @if($integration->events && count($integration->events) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($integration->events as $event)
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                {{ ucfirst(str_replace('_', ' ', $event)) }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">Nenhum evento configurado</p>
                @endif
            </div>

            <!-- Último Erro -->
            @if($integration->last_error)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <h4 class="font-medium text-red-900 mb-2">Último Erro</h4>
                    <p class="text-sm text-red-800 font-mono">{{ $integration->last_error }}</p>
                </div>
            @endif

            <!-- Ações -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <a href="{{ route('cicd.integrations.index') }}" class="text-blue-600 hover:underline">
                            ← Voltar para integrações
                        </a>
                    </div>
                    <form action="{{ route('cicd.integrations.destroy', $integration) }}" method="POST" 
                        onsubmit="return confirm('Tem certeza que deseja excluir esta integração?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                            Excluir Integração
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
