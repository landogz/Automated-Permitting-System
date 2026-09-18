@extends('layouts.velzon.policy')

@php
    $title = __('Contact Us');
    $agency = (string) config('apics_public.agency_name');
    $lgu = (string) config('apics_public.lgu_name');
    $address = (string) config('apics_public.address');
    $phone = (string) config('apics_public.phone');
    $email = (string) config('apics_public.email');
    $hours = (string) config('apics_public.office_hours');
    $telHref = preg_replace('/[^\d+]/', '', $phone) ?: $phone;
@endphp

@section('title', $title)

@section('policy-body')
<p class="text-muted">{{ __('Reach the Office of the City Building Official for inquiries, feedback, follow-ups, and complaints related to APICS permitting services.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Office') }}</h2>
<p class="mb-1"><strong>{{ $agency }}</strong></p>
<p class="mb-1">{{ $lgu }}</p>
<p class="mb-3">{{ $address }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Contact details') }}</h2>
<ul>
    <li>
        <strong>{{ __('Phone') }}:</strong>
        <a href="tel:{{ $telHref }}">{{ $phone }}</a>
    </li>
    <li>
        <strong>{{ __('Email') }}:</strong>
        <a href="mailto:{{ $email }}">{{ $email }}</a>
    </li>
    <li>
        <strong>{{ __('Office hours') }}:</strong>
        {{ $hours }}
    </li>
</ul>

<h2 class="h5 fw-semibold mt-4">{{ __('Channels') }}</h2>
<ul>
    <li>{{ __('In person: OCBO front desk during the office hours listed above') }}</li>
    <li>{{ __('Online: use APICS Sign in / Register for application concerns') }}</li>
    <li>
        <a href="https://www.foi.gov.ph" rel="noopener noreferrer" target="_blank">{{ __('Freedom of Information (foi.gov.ph)') }}</a>
    </li>
</ul>

<p class="mb-0 text-muted fs-13">{{ __('Confirm phone and email with City Hall ICT before publishing as official. Override via APICS_OFFICE_PHONE and APICS_OFFICE_EMAIL in the environment.') }}</p>
@endsection
