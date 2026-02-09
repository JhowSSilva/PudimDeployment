<div class="bg-neutral-800 shadow rounded-lg">
    <ul role="list" class="divide-y divide-neutral-700">
        @forelse($deployments as $deployment)
            <li class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-3">
                            @php
                                $statusConfig = match($deployment->status) {
                                    'success' => ['color' => 'green', 'icon' => 'check-circle'],
                                    'failed' => ['color' => 'red', 'icon' => 'x-circle'],
                                    'running' => ['color' => 'blue', 'icon' => 'arrow-path'],
                                    'pending' => ['color' => 'yellow', 'icon' => 'clock'],
                                    default => ['color' => 'gray', 'icon' => 'question-mark-circle']
                                };
                            @endphp
                            
                            @if($deployment->status === 'success')
                                <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($deployment->status === 'failed')
                                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($deployment->status === 'running')
                                <svg class="h-5 w-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            @else
                                <svg class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-100 truncate">
                                    {{ $deployment->site->domain ?? 'Site desconhecido' }}
                                </p>
                                <p class="text-sm text-neutral-500 truncate">
                                    @if($deployment->commit_message)
                                        {{ Str::limit($deployment->commit_message, 50) }}
                                    @else
                                        Deploy #{{ $deployment->id }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center text-xs text-neutral-500">
                            <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            {{ $deployment->user->name ?? 'Sistema' }}
                            <span class="mx-2">•</span>
                            {{ $deployment->created_at->diffForHumans() }}
                            @if($deployment->duration_seconds)
                                <span class="mx-2">•</span>
                                {{ $deployment->duration_seconds }}s
                            @endif
                        </div>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                            @if($deployment->status === 'success') bg-success-900/30 text-success-400
                            @elseif($deployment->status === 'failed') bg-error-900/30 text-error-400
                            @elseif($deployment->status === 'running') bg-info-900/30 text-info-400
                            @elseif($deployment->status === 'pending') bg-warning-900/30 text-warning-400
                            @else bg-neutral-700 text-neutral-800
                            @endif">
                            {{ ucfirst($deployment->status) }}
                        </span>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-neutral-100">Nenhum deployment</h3>
                <p class="mt-1 text-sm text-neutral-500">Comece fazendo deploy de um site.</p>
            </li>
        @endforelse
    </ul>

    @if($deployments->count() > 0)
        <div class="bg-neutral-900 px-6 py-3">
            <a href="#" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                Ver todos os deployments
                <span aria-hidden="true"> &rarr;</span>
            </a>
        </div>
    @endif
</div>
