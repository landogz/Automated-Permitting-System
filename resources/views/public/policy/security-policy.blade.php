@extends('layouts.velzon.policy')

@php($title = __('Security Policy'))

@section('policy-body')
<p class="text-muted">{{ __('DICT GWTD requires government sites to publish a security policy so citizens can trust online transactions.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('How we protect APICS') }}</h2>
<ul>
    <li>{{ __('Authentication via Laravel Sanctum token / session controls under /api/v1') }}</li>
    <li>{{ __('Server-side validation (FormRequest), authorization policies, and audit logging of mutations') }}</li>
    <li>{{ __('Password rules, CSRF protection on web state changes, and rate limiting on sensitive endpoints') }}</li>
    <li>{{ __('Role-based access for applicants vs OCBO staff') }}</li>
</ul>

<h2 class="h5 fw-semibold mt-4">{{ __('Your responsibilities') }}</h2>
<ul>
    <li>{{ __('Keep your account credentials confidential') }}</li>
    <li>{{ __('Sign out on shared devices') }}</li>
    <li>{{ __('Report suspected account misuse via Contact Us') }}</li>
</ul>

<p class="mb-0"><a href="{{ route('privacy') }}" class="link-primary">{{ __('Read the Data Privacy Policy') }}</a></p>
@endsection
