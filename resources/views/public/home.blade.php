@extends('layouts.velzon.landing')

@section('title', 'Home')

@section('content')
<div data-landing-home>
{{-- Hero --}}
<section class="section pb-0 hero-section position-relative" id="hero">
    <div class="bg-overlay bg-overlay-pattern" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row align-items-center justify-content-between g-4 mt-lg-5 pt-5">
            <div class="col-lg-6">
                <div class="mb-4 landing-hero-copy">
                    <span class="landing-phase-chip mb-3">{{ __('CSFP · OCBO · Phase I') }}</span>

                    <div class="d-flex align-items-center gap-3 mb-3 landing-hero-brand">
                        <x-branding.logo :height="80" class="rounded-circle border border-2 border-white shadow-sm" />
                        <div>
                            <p class="landing-hero-brand-name" id="hero-heading">APICS</p>
                            <p class="text-muted mb-0 fs-13">{{ __('City of San Fernando, Pampanga · OCBO') }}</p>
                        </div>
                    </div>

                    <h1 class="landing-hero-title">{{ __('Paperless building permits for the City of San Fernando') }}</h1>
                    <p class="landing-hero-lede">{{ __('Automated Permitting, Inspection, and Compliance System — apply online, track status, and support transparent evaluation, inspection, and audit-ready compliance for the Office of the City Building Official.') }}</p>

                    <div class="d-flex flex-wrap gap-2" data-auth-visible="guest">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">{{ __('Register to apply') }} <i class="ri-arrow-right-line align-middle ms-1" aria-hidden="true"></i></a>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">{{ __('Sign in') }}</a>
                    </div>

                    <div class="d-flex flex-wrap gap-2 d-none" data-auth-visible="applicant">
                        <a href="{{ route('applications.index') }}" class="btn btn-primary btn-lg">{{ __('Go to my applications') }} <i class="ri-arrow-right-line align-middle ms-1" aria-hidden="true"></i></a>
                        <a href="#workflow" class="btn btn-outline-secondary btn-lg">{{ __('How filing works') }}</a>
                    </div>

                    <div class="d-flex flex-wrap gap-2 d-none" data-auth-visible="admin">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg">{{ __('Open admin console') }} <i class="ri-arrow-right-line align-middle ms-1" aria-hidden="true"></i></a>
                        <a href="{{ route('admin.evaluation-queue') }}" class="btn btn-outline-primary btn-lg">{{ __('Evaluation queue') }}</a>
                    </div>

                    <p class="text-muted fs-13 mt-3 mb-0">{{ __('For applicants, evaluators, and OCBO administrators — aligned with LGU QMS, P.D. 1096, and RA 11032.') }}</p>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card landing-product-chrome landing-hero-visual mb-0">
                    <div class="card-header align-items-center d-flex flex-wrap gap-2 py-3">
                        <h2 class="card-title mb-0 flex-grow-1 fs-15">{{ __('Applications') }}</h2>
                        <div class="landing-live-stats" aria-label="{{ __('Sample live counts') }}">
                            <span class="landing-live-stat">
                                <strong data-count-to="apps" data-count-target="3">3</strong>
                                {{ __('Apps') }}
                            </span>
                            <span class="landing-live-stat">
                                <strong data-count-to="eval" data-count-target="1">1</strong>
                                {{ __('Under eval') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <ul class="list-unstyled landing-app-queue mb-0" aria-label="{{ __('Sample applications') }}">
                            <li class="landing-app-queue__item landing-status-pulse">
                                <div class="landing-app-queue__main">
                                    <span class="landing-app-queue__no">BP-2026-000142</span>
                                    <span class="landing-app-queue__meta">{{ __('Two-storey residential · San Juan') }}</span>
                                </div>
                                <x-status.badge status="under_evaluation" />
                            </li>
                            <li class="landing-app-queue__item landing-status-pulse">
                                <div class="landing-app-queue__main">
                                    <span class="landing-app-queue__no">BP-2026-000138</span>
                                    <span class="landing-app-queue__meta">{{ __('Commercial renovation · Dolores') }}</span>
                                </div>
                                <x-status.badge status="for_inspection" />
                            </li>
                            <li class="landing-app-queue__item landing-status-pulse">
                                <div class="landing-app-queue__main">
                                    <span class="landing-app-queue__no">BP-2026-000121</span>
                                    <span class="landing-app-queue__meta">{{ __('Warehouse expansion · Sindalan') }}</span>
                                </div>
                                <x-status.badge status="for_payment" />
                            </li>
                        </ul>
                        <p class="text-muted fs-11 text-uppercase mb-2 mt-3">{{ __('Status pipeline') }}</p>
                        <div class="d-flex flex-wrap gap-1 landing-pipeline" role="list" aria-label="{{ __('Permit status pipeline') }}">
                            @foreach (['draft', 'submitted', 'under_evaluation', 'for_inspection', 'for_payment', 'for_releasing', 'released'] as $step)
                                <x-status.badge :status="$step" role="listitem" />
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="position-absolute start-0 end-0 bottom-0 hero-shape-svg" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1440 120">
            <path fill="var(--vz-body-bg)" d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40 L 1440,140 L 0,140 Z"></path>
        </svg>
    </div>
</section>

{{-- Compliance / mandate --}}
<section class="section landing-section-fade landing-surface-white" id="trust">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5 landing-reveal">
                <span class="landing-eyebrow">{{ __('Statutory compliance') }}</span>
                <h2 class="fw-semibold mb-3">{{ __('Built on LGU mandate') }}</h2>
                <p class="text-muted mb-0">{{ __('APICS Phase I is delivered for the City Government of San Fernando, Pampanga (CGSFP), Office of the City Building Official. The capabilities below reflect the project charter and governing issuances.') }}</p>
            </div>
        </div>
        <div class="row g-3 align-items-stretch">
            @foreach ([
                ['P.D. 1096', __('National Building Code alignment for permitting practices.')],
                ['RA 11032', __('Ease of Doing Business — transparent, time-tracked processing.')],
                ['LGU QMS', __('Unified forms and evaluation slips used by OCBO offices.')],
                ['API-first', __('Sanctum /api/v1 for web SPA and future mobile clients.')],
            ] as [$title, $copy])
                <div class="col-12 col-sm-6 col-lg-3 landing-reveal">
                    <article class="landing-mandate-card landing-lift">
                        <h3 class="landing-mandate-card__title">{{ $title }}</h3>
                        <p class="landing-mandate-card__copy">{{ $copy }}</p>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="row justify-content-center mt-4 landing-reveal">
            <div class="col-lg-10">
                <aside class="gwt-transparency-band" aria-label="{{ __('Philippine Transparency Seal') }}">
                    <a href="{{ route('transparency') }}" class="gwt-transparency-band__seal">
                        <img
                            src="{{ asset('images/branding/philippine-transparency-seal.svg') }}"
                            alt="{{ __('Philippine Transparency Seal — view mandated disclosures') }}"
                            width="100"
                            height="100"
                            decoding="async"
                        >
                    </a>
                    <div>
                        <h3 class="h6 fw-semibold mb-1">{{ __('Philippine Transparency Seal') }}</h3>
                        <p class="text-muted fs-14 mb-2">{{ __('Mandated disclosures under the General Appropriations Act Transparency Seal provision — agency mandate, officials, budgets, programs, and procurement information.') }}</p>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <a href="{{ route('transparency') }}" class="link-primary fw-semibold fs-13">{{ __('View Transparency page') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                            <a href="https://www.foi.gov.ph" class="gwt-foi-inline fw-semibold fs-13" rel="noopener noreferrer" target="_blank">{{ __('FOI Portal') }} <i class="ri-external-link-line align-bottom" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>

{{-- Capabilities --}}
<section class="section landing-section-fade landing-surface-soft bg-light" id="services">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5 landing-reveal">
                    <span class="landing-eyebrow">{{ __('End-to-end workflow') }}</span>
                    <h2 class="mb-3 fw-semibold">{{ __('Built for OCBO permitting workflows') }}</h2>
                    <p class="text-muted">{{ __('Phase I modules already in the product — not a marketing checklist. Each link opens a live area of APICS.') }}</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            @foreach ([
                ['ri-file-list-3-line', 'Online application & intake', 'Create drafts, upload supporting documents, and submit unified QMS forms for building permit intake.', route('applications.index'), 'Open applications'],
                ['ri-survey-line', 'Form definitions', 'Administer dynamic QMS form schemas used across intake and evaluation — versioned and API-driven.', route('admin.forms'), 'Manage forms'],
                ['ri-building-line', 'Departments & routing', 'Maintain office master data for transparent routing across evaluation and inspection offices.', route('admin.departments'), 'Manage departments'],
                ['ri-checkbox-circle-line', 'Permit classifier', 'Support Simple, Complex, and Highly Technical classification paths that drive evaluation timelines.', route('admin.classification-rules'), 'Classifier rules'],
                ['ri-search-eye-line', 'Inspection & compliance', 'Coordinate inspection workflows and compliance notices so applicants and offices share the same status trail.', route('admin.inspections'), 'Inspections'],
                ['ri-history-line', 'Audit trail', 'Review auth and mutation events with actor, IP, and safe metadata — required for ICT oversight.', route('admin.audit'), 'View audit logs'],
            ] as [$icon, $title, $copy, $href, $label])
                <div class="col-12 col-md-6 col-lg-4 landing-reveal">
                    <div class="card card-animate h-100 mb-0 landing-lift">
                        <div class="card-body p-4">
                            <div class="landing-feature-icon mb-3" aria-hidden="true">
                                <i class="{{ $icon }}"></i>
                            </div>
                            <h3 class="fs-16">{{ __($title) }}</h3>
                            <p class="text-muted mb-3">{{ __($copy) }}</p>
                            <a href="{{ $href }}" class="fs-13 fw-medium link-primary">{{ __($label) }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Showcase --}}
<section class="section landing-section-fade landing-surface-white" id="showcase">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5 landing-reveal">
                <span class="landing-eyebrow">{{ __('Workspace preview') }}</span>
                <h2 class="fw-semibold mb-3">{{ __('What you work with after sign-in') }}</h2>
                <p class="text-muted mb-0">{{ __('Applicant tracking and the OCBO admin console share the same API-first foundation — Axios-driven lists, no full-page reloads on day-to-day actions.') }}</p>
            </div>
        </div>
        <div class="landing-reveal landing-browser-wrap">
                <div class="landing-browser">
                    <div class="landing-browser__chrome">
                        <div class="landing-browser__dots" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="landing-browser__url" title="apics.cityofsanfernando.gov.ph/master-data">
                            apics.cityofsanfernando.gov.ph/master-data
                        </div>
                        <div class="landing-browser__actions">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary">{{ __('Open console') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <div class="landing-browser__body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <x-branding.logo :height="28" class="rounded-circle" />
                            <h3 class="mb-0 flex-grow-1 fs-15 fw-semibold">APICS · {{ __('Admin console') }}</h3>
                        </div>
                        <div class="row g-4">
                            <div class="col-12 col-lg-4">
                                <h4 class="text-muted text-uppercase fs-12">{{ __('Quick links') }}</h4>
                                <div class="list-group list-group-flush border rounded">
                                    <a href="{{ route('admin.departments') }}" class="list-group-item list-group-item-action">{{ __('Departments') }}</a>
                                    <a href="{{ route('admin.forms') }}" class="list-group-item list-group-item-action">{{ __('Form definitions') }}</a>
                                    <a href="{{ route('admin.evaluation-queue') }}" class="list-group-item list-group-item-action">{{ __('Evaluation queue') }}</a>
                                    <a href="{{ route('admin.audit') }}" class="list-group-item list-group-item-action">{{ __('Audit trail') }}</a>
                                </div>
                            </div>
                            <div class="col-12 col-lg-8">
                                <h4 class="fs-16 mb-1">{{ __('Office master data') }}</h4>
                                <p class="text-muted fs-12 mb-3">{{ __('Departments used in routing — sample of live admin list chrome') }}</p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-nowrap align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">{{ __('Code') }}</th>
                                                <th scope="col">{{ __('Office') }}</th>
                                                <th scope="col">{{ __('Active') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-medium">OCBO</td>
                                                <td>{{ __('Office of the City Building Official') }}</td>
                                                <td><span class="badge bg-success-subtle text-success">{{ __('Yes') }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-medium">ENG</td>
                                                <td>{{ __('City Engineering Office') }}</td>
                                                <td><span class="badge bg-success-subtle text-success">{{ __('Yes') }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-medium">CTO</td>
                                                <td>{{ __('City Treasurer’s Office') }}</td>
                                                <td><span class="badge bg-success-subtle text-success">{{ __('Yes') }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-medium">CICTO</td>
                                                <td>{{ __('City Information & Communications Technology Office') }}</td>
                                                <td><span class="badge bg-success-subtle text-success">{{ __('Yes') }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-muted fs-12 mt-3 mb-0">{{ __('Illustrative admin chrome — live rows load after staff authentication. Application status samples appear only in the hero preview above.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</section>

{{-- Workflow: intake → release --}}
<section class="section landing-section-fade landing-surface-soft bg-light" id="workflow">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-5 landing-reveal">
                <span class="landing-eyebrow">{{ __('Process path') }}</span>
                <h2 class="fw-semibold mb-3">{{ __('From online intake to release') }}</h2>
                <p class="text-muted mb-4">{{ __('APICS follows the Phase I OCBO process: apply, complete intake, classify, evaluate and inspect, issue Order of Payment, then release or issue compliance notices — with a full audit trail throughout.') }}</p>
                <a href="{{ route('applications.index') }}" class="btn btn-soft-primary">{{ __('Go to applications') }} <i class="ri-arrow-right-line align-middle" aria-hidden="true"></i></a>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    @foreach ([
                        [__('Intake completeness'), __('QMS forms & uploads')],
                        [__('Classifier'), __('Simple / Complex / HT')],
                        [__('Evaluation & routing'), __('Office time tracking')],
                        [__('Inspection'), __('Field coordination')],
                        [__('Order of Payment'), __('Basic G-02 flow')],
                        [__('Release & archive'), __('Records-ready trail')],
                    ] as [$label, $hint])
                        <div class="col-12 col-sm-6 landing-reveal">
                            <div class="card card-animate mb-0 h-100 landing-lift">
                                <div class="card-body">
                                    <h3 class="fs-15 mb-1">{{ $label }}</h3>
                                    <p class="text-muted mb-0 fs-13">{{ $hint }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Four steps: Register → Sign in → Track → Mandate --}}
<section class="section landing-section-fade landing-surface-white" id="how-it-works">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8 text-center landing-reveal">
                <span class="landing-eyebrow">{{ __('Getting started') }}</span>
                <h2 class="fw-semibold mb-2">{{ __('Four steps to get started') }}</h2>
                <p class="text-muted mb-0">{{ __('The applicant journey: register, sign in after approval, track your permit pipeline, and rely on a statute-aligned platform.') }}</p>
            </div>
        </div>

        {{-- Guest: Register → Sign in → Track → Mandate --}}
        <div class="landing-steps landing-reveal" data-auth-visible="guest">
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">01</div>
                    <h3 class="fs-16">{{ __('Register') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('New applicants self-register; OCBO admin approval is required before first sign-in.') }}</p>
                    <a href="{{ route('register') }}" class="fs-13 fw-medium link-primary">{{ __('Start registration') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">02</div>
                    <h3 class="fs-16">{{ __('Sign in') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('After approval, sign in to reach applications or the OCBO admin console.') }}</p>
                    <a href="{{ route('login') }}" class="fs-13 fw-medium link-primary">{{ __('Open sign-in') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">03</div>
                    <h3 class="fs-16">{{ __('Track the pipeline') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Follow Draft → Submitted → Evaluation → Inspection → Payment → Release with shared status labels.') }}</p>
                    <a href="{{ route('applications.index') }}" class="fs-13 fw-medium link-primary">{{ __('View applications') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">04</div>
                    <h3 class="fs-16">{{ __('Review the mandate') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('APICS is grounded in P.D. 1096, RA 11032, and LGU QMS — see the compliance foundation above.') }}</p>
                    <a href="#trust" class="fs-13 fw-medium link-primary">{{ __('Read mandate') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        {{-- Applicant --}}
        <div class="landing-steps landing-reveal d-none" data-auth-visible="applicant">
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">01</div>
                    <h3 class="fs-16">{{ __('Start an application') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Create a draft from unified QMS forms, attach documents, and submit for intake.') }}</p>
                    <a href="{{ route('applications.index') }}" class="fs-13 fw-medium link-primary">{{ __('Start application') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">02</div>
                    <h3 class="fs-16">{{ __('Your workspace') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Open Applications to draft, submit, and track building permit filings.') }}</p>
                    <a href="{{ route('applications.index') }}" class="fs-13 fw-medium link-primary">{{ __('Open applications') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">03</div>
                    <h3 class="fs-16">{{ __('Track the pipeline') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Follow Draft → Submitted → Evaluation → Inspection → Payment → Release with shared status labels.') }}</p>
                    <a href="{{ route('applications.index') }}" class="fs-13 fw-medium link-primary">{{ __('View applications') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">04</div>
                    <h3 class="fs-16">{{ __('Review the mandate') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('APICS is grounded in P.D. 1096, RA 11032, and LGU QMS — see the compliance foundation above.') }}</p>
                    <a href="#trust" class="fs-13 fw-medium link-primary">{{ __('Read mandate') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        {{-- Admin --}}
        <div class="landing-steps landing-reveal d-none" data-auth-visible="admin">
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">01</div>
                    <h3 class="fs-16">{{ __('Staff console') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Open the OCBO admin console for registrations, master data, and audit.') }}</p>
                    <a href="{{ route('admin.dashboard') }}" class="fs-13 fw-medium link-primary">{{ __('View console') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">02</div>
                    <h3 class="fs-16">{{ __('Manage master data') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Keep departments, form definitions, and workflow rules current for OCBO offices.') }}</p>
                    <a href="{{ route('admin.departments') }}" class="fs-13 fw-medium link-primary">{{ __('Manage departments') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">03</div>
                    <h3 class="fs-16">{{ __('Track the pipeline') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Follow Draft → Submitted → Evaluation → Inspection → Payment → Release with shared status labels.') }}</p>
                    <a href="{{ route('applications.index') }}" class="fs-13 fw-medium link-primary">{{ __('View applications') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="landing-steps__item">
                <div class="landing-steps__card landing-lift">
                    <div class="landing-step-icon mx-auto mb-3" aria-hidden="true">04</div>
                    <h3 class="fs-16">{{ __('Audit trail') }}</h3>
                    <p class="text-muted fs-14 mb-3">{{ __('Review auth and mutation events with actor, IP, and safe metadata for ICT oversight.') }}</p>
                    <a href="{{ route('admin.audit') }}" class="fs-13 fw-medium link-primary">{{ __('Open audit trail') }} <i class="ri-arrow-right-s-line align-bottom" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Access — midnight slate (matches auth left panel) --}}
<section class="section landing-access landing-section-fade" id="access">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 landing-reveal">
                <span class="landing-eyebrow landing-eyebrow--on-dark">{{ __('Secure access') }}</span>
                <h2 class="landing-access__title mb-3">{{ __('Citizen applications & OCBO admin') }}</h2>
                <p class="landing-access__copy mb-4">{{ __('APICS uses Laravel Sanctum token authentication under /api/v1. New applicants self-register and wait for OCBO admin approval before signing in. Authorized staff use the admin console for master data, registration reviews, and audit.') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('register') }}" class="btn btn-light" data-auth-visible="guest">{{ __('Register') }}</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light" data-auth-visible="guest">{{ __('Sign in') }}</a>
                    <a href="{{ route('applications.index') }}" class="btn btn-light d-none" data-auth-visible="applicant">{{ __('My applications') }}</a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light d-none" data-auth-visible="admin">{{ __('Admin console') }}</a>
                    <a href="{{ route('admin.evaluation-queue') }}" class="btn btn-outline-light d-none" data-auth-visible="admin">{{ __('Evaluation queue') }}</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 landing-reveal">
                        <div class="landing-access-card landing-lift p-4">
                            <h3>{{ __('Applicant portal') }}</h3>
                            <p>{{ __('Draft, submit, and track building permit applications online.') }}</p>
                            <a href="{{ route('applications.index') }}">{{ __('Applications') }} <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 landing-reveal">
                        <div class="landing-access-card landing-lift p-4">
                            <h3>{{ __('OCBO admin') }}</h3>
                            <p>{{ __('Departments, form definitions, and immutable audit visibility.') }}</p>
                            <a href="{{ route('admin.dashboard') }}">{{ __('Admin') }} <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Closing CTA --}}
<section class="section landing-section-fade landing-surface-white">
    <div class="container">
        <div class="landing-cta-panel landing-reveal">
            <h2 class="fw-semibold mb-3">{{ __('Ready to use APICS?') }}</h2>
            <p class="text-muted mb-4" data-auth-visible="guest">{{ __('Register as an applicant (admin approval required), then sign in to draft and track permits.') }}</p>
            <p class="text-muted mb-4 d-none" data-auth-visible="applicant">{{ __('Continue to your applications to create drafts, submit for intake, and track OCBO status updates.') }}</p>
            <p class="text-muted mb-4 d-none" data-auth-visible="admin">{{ __('Open the admin console for registrations, master data, workflows, and audit.') }}</p>
            <div class="landing-cta-panel__actions">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg" data-auth-visible="guest">{{ __('Register as applicant') }}</a>
                <a href="{{ route('login') }}" class="landing-cta-panel__link" data-auth-visible="guest">{{ __('Sign in to workspace') }} <span aria-hidden="true">→</span></a>
                <a href="{{ route('applications.index') }}" class="btn btn-primary btn-lg d-none" data-auth-visible="applicant">{{ __('My applications') }}</a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg d-none" data-auth-visible="admin">{{ __('Admin console') }}</a>
                <a href="{{ route('admin.evaluation-queue') }}" class="landing-cta-panel__link d-none" data-auth-visible="admin">{{ __('Evaluation queue') }} <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
