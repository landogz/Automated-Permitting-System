@extends('layouts.velzon.auth')

@section('title', 'Sign In')
@section('panel-title', __('Sign in to continue'))
@section('panel-copy', __('Access your applicant filings or OCBO staff workspace. Credentials are protected and activity is audited.'))

@section('content')
<div class="apics-auth-form">
    <h1 class="apics-auth-card__heading">{{ __('Welcome back') }}</h1>
    <p class="apics-auth-card__lede">{{ __('Enter your email and password to access your APICS account.') }}</p>

    <form id="login-form" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" autocomplete="username" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password-input">{{ __('Password') }}</label>
            <div class="position-relative auth-pass-inputgroup">
                <input type="password" class="form-control pe-5 password-input" placeholder="{{ __('Enter password') }}" id="password-input" name="password" autocomplete="current-password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon" data-password-toggle="password-input" aria-label="{{ __('Show password') }}" aria-pressed="false">
                    <i class="ri-eye-line align-middle" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="apics-auth-row mb-4">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" value="1" id="auth-remember-check">
                <label class="form-check-label" for="auth-remember-check">{{ __('Remember me') }}</label>
            </div>
            <a href="#forgot-password-help" class="apics-auth-link" id="forgot-password-link" data-bs-toggle="modal" data-bs-target="#modal-forgot-password">{{ __('Forgot password?') }}</a>
        </div>

        <button class="btn apics-auth-submit" type="submit" id="login-submit">
            <i class="ri-login-circle-line" aria-hidden="true"></i>
            <span>{{ __('Sign in') }}</span>
        </button>
    </form>

    @if ($quickLoginEnabled && count($quickUsers))
        <div class="apics-auth-demo">
            <p class="apics-auth-demo__title">{{ __('Quick login (local demo)') }}</p>
            <p class="apics-auth-demo__hint">{{ count($quickUsers) }} {{ __('seeded office & applicant accounts — click to sign in instantly.') }}</p>
            <div class="row g-2" id="quick-login-grid">
                @foreach ($quickUsers as $demo)
                    <div class="col-12 col-sm-6">
                        <button
                            type="button"
                            class="btn w-100 text-start py-2 quick-login-btn"
                            data-email="{{ $demo['email'] }}"
                            data-redirect="{{ $demo['redirect'] }}"
                        >
                            <span class="d-block fw-semibold">{{ $demo['label'] }}</span>
                            <span class="d-block text-muted fs-12 text-truncate">{{ $demo['email'] }}</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="apics-auth-footer">
        <p class="mb-0">{{ __('Need an account?') }} <a href="{{ route('register') }}" class="apics-auth-link">{{ __('Register as applicant') }}</a></p>
        <p class="mb-0">
            <a href="{{ route('home') }}" class="apics-auth-link apics-auth-link--escape">{{ __('Back to home') }}</a>
        </p>
    </div>
</div>

<div class="modal fade" id="modal-forgot-password" tabindex="-1" aria-labelledby="modal-forgot-password-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="modal-forgot-password-label">{{ __('Forgot password') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-2" style="color: #64748b;">{{ __('For security, password resets for APICS accounts are handled by OCBO administrators.') }}</p>
                <ul class="small mb-0 ps-3" style="color: #64748b;">
                    <li class="mb-1">{{ __('Applicants: contact the Office of the City Building Official during business hours.') }}</li>
                    <li class="mb-1">{{ __('Staff: ask your system administrator to reset your account.') }}</li>
                    <li>{{ __('After you can sign in, use Change Password from your account menu.') }}</li>
                </ul>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <a href="{{ route('home') }}" class="btn apics-auth-submit" style="width: auto; min-height: 2.4rem; padding-inline: 1rem;">{{ __('Back to home') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
