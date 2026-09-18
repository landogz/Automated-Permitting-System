@extends('layouts.velzon.policy')

@php($title = __('Privacy Notice'))

@section('policy-body')
<p class="text-muted">{{ __('This notice describes how APICS processes personal data under the Data Privacy Act of 2012 (RA 10173) for OCBO permitting services.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Personal data we collect') }}</h2>
<ul>
    <li>{{ __('Account identity: full name, email address, mobile number') }}</li>
    <li>{{ __('Application data: project details, property information, uploaded supporting documents') }}</li>
    <li>{{ __('Technical logs: authentication events, IP address, and audit metadata required for ICT oversight') }}</li>
</ul>

<h2 class="h5 fw-semibold mt-4">{{ __('Purpose and legal basis') }}</h2>
<p>{{ __('Data is processed to evaluate and issue building-related permits, enforce P.D. 1096 and LGU ordinances, and meet audit and anti-fraud requirements. Processing is necessary for the performance of a public function and contractual service delivery.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Sharing') }}</h2>
<p>{{ __('Data may be shared with evaluating offices (e.g., Engineering, Treasurer) strictly for routing and assessment. We do not sell personal data.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Your rights') }}</h2>
<p>{{ __('You may request access, correction, or other rights under RA 10173 through the City Data Protection Officer / OCBO channel listed on Contact Us. Identity verification may be required.') }}</p>

<p class="mb-0"><a href="{{ route('contact') }}" class="link-primary">{{ __('Contact for privacy requests') }}</a></p>
@endsection
