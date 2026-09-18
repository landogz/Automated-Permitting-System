@extends('layouts.velzon.policy')

@php
    $title = __('Sitemap');
    $q = trim((string) request('q', ''));
    $links = [
        ['Home', route('home'), __('Public landing page')],
        ['Sign in', route('login'), __('Applicant and staff authentication')],
        ['Register', route('register'), __('Applicant self-registration')],
        ['Applications', route('applications.index'), __('Applicant workspace')],
        ['Transparency Seal', route('transparency'), __('Mandated disclosures')],
        ["Citizen's Charter", route('citizens-charter'), __('RA 11032 service standards')],
        ['Privacy Notice', route('privacy'), __('RA 10173 privacy disclosures')],
        ['Accessibility Statement', route('accessibility'), __('WCAG / DICT accessibility')],
        ['Contact Us', route('contact'), __('Feedback and office address')],
        ['FAQs', route('faqs'), __('Frequently asked questions')],
        ['Downloads', route('downloads'), __('Forms and manuals')],
        ['Archives', route('archives'), __('News and releases archive')],
        ['Intellectual Property', route('intellectual-property'), __('IPR / public domain policy')],
        ['Security Policy', route('security-policy'), __('Site security policy')],
        ['Admin console', route('admin.dashboard'), __('Staff console (authorized users)')],
    ];

    if ($q !== '') {
        $needle = mb_strtolower($q);
        $links = array_values(array_filter(
            $links,
            static fn (array $row): bool => str_contains(mb_strtolower($row[0].' '.$row[2]), $needle)
        ));
    }
@endphp

@section('title', $title)

@section('policy-body')
@if ($q !== '')
    <p class="alert alert-light border" role="status">{{ __('Showing links matching “:q”.', ['q' => $q]) }}</p>
@endif

@if ($links === [])
    <p>{{ __('No matching pages. Try a shorter keyword or browse the list without a search term.') }}</p>
@else
    <ul class="list-unstyled mb-0">
        @foreach ($links as [$label, $href, $hint])
            <li class="mb-3">
                <a href="{{ $href }}" class="fw-semibold link-primary">{{ $label }}</a>
                <div class="text-muted fs-13">{{ $hint }}</div>
            </li>
        @endforeach
    </ul>
@endif
@endsection
