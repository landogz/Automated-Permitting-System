{{-- Interactive privilege pipeline flowchart --}}
@php
    $flowNodes = $flowchart ?? [];
    $citizen = array_values(array_filter($flowNodes, fn ($n) => ($n['lane'] ?? '') === 'citizen'));
    $pipeline = array_values(array_filter($flowNodes, fn ($n) => ($n['lane'] ?? '') === 'pipeline'));
    $admin = array_values(array_filter($flowNodes, fn ($n) => ($n['lane'] ?? '') === 'admin'));

    $laneLabels = $isTl
        ? ['citizen' => 'Mamamayan', 'pipeline' => 'Pipeline ng operasyon', 'admin' => 'Master data / ICT']
        : ['citizen' => 'Citizen', 'pipeline' => 'Operations pipeline', 'admin' => 'Master data / ICT'];

    $buildPayload = static function (array $node, string $locale, array $privileges, array $applicant): array {
        $key = (string) ($node['key'] ?? '');
        $label = $node[$locale]['label'] ?? $node['en']['label'] ?? $key;

        if ($key === 'applicant') {
            return [
                'key' => 'applicant',
                'title' => $applicant['title'] ?? $label,
                'summary' => $applicant['summary'] ?? '',
                'code' => '',
                'screens' => [],
                'functions' => $applicant['functions'] ?? [],
                'anchor' => '#applicant',
            ];
        }

        $row = $privileges[$key] ?? [];
        $copy = $row[$locale] ?? $row['en'] ?? [];

        return [
            'key' => $key,
            'title' => $copy['title'] ?? $label,
            'summary' => $copy['summary'] ?? '',
            'code' => $key,
            'screens' => $row['screens'] ?? [],
            'functions' => $copy['functions'] ?? [],
            'anchor' => '#priv-'.str_replace('.', '-', $key),
        ];
    };

    $payloadMap = [];
    foreach ($flowNodes as $node) {
        $key = (string) ($node['key'] ?? '');
        if ($key === '') {
            continue;
        }
        $payloadMap[$key] = $buildPayload($node, $locale, $privileges, $applicant);
    }
@endphp

<section class="apics-priv-flow mb-4" data-privilege-flowchart aria-label="{{ $intro['flowchart_title'] }}">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
        <div>
            <h2 class="h5 fw-semibold mb-1">{{ $intro['flowchart_title'] }}</h2>
            <p class="text-muted fs-13 mb-0">{{ $intro['flowchart_hint'] }}</p>
        </div>
    </div>

    <script type="application/json" id="privilege-flow-data">@json($payloadMap)</script>

    <div class="apics-priv-flow__canvas card border shadow-none">
        <div class="card-body">
            <p class="apics-priv-flow__lane-label">{{ $laneLabels['citizen'] }}</p>
            <div class="apics-priv-flow__row apics-priv-flow__row--citizen mb-3">
                @foreach ($citizen as $node)
                    @php $copy = $node[$locale] ?? $node['en']; @endphp
                    <button
                        type="button"
                        class="apics-priv-flow__node apics-priv-flow__node--citizen"
                        data-flow-node="{{ $node['key'] }}"
                    >
                        <i class="{{ $node['icon'] }} apics-priv-flow__icon" aria-hidden="true"></i>
                        <span class="apics-priv-flow__node-label">{{ $copy['label'] }}</span>
                        <span class="apics-priv-flow__node-short">{{ $copy['short'] }}</span>
                    </button>
                @endforeach
                <span class="apics-priv-flow__arrow d-none d-md-inline" aria-hidden="true"><i class="ri-arrow-down-line"></i></span>
            </div>

            <p class="apics-priv-flow__lane-label">{{ $laneLabels['pipeline'] }}</p>
            <div class="apics-priv-flow__row apics-priv-flow__row--pipeline mb-3" role="list">
                @foreach ($pipeline as $index => $node)
                    @php $copy = $node[$locale] ?? $node['en']; @endphp
                    @if ($index > 0)
                        <span class="apics-priv-flow__connector" aria-hidden="true"><i class="ri-arrow-right-s-line"></i></span>
                    @endif
                    <button
                        type="button"
                        class="apics-priv-flow__node"
                        role="listitem"
                        data-flow-node="{{ $node['key'] }}"
                    >
                        <i class="{{ $node['icon'] }} apics-priv-flow__icon" aria-hidden="true"></i>
                        <span class="apics-priv-flow__node-label">{{ $copy['label'] }}</span>
                        <span class="apics-priv-flow__node-short">{{ $copy['short'] }}</span>
                    </button>
                @endforeach
            </div>

            <p class="apics-priv-flow__lane-label">{{ $laneLabels['admin'] }}</p>
            <div class="apics-priv-flow__row apics-priv-flow__row--admin" role="list">
                @foreach ($admin as $node)
                    @php $copy = $node[$locale] ?? $node['en']; @endphp
                    <button
                        type="button"
                        class="apics-priv-flow__node apics-priv-flow__node--admin"
                        role="listitem"
                        data-flow-node="{{ $node['key'] }}"
                    >
                        <i class="{{ $node['icon'] }} apics-priv-flow__icon" aria-hidden="true"></i>
                        <span class="apics-priv-flow__node-label">{{ $copy['label'] }}</span>
                        <span class="apics-priv-flow__node-short">{{ $copy['short'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="privilege-flow-modal" tabindex="-1" aria-labelledby="privilege-flow-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="privilege-flow-modal-title">—</h5>
                    <code class="fs-12" id="privilege-flow-modal-code"></code>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ $intro['modal_close'] }}"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="privilege-flow-modal-summary"></p>
                <div id="privilege-flow-modal-screens-wrap" class="mb-3">
                    <p class="fw-semibold fs-13 mb-1">{{ $intro['modal_screens'] }}</p>
                    <p class="fs-13 mb-0" id="privilege-flow-modal-screens"></p>
                </div>
                <p class="fw-semibold fs-13 mb-2">{{ $intro['modal_functions'] }}</p>
                <ul class="mb-0" id="privilege-flow-modal-functions"></ul>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <a href="#privileges" class="btn btn-soft-primary" id="privilege-flow-modal-more">{{ $intro['modal_read_more'] }}</a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ $intro['modal_close'] }}</button>
            </div>
        </div>
    </div>
</div>
