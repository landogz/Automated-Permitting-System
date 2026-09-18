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
            <strong>{{ __('Applicant registration') }}</strong>
            <div class="text-muted fs-13">{{ __('Create an APICS applicant account online') }}</div>
        </div>
        <a href="{{ route('register') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">{{ __('Register') }}</a>
    </li>
</ul>
@endsection
