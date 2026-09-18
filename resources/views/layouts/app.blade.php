<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'APICS') — CSFP OCBO</title>
    <link rel="shortcut icon" href="{{ asset('images/branding/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/branding/apple-touch-icon.png') }}">
    <link rel="icon" href="{{ asset('images/branding/csfp-seal.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|fraunces:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="min-h-full bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow-lg">{{ __('Skip to content') }}</a>

    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/70" style="padding-top: env(safe-area-inset-top);">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3">
                <x-branding.logo :height="40" class="rounded-full shadow-sm transition group-hover:-translate-y-0.5" />
                <span class="min-w-0">
                    <span class="block truncate font-display text-lg font-semibold tracking-tight text-slate-900 dark:text-white">APICS</span>
                    <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ __('City of San Fernando · OCBO') }}</span>
                </span>
            </a>
            <nav class="flex flex-wrap items-center justify-end gap-1 text-sm sm:gap-2" aria-label="{{ __('Primary') }}">
                <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('Home') }}</a>
                <a href="{{ route('applications.index') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('Applications') }}</a>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('Admin') }}</a>
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-xl bg-slate-900 px-4 py-2 font-medium text-white shadow-md shadow-slate-900/10 transition hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 active:scale-[0.98] dark:bg-emerald-600">{{ __('Sign in') }}</a>
            </nav>
        </div>
    </header>

    <main
        id="main"
        tabindex="-1"
        @class([
            'w-full',
            'mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8' => ! View::hasSection('fullBleed'),
        ])
    >
        @yield('content')
    </main>

    <footer class="border-t border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-12 lg:px-8 lg:py-12">
            <div class="lg:col-span-5">
                <div class="flex items-center gap-3">
                    <x-branding.logo :height="40" class="rounded-full" />
                    <div>
                        <p class="font-display text-lg font-semibold tracking-tight">APICS</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Automated Permitting, Inspection, and Compliance System') }}</p>
                    </div>
                </div>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                    {{ __('Phase I digital workflows for the Office of the City Building Official — online intake, transparent evaluation, inspection coordination, and audit-ready records.') }}
                </p>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:col-span-7 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Platform') }}</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="text-slate-700 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('applications.index') }}" class="text-slate-700 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">{{ __('Applications') }}</a></li>
                        <li><a href="{{ route('login') }}" class="text-slate-700 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">{{ __('Sign in') }}</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('OCBO Admin') }}</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('admin.dashboard') }}" class="text-slate-700 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">{{ __('Admin console') }}</a></li>
                        <li><a href="{{ route('admin.departments') }}" class="text-slate-700 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">{{ __('Departments') }}</a></li>
                        <li><a href="{{ route('admin.forms') }}" class="text-slate-700 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">{{ __('Form definitions') }}</a></li>
                        <li><a href="{{ route('admin.audit') }}" class="text-slate-700 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-400">{{ __('Audit trail') }}</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Agency') }}</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <li>{{ __('City of San Fernando, Pampanga') }}</li>
                        <li>{{ __('Office of the City Building Official') }}</li>
                        <li>{{ __('Aligned with P.D. 1096 & RA 11032') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-200/80 px-4 py-4 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400 sm:px-6 lg:px-8">
            <p>{{ __('Republic of the Philippines · Province of Pampanga · City of San Fernando') }}</p>
            <p class="mt-1">{{ __('Office of the City Building Official · APICS Phase I') }} · &copy; {{ date('Y') }}</p>
        </div>
    </footer>
</body>
</html>
