@extends('layouts.velzon.auth')

@section('title', 'Sign Up')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 col-xl-5">
        <div class="card mt-4 card-bg-fill">
            <div class="card-body p-4">
                <div class="text-center mt-2">
                    <h5 class="text-primary">Create Applicant Account</h5>
                    <p class="text-muted">Register for APICS. An OCBO admin must approve before you can sign in.</p>
                </div>
                <div class="p-2 mt-4">
                    <form id="register-form" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" autocomplete="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" autocomplete="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Mobile phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="09XXXXXXXXX" autocomplete="tel">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                            <div class="position-relative auth-pass-inputgroup">
                                <input type="password" class="form-control pe-5 password-input" placeholder="Enter password" id="password" name="password" autocomplete="new-password" required>
                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon" aria-label="Show password">
                                    <i class="ri-eye-fill align-middle"></i>
                                </button>
                            </div>
                            <p class="text-muted fs-12 mb-0 mt-1">Min. 8 characters with upper, lower, number, and symbol.</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Confirm password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password" required>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-success w-100" type="submit" id="register-submit">Sign Up</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Sign in</a></p>
            <p class="mt-2 mb-0"><a href="{{ route('home') }}" class="fw-semibold text-primary text-decoration-underline">Back to home</a></p>
        </div>
    </div>
</div>
@endsection
