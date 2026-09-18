@extends('layouts.velzon.app')

@section('title', 'Fee Rules')
@section('page-title', 'Fee Rules')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Fee Rules</li>
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Active</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1 text-success" data-fr-stat="active">—</h4>
                <p class="text-muted mb-0 fs-12">Drive G-02 lines</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">LGU / BFP</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1">
                    <span data-fr-stat="lgu">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-fr-stat="bfp">—</span>
                </h4>
                <p class="text-muted mb-0 fs-12">Agency split</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">DPWH / CTO</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1">
                    <span data-fr-stat="dpwh">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-fr-stat="cto">—</span>
                </h4>
                <p class="text-muted mb-0 fs-12">Agency split</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-animate border mb-0 h-100">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted fs-11 mb-1">Inactive · Total</p>
                <h4 class="fs-22 fw-semibold ff-secondary mb-1">
                    <span class="text-muted" data-fr-stat="inactive">—</span>
                    <span class="text-muted fs-14">/</span>
                    <span data-fr-stat="total">—</span>
                </h4>
                <p class="text-muted mb-0 fs-12">Fee catalog</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Order of Payment fee engine</h4>
                    <p class="text-muted mb-0 fs-13">
                        Active rules drive G-02 Order of Payment lines (LGU / BFP / DPWH / CTO stubs).
                        Use <strong>View rule</strong> for basis, rates, and conditions.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-open-add-fee-rule">
                    <i class="ri-add-line align-bottom me-1"></i> Add rule
                </button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="fee-rules-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-fee-rule" tabindex="-1" aria-labelledby="modal-add-fee-rule-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-add-fee-rule-label">Add fee rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-fee-rule" class="apics-modal-form" novalidate>
                <input type="hidden" id="fee-uuid" value="">
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                <strong>Fixed</strong> uses Amount. <strong>Area × rate</strong> multiplies lot/floor area by Rate.
                                Lower priority numbers are evaluated first when matching conditions.
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="fee-code">Code</label>
                            <input id="fee-code" class="form-control" required maxlength="50">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="fee-name">Name</label>
                            <input id="fee-name" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fee-agency">Agency</label>
                            <select id="fee-agency" class="form-select" required>
                                <option value="lgu">LGU</option>
                                <option value="bfp">BFP</option>
                                <option value="dpwh">DPWH</option>
                                <option value="cto">CTO</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fee-basis">Basis</label>
                            <select id="fee-basis" class="form-select" required>
                                <option value="fixed">Fixed amount</option>
                                <option value="area_rate">Area × rate</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fee-priority">Priority</label>
                            <input id="fee-priority" type="number" min="1" class="form-control" value="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fee-amount">Amount (₱)</label>
                            <input id="fee-amount" type="number" min="0" step="0.01" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fee-rate">Rate (₱ / sqm)</label>
                            <input id="fee-rate" type="number" min="0" step="0.0001" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fee-active">Status</label>
                            <select id="fee-active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta" id="btn-save-fee-rule">
                        <i class="ri-save-line" aria-hidden="true"></i>
                        <span class="btn-label">Save rule</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-fee-detail" tabindex="-1" aria-labelledby="modal-fee-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-fee-detail-label">Fee rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="fee-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn apics-btn-cta" id="btn-fee-edit">
                    <i class="ri-pencil-line" aria-hidden="true"></i>
                    <span class="btn-label">Edit</span>
                </button>
                <button type="button" class="btn apics-btn-cta apics-btn-cta--danger" id="btn-fee-delete">
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
