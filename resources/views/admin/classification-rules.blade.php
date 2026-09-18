@extends('layouts.velzon.app')

@section('title', 'Classification Rules')
@section('page-title', 'Classification Rules')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Classifier</li>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Active rules</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1 text-success" data-cr-stat="active">—</h4>
                <p class="text-muted mb-0 fs-12">Used by auto-classify</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Simple / Complex</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1">
                    <span data-cr-stat="simple">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-cr-stat="complex">—</span>
                </h4>
                <p class="text-muted mb-0 fs-12">By target classification</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Highly technical</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1" data-cr-stat="highly_technical">—</h4>
                <p class="text-muted mb-0 fs-12">HT track rules</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Inactive · Total</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1">
                    <span class="text-muted" data-cr-stat="inactive">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-cr-stat="total">—</span>
                </h4>
                <p class="text-muted mb-0 fs-12">Catalog size</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Permit classifier rules</h4>
                    <p class="text-muted mb-0 fs-13">
                        Evaluated by priority (lowest number first). Matching applications become Simple, Complex, or Highly Technical.
                        Use <strong>View rule</strong> for conditions and SLA.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-open-add-rule">
                    <i class="ri-add-line align-bottom me-1"></i> Add rule
                </button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="rules-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-rule" tabindex="-1" aria-labelledby="modal-add-rule-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-rule-label">Add classification rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-rule" class="apics-modal-form" novalidate>
                <input type="hidden" id="rule-uuid" value="">
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                Leave the condition blank for an unconditional match (useful as a low-priority default).
                                Payload fields commonly used: <code>lot_area</code>, <code>floor_area</code>, <code>occupancy</code>.
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="rule-code">Code</label>
                            <input id="rule-code" class="form-control" required maxlength="50" placeholder="e.g. COMPLEX-AREA">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="rule-name">Name</label>
                            <input id="rule-name" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="rule-classification">Classification</label>
                            <select id="rule-classification" class="form-select" required>
                                <option value="simple">Simple</option>
                                <option value="complex">Complex</option>
                                <option value="highly_technical">Highly Technical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="rule-priority">Priority</label>
                            <input id="rule-priority" type="number" class="form-control" value="100" min="1" max="9999">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="rule-sla">SLA hours</label>
                            <input id="rule-sla" type="number" class="form-control" value="72" min="1" max="8760">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="rule-active">Status</label>
                            <select id="rule-active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12"><hr class="my-1"><h6 class="fs-13 text-muted text-uppercase mb-0">Condition (optional)</h6></div>
                        <div class="col-md-4">
                            <label class="form-label" for="rule-field">Field</label>
                            <input id="rule-field" class="form-control" value="lot_area" placeholder="payload field">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="rule-operator">Operator</label>
                            <select id="rule-operator" class="form-select">
                                <option value=">=">>=</option>
                                <option value=">">&gt;</option>
                                <option value="<=">&lt;=</option>
                                <option value="<">&lt;</option>
                                <option value="=">=</option>
                                <option value="contains">contains</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="rule-value">Value</label>
                            <input id="rule-value" class="form-control" placeholder="e.g. 200">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta" id="btn-save-rule">
                        <i class="ri-save-line" aria-hidden="true"></i>
                        <span class="btn-label">Save rule</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-rule-detail" tabindex="-1" aria-labelledby="modal-rule-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-rule-detail-label">Classification rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="rule-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn apics-btn-cta" id="btn-rule-edit">
                    <i class="ri-pencil-line" aria-hidden="true"></i>
                    <span class="btn-label">Edit</span>
                </button>
                <button type="button" class="btn apics-btn-secondary" id="btn-rule-toggle">
                    <i class="ri-toggle-line" aria-hidden="true"></i>
                    <span class="btn-label">Toggle active</span>
                </button>
                <button type="button" class="btn apics-btn-cta apics-btn-cta--danger" id="btn-rule-delete">
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
