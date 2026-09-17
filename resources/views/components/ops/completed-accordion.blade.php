@props([
    'id' => 'ops-completed',
    'title' => 'Completed',
    'hint' => 'Finished work for this step. Expand to review history.',
    'expanded' => false,
])

@php
    $collapseId = $id.'-collapse';
    $isExpanded = (bool) $expanded;
@endphp

<div {{ $attributes->class(['accordion', 'custom-accordionwithicon', 'accordion-secondary', 'mt-3']) }} id="{{ $id }}-accordion">
    <div class="accordion-item material-shadow border">
        <h2 class="accordion-header" id="{{ $id }}-heading">
            <button
                class="accordion-button {{ $isExpanded ? '' : 'collapsed' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $collapseId }}"
                aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
                aria-controls="{{ $collapseId }}"
            >
                <span class="d-flex flex-wrap align-items-center gap-2 w-100 pe-3">
                    <span class="d-inline-flex align-items-center gap-2">
                        <i class="ri-checkbox-circle-line text-success" aria-hidden="true"></i>
                        <span class="fw-semibold">{{ __($title) }}</span>
                        <span class="badge bg-success-subtle text-success" data-ops-completed-count="{{ $id }}">0</span>
                    </span>
                    <span class="text-muted fw-normal fs-12 ms-md-2 d-none d-sm-inline">{{ __($hint) }}</span>
                </span>
            </button>
        </h2>
        <div
            id="{{ $collapseId }}"
            class="accordion-collapse collapse {{ $isExpanded ? 'show' : '' }}"
            aria-labelledby="{{ $id }}-heading"
            data-bs-parent="#{{ $id }}-accordion"
        >
            <div class="accordion-body pt-0">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
