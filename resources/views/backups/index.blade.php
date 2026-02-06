<x-layout>
    <div class="py-8 fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ loading: false }">
            {{-- Header --}}
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Database Backups
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage automated backups across multiple cloud providers
                    </p>
                </div>
                <a href="{{ route('backups.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Backup
                </a>
            </div>

            {{-- Filters & Search --}}
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <form method="GET" class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search backups..." 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <select name="status" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Filter
                    </button>
                </form>
            </div>

            {{-- Backup List --}}
            <div class="space-y-4">
                @forelse($backups as $backup)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition p-6">
                        <div class="flex items-start justify-between">
                            {{-- Left: Info --}}
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $backup->name }}
                                    </h3>
                                    @if($backup->status === 'active')
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                                            Active
                                        </span>
                                    @elseif($backup->status === 'paused')
                                        <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full">
                                            Paused
                                        </span>
                                    @elseif($backup->status === 'running')
                                        <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                                            Running
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full">
                                            Failed
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
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
                                        <span class="text-gray-600 dark:text-gray-400">Last backup:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $backup->last_backup_at ? $backup->last_backup_at->diffForHumans() : 'Never' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Next backup:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $backup->next_backup_at ? $backup->next_backup_at->diffForHumans() : '-' }}
                                        </span>
                                    </div>
                                    @if($backup->last_backup_size)
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Size:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ \Illuminate\Support\Number::fileSize($backup->last_backup_size) }}
                                        </span>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Success:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
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
                                                class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                                                title="Pause">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"/>
                                            </svg>
                                        </button>
                                    </form>
                                @elseif($backup->status === 'paused')
                                    <form action="{{ route('backups.resume', $backup) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 text-green-600 hover:text-green-900 hover:bg-green-50 dark:hover:bg-green-900 rounded-lg"
                                                title="Resume">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('backups.run', $backup) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900 rounded-lg"
                                            title="Run Now"
                                            {{ $backup->status === 'running' ? 'disabled' : '' }}>
                                        Run
                                    </button>
                                </form>

                                <a href="{{ route('backups.files', $backup) }}" 
                                   class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                    Files
                                </a>

                                <a href="{{ route('backups.edit', $backup) }}" 
                                   class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                    Edit
                                </a>

                                <form action="{{ route('backups.destroy', $backup) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this backup configuration? This will not delete existing backup files.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900 rounded-lg">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No backups configured</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first backup configuration.</p>
                        <div class="mt-6">
                            <a href="{{ route('backups.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Backup
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
