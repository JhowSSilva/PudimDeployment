@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-neutral-200 mb-1.5']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-error-400">*</span>
    @endif
</label>
