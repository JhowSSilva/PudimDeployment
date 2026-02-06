<x-layout title="Notificações">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Notificações</h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        Acompanhe todas as atualizações de seus servidores e deployments
                    </p>
                </div>
                @if($notifications->where('is_read', false)->count() > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-as-read') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition">
                            Marcar todas como lidas
                        </button>
                    </form>
                @endif
            </div>

            <!-- Notifications List -->
            <div class="space-y-3">
                @forelse($notifications as $notification)
                    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 {{ !$notification->is_read ? 'ring-2 ring-primary-100 dark:ring-primary-900' : '' }} overflow-hidden transition">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 text-3xl">
                                    {{ $notification->icon }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                                                {{ $notification->title }}
                                                @if(!$notification->is_read)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                                        Nova
                                                    </span>
                                                @endif
                                            </h3>
                                            <p class="mt-2 text-neutral-600 dark:text-neutral-300">
                                                {{ $notification->message }}
                                            </p>
                                        </div>
                                        <span class="text-sm text-neutral-500 dark:text-neutral-400 whitespace-nowrap">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    
                                    <div class="mt-4 flex items-center gap-4">
                                        @if($notification->action_url)
                                            <a href="{{ $notification->action_url }}" 
                                               class="inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                                {{ $notification->action_text ?? 'Ver detalhes' }}
                                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        @endif
                                        
                                        @if(!$notification->is_read)
                                            <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">
                                                    ✓ Marcar como lida
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-semibold text-neutral-900 dark:text-white">Nenhuma notificação</h3>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                            Você não possui notificações no momento.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layout>
