@extends('layouts.velzon.app')

@section('title', 'Form Definitions')
@section('page-title', 'Form Definitions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Forms</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex flex-wrap gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Form definitions</h4>
                    <p class="text-muted mb-0 fs-13">Configure the fields applicants fill on QMS forms. Changes apply to new drafts immediately.</p>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-primary" id="btn-add-form">
                        <i class="ri-add-line align-bottom me-1"></i> Add form
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="forms-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-form-builder" tabindex="-1" aria-labelledby="modal-form-builder-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-form-builder-label">Form builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-builder" class="apics-modal-form" novalidate>
                <input type="hidden" id="form-uuid" value="">
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                Field <strong>keys</strong> are stored in application payload (e.g. <code>lot_area</code>, <code>floor_area</code>).
                                Use lowercase snake_case. Fee rules and classifiers read these keys.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="form-code" class="form-label">Form code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="form-code" required maxlength="50" placeholder="QMS-36" autocomplete="off">
                        </div>
                        <div class="col-md-5">
                            <label for="form-title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="form-title" required maxlength="255" placeholder="Unified Application Form" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <label for="form-revision" class="form-label">Revision</label>
                            <input type="text" class="form-control" id="form-revision" maxlength="20" value="01">
                        </div>
                        <div class="col-md-2">
                            <label for="form-effective-date" class="form-label">Effective date</label>
                            <input type="date" class="form-control" id="form-effective-date">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="form-active" checked>
                                <label class="form-check-label" for="form-active">Active (available for new applications)</label>
                            </div>
                        </div>
                    </div>

                    <div id="form-schema-editor" class="mb-4"></div>

                    <div class="border rounded p-3">
                        <label for="form-attachments" class="form-label mb-1">Required attachments (upload slots)</label>
                        <textarea class="form-control font-monospace" id="form-attachments" rows="4" placeholder="tax_declaration&#10;lot_plan&#10;structural_plans"></textarea>
                        <div class="form-text">One key per line (snake_case). Each becomes a required file upload slot on <code>/applications</code> (PDF/JPG/PNG/WEBP).</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta" id="btn-save-form">
                        <i class="ri-save-line" aria-hidden="true"></i>
                        <span class="btn-label">Save form</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
