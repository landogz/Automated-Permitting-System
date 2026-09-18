@extends('layouts.velzon.app')

@section('title', 'Inspections')
@section('page-title', 'Inspections')

@section('page-actions')
    <x-ops.page-tools />
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Inspections</li>
@endsection

@section('content')
<x-ops.process-flow current="inspections" />

<div class="row g-3 mb-3" id="insp-summary-cards">
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--blue mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Scheduled today</p>
                <p class="apics-kpi-card__value" data-insp-stat="today">—</p>
                <p class="apics-kpi-card__hint">Site visits on today’s calendar</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--indigo mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Pending joint</p>
                <p class="apics-kpi-card__value" data-insp-stat="joint">—</p>
                <p class="apics-kpi-card__hint">Active joint inspections</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--rose mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Failed / re-inspect</p>
                <p class="apics-kpi-card__value" data-insp-stat="failed">—</p>
                <p class="apics-kpi-card__hint">Require follow-up</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card apics-kpi-card apics-kpi-card--emerald mb-0">
            <div class="card-body">
                <p class="apics-kpi-card__label">Passed &amp; cleared</p>
                <p class="apics-kpi-card__value" data-insp-stat="passed">—</p>
                <p class="apics-kpi-card__hint">Ready for payment</p>
            </div>
        </div>
    </div>
</div>

<x-ops.queue-tabs
    id="insp-queue"
    active-label="Active"
    completed-label="Completed"
    completed-hint="Passed, failed, or cancelled inspections. Print QMS/O-03/DPWH forms from the Actions menu."
>
    <x-slot:active>
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Active inspections</h4>
                    <p class="text-muted mb-0 fs-13">
                        Schedule with QMS-38/39, then <strong>Record / complete forms</strong> (Save only or Save &amp; complete).
                    </p>
                </div>
                <div class="btn-group apics-view-toggle" role="group" aria-label="View mode">
                    <button type="button" class="btn btn-sm btn-primary" id="btn-insp-view-table" title="Table view">
                        <i class="ri-table-line" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Table</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-soft-secondary" id="btn-insp-view-calendar" title="Calendar / agenda view">
                        <i class="ri-calendar-line" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Calendar</span>
                    </button>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-schedule-inspection">
                    <i class="ri-add-line align-bottom me-1"></i> Schedule
                </button>
            </div>
            <div class="card-body">
                <div id="insp-table-pane" class="apics-dt-shell">
                    <table id="inspections-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="insp-calendar-pane" class="apics-insp-calendar" aria-live="polite"></div>
            </div>
        </div>
    </x-slot:active>
    <x-slot:completed>
        <div class="apics-dt-shell">
            <table id="inspections-completed-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                <thead class="table-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </x-slot:completed>
</x-ops.queue-tabs>

