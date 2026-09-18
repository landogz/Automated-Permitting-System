@extends('print.layout')

@section('title', $formCode.' · '.$inspection->inspection_no.' | APICS')

@push('styles')
<style>
    .print-table { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
    .print-table th, .print-table td {
        border: 1px solid var(--rule);
        padding: 7px 8px;
        text-align: left;
        vertical-align: top;
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
        'documentNo' => $inspection->inspection_no,
        'subtitle' => $inspection->application?->project_title,
    ])

    <section class="meta">
        <div>
            <span class="meta__label">Application</span>
            <span class="meta__value">{{ $inspection->application?->application_no ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Inspection type</span>
            <span class="meta__value">{{ $inspection->type }}</span>
        </div>
        <div>
            <span class="meta__label">Status / result</span>
            <span class="meta__value">
                {{ $inspection->status?->value ?? $inspection->status }}
                @if ($inspection->result)
                    · {{ $inspection->result?->value ?? $inspection->result }}
                @endif
            </span>
        </div>
        <div>
            <span class="meta__label">Scheduled</span>
            <span class="meta__value">{{ optional($inspection->scheduled_at)->format('M d, Y · h:i A') ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Location</span>
            <span class="meta__value">{{ $inspection->location ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Lead inspector</span>
            <span class="meta__value">{{ $inspection->inspector?->name ?? '—' }}</span>
        </div>
    </section>

    @if (in_array($doc, ['qms-38', 'qms-39'], true))
        <h2 class="section-title">{{ $doc === 'qms-39' ? 'Team assignment (QMS-39)' : 'Schedule details (QMS-38)' }}</h2>
        <section class="meta">
            <div>
                <span class="meta__label">Purpose</span>
                <span class="meta__value">{{ $scheduleSheet['purpose'] ?? '—' }}</span>
            </div>
            <div>
                <span class="meta__label">Meeting point</span>
                <span class="meta__value">{{ $scheduleSheet['meeting_point'] ?? '—' }}</span>
            </div>
            <div>
                <span class="meta__label">Disciplines</span>
                <span class="meta__value">{{ !empty($scheduleSheet['disciplines']) ? implode(', ', $scheduleSheet['disciplines']) : '—' }}</span>
            </div>
            <div>
                <span class="meta__label">Coordination notes</span>
                <span class="meta__value">{{ $scheduleSheet['coordination_notes'] ?? $scheduleSheet['remarks'] ?? '—' }}</span>
            </div>
        </section>

        <table class="print-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Discipline</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($team as $member)
                    <tr>
                        <td>{{ $member['name'] }}</td>
                        <td>{{ $member['role'] }}</td>
                        <td>{{ $member['discipline'] !== '' ? $member['discipline'] : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">{{ $inspection->inspector?->name ?? 'No team members recorded' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if ($doc === 'o-03')
        <h2 class="section-title">Inspector notes (O-03)</h2>
        <section class="meta">
            <div>
                <span class="meta__label">Weather / access</span>
                <span class="meta__value">{{ $inspectorNotes['weather'] !== '' ? $inspectorNotes['weather'] : '—' }}</span>
            </div>
            <div>
                <span class="meta__label">Site conditions</span>
                <span class="meta__value">{{ $inspectorNotes['site_conditions'] !== '' ? $inspectorNotes['site_conditions'] : '—' }}</span>
            </div>
        </section>
        <div class="notes"><strong>Findings</strong>
{{ $inspectorNotes['findings'] !== '' ? $inspectorNotes['findings'] : ($inspection->notes ?? '—') }}</div>
        @if (($inspectorNotes['observed_defects'] ?? '') !== '')
            <div class="notes"><strong>Observed defects</strong>
{{ $inspectorNotes['observed_defects'] }}</div>
        @endif
        @if (($inspectorNotes['recommendations'] ?? '') !== '')
            <div class="notes"><strong>Recommendations</strong>
{{ $inspectorNotes['recommendations'] }}</div>
        @endif
    @endif

    @if ($doc === 'qms-65')
        <h2 class="section-title">Compliance checklist (QMS-65)</h2>
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
                @foreach ($compliance['items'] as $item)
                    <tr>
                        <td>{{ $item['code'] ?? '—' }}</td>
                        <td>{{ $item['label'] ?? $item['item'] ?? '—' }}</td>
                        <td class="status">{{ strtoupper((string) ($item['status'] ?? 'na')) }}</td>
                        <td>{{ $item['remarks'] ?? $item['notes'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if (($compliance['overall_remarks'] ?? '') !== '')
            <div class="notes"><strong>Overall remarks</strong>
{{ $compliance['overall_remarks'] }}</div>
        @endif
    @endif

    @if ($doc === 'dpwh-77-006-e')
        <h2 class="section-title">Final electrical inspection (DPWH 77-006-E)</h2>
        <table class="print-table">
            <thead>
                <tr>
                    <th>Inspection item</th>
                    <th style="width:90px">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    'service_entrance' => 'Service entrance',
                    'grounding' => 'Grounding / bonding',
                    'panel_boards' => 'Panel boards / overcurrent',
                    'wiring_methods' => 'Wiring methods / raceways',
                    'fixtures_devices' => 'Fixtures / devices',
                    'load_schedule' => 'Load schedule conformance',
                ] as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="status">{{ strtoupper((string) ($electrical[$key] ?? 'na')) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Electrical result</strong></td>
                    <td class="status">{{ strtoupper((string) ($electrical['result'] ?? 'na')) }}</td>
                </tr>
            </tbody>
        </table>
        @if (($electrical['remarks'] ?? '') !== '')
            <div class="notes"><strong>Remarks</strong>
{{ $electrical['remarks'] }}</div>
        @endif
    @endif

    <section class="signatures">
        <div class="sig">
            <div class="sig__line"></div>
            <div class="sig__name">{{ $inspection->inspector?->name ?? '' }}</div>
            <div class="sig__role">Inspected by</div>
        </div>
        <div class="sig">
            <div class="sig__line"></div>
            <div class="sig__name">{{ $inspection->scheduledByUser?->name ?? '' }}</div>
            <div class="sig__role">Scheduled / noted by</div>
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
