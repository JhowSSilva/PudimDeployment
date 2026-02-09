@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-1 text-sm text-error-400 space-y-1']) }} role="alert">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