{{-- Schedule · QMS-38 / QMS-39 --}}
<div class="modal fade" id="modal-schedule-inspection" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule inspection (QMS-38 / QMS-39)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-schedule-inspection" class="apics-modal-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <x-forms.application-select
                            id="insp-app-uuid"
                            label="Application"
                            :required="true"
                            for-step="inspection"
                            help="Only for_inspection applications without an open inspection appear here (one open ticket per application)."
                        />
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="insp-type">Type</label>
                            <select id="insp-type" class="form-select">
                                <option value="joint_structural">Joint · Structural</option>
                                <option value="joint_architectural">Joint · Architectural</option>
                                <option value="joint_electrical">Joint · Electrical</option>
                                <option value="joint_sanitary">Joint · Sanitary</option>
                                <option value="joint_mechanical">Joint · Mechanical</option>
                                <option value="joint_fire_safety">Joint · Fire Safety</option>
                                <option value="electrical">Electrical only (DPWH 77-006-E)</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="insp-scheduled-at">Scheduled at</label>
                            <input id="insp-scheduled-at" type="datetime-local" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="insp-purpose">Purpose (QMS-38)</label>
                        <textarea id="insp-purpose" class="form-control" rows="2" maxlength="2000" placeholder="Joint site inspection of approved works…"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="insp-meeting-point">Meeting point</label>
                        <input id="insp-meeting-point" type="text" class="form-control" maxlength="500" placeholder="Site gate / barangay hall…">
                    </div>
                    <div class="mb-3">
                        <span class="form-label d-block">Disciplines</span>
                        <div id="insp-disciplines" class="d-flex flex-wrap"></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <span class="form-label mb-0">Inspection team (QMS-39)</span>
                            <button type="button" class="btn btn-sm btn-soft-primary" id="btn-insp-add-team">
                                <i class="ri-user-add-line me-1" aria-hidden="true"></i>Add
                            </button>
                        </div>
                        <div id="insp-team-rows"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="insp-coordination">Coordination notes</label>
                        <textarea id="insp-coordination" class="form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="insp-schedule-remarks">Scheduling remarks</label>
                        <textarea id="insp-schedule-remarks" class="form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <div class="mb-0">
                        <x-forms.location-picker
                            id="insp-location"
                            label="Inspection location"
                            :required="false"
                            placeholder="Search site or click the map…"
                            help="Defaults from the application site when available. Search or pin on the map."
                        />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Complete · O-03 / QMS-65 / DPWH 77-006-E --}}
