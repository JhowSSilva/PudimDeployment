@props([
    'variant' => 'primary', // primary, secondary, ghost, danger
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left', // left or right
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

// Size variants
$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-xs rounded-lg',
    'md' => 'px-4 py-2.5 text-sm rounded-lg',
    'lg' => 'px-6 py-3 text-base rounded-xl',
];

// Color variants
$variantClasses = [
    'primary' => 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white shadow-sm hover:shadow-primary focus:ring-primary-500 transform hover:scale-[1.02] active:scale-[0.98]',
    'secondary' => 'bg-white hover:bg-neutral-50 active:bg-neutral-100 text-neutral-700 border border-neutral-300 hover:border-neutral-400 shadow-sm focus:ring-primary-500',
    'ghost' => 'bg-transparent hover:bg-neutral-100 active:bg-neutral-200 text-neutral-700 hover:text-neutral-900 focus:ring-primary-500',
    'danger' => 'bg-error-600 hover:bg-error-700 active:bg-error-800 text-white shadow-sm hover:shadow-error focus:ring-error-500 transform hover:scale-[1.02] active:scale-[0.98]',
    'success' => 'bg-success-600 hover:bg-success-700 active:bg-success-800 text-white shadow-sm hover:shadow-success focus:ring-success-500 transform hover:scale-[1.02] active:scale-[0.98]',
];

$classes = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']);
@endphp

@if($href)
    <a 
        href="{{ $disabled ? '#' : $href }}" 
        {{ $attributes->merge(['class' => $classes . ($disabled ? ' pointer-events-none' : '')]) }}
    >
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <span class="-ml-1 mr-2 {{ $size === 'sm' ? 'w-4 h-4' : ($size === 'lg' ? 'w-6 h-6' : 'w-5 h-5') }}">
                {!! $icon !!}
            </span>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <span class="-mr-1 ml-2 {{ $size === 'sm' ? 'w-4 h-4' : ($size === 'lg' ? 'w-6 h-6' : 'w-5 h-5') }}">
                {!! $icon !!}
            </span>
        @endif
    </a>
@else
    <button 
        type="{{ $type }}" 
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <span class="-ml-1 mr-2 {{ $size === 'sm' ? 'w-4 h-4' : ($size === 'lg' ? 'w-6 h-6' : 'w-5 h-5') }}">
                {!! $icon !!}
            </span>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <span class="-mr-1 ml-2 {{ $size === 'sm' ? 'w-4 h-4' : ($size === 'lg' ? 'w-6 h-6' : 'w-5 h-5') }}">
                {!! $icon !!}
            </span>
        @endif
    </button>
@endif
