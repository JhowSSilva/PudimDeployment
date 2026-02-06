<div class="relative" x-data="{ open: @entangle('showDropdown') }" wire:poll.30s="refreshNotifications">
    <!-- Notification Bell Button -->
    <button @click="open = !open" type="button" class="relative p-2 text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-96 origin-top-right rounded-xl bg-white dark:bg-neutral-800 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
         style="display: none;">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">
                Notificações
                @if($unreadCount > 0)
                    <span class="ml-2 text-xs font-normal text-neutral-500">({{ $unreadCount }} novas)</span>
                @endif
            </h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                    Marcar todas como lidas
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div wire:key="notification-{{ $notification['id'] }}"
                     class="px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition border-b border-neutral-100 dark:border-neutral-700 last:border-0 {{ !$notification['is_read'] ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 text-2xl">
                            {{ $notification['icon'] ?? 'ℹ️' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white">
                                {{ $notification['title'] }}
                            </p>
                            <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                                {{ $notification['message'] }}
                            </p>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-xs text-neutral-500 dark:text-neutral-500">
                                    {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                                </span>
                                @if($notification['action_url'])
                                    <a href="{{ $notification['action_url'] }}" 
                                       class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                                        {{ $notification['action_text'] ?? 'Ver detalhes' }} →
                                    </a>
                                @endif
                                @if(!$notification['is_read'])
                                    <button wire:click="markAsRead({{ $notification['id'] }})" 
                                            class="text-xs text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
                                        ✓ Marcar como lida
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="mt-4 text-sm text-neutral-500 dark:text-neutral-400">Nenhuma notificação</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if(count($notifications) > 0)
            <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-700">
                <a href="{{ route('notifications.index') }}" 
                   class="block text-center text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                    Ver todas as notificações →
                </a>
            </div>
        @endif
    </div>
</div>
