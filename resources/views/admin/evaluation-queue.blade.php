@extends('layouts.velzon.app')

@section('title', 'Evaluation Queue')
@section('page-title', 'Evaluation Queue')

@section('page-actions')
    <x-ops.page-tools />
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Evaluation</li>
@endsection

@section('content')
<x-ops.process-flow current="evaluation" />

<div class="row g-3 mb-3" id="eval-summary-cards">
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--amber mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Pending review</p>
                <p class="apics-kpi-card__value" data-eval-stat="pending">—</p>
                <p class="apics-kpi-card__hint">Awaiting initial classification</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--blue mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Under evaluation</p>
                <p class="apics-kpi-card__value" data-eval-stat="under_evaluation">—</p>
                <p class="apics-kpi-card__hint">With technical reviewers</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--rose mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">SLA warning</p>
                <p class="apics-kpi-card__value" data-eval-stat="sla_warn">—</p>
                <p class="apics-kpi-card__hint">Within 48h of RA 11032 cap</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--emerald mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Completed today</p>
                <p class="apics-kpi-card__value" data-eval-stat="completed_today">—</p>
                <p class="apics-kpi-card__hint">Forwarded to inspection</p>
            </div>
        </div>
    </div>
</div>

<x-ops.queue-tabs
    id="eval-queue"
    active-label="Active"
    completed-label="Completed"
    completed-hint="Applications that already left this step (inspection onward)."
>
    <x-slot:active>
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Active queue</h4>
                    <p class="text-muted mb-0 fs-13">
                        Submitted / under evaluation. Use <strong>Review</strong> before classifying or deciding.
                    </p>
                </div>
                <div id="eval-timer-pill" class="apics-timer-pill is-idle" aria-live="polite">
                    <span class="apics-timer-pill__dot" aria-hidden="true"></span>
                    <span>
                        Reviewing: <strong data-timer-app>—</strong>
                        (<span data-timer-elapsed>0m 00s</span>)
                    </span>
                    <button type="button" class="btn btn-sm btn-warning ms-1" id="btn-stop-timer">Stop &amp; Pause</button>
                </div>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="queue-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-slot:active>
    <x-slot:completed>
        <div class="apics-dt-shell">
            <table id="queue-completed-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                <thead class="table-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </x-slot:completed>
</x-ops.queue-tabs>

<div class="card mt-3">
    <div class="card-header">
        <h4 class="card-title mb-1">Evaluation time (QMS review)</h4>
        <p class="text-muted mb-0 fs-13">Closed sessions rolled up by department and staff.</p>
    </div>
    <div class="card-body" id="eval-time-summary">
        <p class="text-muted small mb-0">Loading time summary…</p>
    </div>
</div>

{{-- Evaluate · QMS-63 / QMS-64 --}}
<div class="modal fade" id="modal-evaluate-application" tabindex="-1" aria-hidden="true" aria-labelledby="modal-evaluate-label">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal apics-insp-record-modal">
        <div class="modal-content border-0 shadow">
            <div class="modal-header align-items-start">
                <div class="pe-3 min-w-0">
                    <h5 class="modal-title mb-0" id="modal-evaluate-label">Evaluate application</h5>
                    <div class="apics-insp-record-modal__meta" id="eval-modal-meta">—</div>
                    <div class="mt-1 d-flex flex-wrap gap-1">
                        <span class="apics-insp-form-chip apics-insp-form-chip--primary">QMS-63</span>
                        <span class="apics-insp-form-chip apics-insp-form-chip--success">QMS-64</span>
                    </div>
                </div>
                <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-evaluate-application" class="apics-modal-form">
                <div class="modal-body pt-2">
                    <div class="apics-insp-outcome">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="apics-insp-section__icon apics-insp-section__icon--notes" aria-hidden="true">
                                <i class="ri-flag-2-line"></i>
                            </span>
                            <div>
                                <p class="apics-insp-section__title mb-0">Evaluation outcome</p>
                                <p class="apics-insp-section__hint mb-0">Save only keeps a draft. Save &amp; decide advances status.</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="eval-result">Result</label>
                                <select id="eval-result" class="form-select" required>
                                    <option value="compliant">Compliant</option>
                                    <option value="non_compliant">Non-compliant</option>
                                    <option value="needs_info">Needs info</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="eval-remarks">Decision remarks</label>
                                <textarea id="eval-remarks" class="form-control" rows="2" maxlength="5000" placeholder="Summary for compliance / applicant…"></textarea>
                            </div>
                        </div>
                    </div>

                    <section class="apics-insp-section" aria-labelledby="eval-section-qms63-title">
                        <div class="apics-insp-section__head">
                            <div class="apics-insp-section__head-main">
                                <span class="apics-insp-section__icon apics-insp-section__icon--compliance" aria-hidden="true">
                                    <i class="ri-checkbox-multiple-line"></i>
                                </span>
                                <div>
                                    <h6 class="apics-insp-section__title" id="eval-section-qms63-title">Documentary evaluation</h6>
                                    <p class="apics-insp-section__hint">QMS-63 · Completeness checklist</p>
                                </div>
                            </div>
                            <span class="apics-insp-form-chip apics-insp-form-chip--primary">QMS-63</span>
                        </div>
                        <div class="apics-insp-section__body">
                            <div id="eval-qms63-checklist" class="apics-insp-checklist mb-3"></div>
                            <label class="form-label" for="eval-overall">Overall remarks</label>
                            <textarea id="eval-overall" class="form-control" rows="2" maxlength="5000" placeholder="Documentary review summary…"></textarea>
                        </div>
                    </section>

                    <section class="apics-insp-section" aria-labelledby="eval-section-qms64-title">
                        <div class="apics-insp-section__head">
                            <div class="apics-insp-section__head-main">
                                <span class="apics-insp-section__icon apics-insp-section__icon--notes" aria-hidden="true">
                                    <i class="ri-ruler-2-line"></i>
                                </span>
                                <div>
                                    <h6 class="apics-insp-section__title" id="eval-section-qms64-title">Technical findings</h6>
                                    <p class="apics-insp-section__hint">QMS-64 · Discipline review checklist</p>
                                </div>
                            </div>
                            <span class="apics-insp-form-chip apics-insp-form-chip--success">QMS-64</span>
                        </div>
                        <div class="apics-insp-section__body">
                            <div id="eval-qms64-checklist" class="apics-insp-checklist mb-3"></div>
                            <label class="form-label" for="eval-discipline">Discipline remarks</label>
                            <textarea id="eval-discipline" class="form-control" rows="2" maxlength="5000" placeholder="Technical / discipline notes…"></textarea>
                        </div>
                    </section>
                </div>
                <div class="modal-footer flex-wrap">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <div class="ms-md-auto d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-soft-primary" id="btn-eval-save-only">
                            <i class="ri-save-line align-bottom me-1" aria-hidden="true"></i>Save only
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn-eval-save-decide">
                            <i class="ri-checkbox-circle-line align-bottom me-1" aria-hidden="true"></i>Save &amp; decide
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
