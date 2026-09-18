<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'APICS') | CSFP OCBO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('Automated Permitting, Inspection, and Compliance System for the Office of the City Building Official, City of San Fernando, Pampanga.') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('images/branding/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/branding/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/branding/csfp-seal.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="{{ asset('master/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('master/assets/js/layout.js') }}"></script>
    <link href="{{ asset('master/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body data-bs-spy="scroll" data-bs-target="#navbar-example" class="gwt-public">
<div class="skip-links">
    <a href="#main" class="skip-link">{{ __('Skip to content') }}</a>
    <a href="#standard-footer" class="skip-link">{{ __('Skip to footer') }}</a>
</div>

<div class="layout-wrapper landing">
    <header class="gwt-site-header">
        <x-gov.gwt-topbar />
        <x-gov.gwt-masthead />

        <nav class="navbar navbar-expand-lg navbar-landing" id="navbar" aria-label="{{ __('APICS primary') }}">
            <div class="container gwt-product-nav">
                {{-- Masthead already carries agency seal + name; avoid duplicate brand on small screens --}}
                <a class="navbar-brand d-none" href="{{ route('home') }}" aria-hidden="true" tabindex="-1">
                    <span class="fw-semibold text-body">APICS</span>
                </a>

                <div class="d-flex align-items-center gap-1 gap-sm-2 ms-auto me-1 me-lg-0 order-lg-3 gwt-product-nav__auth" id="landing-auth-actions">
                    <a href="{{ route('login') }}" class="btn btn-sm btn-link fw-medium text-decoration-none text-body px-1 px-sm-2" data-auth-visible="guest">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-soft-primary d-none d-sm-inline-flex" data-auth-visible="guest">{{ __('Register') }}</a>
                    <a href="{{ route('applications.index') }}" class="btn btn-sm btn-primary" data-auth-visible="guest">{{ __('Apply') }}</a>
                    <a href="{{ route('applications.index') }}" class="btn btn-sm btn-primary d-none" data-auth-visible="applicant">{{ __('My applications') }}</a>
                    <button type="button" class="btn btn-sm btn-soft-danger d-none" data-auth-visible="applicant" data-landing-logout>{{ __('Sign out') }}</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary d-none" data-auth-visible="admin">{{ __('Admin') }}</a>
                    <button type="button" class="btn btn-sm btn-soft-danger d-none" data-auth-visible="admin" data-landing-logout>{{ __('Sign out') }}</button>
                </div>

                <button class="navbar-toggler py-0 fs-20 text-body order-lg-4 gwt-product-nav__toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <i class="mdi mdi-menu"></i>
                </button>

                <div class="collapse navbar-collapse order-lg-2" id="navbarSupportedContent">
                    <ul class="navbar-nav mx-auto mt-2 mt-lg-0" id="navbar-example">
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#hero">{{ __('Home') }}</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#trust">{{ __('Mandate') }}</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#services">{{ __('Capabilities') }}</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('citizens-charter') }}">{{ __('Charter') }}</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#workflow">{{ __('Workflow') }}</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#access">{{ __('Access') }}</a></li>
                        <li class="nav-item d-sm-none" data-auth-visible="guest">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    </ul>
                    <p class="d-none text-muted fs-12 mb-0 text-capitalize me-2" data-auth-visible="applicant,admin" data-auth-user-chip>
                        <span data-auth-name></span>
                        <span class="mx-1" data-auth-sep aria-hidden="true">·</span>
                        <span data-auth-role></span>
                    </p>
                </div>
            </div>
        </nav>
    </header>
    <div class="vertical-overlay" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent.show" style="pointer-events: none;"></div>

    <main id="main" class="gwt-main" tabindex="-1">
        @yield('content')
    </main>

    <x-gov.gwt-agency-footer />
    <x-gov.gwt-standard-footer />

    <button onclick="topFunction()" class="btn btn-icon landing-back-top" id="back-to-top" type="button" aria-label="{{ __('Back to top') }}">
        <i class="ri-arrow-up-line"></i>
    </button>
</div>

<script src="{{ asset('master/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('master/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('master/assets/js/plugins.js') }}"></script>
<script src="{{ asset('master/assets/libs/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('master/assets/js/pages/landing.init.js') }}"></script>
<script>
(function () {
    var yearEl = document.getElementById('gwt-year');
    if (yearEl) yearEl.textContent = String(new Date().getFullYear());

    var clock = document.getElementById('gwt-phst-clock');
    if (!clock) return;

    var narrowMq = window.matchMedia('(max-width: 575.98px)');

    function buildFmt() {
        return new Intl.DateTimeFormat('en-PH', narrowMq.matches
            ? {
                timeZone: 'Asia/Manila',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            }
            : {
                timeZone: 'Asia/Manila',
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
    }

    var fmt = buildFmt();
    if (typeof narrowMq.addEventListener === 'function') {
        narrowMq.addEventListener('change', function () { fmt = buildFmt(); tick(); });
    }

    /** Offset ms = server epoch − local Date.now(); synced when WorldTimeAPI is reachable. */
    var manilaOffsetMs = 0;
    var synced = false;

    function nowInManila() {
        return new Date(Date.now() + manilaOffsetMs);
    }

    function tick() {
        var now = nowInManila();
        clock.textContent = fmt.format(now);
        clock.setAttribute('datetime', now.toISOString());
        clock.setAttribute(
            'title',
            synced
                ? 'Philippine Standard Time (synced)'
                : 'Philippine Standard Time (device clock · Asia/Manila)'
        );
    }

    function syncPhst() {
        fetch('https://worldtimeapi.org/api/timezone/Asia/Manila', {
            method: 'GET',
            cache: 'no-store',
            signal: AbortSignal.timeout ? AbortSignal.timeout(4000) : undefined
        })
            .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
            .then(function (data) {
                if (!data || !data.unixtime) return;
                var serverMs = Number(data.unixtime) * 1000;
                manilaOffsetMs = serverMs - Date.now();
                synced = true;
                tick();
            })
            .catch(function () {
                /* Fallback: format visitor clock in Asia/Manila (RA 10535 display still useful). */
                synced = false;
            });
    }

    tick();
    setInterval(tick, 1000);
    syncPhst();
    setInterval(syncPhst, 15 * 60 * 1000);
})();
</script>
@vite(['resources/js/app.ts'])
</body>
</html>
