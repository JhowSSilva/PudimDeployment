@props([
    'title',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12']) }}>
    @isset($icon)
        <div class="w-16 h-16 bg-neutral-800 dark:bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <div class="w-8 h-8 text-neutral-400 dark:text-neutral-500">
                {{ $icon }}
            </div>
        </div>
    @endisset
    
    <h3 class="text-lg font-semibold text-neutral-100 mb-2">{{ $title }}</h3>
    
    @if($description)
        <p class="text-sm text-neutral-400 mb-6 max-w-sm mx-auto">
            {{ $description }}
        </p>
    @endif
    
    @if($action && $actionLabel)
        <x-button :href="$action" variant="primary">
            {{ $actionLabel }}
        </x-button>
    @endif
    
    {{ $slot }}
</div>