<div class="modal fade" id="modal-complete-inspection" tabindex="-1" aria-hidden="true" aria-labelledby="modal-complete-inspection-label">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal apics-insp-record-modal">
        <div class="modal-content border-0 shadow">
            <div class="modal-header align-items-start">
                <div class="pe-3 min-w-0">
                    <h5 class="modal-title mb-0" id="modal-complete-inspection-label">Record inspection forms</h5>
                    <div class="apics-insp-record-modal__meta" id="insp-complete-meta">
                        <span class="apics-insp-record-modal__meta-app" id="insp-complete-meta-app">—</span>
                        <span class="apics-insp-form-chip apics-insp-form-chip--primary">O-03</span>
                        <span class="apics-insp-form-chip apics-insp-form-chip--success">QMS-65</span>
                        <span class="apics-insp-form-chip apics-insp-form-chip--warning d-none" id="insp-complete-chip-elec">77-006-E</span>
                    </div>
                </div>
                <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-complete-inspection" class="apics-modal-form">
                <div class="modal-body pt-2">
                    <div class="apics-insp-outcome">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="apics-insp-section__icon apics-insp-section__icon--notes" aria-hidden="true">
                                <i class="ri-flag-2-line"></i>
                            </span>
                            <div>
                                <p class="apics-insp-section__title mb-0">Inspection outcome</p>
                                <p class="apics-insp-section__hint mb-0">Set the official result before completing. Save only keeps the ticket open.</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="insp-complete-result">Result</label>
                                <select id="insp-complete-result" class="form-select" required>
                                    <option value="passed">Passed</option>
                                    <option value="conditional">Conditional</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="insp-complete-notes">
                                    Summary notes
                                    <span class="text-muted fw-normal">· required if failed</span>
                                </label>
                                <textarea id="insp-complete-notes" class="form-control" rows="2" maxlength="5000" placeholder="Short outcome summary for compliance / applicant…"></textarea>
                            </div>
                        </div>
                    </div>

                    <section class="apics-insp-section" aria-labelledby="insp-section-o03-title">
                        <div class="apics-insp-section__head">
                            <div class="apics-insp-section__head-main">
                                <span class="apics-insp-section__icon apics-insp-section__icon--notes" aria-hidden="true">
                                    <i class="ri-file-list-3-line"></i>
                                </span>
                                <div>
                                    <h6 class="apics-insp-section__title" id="insp-section-o03-title">Individual inspector notes</h6>
                                    <p class="apics-insp-section__hint">O-03 · Site observations and recommendations</p>
                                </div>
                            </div>
                            <span class="apics-insp-form-chip apics-insp-form-chip--primary">O-03</span>
                        </div>
                        <div class="apics-insp-section__body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="insp-complete-weather">Weather / access</label>
                                    <input id="insp-complete-weather" type="text" class="form-control" maxlength="255" placeholder="e.g. Fair · gate accessible">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="insp-complete-site">Site conditions</label>
                                    <input id="insp-complete-site" type="text" class="form-control" maxlength="2000" placeholder="e.g. Clear staging area">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="insp-complete-findings">Findings</label>
                                    <textarea id="insp-complete-findings" class="form-control" rows="3" maxlength="5000" placeholder="Record observed conditions relative to approved plans…"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="insp-complete-defects">Observed defects</label>
                                    <textarea id="insp-complete-defects" class="form-control" rows="2" maxlength="5000" placeholder="Non-conformances, if any…"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="insp-complete-recommendations">Recommendations</label>
                                    <textarea id="insp-complete-recommendations" class="form-control" rows="2" maxlength="5000" placeholder="Corrective actions or next steps…"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="apics-insp-section" aria-labelledby="insp-section-qms65-title">
                        <div class="apics-insp-section__head">
                            <div class="apics-insp-section__head-main">
                                <span class="apics-insp-section__icon apics-insp-section__icon--compliance" aria-hidden="true">
                                    <i class="ri-checkbox-multiple-line"></i>
                                </span>
                                <div>
                                    <h6 class="apics-insp-section__title" id="insp-section-qms65-title">Compliance sheet</h6>
                                    <p class="apics-insp-section__hint">QMS-65 · Mark each item OK, Fail, or N/A</p>
                                </div>
                            </div>
                            <span class="apics-insp-form-chip apics-insp-form-chip--success">QMS-65</span>
                        </div>
                        <div class="apics-insp-section__body">
                            <div id="insp-complete-compliance" class="apics-insp-checklist mb-3"></div>
                            <label class="form-label" for="insp-complete-overall">Overall remarks</label>
                            <textarea id="insp-complete-overall" class="form-control" rows="2" maxlength="5000" placeholder="Summary of compliance findings…"></textarea>
                        </div>
                    </section>

                    <section class="apics-insp-section d-none" id="insp-complete-electrical-section" aria-labelledby="insp-section-elec-title">
                        <div class="apics-insp-section__head">
                            <div class="apics-insp-section__head-main">
                                <span class="apics-insp-section__icon apics-insp-section__icon--electrical" aria-hidden="true">
                                    <i class="ri-flashlight-line"></i>
                                </span>
                                <div>
                                    <h6 class="apics-insp-section__title" id="insp-section-elec-title">Final electrical inspection</h6>
                                    <p class="apics-insp-section__hint">DPWH Form 77-006-E · Required for electrical / final types</p>
                                </div>
                            </div>
                            <span class="apics-insp-form-chip apics-insp-form-chip--warning">77-006-E</span>
                        </div>
                        <div class="apics-insp-section__body">
                            <div id="insp-complete-electrical" class="mb-3"></div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="insp-complete-elec-result">Electrical result</label>
                                    <select id="insp-complete-elec-result" class="form-select">
                                        <option value="na">N/A</option>
                                        <option value="passed">Passed</option>
                                        <option value="conditional">Conditional</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="insp-complete-elec-remarks">Electrical remarks</label>
                                    <textarea id="insp-complete-elec-remarks" class="form-control" rows="2" maxlength="5000" placeholder="Notes on electrical installations…"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="modal-footer flex-wrap">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <div class="ms-md-auto d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-soft-primary" id="btn-insp-save-only">
                            <i class="ri-save-line align-bottom me-1" aria-hidden="true"></i>Save only
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn-insp-save-complete">
                            <i class="ri-checkbox-circle-line align-bottom me-1" aria-hidden="true"></i>Save &amp; complete
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
