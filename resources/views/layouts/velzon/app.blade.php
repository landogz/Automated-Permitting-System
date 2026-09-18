<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'APICS') | CSFP OCBO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="Automated Permitting, Inspection, and Compliance System" name="description" />
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
    @stack('styles')
</head>
<body>
<div id="layout-wrapper">
    @include('layouts.velzon.partials.topbar')
    @include('layouts.velzon.partials.sidebar')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent gap-2 flex-wrap">
                            <h4 class="mb-sm-0">@yield('page-title')</h4>
                            <div class="page-title-right d-flex flex-wrap align-items-center gap-2 ms-sm-auto">
                                @hasSection('page-actions')
                                    <div class="apics-page-actions d-flex flex-wrap align-items-center gap-1">
                                        @yield('page-actions')
                                    </div>
                                @endif
                                <ol class="breadcrumb m-0">
                                    @yield('breadcrumb')
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>document.write(new Date().getFullYear())</script> © APICS — City of San Fernando, Pampanga
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Office of the City Building Official
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<div class="vertical-overlay"></div>

@include('components.account.profile-modals')

<script src="{{ asset('master/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('master/assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('master/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('master/assets/js/plugins.js') }}"></script>
<script src="{{ asset('master/assets/js/app.js') }}"></script>
@vite(['resources/js/app.ts'])
@stack('scripts')
</body>
</html>
