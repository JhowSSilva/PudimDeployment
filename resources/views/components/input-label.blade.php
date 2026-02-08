@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-neutral-200']) }}>
    {{ $value ?? $slot }}
</label>
