<!doctype html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-layout="vertical"
    data-topbar="light"
    data-sidebar="dark"
    data-sidebar-size="lg"
    data-sidebar-image="none"
    data-preloader="disable"
    data-theme="default"
    data-theme-colors="default"
>
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'APICS') — CSFP OCBO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="{{ __('Automated Permitting, Inspection, and Compliance System for the Office of the City Building Official, City of San Fernando, Pampanga.') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('images/branding/apics-logo.png') }}">

    <script src="{{ asset('master/assets/js/layout.js') }}"></script>
    <link href="{{ asset('master/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('master/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    @stack('styles')
</head>
<body data-bs-spy="scroll" data-bs-target="#navbar-example">
    <a href="#main" class="visually-hidden-focusable position-absolute top-0 start-0 m-3 btn btn-primary z-3">{{ __('Skip to content') }}</a>

    <div class="layout-wrapper landing">
        @yield('content')

        <button type="button" onclick="topFunction()" class="btn btn-danger btn-icon landing-back-top" id="back-to-top" aria-label="{{ __('Back to top') }}">
            <i class="ri-arrow-up-line"></i>
        </button>
    </div>

    <script src="{{ asset('master/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('master/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('master/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('master/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('master/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('master/assets/js/pages/apics-landing.init.js') }}"></script>
    @stack('scripts')
</body>
</html>
