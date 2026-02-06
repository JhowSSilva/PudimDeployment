<!-- Loading Spinner Component -->
<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center']) }}>
    @if($attributes->get('type') === 'dots')
        <!-- Dots Spinner -->
        <div class="flex space-x-2">
            <div class="w-2 h-2 bg-amber-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
            <div class="w-2 h-2 bg-amber-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
            <div class="w-2 h-2 bg-amber-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
        </div>
    @elseif($attributes->get('type') === 'pulse')
        <!-- Pulse Spinner -->
        <div class="relative">
            <div class="w-8 h-8 bg-amber-600 rounded-full animate-ping opacity-75"></div>
            <div class="absolute top-0 left-0 w-8 h-8 bg-amber-600 rounded-full"></div>
        </div>
    @elseif($attributes->get('type') === 'bars')
        <!-- Bars Spinner -->
        <div class="flex space-x-1">
            <div class="w-1 h-6 bg-amber-600 animate-pulse" style="animation-delay: 0ms"></div>
            <div class="w-1 h-6 bg-amber-600 animate-pulse" style="animation-delay: 100ms"></div>
            <div class="w-1 h-6 bg-amber-600 animate-pulse" style="animation-delay: 200ms"></div>
            <div class="w-1 h-6 bg-amber-600 animate-pulse" style="animation-delay: 300ms"></div>
        </div>
    @else
        <!-- Default Circle Spinner -->
        <svg class="{{ $attributes->get('size') === 'sm' ? 'w-4 h-4' : ($attributes->get('size') === 'lg' ? 'w-12 h-12' : 'w-8 h-8') }} animate-spin text-amber-600" 
             fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" 
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    
    @if($slot->isNotEmpty())
        <span class="ml-3 {{ $attributes->get('text-size') ?? 'text-sm' }} {{ $attributes->get('text-color') ?? 'text-neutral-600' }}">
            {{ $slot }}
        </span>
    @endif
</div>
