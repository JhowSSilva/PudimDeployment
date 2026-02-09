<x-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-neutral-100">Arquivos de Backup</h2>
                    <p class="mt-1 text-sm text-neutral-400">{{ $backup->name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('backups.edit', $backup) }}" 
                       class="px-4 py-2 text-neutral-300 hover:text-neutral-100 transition">
                        Editar Config
                    </a>
                    <a href="{{ route('backups.index') }}" 
                       class="px-4 py-2 text-neutral-300 hover:text-neutral-100 transition">
                        Voltar
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-neutral-800 rounded-lg shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-info-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-400">Total de Arquivos</p>
                            <p class="text-2xl font-bold text-neutral-100">{{ $files->total() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-800 rounded-lg shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-success-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-400">Último Backup</p>
                            <p class="text-lg font-bold text-neutral-100">
                                {{ $backup->last_backup_at ? $backup->last_backup_at->format('d/m H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-800 rounded-lg shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-400">Tamanho Total</p>
                            <p class="text-lg font-bold text-neutral-100">
                                {{ \Illuminate\Support\Number::fileSize($files->sum('file_size')) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-800 rounded-lg shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-400">Retention</p>
                            <p class="text-lg font-bold text-neutral-100">{{ $backup->retention_days }} dias</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Table -->
            <div class="bg-neutral-800 rounded-lg shadow overflow-hidden">
                @if($files->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-neutral-100">Nenhum arquivo de backup</h3>
                        <p class="mt-1 text-sm text-neutral-500">Execute o backup para criar o primeiro arquivo.</p>
                        <div class="mt-6">
                            <form action="{{ route('backups.run', $backup) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition shadow-lg shadow-primary-600/20">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    </svg>
                                    Executar Backup Agora
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-neutral-700">
                        <thead class="bg-neutral-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Arquivo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Tamanho</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Expira em</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-neutral-800 divide-y divide-neutral-700">
                            @foreach($files as $file)
                                <tr class="hover:bg-neutral-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-neutral-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <div>
                                                <div class="text-sm font-medium text-neutral-100">{{ $file->filename }}</div>
                                                <div class="text-xs text-neutral-500 font-mono">SHA-256</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-neutral-100">{{ $file->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-neutral-500">{{ $file->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-neutral-100">
                                            {{ \Illuminate\Support\Number::fileSize($file->file_size) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($file->isExpired())
                                            <span class="px-2 py-1 text-xs font-semibold bg-error-900/30 text-error-400 rounded-full">
                                                Expired
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold bg-success-900/30 text-success-400 rounded-full">
                                                Available
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                        @if($file->expires_at)
                                            {{ $file->expires_at->diffForHumans() }}
                                        @else
                                            <span class="text-neutral-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('backups.files.download', $file) }}" 
                                               class="p-2 text-info-400 hover:text-blue-900 hover:bg-info-900/20 rounded-lg transition"
                                               title="Download">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('backups.files.destroy', $file) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Tem certeza que deseja deletar este arquivo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-2 text-error-400 hover:text-red-900 hover:bg-error-900/20 rounded-lg transition"
                                                        title="Deletar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    @if($files->hasPages())
                        <div class="bg-neutral-900 px-6 py-4 border-t border-neutral-700">
                            {{ $files->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-layout>
