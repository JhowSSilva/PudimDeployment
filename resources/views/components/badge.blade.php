@props([
    'variant' => 'neutral', // neutral, success, error, warning, info, primary
    'size' => 'md', // sm, md
    'dot' => false, // Show animated dot indicator
    'pulse' => false, // Pulse animation for dot
])

@php
$baseClasses = 'inline-flex items-center font-medium rounded-full ring-1 ring-inset';

// Size variants
$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-xs',
];

// Color variants
$variantClasses = [
    'neutral' => 'bg-neutral-700/50 text-neutral-300 ring-neutral-600/30',
    'success' => 'bg-success-900/40 text-success-300 ring-success-500/30',
    'error' => 'bg-error-900/40 text-error-300 ring-error-500/30',
    'warning' => 'bg-warning-900/40 text-warning-300 ring-warning-500/30',
    'info' => 'bg-info-900/40 text-info-300 ring-info-500/30',
    'primary' => 'bg-primary-900/40 text-primary-300 ring-primary-500/30',
];

// Dot colors
$dotColors = [
    'neutral' => 'bg-neutral-500',
    'success' => 'bg-success-500',
    'error' => 'bg-error-500',
    'warning' => 'bg-warning-500',
    'info' => 'bg-info-500',
    'primary' => 'bg-primary-500',
];

$classes = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($variantClasses[$variant] ?? $variantClasses['neutral']);
$dotClass = ($dotColors[$variant] ?? $dotColors['neutral']) . ' w-1.5 h-1.5 rounded-full mr-1.5';
if ($pulse) {
    $dotClass .= ' animate-pulse';
}
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="{{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
