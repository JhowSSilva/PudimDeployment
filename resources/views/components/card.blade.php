@props([
    'padding' => true,
    'hover' => true,
])

@php
$classes = 'bg-neutral-800 rounded-xl shadow-lg border border-neutral-700/50 overflow-hidden transition-all duration-200';

if ($padding) {
    $classes .= ' p-6';
}

if ($hover) {
    $classes .= ' hover:shadow-xl hover:border-neutral-600/50';
}
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
