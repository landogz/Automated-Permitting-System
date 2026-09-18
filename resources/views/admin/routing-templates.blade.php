@extends('layouts.velzon.app')

@section('title', 'Routing Templates')
@section('page-title', 'Routing Templates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Routing</li>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Active</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1 text-success" data-rt-stat="active">—</h4>
                <p class="text-muted mb-0 fs-12">Ready for slip generation</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Simple / Complex</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1">
                    <span data-rt-stat="simple">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-rt-stat="complex">—</span>
                </h4>
                <p class="text-muted mb-0 fs-12">By classification</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Highly technical</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1" data-rt-stat="highly_technical">—</h4>
                <p class="text-muted mb-0 fs-12">HT routing path</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Inactive · Total</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1">
                    <span class="text-muted" data-rt-stat="inactive">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-rt-stat="total">—</span>
                </h4>
                <p class="text-muted mb-0 fs-12">Template catalog</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Routing slip templates</h4>
                    <p class="text-muted mb-0 fs-13">
                        Ordered department steps used when generating evaluation routing slips by classification.
                        Use <strong>View template</strong> for the full step path and SLA hours.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-open-add-template">
                    <i class="ri-add-line align-bottom me-1"></i> Add template
                </button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="templates-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-template" tabindex="-1" aria-labelledby="modal-add-template-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-template-label">Add routing template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-template" class="apics-modal-form" novalidate>
                <input type="hidden" id="tpl-uuid" value="">
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                Add at least one department step. Step order follows the list top → bottom.
                                Default step SLA is 24 hours (editable per step).
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="tpl-code">Code</label>
                            <input id="tpl-code" class="form-control" required maxlength="50">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="tpl-name">Name</label>
                            <input id="tpl-name" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="tpl-classification">Classification</label>
                            <select id="tpl-classification" class="form-select" required>
                                <option value="simple">Simple</option>
                                <option value="complex">Complex</option>
                                <option value="highly_technical">Highly Technical</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="tpl-active">Status</label>
                            <select id="tpl-active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <h6 class="fs-13 text-uppercase text-muted mb-2">Steps</h6>
                    <div id="tpl-steps" class="vstack gap-2"></div>
                    <button type="button" class="btn btn-soft-secondary btn-sm mt-2" id="btn-add-step">
                        <i class="ri-add-line align-bottom me-1"></i> Add step
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta" id="btn-save-template">
                        <i class="ri-save-line" aria-hidden="true"></i>
                        <span class="btn-label">Save template</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-tpl-detail" tabindex="-1" aria-labelledby="modal-tpl-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-tpl-detail-label">Routing template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="tpl-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn apics-btn-cta" id="btn-tpl-edit">
                    <i class="ri-pencil-line" aria-hidden="true"></i>
                    <span class="btn-label">Edit</span>
                </button>
                <button type="button" class="btn apics-btn-cta apics-btn-cta--danger" id="btn-tpl-delete">
                    <i class="ri-delete-bin-line" aria-hidden="true"></i>
                    <span class="btn-label">Delete</span>
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
