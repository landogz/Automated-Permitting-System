@extends('layouts.velzon.policy')

@php($title = __('Citizen’s Charter'))

@section('policy-body')
<p class="text-muted">{{ __('Published in support of RA 11032 (Ease of Doing Business and Efficient Government Service Delivery Act) for OCBO frontline permitting services delivered through APICS.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Mandate') }}</h2>
<p>{{ __('The Office of the City Building Official enforces the National Building Code (P.D. 1096) and related ordinances for the City of San Fernando, Pampanga, including building and ancillary permit evaluation, inspection, and compliance.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Frontline services (Phase I)') }}</h2>
<ul>
    <li>{{ __('Online applicant registration (subject to OCBO approval)') }}</li>
    <li>{{ __('Building permit application draft, document upload, and submission') }}</li>
    <li>{{ __('Application status tracking across evaluation, inspection, payment, and release') }}</li>
    <li>{{ __('Staff evaluation queue, routing, Orders of Payment (G-02), and compliance notices') }}</li>
</ul>

<h2 class="h5 fw-semibold mt-4">{{ __('Service standards') }}</h2>
<ul>
    <li>{{ __('Registration review: within published OCBO office working days after complete submission') }}</li>
    <li>{{ __('Simple / Complex / Highly Technical classification drives evaluation timelines per office rules') }}</li>
    <li>{{ __('Applicants receive status updates in-app; processing days exclude weekends and declared holidays') }}</li>
</ul>

<h2 class="h5 fw-semibold mt-4">{{ __('How to avail') }}</h2>
<ol>
    <li>{{ __('Register for an applicant account and wait for OCBO approval.') }}</li>
    <li>{{ __('Sign in and create a draft application using unified QMS forms.') }}</li>
    <li>{{ __('Upload required documents and submit for intake.') }}</li>
    <li>{{ __('Track pipeline status until release or compliance notice.') }}</li>
</ol>

<p class="mb-0"><a href="{{ route('register') }}" class="btn btn-primary">{{ __('Register as applicant') }}</a></p>
@endsection
