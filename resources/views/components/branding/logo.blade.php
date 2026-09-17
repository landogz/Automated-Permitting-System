@props([
    'height' => 40,
    'alt' => 'APICS — City of San Fernando, Pampanga · Office of the City Building Official',
])

<img
    src="{{ asset('images/branding/apics-logo.png') }}"
    alt="{{ $alt }}"
    height="{{ $height }}"
    width="{{ $height }}"
    {{ $attributes->merge(['class' => 'flex-shrink-0']) }}
    decoding="async"
>
