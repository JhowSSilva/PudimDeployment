@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-neutral-600 bg-neutral-900 text-white focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm']) }}>
