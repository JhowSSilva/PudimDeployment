<x-layout>
    <div class="py-8 fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ loading: false }">
            {{-- Header --}}
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-neutral-100">
                        Backups de Banco de Dados
                    </h2>
                    <p class="mt-1 text-sm text-neutral-400">
                        Gerencie backups automatizados em múltiplos provedores de nuvem
                    </p>
                </div>
                <a href="{{ route('backups.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Criar Backup
                </a>
            </div>

            {{-- Filters & Search --}}
            <div class="mb-6 bg-neutral-800 border border-neutral-700/50 rounded-lg shadow-lg p-4">
                <form method="GET" class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Buscar backups..." 
                               class="w-full px-4 py-2 border border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-neutral-900 text-neutral-100">
                    </div>
                    <select name="status" class="px-4 py-2 border border-neutral-600 rounded-lg bg-neutral-900 text-neutral-100">
                        <option value="">Todos os Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Pausado</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Executando</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Falhou</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-neutral-700 text-neutral-200 rounded-lg hover:bg-neutral-600 transition">
                        Filtrar
                    </button>
                </form>
            </div>

            {{-- Backup List --}}
            <div class="space-y-4">
                @forelse($backups as $backup)
                    <div class="bg-neutral-800 border border-neutral-700/50 rounded-lg shadow-lg hover:shadow-xl transition p-6">
                        <div class="flex items-start justify-between">
                            {{-- Left: Info --}}
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-white">
                                        {{ $backup->name }}
                                    </h3>
                                    @if($backup->status === 'active')
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-900 text-green-200 rounded-full">
                                            Ativo
                                        </span>
                                    @elseif($backup->status === 'paused')
                                        <span class="px-2 py-1 text-xs font-semibold bg-yellow-900 text-yellow-200 rounded-full">
                                            Pausado
                                        </span>
                                    @elseif($backup->status === 'running')
                                        <span class="px-2 py-1 text-xs font-semibold bg-blue-900 text-blue-200 rounded-full">
                                            Executando
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold bg-red-900 text-red-200 rounded-full">
                                            Falhou
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-4 text-sm text-neutral-300 mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                        </svg>
                                        {{ ucfirst($backup->database->type) }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $backup->database->name }}</span>
                                    <span>•</span>
                                    <span>{{ config("backup-providers.providers.{$backup->storage_provider}.name") ?? $backup->storage_provider }}</span>
                                    <span>•</span>
                                    <span>{{ $backup->database->server->name }}</span>
                                </div>

                                <div class="flex items-center gap-6 text-sm">
                                    <div>
                                        <span class="text-neutral-400">Último backup:</span>
                                        <span class="font-medium text-white">
                                            {{ $backup->last_backup_at ? $backup->last_backup_at->diffForHumans() : 'Nunca' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-neutral-400">Próximo backup:</span>
                                        <span class="font-medium text-white">
                                            {{ $backup->next_backup_at ? $backup->next_backup_at->diffForHumans() : '-' }}
                                        </span>
                                    </div>
                                    @if($backup->last_backup_size)
                                    <div>
                                        <span class="text-neutral-400">Tamanho:</span>
                                        <span class="font-medium text-white">
                                            {{ \Illuminate\Support\Number::fileSize($backup->last_backup_size) }}
                                        </span>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="text-neutral-400">Sucesso:</span>
                                        <span class="font-medium text-white">
                                            {{ $backup->success_rate }}%
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Actions --}}
                            <div class="flex items-center gap-2 ml-4">
                                @if($backup->status === 'active')
                                    <form action="{{ route('backups.pause', $backup) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 text-neutral-400 hover:text-white hover:bg-neutral-700 rounded-lg"
                                                title="Pausar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"/>
                                            </svg>
                                        </button>
                                    </form>
                                @elseif($backup->status === 'paused')
                                    <form action="{{ route('backups.resume', $backup) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 text-green-400 hover:text-green-300 hover:bg-green-900/40 rounded-lg"
                                                title="Retomar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('backups.run', $backup) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-2 text-sm font-medium text-blue-400 hover:text-blue-300 hover:bg-blue-900/40 rounded-lg"
                                            title="Executar Agora"
                                            {{ $backup->status === 'running' ? 'disabled' : '' }}>
                                        Executar
                                    </button>
                                </form>

                                <a href="{{ route('backups.files', $backup) }}" 
                                   class="px-3 py-2 text-sm font-medium text-neutral-400 hover:text-white hover:bg-neutral-700 rounded-lg">
                                    Arquivos
                                </a>

                                <a href="{{ route('backups.edit', $backup) }}" 
                                   class="px-3 py-2 text-sm font-medium text-neutral-400 hover:text-white hover:bg-neutral-700 rounded-lg">
                                    Editar
                                </a>

                                <form action="{{ route('backups.destroy', $backup) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Tem certeza que deseja deletar esta configuração de backup? Isso não irá deletar os arquivos de backup existentes.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-2 text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-900/40 rounded-lg">
                                        Deletar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-neutral-800 border border-neutral-700/50 rounded-lg shadow-lg p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-neutral-100">Nenhum backup configurado</h3>
                        <p class="mt-1 text-sm text-neutral-400">Comece criando sua primeira configuração de backup.</p>
                        <div class="mt-6">
                            <a href="{{ route('backups.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Criar Backup
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($backups->hasPages())
                <div class="mt-6">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
