<x-layout title="Gerenciamento de Queue Workers">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-neutral-100">Gerenciamento de Queue Workers</h1>
                <p class="mt-2 text-neutral-400">Gerencie workers de filas dos seus servidores</p>
            </div>
            @if($servers->isEmpty())
                <div class="bg-neutral-800 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg border border-neutral-700/50">
                    <div class="p-6 text-center">
                        <div class="text-neutral-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-neutral-100 mb-2">
                            Nenhum servidor encontrado
                        </h3>
                        <p class="text-neutral-400 mb-4">
                            Você precisa adicionar servidores antes de gerenciar queue workers.
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
                        <div class="bg-neutral-800 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg border border-neutral-700/50">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="bg-primary-900/40 p-2 rounded-lg mr-4">
                                            <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-medium text-neutral-100">
                                                {{ $server->name }}
                                            </h3>
                                            <p class="text-sm text-neutral-500">
                                                {{ $server->ip_address }}
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('servers.queue-workers.index', $server) }}" 
                                       class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out text-sm">
                                        Gerenciar Workers
                                    </a>
                                </div>

                                @if($server->queueWorkers->isEmpty())
                                    <div class="text-center py-4 text-neutral-500 text-sm">
                                        Nenhum queue worker configurado neste servidor
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($server->queueWorkers as $worker)
                                            <div class="border border-neutral-700 rounded-lg p-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center">
                                                        <svg class="h-5 w-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg>
                                                        <h4 class="font-medium text-neutral-100">
                                                            {{ $worker->queue }}
                                                        </h4>
                                                    </div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                               {{ $worker->status === 'running' ? 'bg-success-900/30 text-success-400' : 
                                                                  ($worker->status === 'stopped' ? 'bg-error-900/30 text-error-400' : 
                                                                   'bg-warning-900/30 text-warning-400') }}">
                                                        {{ ucfirst($worker->status) }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-neutral-500 mb-2">
                                                    {{ $worker->max_jobs ?? 'Unlimited' }} jobs máximos
                                                </p>
                                                <p class="text-sm text-neutral-500 mb-3">
                                                    Timeout: {{ $worker->timeout }}s
                                                </p>
                                                <a href="{{ route('servers.queue-workers.show', [$server, $worker]) }}" 
                                                   class="text-primary-600 hover:text-primary-800 text-sm font-medium">
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