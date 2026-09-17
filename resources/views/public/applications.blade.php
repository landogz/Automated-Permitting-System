@extends('layouts.velzon.app')

@section('title', 'My Applications')
@section('page-title', 'My Applications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">My Applications</li>
@endsection

@section('content')
{{-- Guest / unsigned state --}}
<div id="applications-auth-gate" class="row justify-content-center d-none">
    <div class="col-lg-8 col-xl-6">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="bg-primary-subtle p-4 p-sm-5 text-center">
                <div class="avatar-lg mx-auto mb-3">
                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1">
                        <i class="ri-file-list-3-line"></i>
                    </span>
                </div>
                <h4 class="fw-semibold mb-2">Sign in to manage your permits</h4>
                <p class="text-muted mb-4 mb-sm-5 px-sm-4">
                    Track building permit applications with the Office of the City Building Official,
                    City of San Fernando, Pampanga.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">
                        <i class="ri-login-box-line align-middle me-1"></i> Sign in
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-soft-primary btn-lg px-4">
                        Register as applicant
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 text-center text-sm-start">
                    <div class="col-sm-4">
                        <p class="text-uppercase text-muted fw-medium fs-11 mb-1">Step 1</p>
                        <p class="mb-0 fw-medium">Create an applicant account</p>
                    </div>
                    <div class="col-sm-4">
                        <p class="text-uppercase text-muted fw-medium fs-11 mb-1">Step 2</p>
                        <p class="mb-0 fw-medium">Wait for OCBO approval</p>
                    </div>
                    <div class="col-sm-4">
                        <p class="text-uppercase text-muted fw-medium fs-11 mb-1">Step 3</p>
                        <p class="mb-0 fw-medium">File and track applications</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Authenticated applicant workspace --}}
<div id="applications-workspace" class="d-none">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 overflow-hidden mb-3 mb-lg-4">
                <div class="bg-primary bg-gradient p-3 p-sm-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div class="text-white">
                            <p class="text-white-50 text-uppercase fw-medium fs-12 mb-1">Applicant portal · OCBO CSFP</p>
                            <h4 class="text-white mb-1" id="applications-welcome">Welcome</h4>
                            <p class="text-white-50 mb-0 fs-13">
                                File permit applications, submit drafts for intake, and track status with APICS.
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-light" id="btn-new-application">
                                <i class="ri-add-line align-bottom me-1"></i> New application
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-outline-light">
                                <i class="ri-home-4-line align-bottom me-1"></i> Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 mb-lg-4" id="applications-stats">
        <div class="col-6 col-xl-3">
            <div class="card card-animate mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Total</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0" data-stat="total">—</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                <i class="ri-folder-3-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card card-animate mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Drafts</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0" data-stat="draft">—</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4">
                                <i class="ri-draft-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card card-animate mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">In progress</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0" data-stat="in_progress">—</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                                <i class="ri-loader-4-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card card-animate mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Released</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0" data-stat="released">—</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                <i class="ri-checkbox-circle-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card" id="applications-list-card">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Application history</h5>
                            <p class="text-muted mb-0 fs-13 mt-1">Search, filter, and manage your permit filings.</p>
                        </div>
                        <div class="col-sm-auto">
                            <button type="button" class="btn btn-success" id="btn-new-application-secondary">
                                <i class="ri-add-line align-bottom me-1"></i> New application
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body border border-dashed border-end-0 border-start-0 bg-light-subtle">
                    <div class="row g-2 g-md-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-12 mb-1" for="applications-howto">How filing works</label>
                            <p class="mb-0 fs-13 text-muted" id="applications-howto">
                                Create a draft → complete project details → submit for OCBO intake.
                            </p>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <x-status.badge status="draft" />
                                <x-status.badge status="submitted" />
                                <x-status.badge status="under_evaluation" />
                                <x-status.badge status="for_inspection" />
                                <x-status.badge status="for_payment" />
                                <x-status.badge status="released" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div id="applications-skeleton" class="row g-3 mb-3">
                        <div class="col-12"><div class="placeholder-glow"><span class="placeholder col-12 rounded" style="height: 52px;"></span></div></div>
                        <div class="col-12"><div class="placeholder-glow"><span class="placeholder col-12 rounded" style="height: 52px;"></span></div></div>
                        <div class="col-12"><div class="placeholder-glow"><span class="placeholder col-12 rounded" style="height: 52px;"></span></div></div>
                    </div>

                    <div class="apics-dt-shell">
                        <table id="applications-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                            <thead class="table-light"></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- New / Edit application modal (tabbed QMS filing) --}}
