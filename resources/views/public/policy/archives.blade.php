@extends('layouts.velzon.policy')

@php($title = __('Archives'))

@section('policy-body')
<p class="text-muted">{{ __('Past announcements and releases, organized by month — a GWTD-required agency footer element.') }}</p>

<section id="2026" class="mb-4">
    <h2 class="h5 fw-semibold">2026</h2>
    <ul class="mb-0">
        <li class="mb-2">
            <time datetime="2026-09">{{ __('September 2026') }}</time> —
            {{ __('APICS Phase I public portal and applicant registration soft launch materials (in progress).') }}
        </li>
        <li class="mb-0 text-muted">{{ __('Additional press releases will appear here as OCBO publishes them.') }}</li>
    </ul>
</section>

<p class="mb-0"><a href="{{ route('faqs') }}" class="link-primary">{{ __('Browse FAQs') }}</a></p>
@endsection
