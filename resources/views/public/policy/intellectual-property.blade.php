@extends('layouts.velzon.policy')

@php($title = __('Intellectual Property Rights Policy'))

@section('policy-body')
<p class="text-muted">{{ __('Published in the standard footer per DICT Government Website Template Design (GWTD) user-policy requirements, aligned with the Government Digital Assets Licensing Framework (GDALF) principles.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Public domain notice') }}</h2>
<p>{{ __('Unless otherwise stated, content on this website is public domain. You may reuse government information with proper attribution to the City Government of San Fernando / Office of the City Building Official.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Exceptions') }}</h2>
<ul>
    <li>{{ __('Third-party materials (logos, licensed fonts, vendor libraries) remain under their respective licenses.') }}</li>
    <li>{{ __('Personal data and confidential application documents are not public domain and are protected under RA 10173.') }}</li>
    <li>{{ __('The Philippine coat of arms and Transparency Seal are used under official government identity protocols.') }}</li>
</ul>

<p class="mb-0"><a href="{{ route('contact') }}" class="link-primary">{{ __('Questions about reuse') }}</a></p>
@endsection