<div class="modal fade" id="modal-application-form" tabindex="-1" aria-labelledby="modal-application-form-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal apics-app-form-dialog">
        <div class="modal-content">
            <div class="modal-header apics-app-form__header flex-wrap gap-2 align-items-start">
                <div class="min-w-0 flex-grow-1">
                    <h5 class="modal-title mb-1" id="modal-application-form-label">New permit application</h5>
                    <p class="text-muted small mb-0" id="application-form-subtitle">
                        Drafts stay private until you submit. After submission, OCBO receiving reviews for completeness.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-application" class="apics-modal-form" novalidate>
                <input type="hidden" id="application-uuid" value="">
                <div id="application-form-tabnav" class="apics-app-form__tabnav border-bottom px-3 pt-2 bg-body"></div>
                <div class="modal-body apics-app-form__body">
                    <div class="tab-content">
                        {{-- Tab 1: Project & Site --}}
                        <div class="tab-pane fade show active" data-app-form-panel="project" role="tabpanel">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="application-form-type" class="form-label">Permit form <span class="text-danger">*</span></label>
                                    <select class="form-select" id="application-form-type" required>
                                        <option value="">Loading forms…</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="application-project-title" class="form-label">Project title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="application-project-title" required maxlength="255" placeholder="e.g. Two-storey residential building">
                                </div>
                                <div class="col-12">
                                    <div class="apics-location-picker apics-location-picker--compact" data-location-picker data-address-input="application-project-location" data-lat-input="application-project-location-lat" data-lng-input="application-project-location-lng" data-required="1">
                                        <label class="form-label" for="application-project-location">
                                            Project location <span class="text-danger">*</span>
                                        </label>
                                        <div class="position-relative mb-2">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-search-line" aria-hidden="true"></i></span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="application-project-location"
                                                    name="application-project-location"
                                                    data-location-address
                                                    data-location-search
                                                    required
                                                    maxlength="500"
                                                    autocomplete="off"
                                                    placeholder="Search barangay, street, or landmark in CSFP…"
                                                >
                                                <button type="button" class="btn btn-soft-secondary" data-location-toggle-map aria-expanded="true" title="Toggle map">
                                                    <i class="ri-map-2-line" aria-hidden="true"></i>
                                                    <span class="d-none d-sm-inline ms-1">Hide map</span>
                                                </button>
                                            </div>
                                            <div
                                                class="list-group position-absolute w-100 shadow-sm border rounded mt-1 d-none apics-location-picker__results"
                                                data-location-results
                                                style="z-index: 1080; max-height: 14rem; overflow: auto;"
                                                role="listbox"
                                            ></div>
                                        </div>
                                        <input type="hidden" id="application-project-location-lat" data-location-lat value="">
                                        <input type="hidden" id="application-project-location-lng" data-location-lng value="">
                                        <div class="apics-location-picker__map-wrap">
                                            <div
                                                id="application-project-location-map"
                                                class="apics-location-picker__map border rounded overflow-hidden mb-2"
                                                data-location-map
                                                role="application"
                                                aria-label="Project location map"
                                            ></div>
                                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                                <p class="text-muted fs-12 mb-0">Search or click the map to pin the project site. Drag the marker to adjust.</p>
                                                <p class="text-muted fs-12 mb-0 font-monospace" data-location-coords>No pin yet — search or click the map</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div data-app-form-dyn="project" aria-live="polite"></div>
                        </div>

                        {{-- Tab 2: Owner --}}
                        <div class="tab-pane fade d-none" data-app-form-panel="owner" role="tabpanel">
                            <div data-app-form-dyn="owner" aria-live="polite"></div>
                        </div>

                        {{-- Tab 3: Building --}}
                        <div class="tab-pane fade d-none" data-app-form-panel="building" role="tabpanel">
                            <div data-app-form-dyn="building" aria-live="polite"></div>
                        </div>

                        {{-- Tab 4: Professionals --}}
                        <div class="tab-pane fade d-none" data-app-form-panel="professionals" role="tabpanel">
                            <div data-app-form-dyn="professionals" aria-live="polite"></div>
                        </div>

                        {{-- Tab 5: Documents --}}
                        <div class="tab-pane fade d-none" data-app-form-panel="documents" role="tabpanel">
                            <div class="alert alert-soft-info border-0 mb-3" role="status">
                                <div class="d-flex">
                                    <i class="ri-upload-2-line fs-16 me-2 mt-1"></i>
                                    <div class="fs-13 mb-0">
                                        Save the draft once to enable file uploads. Required clearances and plans appear below after the first save.
                                    </div>
                                </div>
                            </div>
                            <div id="application-attachments" aria-live="polite"></div>
                        </div>
                    </div>
                    {{-- Hidden host kept for collectors that still query #application-dynamic-fields --}}
                    <div id="application-dynamic-fields" class="d-none" aria-hidden="true"></div>
                </div>
                <div class="modal-footer flex-wrap gap-2 justify-content-between">
                    <button type="button" class="btn btn-soft-secondary" id="btn-app-form-back" disabled>
                        <i class="ri-arrow-left-line align-bottom me-1"></i> Back
                    </button>
                    <div class="d-flex flex-wrap gap-2 ms-auto">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-soft-primary" id="btn-app-form-next">
                            Next <i class="ri-arrow-right-line align-bottom ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn-save-application">
                            <i class="ri-save-line align-bottom me-1"></i> Save draft
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View application modal (tabbed) --}}
<div class="modal fade" id="modal-application-view" tabindex="-1" aria-labelledby="modal-application-view-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal apics-app-view-dialog">
        <div class="modal-content">
            <div class="modal-header apics-app-view__header flex-wrap gap-2 align-items-start">
                <div class="min-w-0 flex-grow-1 pe-2">
                    <h5 class="modal-title mb-1" id="modal-application-view-label">Application details</h5>
                    <div id="application-view-header-meta" class="apics-app-view__header-meta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="application-view-tabnav" class="apics-app-form__tabnav border-bottom px-3 pt-2 bg-body"></div>
            <div class="modal-body apics-app-view__body" id="application-view-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-soft-primary d-none" id="btn-view-edit">Edit draft</button>
                <button type="button" class="btn btn-success d-none" id="btn-view-submit">Submit for intake</button>
            </div>
        </div>
    </div>
</div>

{{-- Expanded site map for applicant view --}}
<div class="modal fade" id="modal-application-map-expand" tabindex="-1" aria-labelledby="modal-application-map-expand-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-application-map-expand-label">Project site map</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="application-map-expand-body">
                <div class="text-muted text-center py-5">Loading map…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
