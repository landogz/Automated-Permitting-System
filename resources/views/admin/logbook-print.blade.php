@extends('print.layout')

@section('title', 'Logbook '.$entry->entry_no.' | APICS')

@section('content')
    @include('print.partials.letterhead', [
        'formCode' => strtoupper((string) ($entry->book_type?->value ?? $entry->book_type ?? 'LOG')),
        'formTitle' => 'Official Logbook Entry',
        'documentNo' => $entry->entry_no,
        'subtitle' => $entry->subject,
    ])

    <section class="meta">
        <div>
            <span class="meta__label">Book type</span>
            <span class="meta__value">{{ $entry->book_type?->value ?? $entry->book_type }}</span>
        </div>
        <div>
            <span class="meta__label">Application</span>
            <span class="meta__value">{{ $entry->application?->application_no ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Recipient</span>
            <span class="meta__value">{{ $entry->recipient_name ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Recorded at</span>
            <span class="meta__value">{{ optional($entry->recorded_at)->format('M d, Y · h:i A') ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Recorded by</span>
            <span class="meta__value">{{ $entry->recordedByUser?->name ?? '—' }}</span>
        </div>
        <div>
            <span class="meta__label">Subject</span>
            <span class="meta__value">{{ $entry->subject }}</span>
        </div>
    </section>

    @if ($entry->notes)
        <div class="notes">{{ $entry->notes }}</div>
    @endif

    <section class="signatures">
        <div class="sig">
            <div class="sig__line"></div>
            <div class="sig__name">{{ $entry->recordedByUser?->name ?? '' }}</div>
            <div class="sig__role">Recorded by</div>
        </div>
        <div class="sig">
            <div class="sig__line"></div>
            <div class="sig__role">Checked by</div>
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
