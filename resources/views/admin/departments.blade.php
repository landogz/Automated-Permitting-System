@extends('layouts.velzon.app')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Departments</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex flex-wrap gap-2">
                <h4 class="card-title mb-0 flex-grow-1">Organization units</h4>
                <div class="flex-shrink-0 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-soft-secondary" id="btn-import-departments" data-bs-toggle="modal" data-bs-target="#modal-import-departments">Import JSON</button>
                    <a href="#" class="btn btn-soft-info" id="btn-export-departments">Export CSV</a>
                    <button type="button" class="btn btn-primary" id="btn-add-department" data-bs-toggle="modal" data-bs-target="#modal-add-department">
                        <i class="ri-add-line align-bottom me-1"></i> Add
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="departments-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-department" tabindex="-1" aria-labelledby="modal-add-department-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-department-label">Add department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-department" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dept-code" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dept-code" required maxlength="50" placeholder="e.g. OCBO" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="dept-name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dept-name" required maxlength="255" placeholder="Department name" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="dept-description" class="form-label">Description</label>
                        <textarea class="form-control" id="dept-description" rows="3" placeholder="Optional description"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="dept-active" checked>
                        <label class="form-check-label" for="dept-active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-department">Save department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-import-departments" tabindex="-1" aria-labelledby="modal-import-departments-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-import-departments-label">Import departments (JSON)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-import-departments" novalidate>
                <div class="modal-body">
                    <label for="dept-import-json" class="form-label">Paste a JSON array of department rows</label>
                    <textarea class="form-control font-monospace" id="dept-import-json" rows="10" required spellcheck="false"></textarea>
                    <div class="form-text">Example: <code>[{"code":"ENG","name":"Engineering","is_active":true}]</code></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-run-import">Dry-run &amp; commit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
