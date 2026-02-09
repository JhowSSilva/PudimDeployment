@props(['type' => 'success', 'message'])

@php
$iconColors = [
    'success' => 'text-success-400',
    'error'   => 'text-error-400',
    'warning' => 'text-warning-400',
    'info'    => 'text-info-400',
];
$ringColors = [
    'success' => 'ring-success-500/20',
    'error'   => 'ring-error-500/20',
    'warning' => 'ring-warning-500/20',
    'info'    => 'ring-info-500/20',
];
@endphp

<div x-data="{ show: false, message: '{{ $message ?? '' }}' }" 
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @toast.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3000)"
     class="fixed top-4 right-4 z-50 pointer-events-none"
     role="alert"
     aria-live="assertive"
     style="display: none;">
    <div class="max-w-sm w-full bg-neutral-800 shadow-lg rounded-lg pointer-events-auto ring-1 {{ $ringColors[$type] ?? $ringColors['info'] }}">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($type === 'success')
                        <svg class="h-6 w-6 {{ $iconColors['success'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($type === 'error')
                        <svg class="h-6 w-6 {{ $iconColors['error'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    @elseif($type === 'warning')
                        <svg class="h-6 w-6 {{ $iconColors['warning'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    @else
                        <svg class="h-6 w-6 {{ $iconColors['info'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    @endif
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-neutral-100" x-text="message"></p>
                </div>
                <div class="ml-4 flex flex-shrink-0">
                    <button @click="show = false" class="inline-flex rounded-md bg-neutral-800 text-neutral-400 hover:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-800 transition-colors duration-200">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
