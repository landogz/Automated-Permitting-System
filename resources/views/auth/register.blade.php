@extends('layouts.velzon.auth')

@section('title', 'Sign Up')
@section('panel-title', __('Create your applicant account'))
@section('panel-copy', __('Register to draft and track building permit applications. An OCBO administrator must approve your account before you can sign in.'))

@section('content')
<div class="apics-auth-form apics-auth-form--wide">
    <h1 class="apics-auth-card__heading">{{ __('Create applicant account') }}</h1>
    <p class="apics-auth-card__lede">{{ __('Provide accurate contact details. You will receive an email when your registration is approved or declined.') }}</p>

    <form id="register-form" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Full name') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="{{ __('e.g. Juan Dela Cruz') }}" autocomplete="name" required>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="email" class="form-label">{{ __('Email address') }} <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" autocomplete="email" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">{{ __('Mobile phone') }}</label>
                <div class="apics-auth-phone">
                    <span class="apics-auth-phone__prefix" aria-hidden="true">09</span>
                    <input type="tel" class="form-control" id="phone" name="phone" inputmode="numeric" maxlength="9" placeholder="XXXXXXXXX" autocomplete="tel-national" aria-describedby="phone-help">
                </div>
                <div id="phone-help" class="form-text">{{ __('Philippine mobile — enter the 9 digits after 09.') }}</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label" for="password">{{ __('Password') }} <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password" class="form-control pe-5 password-input" placeholder="{{ __('Create a strong password') }}" id="password" name="password" autocomplete="new-password" required>
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon" data-password-toggle="password" aria-label="{{ __('Show password') }}" aria-pressed="false">
                        <i class="ri-eye-line align-middle" aria-hidden="true"></i>
                    </button>
                </div>
                <p class="form-text mb-0">{{ __('Min. 8 characters with upper, lower, number, and symbol.') }}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">{{ __('Confirm password') }} <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password" class="form-control pe-5 password-input" id="password_confirmation" name="password_confirmation" placeholder="{{ __('Re-enter password') }}" autocomplete="new-password" required>
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-confirm-addon" aria-label="{{ __('Show password') }}" data-password-toggle="password_confirmation" aria-pressed="false">
                        <i class="ri-eye-line align-middle" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <button class="btn apics-auth-submit mt-1" type="submit" id="register-submit">
            <i class="ri-user-add-line" aria-hidden="true"></i>
            <span>{{ __('Create account') }}</span>
        </button>
    </form>

    <div class="apics-auth-footer">
        <p class="mb-0">{{ __('Already have an account?') }} <a href="{{ route('login') }}" class="apics-auth-link">{{ __('Sign in') }}</a></p>
        <p class="mb-0">
            <a href="{{ route('home') }}" class="apics-auth-link apics-auth-link--escape">{{ __('Back to home') }}</a>
        </p>
    </div>
</div>
@endsection
