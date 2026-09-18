@extends('layouts.velzon.policy')

@php($title = __('Accessibility Statement'))

@section('policy-body')
<p>{{ __('The Office of the City Building Official is committed to making APICS accessible to the widest possible audience, regardless of ability or technology, consistent with RA 7277 (as amended), BP 344, and DICT web accessibility guidance.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Conformance status') }}</h2>
<p>{{ __('This website aims to conform to the Web Content Accessibility Guidelines (WCAG) 2.2 Level AA.') }}</p>

<h2 class="h5 fw-semibold mt-4">{{ __('Measures we take') }}</h2>
<ul>
    <li>{{ __('Skip link to main content on public pages') }}</li>
    <li>{{ __('Semantic headings and landmarks') }}</li>
    <li>{{ __('Keyboard-operable navigation and forms') }}</li>
    <li>{{ __('Visible focus indicators and text alternatives for meaningful images') }}</li>
    <li>{{ __('Respect for prefers-reduced-motion') }}</li>
</ul>

<h2 class="h5 fw-semibold mt-4">{{ __('Known limitations') }}</h2>
<ul>
    <li>{{ __('Some PDF printouts and legacy attachments may not yet be fully tagged for assistive technology. Accessible alternatives are available on request.') }}</li>
    <li>{{ __('Filipino language UI coverage is expanding; English is the primary interface language in Phase I.') }}</li>
</ul>

<h2 class="h5 fw-semibold mt-4">{{ __('Feedback') }}</h2>
<p>{{ __('If you encounter an accessibility barrier, contact OCBO via the Contact Us page. We aim to respond within five (5) working days.') }}</p>
<p class="mb-0"><a href="{{ route('contact') }}" class="link-primary">{{ __('Report an accessibility issue') }}</a></p>

<p class="text-muted fs-13 mt-4 mb-0">{{ __('This statement was last reviewed on :date.', ['date' => '19 September 2026']) }}</p>
@endsection
