@extends('layouts.velzon.policy')

@section('title', $title)

@section('policy-body')
@php
    $isTl = $locale === 'tl';
    $ui = $isTl ? [
        'switch' => 'Bagpalit sa English',
        'toc' => 'Nilalaman',
        'privileges' => 'Mga pribilehiyo ng sistema',
        'roles' => 'Mga tungkulin (roles) at pribilehiyo',
        'applicant' => 'Portal ng aplikante',
        'screens' => 'Mga screen / module',
        'functions' => 'Mga function na saklaw',
        'code' => 'Code ng pribilehiyo',
        'none' => 'Wala (portal ng aplikante lamang)',
        'matrix_note' => 'Ang talahanayan ay sumasalamin sa RolesAndPermissionsSeeder ng APICS Phase I.',
        'back' => 'Bumalik sa itaas',
        'flow' => 'Flowchart',
    ] : [
        'switch' => 'Switch to Tagalog',
        'toc' => 'On this page',
        'privileges' => 'System privileges',
        'roles' => 'Roles and assigned privileges',
        'applicant' => 'Applicant portal',
        'screens' => 'Screens / modules',
        'functions' => 'Functions covered',
        'code' => 'Privilege code',
        'none' => 'None (applicant portal only)',
        'matrix_note' => 'This matrix mirrors the APICS Phase I RolesAndPermissionsSeeder.',
        'back' => 'Back to top',
        'flow' => 'Flowchart',
    ];
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <p class="text-muted mb-0 fs-13">
        {{ $isTl ? 'Wika' : 'Language' }}:
        <strong>{{ $isTl ? 'Tagalog' : 'English' }}</strong>
    </p>
    <a
        href="{{ route('documentation.privileges', ['locale' => $altLocale]) }}"
        class="btn btn-sm btn-soft-primary"
        hreflang="{{ $altLocale }}"
        lang="{{ $altLocale }}"
    >{{ $ui['switch'] }}</a>
</div>

<p class="lead fs-15">{{ $intro['lede'] }}</p>
<p class="alert alert-light border fs-13 mb-4" role="note">{{ $intro['note'] }}</p>

@include('public.documentation.partials.flowchart')

<nav class="mb-4" aria-label="{{ $ui['toc'] }}">
    <p class="fw-semibold mb-2">{{ $ui['toc'] }}</p>
    <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0">
        <li><a class="badge bg-primary-subtle text-primary text-decoration-none" href="#applicant">{{ $ui['applicant'] }}</a></li>
        <li><a class="badge bg-primary-subtle text-primary text-decoration-none" href="#privileges">{{ $ui['privileges'] }}</a></li>
        <li><a class="badge bg-primary-subtle text-primary text-decoration-none" href="#roles">{{ $ui['roles'] }}</a></li>
    </ul>
</nav>

<section id="applicant" class="mb-5">
    <h2 class="h4 fw-semibold mb-3">{{ $applicant['title'] }}</h2>
    <p class="text-muted">{{ $applicant['summary'] }}</p>
    <div class="card border shadow-none">
        <div class="card-body">
            <h3 class="h6 text-uppercase text-muted mb-3">{{ $ui['functions'] }}</h3>
            <ul class="mb-0">
                @foreach ($applicant['functions'] as $fn)
                    <li class="mb-1">{{ $fn }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section id="privileges" class="mb-5">
    <h2 class="h4 fw-semibold mb-3">{{ $ui['privileges'] }}</h2>
    <div class="vstack gap-3">
        @foreach ($privileges as $code => $row)
            @php
                $copy = $row[$locale] ?? $row['en'];
                $screens = $row['screens'] ?? [];
            @endphp
            <article class="card border shadow-none apics-privilege-card" id="priv-{{ str_replace('.', '-', $code) }}">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                        <h3 class="h5 fw-semibold mb-0">{{ $copy['title'] }}</h3>
                        <code class="fs-12 text-body-secondary">{{ $code }}</code>
                    </div>
                    <p class="text-muted mb-3">{{ $copy['summary'] }}</p>
                    @if ($screens !== [])
                        <p class="fs-13 mb-2">
                            <span class="fw-semibold">{{ $ui['screens'] }}:</span>
                            {{ implode(' · ', $screens) }}
                        </p>
                    @endif
                    <h4 class="h6 text-uppercase text-muted mb-2">{{ $ui['functions'] }}</h4>
                    <ul class="mb-0">
                        @foreach ($copy['functions'] as $fn)
                            <li class="mb-1">{{ $fn }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section id="roles" class="mb-4">
    <h2 class="h4 fw-semibold mb-2">{{ $ui['roles'] }}</h2>
    <p class="text-muted fs-13 mb-3">{{ $ui['matrix_note'] }}</p>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">{{ $isTl ? 'Tungkulin' : 'Role' }}</th>
                    <th scope="col">{{ $isTl ? 'Buod' : 'Summary' }}</th>
                    <th scope="col">{{ $ui['code'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $roleKey => $role)
                    @php $roleCopy = $role[$locale] ?? $role['en']; @endphp
                    <tr>
                        <td class="text-nowrap fw-semibold">{{ $roleCopy['title'] }}</td>
                        <td>{{ $roleCopy['summary'] }}</td>
                        <td>
                            @if (($role['permissions'] ?? []) === [])
                                <span class="text-muted fs-13">{{ $ui['none'] }}</span>
                            @else
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($role['permissions'] as $perm)
                                        <a href="#priv-{{ str_replace('.', '-', $perm) }}" class="badge bg-secondary-subtle text-secondary text-decoration-none font-monospace">{{ $perm }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<p class="mb-0">
    <a href="#main" class="link-primary fs-13">{{ $ui['back'] }}</a>
</p>
@endsection
