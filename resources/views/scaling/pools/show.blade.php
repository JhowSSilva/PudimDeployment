<x-layout title="Server Pool: {{ $pool->name }}">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white">
            {{ $pool->name }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('scaling.pools.edit', $pool) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Editar
            </a>
            <a href="{{ route('scaling.pools.index') }}" class="px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-500">
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-900/50 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-neutral-800 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-4">Informações</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-neutral-400">Status:</span>
                    <span class="px-3 py-1 rounded text-xs font-semibold {{ $pool->status === 'active' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">
                        {{ ucfirst($pool->status) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Servidores:</span>
                    <span class="text-white">{{ $pool->servers->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Mínimo:</span>
                    <span class="text-white">{{ $pool->min_servers }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">Máximo:</span>
                    <span class="text-white">{{ $pool->max_servers }}</span>
                </div>
            </div>

            @if($pool->description)
                <div class="mt-4 pt-4 border-t border-neutral-700">
                    <p class="text-sm text-neutral-400">{{ $pool->description }}</p>
                </div>
            @endif
        </div>

        <div class="bg-neutral-800 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-4">Health Status</h3>
            <div class="space-y-2">
                @foreach($healthStatus as $status => $count)
                    <div class="flex justify-between items-center">
                        <span class="text-neutral-400">{{ ucfirst($status) }}:</span>
                        <span class="px-3 py-1 rounded text-xs font-semibold
                            {{ $status === 'healthy' ? 'bg-green-900 text-green-300' : '' }}
                            {{ $status === 'unhealthy' ? 'bg-red-900 text-red-300' : '' }}
                            {{ $status === 'unknown' ? 'bg-neutral-600 text-neutral-300' : '' }}">
                            {{ $count }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-neutral-800 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-white mb-4">Servidores</h3>
        <div class="space-y-2">
            @forelse($pool->servers as $server)
                <div class="p-4 bg-neutral-700 rounded-lg flex justify-between items-center">
                    <div>
                        <h4 class="text-white font-medium">{{ $server->name }}</h4>
                        <p class="text-sm text-neutral-400">{{ $server->ip_address }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded text-xs font-semibold {{ $server->status === 'active' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">
                            {{ ucfirst($server->status) }}
                        </span>
                        <a href="{{ route('servers.show', $server) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                            Ver →
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-neutral-500 text-center py-4">Nenhum servidor associado</p>
            @endforelse
        </div>
    </div>
</x-layout>
