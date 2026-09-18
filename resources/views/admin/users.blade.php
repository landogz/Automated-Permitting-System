@extends('layouts.velzon.app')

@section('title', 'Users & Roles')
@section('page-title', 'Users & Roles')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Users &amp; Roles</li>
@endsection

@section('content')
<div id="apics-users-console" class="apics-users-console">
    <div class="row g-3 mb-3" data-users-kpi>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--slate mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Directory total</p>
                    <p class="apics-kpi-card__value" data-users-stat="total">—</p>
                    <p class="apics-kpi-card__hint">Staff + applicants</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--indigo mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Office staff</p>
                    <p class="apics-kpi-card__value" data-users-stat="staff">—</p>
                    <p class="apics-kpi-card__hint">OCBO role accounts</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--blue mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Applicants</p>
                    <p class="apics-kpi-card__value" data-users-stat="applicants">—</p>
                    <p class="apics-kpi-card__hint">Citizen portal accounts</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--amber mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Pending approval</p>
                    <p class="apics-kpi-card__value" data-users-stat="pending">—</p>
                    <p class="apics-kpi-card__hint">
                        <a href="{{ route('admin.registrations') }}" class="link-secondary text-decoration-underline">Open registrations</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-0 apics-users-console__card">
        <div class="card-header d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="apics-users-console__mark" aria-hidden="true">
                        <i class="ri-team-line"></i>
                    </span>
                    <h4 class="card-title mb-0">Account directory</h4>
                </div>
                <p class="text-muted mb-0 fs-13">
                    Provision office staff, assign Spatie roles &amp; departments, and activate or deactivate access.
                    Applicant self-registration is reviewed on
                    <a href="{{ route('admin.registrations') }}">Registrations</a>.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                <a href="{{ route('admin.registrations') }}" class="btn btn-soft-secondary btn-sm">
                    <i class="ri-user-follow-line align-middle me-1"></i>
                    Registrations
                </a>
                <button type="button" class="btn btn-primary" id="btn-add-user">
                    <i class="ri-user-add-line align-bottom me-1"></i>
                    Add staff user
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="apics-users-console__legend d-none d-md-flex flex-wrap gap-3 mb-3" aria-hidden="true">
                <span><span class="badge bg-success-subtle text-success">Active</span> can sign in</span>
                <span><span class="badge bg-secondary-subtle text-secondary">Inactive</span> blocked</span>
                <span><span class="badge bg-warning-subtle text-warning">Pending</span> awaiting approval</span>
            </div>
            <div class="apics-dt-shell">
                <table id="users-table" class="table table-hover align-middle w-100 apics-datatable apics-users-table">
                    <thead class="table-light"></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-user-form" tabindex="-1" aria-labelledby="modal-user-form-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal apics-account-modal">
        <div class="modal-content border-0">
            <form id="form-user" class="apics-modal-form" novalidate>
                <input type="hidden" id="user-uuid" value="">
                <div class="modal-header apics-account-modal__header">
                    <div class="apics-account-modal__intro">
                        <span class="apics-account-modal__mark apics-account-modal__mark--primary" aria-hidden="true">
                            <i class="ri-user-add-line" id="modal-user-form-icon"></i>
                        </span>
                        <div class="min-w-0">
                            <h5 class="modal-title mb-1" id="modal-user-form-label">Add staff user</h5>
                            <p class="apics-account-modal__lede mb-0" id="modal-user-form-lede">
                                Create an OCBO office account with role, department, and sign-in credentials.
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body apics-account-modal__body">
                    <div class="apics-users-modal__section mb-3">
                        <p class="apics-users-modal__section-title">Identity</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="user-name" class="form-label">Full name <span class="text-danger">*</span></label>
                                <div class="form-icon">
                                    <input type="text" class="form-control form-control-icon" id="user-name" required maxlength="255" autocomplete="name" placeholder="e.g. Maria Santos">
                                    <i class="ri-user-line" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="user-email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="form-icon">
                                    <input type="email" class="form-control form-control-icon" id="user-email" required maxlength="255" autocomplete="username" placeholder="name@csfp.gov.ph">
                                    <i class="ri-mail-line" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="user-phone" class="form-label">Phone</label>
                                <div class="form-icon">
                                    <input type="text" class="form-control form-control-icon" id="user-phone" maxlength="30" autocomplete="tel" placeholder="09XX XXX XXXX">
                                    <i class="ri-phone-line" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch apics-users-modal__switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="user-active" checked>
                                    <label class="form-check-label" for="user-active">
                                        Account active
                                        <span class="d-block text-muted fs-12 fw-normal">Inactive users cannot sign in</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="apics-users-modal__section mb-3">
                        <p class="apics-users-modal__section-title">Role &amp; organization</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="user-role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="user-role" required></select>
                            </div>
                            <div class="col-md-6">
                                <label for="user-department" class="form-label">Department</label>
                                <select class="form-select" id="user-department">
                                    <option value="">— None —</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="user-approval-wrap" hidden>
                                <label for="user-approval" class="form-label">Approval status</label>
                                <select class="form-select" id="user-approval">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="declined">Declined</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="apics-users-modal__section mb-0">
                        <p class="apics-users-modal__section-title">Credentials</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="user-password" class="form-label">Password <span class="text-danger" id="user-password-required">*</span></label>
                                <div class="form-icon">
                                    <input type="password" class="form-control form-control-icon" id="user-password" autocomplete="new-password" placeholder="••••••••">
                                    <i class="ri-lock-password-line" aria-hidden="true"></i>
                                </div>
                                <div class="form-text" id="user-password-hint">Min 8 chars with upper, lower, number, symbol.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="user-password-confirmation" class="form-label">Confirm password <span class="text-danger" id="user-password-confirm-required">*</span></label>
                                <div class="form-icon">
                                    <input type="password" class="form-control form-control-icon" id="user-password-confirmation" autocomplete="new-password" placeholder="••••••••">
                                    <i class="ri-lock-2-line" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer apics-account-modal__footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary apics-account-modal__cta" id="btn-save-user">
                        <i class="ri-save-line align-middle me-1"></i>
                        Save user
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
