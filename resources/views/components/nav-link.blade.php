@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary-600 dark:border-primary-500 text-sm font-medium leading-5 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:border-primary-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-400 dark:hover:border-neutral-600 focus:outline-none focus:text-neutral-800 dark:focus:text-neutral-200 focus:border-neutral-400 dark:focus:border-neutral-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
