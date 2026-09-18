@props([
    'id' => 'location',
    'label' => 'Location',
    'required' => false,
    'placeholder' => 'Search barangay, street, or landmark…',
    'addressName' => null,
    'help' => 'Search or click the map to drop a pin. Drag the marker to fine-tune.',
])

@php
    $addressId = $id;
    $latId = $id.'-lat';
    $lngId = $id.'-lng';
    $mapId = $id.'-map';
    $isRequired = (bool) $required;
@endphp

<div
    class="apics-location-picker"
    data-location-picker
    data-address-input="{{ $addressId }}"
    data-lat-input="{{ $latId }}"
    data-lng-input="{{ $lngId }}"
    @if ($isRequired) data-required="1" @endif
>
    <label class="form-label" for="{{ $addressId }}">
        {{ __($label) }}
        @if ($isRequired)<span class="text-danger">*</span>@endif
    </label>

    <div class="position-relative mb-2">
        <div class="input-group">
            <span class="input-group-text"><i class="ri-search-line" aria-hidden="true"></i></span>
            <input
                type="text"
                class="form-control"
                id="{{ $addressId }}"
                name="{{ $addressName ?? $addressId }}"
                data-location-address
                data-location-search
                @if ($isRequired) required @endif
                maxlength="500"
                autocomplete="off"
                placeholder="{{ __($placeholder) }}"
            >
        </div>
        <div
            class="list-group w-100 border rounded apics-location-picker__results d-none"
            data-location-results
            role="listbox"
            aria-hidden="true"
        ></div>
    </div>

    <input type="hidden" id="{{ $latId }}" data-location-lat value="">
    <input type="hidden" id="{{ $lngId }}" data-location-lng value="">

    <div
        id="{{ $mapId }}"
        class="apics-location-picker__map border rounded overflow-hidden mb-2"
        data-location-map
        role="application"
        aria-label="{{ __('Map for :label', ['label' => $label]) }}"
    ></div>

    <div class="d-flex flex-wrap justify-content-between gap-2">
        <p class="text-muted fs-12 mb-0">{{ __($help) }}</p>
        <p class="text-muted fs-12 mb-0 font-monospace" data-location-coords>No pin yet — search or click the map</p>
    </div>
</div>
