@props([
    'current' => 'evaluation',
])

@php
    $steps = [
        [
            'key' => 'evaluation',
            'label' => 'Evaluation Queue',
            'hint' => 'Classify & evaluate',
            'detail' => 'Review submitted filings: classify Simple / Complex / Highly Technical, generate a routing slip from the matching Routing Template, track evaluation time, and decide compliance.',
            'route' => 'admin.evaluation-queue',
            'icon' => 'ri-clipboard-line',
            'permission' => 'evaluations.manage',
        ],
        [
            'key' => 'inspections',
            'label' => 'Inspections',
            'hint' => 'Field verification',
            'detail' => 'Schedule joint / electrical / final inspections for applications that finished evaluation, record compliance sheets, and pass or fail the site visit.',
            'route' => 'admin.inspections',
            'icon' => 'ri-search-line',
            'permission' => 'inspections.manage',
        ],
        [
            'key' => 'payment',
            'label' => 'Orders of Payment',
            'hint' => 'G-02 assessment',
            'detail' => 'Generate G-02 Orders of Payment from fee rules (CTO / BFP / DPWH stubs), review line items, and mark paid when the cashier stub clears. After payment, proceed to the Releasing area (G-01 Logbooks).',
            'route' => 'admin.orders-of-payment',
            'icon' => 'ri-bill-line',
            'permission' => 'fees.manage',
        ],
        [
            'key' => 'compliance',
            'label' => 'Compliance Notices',
            'hint' => 'G-03 / G-04',
            'detail' => 'Issue G-03 Notice of Compliance or G-04 Notice of Disapproval for deficient filings, and record applicant appeals for re-evaluation.',
            'route' => 'admin.compliance-notices',
            'icon' => 'ri-notification-3-line',
            'permission' => 'compliance.manage',
        ],
    ];
    $keys = array_column($steps, 'key');
    $currentIndex = array_search($current, $keys, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
    $currentStep = $steps[$currentIndex];
@endphp

<nav {{ $attributes->class(['apics-pipeline', 'mb-3']) }} aria-label="{{ __('Permit operations process') }}">
    {{-- Slim ~48px pipeline strip --}}
    <div class="apics-pipeline__strip" role="list">
        @foreach ($steps as $index => $step)
            @php
                $isCurrent = $step['key'] === $current;
                $isDone = $index < $currentIndex;
            @endphp
            <a
                href="{{ route($step['route']) }}"
                class="apics-pipeline__step {{ $isCurrent ? 'is-current' : '' }} {{ $isDone ? 'is-done' : '' }}"
                role="listitem"
                data-ops-permission="{{ $step['permission'] }}"
                @if ($isCurrent) aria-current="step" @endif
                title="{{ __($step['detail']) }}"
            >
                <span class="apics-pipeline__index" aria-hidden="true">
                    @if ($isDone)
                        <i class="ri-check-line"></i>
                    @else
                        {{ $index + 1 }}
                    @endif
                </span>
                <span class="apics-pipeline__label">{{ __($step['label']) }}</span>
            </a>
        @endforeach
    </div>

    <div class="apics-pipeline__meta d-flex flex-wrap align-items-center gap-2 mt-2">
        <button
            type="button"
            class="btn btn-link btn-sm text-decoration-none px-0 apics-pipeline__guide-toggle"
            data-bs-toggle="collapse"
            data-bs-target="#apics-pipeline-guide"
            aria-expanded="false"
            aria-controls="apics-pipeline-guide"
        >
            <i class="ri-information-line align-middle me-1"></i>
            <span>{{ __('Show workflow guide') }}</span>
        </button>
    </div>

    <div class="collapse" id="apics-pipeline-guide">
        <div class="apics-pipeline__guide card border mt-2 mb-0">
            <div class="card-body py-3">
                <div class="d-flex gap-2">
                    <i class="{{ $currentStep['icon'] }} fs-18 text-primary mt-1 flex-shrink-0" aria-hidden="true"></i>
                    <div>
                        <p class="fw-semibold mb-1">
                            {{ __('Step :n of :total — :label', ['n' => $currentIndex + 1, 'total' => count($steps), 'label' => __($currentStep['label'])]) }}
                        </p>
                        <p class="mb-0 fs-13 text-muted">{{ __($currentStep['detail']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- Application detail + map expand modals live in layouts.velzon.app --}}

{{-- Read-only routing templates catalog for Operations staff --}}
<div class="modal fade" id="modal-ops-routing-templates" tabindex="-1" aria-labelledby="modal-ops-routing-templates-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-ops-routing-templates-label">Routing templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-13 mb-3">
                    Templates define the department path for each classification. After an application is classified,
                    <strong>Generate routing slip</strong> on the Evaluation Queue copies the matching template onto that filing.
                </p>
                <div id="ops-routing-templates-body">
                    <div class="text-center text-muted py-4">Loading…</div>
                </div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <a
                    href="{{ route('admin.routing-templates') }}"
                    class="btn apics-btn-cta d-none"
                    id="btn-ops-manage-routing-templates"
                    data-nav-roles="admin"
                    data-nav-permissions="workflow.manage"
                >
                    <i class="ri-settings-3-line" aria-hidden="true"></i>
                    <span class="btn-label">Manage in Workflow Config</span>
                </a>
                <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-line" aria-hidden="true"></i>
                        <span class="btn-label">Close</span>
                    </button>
            </div>
        </div>
    </div>
</div>
