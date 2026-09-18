@extends('layouts.velzon.app')

@section('title', 'Archives')
@section('page-title', 'Archives')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Archives</li>
@endsection

@section('content')
<div class="row g-3 mb-3" id="archive-summary-cards">
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Digital</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1 text-primary" data-arc-stat="digital">—</h4>
                <p class="text-muted mb-0 fs-12">CICTO vault (stub)</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Physical</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1" data-arc-stat="physical">—</h4>
                <p class="text-muted mb-0 fs-12">Hard-copy storage</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Hybrid</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1" data-arc-stat="hybrid">—</h4>
                <p class="text-muted mb-0 fs-12">Digital + physical</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">All archives</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1" data-arc-stat="total">—</h4>
                <p class="text-muted mb-0 fs-12">Checksummed records</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Records archiving &amp; CICTO backup metadata</h4>
                    <p class="text-muted mb-0 fs-13">
                        Register archived permit packages with storage location, media type, and integrity checksum.
                        Use <strong>View archive</strong> for the full record and checksum.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-archive">
                    <i class="ri-add-line align-bottom me-1"></i> Archive
                </button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="archives-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create --}}
<div class="modal fade" id="modal-add-archive" tabindex="-1" aria-labelledby="modal-add-archive-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-archive-label">Create archive record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-archive" class="apics-modal-form" novalidate>
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                A SHA-256 integrity checksum is generated automatically (CICTO backup stub metadata).
                                Link an application when archiving a specific permit package.
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="arc-title">Title</label>
                            <input id="arc-title" class="form-control" required maxlength="255" placeholder="Archive package title">
                        </div>
                        <div class="col-12">
                            <x-forms.application-select
                                id="arc-app-uuid"
                                label="Application"
                                :required="false"
                                help="Optional. Link to the permit application being archived."
                            />
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="arc-location">Storage location</label>
                            <input id="arc-location" class="form-control" maxlength="255" placeholder="CICTO digital vault (stub)">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="arc-media">Media type</label>
                            <select id="arc-media" class="form-select">
                                <option value="digital">Digital</option>
                                <option value="physical">Physical</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="arc-notes">Notes</label>
                            <textarea id="arc-notes" class="form-control" rows="3" maxlength="5000" placeholder="Retention notes, box/shelf IDs, backup batch…"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta" id="btn-save-archive">
                        <i class="ri-archive-line" aria-hidden="true"></i>
                        <span class="btn-label">Save archive</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Detail --}}
<div class="modal fade" id="modal-archive-detail" tabindex="-1" aria-labelledby="modal-archive-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-archive-detail-label">Archive record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="archive-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn apics-btn-secondary d-none" id="btn-arc-view-app">
                    <i class="ri-eye-line" aria-hidden="true"></i>
                    <span class="btn-label">View application</span>
                </button>
                <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-line" aria-hidden="true"></i>
                        <span class="btn-label">Close</span>
                    </button>
            </div>
        </div>
    </div>
</div>
@endsection
