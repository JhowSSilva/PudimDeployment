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
    'neutral' => 'bg-neutral-50 text-neutral-700 ring-neutral-600/20',
    'success' => 'bg-success-50 text-success-700 ring-success-600/20',
    'error' => 'bg-error-50 text-error-700 ring-error-600/20',
    'warning' => 'bg-warning-50 text-warning-700 ring-warning-600/20',
    'info' => 'bg-info-50 text-info-700 ring-info-600/20',
    'primary' => 'bg-primary-50 text-primary-700 ring-primary-600/20',
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
