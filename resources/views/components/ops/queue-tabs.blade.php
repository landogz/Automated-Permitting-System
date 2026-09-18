@props([
    'id' => 'ops-queue',
    'activeLabel' => 'Active',
    'completedLabel' => 'Completed',
    'activeHint' => '',
    'completedHint' => 'Finished work for this step.',
])

<div {{ $attributes->class(['apics-ops-queue']) }} data-ops-queue="{{ $id }}">
    <div class="apics-ops-queue__tabs nav nav-pills gap-1 mb-3" role="tablist" aria-label="{{ __('Queue views') }}">
        <button
            type="button"
            class="nav-link active"
            data-ops-tab="active"
            role="tab"
            aria-selected="true"
        >
            {{ __($activeLabel) }}
            <span class="badge apics-ops-queue__count ms-1" data-ops-active-count="{{ $id }}">0</span>
        </button>
        <button
            type="button"
            class="nav-link"
            data-ops-tab="completed"
            role="tab"
            aria-selected="false"
        >
            {{ __($completedLabel) }}
            <span class="badge bg-secondary-subtle text-secondary ms-1" data-ops-completed-count="{{ $id }}">0</span>
        </button>
    </div>

    <div class="apics-ops-queue__pane is-active" data-ops-pane="active" role="tabpanel">
        {{ $active }}
    </div>

    <div class="apics-ops-queue__pane d-none" data-ops-pane="completed" role="tabpanel">
        <div class="card border apics-ops-archive-card">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h5 class="card-title mb-1 fs-15">
                    <i class="ri-archive-line text-muted me-1" aria-hidden="true"></i>
                    {{ __('View completed archive') }}
                    <span class="badge bg-secondary-subtle text-secondary ms-1" data-ops-completed-count="{{ $id }}">0</span>
                </h5>
                @if ($completedHint !== '')
                    <p class="text-muted mb-0 fs-12">{{ __($completedHint) }}</p>
                @endif
            </div>
            <div class="card-body pt-3">
                {{ $completed }}
            </div>
        </div>
    </div>
</div>
