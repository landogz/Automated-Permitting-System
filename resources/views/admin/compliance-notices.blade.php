@extends('layouts.velzon.app')

@section('title', 'Compliance Notices')
@section('page-title', 'Compliance Notices')

@section('page-actions')
    <x-ops.page-tools />
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Compliance</li>
@endsection

@section('content')
<x-ops.process-flow current="compliance" />

<div class="row g-3 mb-3" id="notice-summary-cards">
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--amber mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Issued</p>
                <p class="apics-kpi-card__value" data-notice-stat="issued">—</p>
                <p class="apics-kpi-card__hint">Awaiting compliance / appeal</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--blue mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Appealed</p>
                <p class="apics-kpi-card__value" data-notice-stat="appealed">—</p>
                <p class="apics-kpi-card__hint">Pending resolution</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--indigo mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Notice of Compliance (G-03)</p>
                <p class="apics-kpi-card__value" data-notice-stat="g03">—</p>
                <p class="apics-kpi-card__hint">Deficiency notices</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--rose mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Notice of Disapproval (G-04)</p>
                <p class="apics-kpi-card__value" data-notice-stat="g04">—</p>
                <p class="apics-kpi-card__hint"><span data-notice-stat="g04_active">—</span> active · <span data-notice-stat="g04_archived">—</span> archived</p>
            </div>
        </div>
    </div>
</div>

<x-ops.queue-tabs
    id="notice-queue"
    active-label="Active"
    completed-label="Completed"
    completed-hint="Closed notices after appeal resolution or final disposition."
>
    <x-slot:active>
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Active notices &amp; appeals</h4>
                    <p class="text-muted mb-0 fs-13">
                        Issued or appealed notices. Use <strong>View Notice</strong> for the full body and appeal trail.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-issue-notice" id="btn-issue-notice-header">
                    <i class="ri-add-line align-bottom me-1"></i> Issue notice
                </button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="notices-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-slot:active>
    <x-slot:completed>
        <div class="apics-dt-shell">
            <table id="notices-completed-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                <thead class="table-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </x-slot:completed>
</x-ops.queue-tabs>

{{-- Issue modal --}}
<div class="modal fade" id="modal-issue-notice" tabindex="-1" aria-labelledby="modal-issue-notice-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-issue-notice-label">Issue compliance notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-issue-notice" class="apics-modal-form" novalidate>
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                G-03 keeps the application in compliance; G-04 marks it disapproved.
                                Due date defaults to 15 days from issue unless overridden by policy later.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <x-forms.application-select
                            id="notice-app-uuid"
                            label="Application"
                            :required="true"
                            for-step="compliance"
                            help="Only applications flagged for compliance (failed inspection / non-compliant eval) appear here."
                        />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notice-type">Type</label>
                        <select id="notice-type" class="form-select" required>
                            <option value="g03_compliance">G-03 Notice of Compliance</option>
                            <option value="g04_disapproval">G-04 Notice of Disapproval</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notice-title">Title</label>
                        <input id="notice-title" class="form-control" required maxlength="255" placeholder="Short subject line">
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="notice-body">Body</label>
                        <textarea id="notice-body" class="form-control" rows="6" required minlength="10" placeholder="Cite findings, deficiencies, and required corrective actions…"></textarea>
                        <div class="form-text">Selecting an application with a failed inspection auto-fills inspector findings so the applicant can see what went wrong.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta" id="btn-issue-notice">
                        <i class="ri-file-warning-line" aria-hidden="true"></i>
                        <span class="btn-label">Issue notice</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Detail modal --}}
<div class="modal fade" id="modal-notice-detail" tabindex="-1" aria-labelledby="modal-notice-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-notice-detail-label">Compliance notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notice-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn apics-btn-secondary d-none" id="btn-notice-view-app">
                    <i class="ri-eye-line" aria-hidden="true"></i>
                    <span class="btn-label">View application</span>
                </button>
                <button type="button" class="btn apics-btn-secondary d-none" id="btn-notice-file-appeal">
                    <i class="ri-scales-3-line" aria-hidden="true"></i>
                    <span class="btn-label">File appeal</span>
                </button>
                <button type="button" class="btn apics-btn-cta apics-btn-cta--success d-none" id="btn-notice-resolve-appeal">
                    <i class="ri-checkbox-circle-line" aria-hidden="true"></i>
                    <span class="btn-label">Resolve appeal</span>
                </button>
                <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-line" aria-hidden="true"></i>
                        <span class="btn-label">Close</span>
                    </button>
            </div>
        </div>
    </div>
</div>

{{-- Resolve appeal modal --}}
<div class="modal fade" id="modal-resolve-appeal" tabindex="-1" aria-labelledby="modal-resolve-appeal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-resolve-appeal-label">Resolve appeal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-resolve-appeal" class="apics-modal-form" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="resolve-appeal-uuid" value="">
                    <div class="mb-3">
                        <label class="form-label" for="resolve-appeal-status">Decision</label>
                        <select id="resolve-appeal-status" class="form-select" required>
                            <option value="upheld">Uphold appeal (re-open evaluation)</option>
                            <option value="denied">Deny appeal (notice stands)</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="resolve-appeal-notes">Resolution notes</label>
                        <textarea id="resolve-appeal-notes" class="form-control" rows="3" maxlength="5000" placeholder="Document the basis for the decision…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta">
                        <i class="ri-save-line" aria-hidden="true"></i>
                        <span class="btn-label">Save decision</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
