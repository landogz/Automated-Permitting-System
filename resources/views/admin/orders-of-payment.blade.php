@extends('layouts.velzon.app')

@section('title', 'Orders of Payment')
@section('page-title', 'Orders of Payment')

@section('page-actions')
    <x-ops.page-tools />
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Orders of Payment</li>
@endsection

@section('content')
<x-ops.process-flow current="payment" />

<div class="row g-3 mb-3" id="oop-summary-cards">
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--amber mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Awaiting payment</p>
                <p class="apics-kpi-card__value" data-oop-stat="issued">—</p>
                <p class="apics-kpi-card__hint">Issued OoPs · <span data-oop-stat="issued_amount">₱0.00</span></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--emerald mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Paid (CTO stub)</p>
                <p class="apics-kpi-card__value" data-oop-stat="paid_stub">—</p>
                <p class="apics-kpi-card__hint">Cleared · <span data-oop-stat="paid_amount">₱0.00</span></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--rose mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Cancelled</p>
                <p class="apics-kpi-card__value" data-oop-stat="cancelled">—</p>
                <p class="apics-kpi-card__hint">Voided assessments</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--slate mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">All orders</p>
                <p class="apics-kpi-card__value" data-oop-stat="total">—</p>
                <p class="apics-kpi-card__hint">G-02 ledger count</p>
            </div>
        </div>
    </div>
</div>

<x-ops.queue-tabs
    id="oop-queue"
    active-label="Active"
    completed-label="Completed"
    completed-hint="Paid (CTO stub) and cancelled Orders of Payment."
>
    <x-slot:active>
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Active orders (awaiting payment)</h4>
                    <p class="text-muted mb-0 fs-13">
                        Issued OoPs ready for CTO stub payment. Use <strong>View Order</strong> for the full line-item breakdown.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-generate-oop">
                    <i class="ri-add-line align-bottom me-1"></i> Generate OoP
                </button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="oop-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-slot:active>
    <x-slot:completed>
        <div class="apics-dt-shell">
            <table id="oop-completed-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                <thead class="table-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </x-slot:completed>
</x-ops.queue-tabs>

{{-- Generate modal --}}
<div class="modal fade" id="modal-generate-oop" tabindex="-1" aria-labelledby="modal-generate-oop-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-generate-oop-label">Generate Order of Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-generate-oop" class="apics-modal-form" novalidate>
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" role="status">
                        <div class="d-flex">
                            <i class="ri-information-line fs-16 me-2 mt-1"></i>
                            <div class="fs-13 mb-0">
                                Select an application ready for payment. Fee lines are calculated from active fee rules using lot/floor area and classification.
                                Optional override requires a documented reason.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <x-forms.application-select
                            id="oop-app-uuid"
                            label="Application"
                            :required="true"
                            for-step="payment"
                            help="Only applications that passed Inspection (status: for_payment) appear here."
                        />
                    </div>

                    <div id="oop-fee-preview" class="border rounded p-3 mb-3 bg-light-subtle">
                        <p class="text-muted fs-13 mb-0">Select an application to preview the fee assessment.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label" for="oop-override-total">Override total (₱, optional)</label>
                            <input id="oop-override-total" type="number" min="0" step="0.01" class="form-control" placeholder="Leave blank to use computed total">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="oop-override-reason">Override reason</label>
                            <textarea id="oop-override-reason" class="form-control" rows="2" maxlength="500" placeholder="Required when overriding total (min 5 characters)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta" id="btn-issue-oop">
                        <i class="ri-bill-line" aria-hidden="true"></i>
                        <span class="btn-label">Issue OoP</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Detail modal --}}
<div class="modal fade" id="modal-oop-detail" tabindex="-1" aria-labelledby="modal-oop-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-oop-detail-label">Order of Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="oop-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn apics-btn-secondary" id="btn-oop-print">
                    <i class="ri-printer-line" aria-hidden="true"></i>
                    <span class="btn-label">Print G-02</span>
                </button>
                <button type="button" class="btn apics-btn-secondary d-none" id="btn-oop-view-app">
                    <i class="ri-eye-line" aria-hidden="true"></i>
                    <span class="btn-label">View application</span>
                </button>
                <button type="button" class="btn apics-btn-cta apics-btn-cta--success d-none" id="btn-oop-mark-paid">
                    <i class="ri-checkbox-circle-line" aria-hidden="true"></i>
                    <span class="btn-label">Mark paid (CTO stub)</span>
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
