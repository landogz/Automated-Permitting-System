@extends('layouts.velzon.app')

@section('title', 'Users & Roles')
@section('page-title', 'Users & Roles')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Users &amp; Roles</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex flex-wrap gap-2">
                <h4 class="card-title mb-0 flex-grow-1">Staff &amp; applicant accounts</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-primary" id="btn-add-user" data-bs-toggle="modal" data-bs-target="#modal-user-form">
                        <i class="ri-user-add-line align-bottom me-1"></i> Add staff user
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-13 mb-3">Create office staff accounts, assign Spatie roles, and activate or deactivate users. Applicant self-registration still goes through <a href="{{ route('admin.registrations') }}">Registrations</a>.</p>
                <div class="apics-dt-shell">
                    <table id="users-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-user-form" tabindex="-1" aria-labelledby="modal-user-form-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-user-form-label">Add staff user</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-user" class="apics-modal-form" novalidate>
                <input type="hidden" id="user-uuid" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="user-name" class="form-label">Full name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user-name" required maxlength="255" autocomplete="name">
                        </div>
                        <div class="col-md-6">
                            <label for="user-email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="user-email" required maxlength="255" autocomplete="username">
                        </div>
                        <div class="col-md-6">
                            <label for="user-phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="user-phone" maxlength="30" autocomplete="tel">
                        </div>
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
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="user-active" checked>
                                <label class="form-check-label" for="user-active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="user-approval-wrap" hidden>
                            <label for="user-approval" class="form-label">Approval status</label>
                            <select class="form-select" id="user-approval">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="declined">Declined</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="user-password" class="form-label">Password <span class="text-danger" id="user-password-required">*</span></label>
                            <input type="password" class="form-control" id="user-password" autocomplete="new-password">
                            <div class="form-text" id="user-password-hint">Min 8 chars with upper, lower, number, symbol.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="user-password-confirmation" class="form-label">Confirm password <span class="text-danger" id="user-password-confirm-required">*</span></label>
                            <input type="password" class="form-control" id="user-password-confirmation" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-user">Save user</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
