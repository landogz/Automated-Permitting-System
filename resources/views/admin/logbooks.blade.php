@extends('layouts.velzon.app')

@section('title', 'Logbooks')
@section('page-title', 'Records Logbooks')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Logbooks</li>
@endsection

@section('content')
<div class="row g-3 mb-3" id="logbook-summary-cards">
    <div class="col-6 col-md-4 col-xl">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body py-3">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">G-01 Releasing</p>
                <h4 class="fs-20 fw-semibold ff-secondary mb-0" data-lb-stat="g01_releasing">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body py-3">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">O-02 Occupancy</p>
                <h4 class="fs-20 fw-semibold ff-secondary mb-0" data-lb-stat="o02_occupancy">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body py-3">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">E-series</p>
                <h4 class="fs-20 fw-semibold ff-secondary mb-0" data-lb-stat="e_series">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body py-3">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">G-05 / G-06</p>
                <h4 class="fs-20 fw-semibold ff-secondary mb-0">
                    <span data-lb-stat="g05">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-lb-stat="g06">—</span>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body py-3">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">All entries</p>
                <h4 class="fs-20 fw-semibold ff-secondary mb-0" data-lb-stat="total">—</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Official logbooks</h4>
                    <p class="text-muted mb-0 fs-13">
                        Record G-01 releasing, O-02 occupancy, E-series, G-05, and G-06 entries.
                        Use <strong>View entry</strong> for full details or <strong>Print / PDF</strong> for the signed slip.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-logbook">
                    <i class="ri-add-line align-bottom me-1"></i> Add entry
                </button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="logbooks-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add entry --}}
<div class="modal fade" id="modal-add-logbook" tabindex="-1" aria-labelledby="modal-add-logbook-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-logbook-label">Add logbook entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-logbook" class="apics-modal-form" novalidate>
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                G-01 releasing entries linked to an application with status <strong>For Releasing</strong> will set that application to <strong>Released</strong>.
                                Print uses a short-lived signed URL.
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label" for="lb-book-type">Book type</label>
                            <select id="lb-book-type" class="form-select" required>
                                <option value="g01_releasing">G-01 Releasing</option>
                                <option value="o02_occupancy">O-02 Occupancy</option>
                                <option value="e_series">E-series</option>
                                <option value="g05">G-05</option>
                                <option value="g06">G-06</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="lb-subject">Subject</label>
                            <input id="lb-subject" class="form-control" required maxlength="255" placeholder="What is being recorded?">
                        </div>
                        <div class="col-12">
                            <x-forms.application-select
                                id="lb-app-uuid"
                                label="Application"
                                :required="false"
                                for-step="releasing"
                                help="For G-01, pick a For Releasing application (paid). Other book types may leave this blank."
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="lb-recipient">Recipient name</label>
                            <input id="lb-recipient" class="form-control" maxlength="255" placeholder="Claimant / receiving party">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="lb-contact">Recipient contact</label>
                            <input id="lb-contact" class="form-control" maxlength="100" placeholder="Phone or email">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="lb-notes">Notes</label>
                            <textarea id="lb-notes" class="form-control" rows="3" maxlength="5000" placeholder="Additional remarks for the official record…"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-logbook">
                        <i class="ri-book-marked-line align-bottom me-1"></i> Save entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Detail --}}
<div class="modal fade" id="modal-logbook-detail" tabindex="-1" aria-labelledby="modal-logbook-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-logbook-detail-label">Logbook entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logbook-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-soft-primary d-none" id="btn-lb-view-app">View application</button>
                <button type="button" class="btn btn-primary d-none" id="btn-lb-print">
                    <i class="ri-printer-line align-bottom me-1"></i> Print / PDF
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
