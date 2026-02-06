<x-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-neutral-900">SSH Terminal</h2>
                <p class="mt-1 text-sm text-neutral-600">
                    Acesse seus servidores via terminal web integrado
                </p>
            </div>

            @if($servers->isEmpty())
                <!-- Empty State -->
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-neutral-900">Nenhum servidor disponível</h3>
                    <p class="mt-1 text-sm text-neutral-500">
                        Adicione um servidor para começar a usar o terminal SSH.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('servers.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition shadow-lg shadow-amber-600/20">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Adicionar Servidor
                        </a>
                    </div>
                </div>
            @else
                <!-- Server Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($servers as $server)
                        <a href="{{ route('terminal.show', $server) }}" 
                           class="group block bg-white rounded-lg shadow hover:shadow-xl transition-all p-6 border border-neutral-200 hover:border-amber-600">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-neutral-900 rounded-lg flex items-center justify-center group-hover:bg-amber-600 transition-colors">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-neutral-900 group-hover:text-amber-600 transition-colors truncate">
                                        {{ $server->name }}
                                    </h3>
                                    <p class="text-sm text-neutral-600 mt-1">
                                        {{ $server->ip_address }}
                                    </p>
                                    <div class="mt-3 flex items-center gap-3">
                                        <!-- Status -->
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $server->status === 'online' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $server->status === 'offline' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $server->status === 'provisioning' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                                {{ $server->status === 'online' ? 'bg-green-500' : '' }}
                                                {{ $server->status === 'offline' ? 'bg-red-500' : '' }}
                                                {{ $server->status === 'provisioning' ? 'bg-yellow-500' : '' }}"></span>
                                            {{ ucfirst($server->status) }}
                                        </span>

                                        <!-- OS Icon -->
                                        <span class="text-xs text-neutral-500">
                                            @if(str_contains(strtolower($server->os ?? ''), 'ubuntu'))
                                                🐧 Ubuntu
                                            @elseif(str_contains(strtolower($server->os ?? ''), 'debian'))
                                                🐧 Debian
                                            @elseif(str_contains(strtolower($server->os ?? ''), 'centos'))
                                                🐧 CentOS
                                            @else
                                                🐧 Linux
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- Arrow Icon -->
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-neutral-400 group-hover:text-amber-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layout>
