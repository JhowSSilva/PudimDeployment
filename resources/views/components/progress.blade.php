<!-- Progress Bar Component -->
@props(['value' => 0, 'label' => null, 'height' => '2', 'description' => null, 'indeterminate' => false])

<div x-data="{ 
        progress: {{ $value }},
        indeterminate: {{ $indeterminate ? 'true' : 'false' }}
     }"
     class="w-full"
     role="progressbar"
     :aria-valuenow="indeterminate ? undefined : progress"
     aria-valuemin="0"
     aria-valuemax="100">
    
    @if($label)
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-neutral-300">{{ $label }}</span>
            <span x-show="!indeterminate" x-text="progress + '%'" class="text-sm text-neutral-400"></span>
        </div>
    @endif
    
    <div class="h-{{ $height }} bg-neutral-700 rounded-full overflow-hidden">
        <div x-show="indeterminate" 
             class="h-full bg-primary-600 rounded-full animate-progress-indeterminate w-1/3">
        </div>
        <div x-show="!indeterminate"
             class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-300 ease-out"
             :style="`width: ${progress}%`">
        </div>
    </div>
    
    @if($description)
        <p class="text-xs text-neutral-500 mt-2">{{ $description }}</p>
    @endif
</div>

<style>
@keyframes progress-indeterminate {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(400%); }
}
.animate-progress-indeterminate {
    animation: progress-indeterminate 1.5s ease-in-out infinite;
}
</style>
