<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Sign In') | APICS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="APICS — Automated Permitting, Inspection, and Compliance System for the Office of the City Building Official, City of San Fernando, Pampanga.">
    <link rel="shortcut icon" href="{{ asset('images/branding/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/branding/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/branding/csfp-seal.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="{{ asset('master/assets/js/layout.js') }}"></script>
    <link href="{{ asset('master/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    @vite(['resources/css/apics-auth.css', 'resources/js/app.ts'])
</head>
<body class="apics-auth">
<a href="#apics-auth-form" class="visually-hidden-focusable position-absolute top-0 start-0 m-3 btn btn-light z-3">{{ __('Skip to form') }}</a>

<div class="apics-auth-shell">
    <aside class="apics-auth-panel" aria-label="{{ __('Agency identity') }}">
        <img
            src="{{ asset('images/branding/csfp-seal.png') }}"
            alt=""
            class="apics-auth-panel__watermark"
            width="360"
            height="360"
            decoding="async"
            aria-hidden="true"
        >

        <div class="apics-auth-panel__brand">
            <img src="{{ asset('images/branding/csfp-seal.png') }}" alt="{{ __('City of San Fernando, Pampanga — Official Seal') }}" class="apics-auth-panel__seal" width="56" height="56" decoding="async">
            <div class="apics-auth-panel__brand-text">
                <strong>APICS</strong>
                <span>{{ __('Office of the City Building Official') }}</span>
            </div>
        </div>

        <div class="apics-auth-panel__hero">
            <div class="apics-auth-panel__copy">
                <span class="apics-auth-panel__eyebrow">
                    <i class="ri-government-line" aria-hidden="true"></i>
                    {{ __('Secure civic portal') }}
                </span>
                <h1>@yield('panel-title', __('Welcome to APICS'))</h1>
                <p>@yield('panel-copy', __('Sign in to file, track, and manage building permit applications with the City of San Fernando OCBO.'))</p>
                <ul class="apics-auth-panel__points">
                    <li>
                        <span class="apics-auth-panel__point-icon" aria-hidden="true"><i class="ri-check-line"></i></span>
                        <span>{{ __('Direct submission of building & ancillary permits') }}</span>
                    </li>
                    <li>
                        <span class="apics-auth-panel__point-icon" aria-hidden="true"><i class="ri-check-line"></i></span>
                        <span>{{ __('Real-time joint inspection scheduling & status tracking') }}</span>
                    </li>
                    <li>
                        <span class="apics-auth-panel__point-icon" aria-hidden="true"><i class="ri-check-line"></i></span>
                        <span>{{ __('Compliant with RA 11032 (Ease of Doing Business)') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="apics-auth-panel__trust">
            <strong>{{ __('Republic of the Philippines') }}</strong>
            {{ __('City Government of San Fernando, Pampanga · OCBO') }}
        </div>
    </aside>

    <main class="apics-auth-main">
        <div id="apics-auth-form" tabindex="-1" class="w-100 d-flex justify-content-center">
            @yield('content')
        </div>
        <p class="apics-auth-main__legal mb-0">&copy; <script>document.write(new Date().getFullYear())</script> APICS · CSFP OCBO</p>
    </main>
</div>

<script src="{{ asset('master/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('master/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('master/assets/js/plugins.js') }}"></script>
{{-- Password eye toggles are handled by Vite modules (utils/password-toggle) — do not load Velzon password-addon.init.js (double-bind cancels the toggle). --}}
</body>
</html>
