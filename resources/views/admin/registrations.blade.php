@extends('layouts.velzon.app')

@section('title', 'Registrations')
@section('page-title', 'Registration Approvals')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Registrations</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex flex-wrap gap-2">
                <h4 class="card-title mb-0 flex-grow-1">Applicant registration queue</h4>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="registrations-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-decline-registration" tabindex="-1" aria-labelledby="modal-decline-registration-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-decline-registration-label">Decline registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-decline-registration" novalidate>
                <input type="hidden" id="decline-user-uuid" value="">
                <div class="modal-body">
                    <p class="text-muted mb-3" id="decline-user-summary">Provide a reason. The applicant will be emailed and cannot sign in.</p>
                    <div class="mb-0">
                        <label for="decline-reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="decline-reason" rows="4" required minlength="5" maxlength="1000" placeholder="Explain why this registration is declined"></textarea>
                        <div class="form-text">Minimum 5 characters.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta apics-btn-cta--danger" id="btn-confirm-decline">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Decline registration</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
