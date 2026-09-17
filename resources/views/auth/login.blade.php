@extends('layouts.velzon.auth')

@section('title', 'Sign In')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8 col-xl-7">
        <div class="card mt-4 card-bg-fill">
            <div class="card-body p-4">
                <div class="text-center mt-2">
                    <h5 class="text-primary">Welcome Back!</h5>
                    <p class="text-muted">Sign in to continue to APICS.</p>
                </div>
                <div class="p-2 mt-4">
                    <form id="login-form" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" autocomplete="username" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password-input">Password</label>
                            <div class="position-relative auth-pass-inputgroup mb-3">
                                <input type="password" class="form-control pe-5 password-input" placeholder="Enter password" id="password-input" name="password" autocomplete="current-password" required>
                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon" aria-label="Show password">
                                    <i class="ri-eye-fill align-middle"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="auth-remember-check">
                            <label class="form-check-label" for="auth-remember-check">Remember me</label>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-success w-100" type="submit" id="login-submit">Sign In</button>
                        </div>
                    </form>

                    @if ($quickLoginEnabled && count($quickUsers))
                        <div class="mt-4 pt-3 border-top">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-2">Quick login (local demo)</p>
                            <p class="text-muted fs-12 mb-3">{{ count($quickUsers) }} seeded office &amp; applicant accounts — click to sign in instantly.</p>
                            <div class="row g-2" id="quick-login-grid">
                                @foreach ($quickUsers as $demo)
                                    <div class="col-12 col-sm-6">
                                        <button
                                            type="button"
                                            class="btn btn-soft-primary w-100 text-start py-2 quick-login-btn"
                                            data-email="{{ $demo['email'] }}"
                                            data-password="{{ $demo['password'] }}"
                                            data-redirect="{{ $demo['redirect'] }}"
                                        >
                                            <span class="d-block fw-semibold">{{ $demo['label'] }}</span>
                                            <span class="d-block text-muted fs-12 text-truncate">{{ $demo['email'] }}</span>
                                            <span class="d-block text-muted fs-11 font-monospace">{{ $demo['password'] }}</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p class="mb-0">Need an account? <a href="{{ route('register') }}" class="fw-semibold text-primary text-decoration-underline">Register as applicant</a></p>
            <p class="mt-2 mb-0"><a href="{{ route('home') }}" class="fw-semibold text-primary text-decoration-underline">Back to home</a></p>
        </div>
    </div>
</div>
@endsection
