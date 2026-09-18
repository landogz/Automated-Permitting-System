@extends('layouts.velzon.policy')

@php($title = __('Transparency Seal'))

@section('policy-body')
<p class="text-muted">{{ __('In compliance with Section 93 (Transparency Seal) of the General Appropriations Act and National Budget Circular guidance, the Office of the City Building Official publishes the following disclosures for APICS / OCBO operations.') }}</p>

<div class="text-center my-4">
    <img
        src="{{ asset('images/branding/philippine-transparency-seal.svg') }}"
        alt="{{ __('Philippine Transparency Seal') }}"
        width="160"
        height="160"
        decoding="async"
    >
</div>

<h2 class="h5 fw-semibold mt-4">{{ __('Mandated disclosures') }}</h2>
<ol class="mb-4">
    <li>{{ __('Agency mandate, functions, officials, and contact information') }}</li>
    <li>{{ __('Annual reports for the last three fiscal years (as applicable)') }}</li>
    <li>{{ __('Approved budgets and corresponding targets') }}</li>
    <li>{{ __('Major programs and projects') }}</li>
    <li>{{ __('Program / project beneficiaries where identified') }}</li>
    <li>{{ __('Status of implementation and evaluation / assessment reports') }}</li>
    <li>{{ __('Annual procurement plan, awarded contracts, and contractors / suppliers / consultants') }}</li>
</ol>

<p class="mb-0">{{ __('Document packages are maintained by OCBO / City Hall records. For certified copies or updates, use the Contact Us channel. This page will be updated as Phase I acceptance packages are finalized.') }}</p>
<p class="mt-3 mb-0"><a href="{{ route('contact') }}" class="link-primary">{{ __('Contact OCBO') }}</a></p>
@endsection
