{{-- Shared government / LGU print letterhead — see .cursor/rules/government-print-format.mdc --}}
@php
    $formCode = $formCode ?? 'FORM';
    $formTitle = $formTitle ?? 'Official document';
    $documentNo = $documentNo ?? '';
    $subtitle = $subtitle ?? null;
@endphp
<header class="gov-letterhead">
    <img src="{{ asset('images/branding/csfp-seal.png') }}" alt="City of San Fernando, Pampanga" width="64" height="64" onerror="this.style.display='none'">
    <div>
        <p class="gov-letterhead__org">Republic of the Philippines · City of San Fernando, Pampanga</p>
        <p class="gov-letterhead__office">Office of the City Building Official (OCBO)</p>
        <p class="gov-letterhead__system">APICS — Automated Permitting, Inspection, and Compliance System</p>
    </div>
</header>

<div class="gov-doc-title">
    <div class="gov-doc-title__code">{{ $formCode }}</div>
    <h1>{{ $formTitle }}</h1>
    @if ($documentNo !== '')
        <p class="gov-doc-title__no">{{ $documentNo }}</p>
    @endif
    @if ($subtitle)
        <p class="gov-doc-title__sub">{{ $subtitle }}</p>
    @endif
</div>
