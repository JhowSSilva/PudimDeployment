@props(['disabled' => false, 'error' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'w-full rounded-md bg-neutral-900 border-neutral-600 text-neutral-100 placeholder:text-neutral-500 shadow-sm transition duration-200'
            . ' focus:border-primary-500 focus:ring-primary-500 focus:ring-offset-0'
            . ($error ? ' border-error-500 focus:border-error-500 focus:ring-error-500' : '')
            . ($disabled ? ' opacity-50 cursor-not-allowed' : ''),
    ]) }}
>
