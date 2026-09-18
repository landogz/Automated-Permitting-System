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
<body data-bs-spy="scroll" data-bs-target="#navbar-example">
<a href="#hero" class="visually-hidden-focusable position-absolute top-0 start-0 m-3 btn btn-primary z-3">{{ __('Skip to content') }}</a>

<div class="layout-wrapper landing">
    <nav class="navbar navbar-expand-lg navbar-landing fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <x-branding.logo :height="36" class="rounded-circle" />
                <span class="fw-semibold text-body">APICS</span>
            </a>

            <div class="d-flex align-items-center gap-2 ms-auto me-2 me-lg-0 order-lg-3" id="landing-auth-actions">
                {{-- Guest (default visible before JS) --}}
                <a href="{{ route('login') }}" class="btn btn-sm btn-link fw-medium text-decoration-none text-body px-1 px-sm-2" data-auth-visible="guest">{{ __('Sign in') }}</a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-soft-primary" data-auth-visible="guest">{{ __('Register') }}</a>
                <a href="{{ route('applications.index') }}" class="btn btn-sm btn-primary" data-auth-visible="guest">{{ __('Apply') }}</a>

                {{-- Applicant --}}
                <a href="{{ route('applications.index') }}" class="btn btn-sm btn-primary d-none" data-auth-visible="applicant">{{ __('My applications') }}</a>
                <button type="button" class="btn btn-sm btn-soft-danger d-none" data-auth-visible="applicant" data-landing-logout>{{ __('Sign out') }}</button>

                {{-- Office / admin --}}
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary d-none" data-auth-visible="admin">{{ __('Admin') }}</a>
                <button type="button" class="btn btn-sm btn-soft-danger d-none" data-auth-visible="admin" data-landing-logout>{{ __('Sign out') }}</button>
            </div>

            <button class="navbar-toggler py-0 fs-20 text-body order-lg-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <i class="mdi mdi-menu"></i>
            </button>

            <div class="collapse navbar-collapse order-lg-2" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto mt-2 mt-lg-0" id="navbar-example">
                    <li class="nav-item"><a class="nav-link active" href="#hero">{{ __('Home') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#trust">{{ __('Mandate') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">{{ __('Capabilities') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#showcase">{{ __('Platform') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#workflow">{{ __('Workflow') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#access">{{ __('Access') }}</a></li>
                </ul>
                <p class="d-none text-muted fs-12 mb-0 text-capitalize me-2" data-auth-visible="applicant,admin" data-auth-user-chip>
                    <span data-auth-name></span>
                    <span class="mx-1" data-auth-sep aria-hidden="true">·</span>
                    <span data-auth-role></span>
                </p>
            </div>
        </div>
    </nav>
    <div class="vertical-overlay" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent.show" style="pointer-events: none;"></div>

    @yield('content')

    <footer class="custom-footer bg-dark py-5 position-relative">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <x-branding.logo :height="40" class="rounded-circle" />
                        <div>
                            <h5 class="text-white mb-0">APICS</h5>
                            <p class="text-white-50 mb-0 fs-12">{{ __('Automated Permitting, Inspection, and Compliance System') }}</p>
                        </div>
                    </div>
                    <p class="text-white-50 mb-0">{{ __('Phase I for the Office of the City Building Official — City of San Fernando, Pampanga.') }}</p>
                </div>
                <div class="col-sm-4 col-lg-2 mb-4 mb-sm-0">
                    <h6 class="text-white text-uppercase fs-12 mb-3">{{ __('Platform') }}</h6>
                    <ul class="list-unstyled footer-list fs-14 mb-0">
                        <li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('home') }}#services">{{ __('Platform overview') }}</a></li>
                        <li><a href="{{ route('home') }}#trust">{{ __('Mandate') }}</a></li>
                        <li data-auth-visible="guest"><a href="{{ route('applications.index') }}">{{ __('Apply') }}</a></li>
                        <li data-auth-visible="guest"><a href="{{ route('login') }}">{{ __('Sign in') }}</a></li>
                        <li data-auth-visible="guest"><a href="{{ route('register') }}">{{ __('Register') }}</a></li>
                        <li class="d-none" data-auth-visible="applicant"><a href="{{ route('applications.index') }}">{{ __('My applications') }}</a></li>
                        <li class="d-none" data-auth-visible="admin"><a href="{{ route('admin.dashboard') }}">{{ __('Admin console') }}</a></li>
                    </ul>
                </div>
                <div class="col-sm-4 col-lg-2 mb-4 mb-sm-0">
                    <h6 class="text-white text-uppercase fs-12 mb-3">{{ __('Admin') }}</h6>
                    <ul class="list-unstyled footer-list fs-14 mb-0">
                        <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li><a href="{{ route('admin.departments') }}">{{ __('Departments') }}</a></li>
                        <li><a href="{{ route('admin.forms') }}">{{ __('Forms') }}</a></li>
                        <li><a href="{{ route('admin.audit') }}">{{ __('Audit') }}</a></li>
                    </ul>
                </div>
                <div class="col-sm-4 col-lg-3">
                    <h6 class="text-white text-uppercase fs-12 mb-3">{{ __('Agency') }}</h6>
                    <p class="text-white-50 fs-14 mb-2">{{ __('Republic of the Philippines') }}<br>{{ __('Province of Pampanga') }}<br>{{ __('City of San Fernando · OCBO') }}</p>
                    <p class="text-white-50 fs-12 mb-0">{{ __('APICS Phase I · TOR-aligned delivery for CGSFP OCBO') }}<br>{{ __('Acceptance documentation in progress · BAC / ICT handover evidence') }}</p>
                </div>
            </div>
            <div class="row text-center text-white-50 mt-4 pt-4 border-top border-secondary">
                <div class="col-12">
                    <p class="mb-0">&copy; <script>document.write(new Date().getFullYear())</script> APICS · CSFP OCBO · {{ __('Phase I') }}</p>
                </div>
            </div>
        </div>
    </footer>

    <button onclick="topFunction()" class="btn btn-danger btn-icon landing-back-top" id="back-to-top" type="button" aria-label="{{ __('Back to top') }}">
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
@vite(['resources/js/app.ts'])
</body>
</html>
