@extends('print.layout')

@section('title', $formCode.' · '.$evaluation->uuid.' | APICS')

@push('styles')
<style>
    .print-table { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
    .print-table th, .print-table td {
        border: 1px solid var(--rule);
        padding: 7px 8px;
        text-align: left;
        vertical-align: top;
        font-size: 12px;
    }
    .print-table th {
        background: var(--band);
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
    }
    .print-table td.status { text-align: center; font-weight: 700; text-transform: uppercase; width: 72px; }
    .section-title {
        font-size: 13px;
        font-weight: 700;
        margin: 0 0 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid var(--rule);
    }
</style>
@endpush

@section('content')
    @include('print.partials.letterhead', [
        'formCode' => $formCode,
        'formTitle' => $formTitle,
        'documentNo' => $evaluation->application?->application_no ?? substr((string) $evaluation->uuid, 0, 8),
        'subtitle' => $evaluation->application?->project_title,
    ])

    <section class="meta">
        <div>
            <span class="meta__label">Application</span>
            <span class="meta__value">{{ $evaluation->application?->application_no ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Result</span>
            <span class="meta__value">{{ $evaluation->result?->value ?? $evaluation->result ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Evaluator</span>
            <span class="meta__value">{{ $evaluation->evaluator?->name ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Decided</span>
            <span class="meta__value">{{ optional($evaluation->decided_at)->format('M d, Y · h:i A') ?? 'Draft' }}</span>
        </div>
        <div>
            <span class="meta__label">Routing step</span>
            <span class="meta__value">{{ $evaluation->step?->label ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Department</span>
            <span class="meta__value">{{ $evaluation->step?->department?->name ?? '—' }}</span>
        </div>
    </section>

    <h2 class="section-title">{{ $formTitle }} checklist</h2>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:90px">Code</th>
                <th>Item</th>
                <th style="width:72px">Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['code'] ?? '—' }}</td>
                    <td>{{ $item['label'] ?? '—' }}</td>
                    <td class="status">{{ strtoupper((string) ($item['status'] ?? 'na')) }}</td>
                    <td>{{ $item['remarks'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($doc === 'qms-63' && ($findings['overall_remarks'] ?? '') !== '')
        <div class="notes"><strong>Overall remarks</strong>
{{ $findings['overall_remarks'] }}</div>
    @endif
    @if ($doc === 'qms-64' && ($findings['discipline_remarks'] ?? '') !== '')
        <div class="notes"><strong>Discipline remarks</strong>
{{ $findings['discipline_remarks'] }}</div>
    @endif
    @if ($evaluation->remarks)
        <div class="notes"><strong>Decision remarks</strong>
{{ $evaluation->remarks }}</div>
    @endif

    <section class="signatures">
        <div class="sig">
            <div class="sig__line"></div>
            <div class="sig__name">{{ $evaluation->evaluator?->name ?? '' }}</div>
            <div class="sig__role">Evaluated by</div>
        </div>
        <div class="sig">
            <div class="sig__line"></div>
            <div class="sig__role">Reviewed by</div>
        </div>
        <div class="sig">
            <div class="sig__line"></div>
            <div class="sig__role">City Building Official</div>
        </div>
    </section>

    <footer class="footer">
        <span>System-generated document · APICS · CSFP OCBO · Not valid without authorized signature when required.</span>
        <span>Printed {{ now()->format('M d, Y · h:i A') }}</span>
    </footer>
@endsection
