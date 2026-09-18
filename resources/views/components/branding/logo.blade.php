@props([
    'height' => 40,
    'alt' => 'City of San Fernando, Pampanga — Official Seal',
])

<img
    src="{{ asset('images/branding/csfp-seal.png') }}"
    alt="{{ $alt }}"
    height="{{ $height }}"
    width="{{ $height }}"
    {{ $attributes->merge(['class' => 'flex-shrink-0']) }}
    decoding="async"
>
