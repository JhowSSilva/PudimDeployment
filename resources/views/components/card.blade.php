@props([
    'padding' => true,
    'hover' => true,
])

@php
$classes = 'bg-white rounded-xl shadow-sm border border-neutral-200 overflow-hidden transition-all duration-200';

if ($padding) {
    $classes .= ' p-6';
}

if ($hover) {
    $classes .= ' hover:shadow-md';
}
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
