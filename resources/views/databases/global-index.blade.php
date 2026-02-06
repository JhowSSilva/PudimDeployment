<x-layout title="Gerenciamento de Bancos de Dados">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Gerenciamento de Bancos de Dados</h1>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">Gerencie todos os bancos de dados dos seus servidores</p>
            </div>
            @if($servers->isEmpty())
                <div class="bg-white/80 dark:bg-neutral-800 backdrop-blur-sm overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="text-neutral-500 dark:text-neutral-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">
                            Nenhum servidor encontrado
                        </h3>
                        <p class="text-neutral-500 dark:text-neutral-400 mb-4">
                            Você precisa adicionar servidores antes de gerenciar bancos de dados.
                        </p>
                        <a href="{{ route('servers.create') }}" 
                           class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                            Adicionar Servidor
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($servers as $server)
                        <div class="bg-white/80 dark:bg-neutral-800 backdrop-blur-sm overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="bg-primary-100 dark:bg-primary-900 p-2 rounded-lg mr-4">
                                            <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-medium text-neutral-900 dark:text-white">
                                                {{ $server->name }}
                                            </h3>
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                                {{ $server->ip_address }}
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('servers.databases.index', $server) }}" 
                                       class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out text-sm">
                                        Gerenciar Bancos
                                    </a>
                                </div>

                                @if($server->databases->isEmpty())
                                    <div class="text-center py-4 text-neutral-500 dark:text-neutral-400 text-sm">
                                        Nenhum banco de dados configurado neste servidor
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($server->databases as $database)
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center">
                                                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                                        </svg>
                                                        <h4 class="font-medium text-neutral-900 dark:text-white">
                                                            {{ $database->name }}
                                                        </h4>
                                                    </div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                               {{ $database->type === 'mysql' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                        {{ strtoupper($database->type) }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-3">
                                                    {{ $database->users->count() }} {{ Str::plural('usuário', $database->users->count()) }}
                                                </p>
                                                <a href="{{ route('servers.databases.show', [$server, $database]) }}" 
                                                   class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200 text-sm font-medium">
                                                    Ver Detalhes →
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layout>