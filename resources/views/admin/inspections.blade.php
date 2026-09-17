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
    completed-hint="Passed, failed, or cancelled inspections."
>
    <x-slot:active>
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <h4 class="card-title mb-1">Active inspections</h4>
                    <p class="text-muted mb-0 fs-13">
                        Scheduled / in progress. Use <strong>Inspect</strong> before completing.
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

<div class="modal fade" id="modal-schedule-inspection" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule inspection</h5>
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
                    <div class="mb-3">
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
                    <div class="mb-3">
                        <label class="form-label" for="insp-scheduled-at">Scheduled at</label>
                        <input id="insp-scheduled-at" type="datetime-local" class="form-control">
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
@endsection
