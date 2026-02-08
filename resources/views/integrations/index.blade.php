<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Integrações
            </h2>
            <a href="{{ route('cicd.integrations.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Nova Integração
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

            @if($integrations->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhuma integração configurada</h3>
                    <p class="text-gray-600 mb-4">Conecte com GitHub, Slack, Discord e outras ferramentas para notificações automáticas.</p>
                    <a href="{{ route('cicd.integrations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Criar Primeira Integração
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($integrations as $integration)
                        <div class="bg-white rounded-lg shadow-sm border-l-4 {{ 
                            $integration->isActive() ? 'border-green-500' : 
                            ($integration->status === 'error' ? 'border-red-500' : 'border-gray-400')
                        }}">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $integration->name }}</h3>
                                        <div class="flex gap-2 mt-1">
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
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                                                $integration->isActive() ? 'bg-green-100 text-green-800' : 
                                                ($integration->status === 'error' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')
                                            }}">
                                                {{ ucfirst($integration->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-sm space-y-2 mb-4">
                                    @if($integration->events && count($integration->events) > 0)
                                        <div>
                                            <span class="text-gray-600">Eventos:</span>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach(array_slice($integration->events, 0, 3) as $event)
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs">
                                                        {{ str_replace('_', ' ', $event) }}
                                                    </span>
                                                @endforeach
                                                @if(count($integration->events) > 3)
                                                    <span class="px-2 py-0.5 text-gray-500 text-xs">
                                                        +{{ count($integration->events) - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <div class="text-gray-600">
                                        <span class="font-medium">{{ $integration->trigger_count }}</span> notificações enviadas
                                    </div>

                                    @if($integration->last_triggered_at)
                                        <div class="text-gray-500 text-xs">
                                            Último uso: {{ $integration->last_triggered_at->diffForHumans() }}
                                        </div>
                                    @endif

                                    @if($integration->last_error)
                                        <div class="text-red-600 text-xs mt-2 p-2 bg-red-50 rounded">
                                            {{ Str::limit($integration->last_error, 80) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('cicd.integrations.show', $integration) }}" 
                                        class="flex-1 px-3 py-2 text-center bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                        Ver Detalhes
                                    </a>

                                    <form action="{{ route('cicd.integrations.toggle', $integration) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full px-3 py-2 border rounded-md text-sm {{ 
                                            $integration->isActive() ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-green-600 text-green-600 hover:bg-green-50'
                                        }}">
                                            {{ $integration->isActive() ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('cicd.integrations.test', $integration) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 text-sm"
                                            title="Enviar notificação de teste">
                                            🧪
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $integrations->links() }}
                </div>
            @endif

            <!-- Providers Disponíveis -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <h4 class="font-medium text-blue-900 mb-2">Providers Suportados</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-blue-800">
                    <div>✓ GitHub</div>
                    <div>✓ GitLab</div>
                    <div>✓ Bitbucket</div>
                    <div>✓ Slack</div>
                    <div>✓ Discord</div>
                    <div>✓ Telegram</div>
                    <div>✓ Webhook</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
