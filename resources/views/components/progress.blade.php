<!-- Progress Bar Component -->
<div x-data="{ 
        progress: {{ $attributes->get('value') ?? 0 }},
        indeterminate: {{ $attributes->get('indeterminate') ? 'true' : 'false' }}
     }"
     class="w-full">
    
    @if($attributes->get('label'))
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-neutral-700">{{ $attributes->get('label') }}</span>
            <span x-show="!indeterminate" x-text="progress + '%'" class="text-sm text-neutral-600"></span>
        </div>
    @endif
    
    <div class="h-{{ $attributes->get('height') ?? '2' }} bg-neutral-200 rounded-full overflow-hidden">
        <!-- Indeterminate Progress -->
        <div x-show="indeterminate" 
             class="h-full bg-amber-600 rounded-full animate-progress-indeterminate w-1/3">
        </div>
        
        <!-- Determinate Progress -->
        <div x-show="!indeterminate"
             class="h-full bg-gradient-to-r from-amber-500 to-amber-600 rounded-full transition-all duration-300 ease-out shadow-lg"
             :style="`width: ${progress}%`">
        </div>
    </div>
    
    @if($attributes->get('description'))
        <p class="text-xs text-neutral-500 mt-2">{{ $attributes->get('description') }}</p>
    @endif
</div>

<style>
@keyframes progress-indeterminate {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(400%);
    }
}

.animate-progress-indeterminate {
    animation: progress-indeterminate 1.5s ease-in-out infinite;
}
</style>
