@extends('layouts.velzon.policy')

@php($title = __('Downloads'))

@section('policy-body')
<p class="text-muted">{{ __('Useful OCBO / APICS documents — forms, manuals, and policies. Additional packages will be published as Phase I acceptance materials are finalized.') }}</p>

<ul class="list-group list-group-flush border rounded mb-0">
    <li class="list-group-item d-flex justify-content-between align-items-start gap-3">
        <div>
            <strong>{{ __('Citizen’s Charter') }}</strong>
            <div class="text-muted fs-13">{{ __('Service standards and how to avail OCBO frontline services') }}</div>
        </div>
        <a href="{{ route('citizens-charter') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">{{ __('Open') }}</a>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-start gap-3">
        <div>
            <strong>{{ __('Transparency disclosures') }}</strong>
            <div class="text-muted fs-13">{{ __('Mandated Transparency Seal information') }}</div>
        </div>
        <a href="{{ route('transparency') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">{{ __('Open') }}</a>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-start gap-3">
        <div>
            <strong>{{ __('Privilege & role documentation') }}</strong>
            <div class="text-muted fs-13">{{ __('English and Tagalog guides to every system privilege and office role') }}</div>
        </div>
        <div class="d-flex flex-shrink-0 gap-1">
            <a href="{{ route('documentation.privileges', ['locale' => 'en']) }}" class="btn btn-sm btn-outline-primary">EN</a>
            <a href="{{ route('documentation.privileges', ['locale' => 'tl']) }}" class="btn btn-sm btn-outline-primary">TL</a>
        </div>
    </li>
</ul>
@endsection
