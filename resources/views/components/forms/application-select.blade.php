@props([
    'id' => 'app-uuid',
    'label' => 'Application',
    'required' => false,
    'help' => 'Search by application number, project title, or applicant name.',
    'forStep' => null,
])

@php
    $searchId = $id.'-search';
    $isRequired = (bool) $required;
    $step = is_string($forStep) && $forStep !== '' ? $forStep : null;
@endphp

<div
    class="apics-app-select mb-0"
    data-apics-app-select
    data-input-id="{{ $id }}"
    @if ($step) data-for-step="{{ $step }}" @endif
    @if ($isRequired) data-required="1" @endif
>
    <label class="form-label" for="{{ $searchId }}">
        {{ $label }}
        @if ($isRequired)
            <span class="text-danger">*</span>
        @else
            <span class="text-muted fw-normal">(optional)</span>
        @endif
    </label>

    <input
        type="hidden"
        id="{{ $id }}"
        @if ($isRequired) required @endif
        value=""
        autocomplete="off"
    >

    <div class="apics-app-select-control input-group">
        <button
            type="button"
            class="form-select text-start apics-app-select-toggle"
            id="{{ $searchId }}"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="{{ $id }}-results"
        >
            <span class="apics-app-select-placeholder text-muted">Search &amp; select application…</span>
            <span class="apics-app-select-label d-none"></span>
        </button>
        <button
            type="button"
            class="btn btn-outline-secondary apics-app-select-clear d-none"
            title="Clear selection"
            aria-label="Clear selected application"
        >
            <i class="ri-close-line"></i>
        </button>
    </div>

    <div class="apics-app-select-panel d-none border rounded bg-body shadow-sm mt-1" role="presentation">
        <div class="p-2 border-bottom">
            <input
                type="search"
                class="form-control form-control-sm apics-app-select-query"
                placeholder="Type to search…"
                autocomplete="off"
                aria-label="Search applications"
            >
        </div>
        <div
            id="{{ $id }}-results"
            class="apics-app-select-results list-group list-group-flush"
            role="listbox"
            aria-label="Application results"
        ></div>
    </div>

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
