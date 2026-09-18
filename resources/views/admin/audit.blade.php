@extends('layouts.velzon.app')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Audit</li>
@endsection

@section('content')
<div id="apics-audit-console" class="apics-audit-console" aria-busy="true">
    <div class="row g-3 mb-3" data-audit-kpi>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--slate mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Total events (24h)</p>
                    <p class="apics-kpi-card__value" data-audit-stat="events_24h">—</p>
                    <p class="apics-kpi-card__hint">Across all OCBO services</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--indigo mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Active sessions</p>
                    <p class="apics-kpi-card__value" data-audit-stat="active_sessions">—</p>
                    <p class="apics-kpi-card__hint">Authenticated staff &amp; applicants</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--amber mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Fee &amp; assessment overrides</p>
                    <p class="apics-kpi-card__value" data-audit-stat="fee_overrides_24h">—</p>
                    <p class="apics-kpi-card__hint">Requires audit verification</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card apics-kpi-card apics-kpi-card--emerald mb-0">
                <div class="card-body">
                    <p class="apics-kpi-card__label">Security anomaly / failed auth</p>
                    <p class="apics-kpi-card__value" data-audit-stat="security_anomalies_24h">—</p>
                    <p class="apics-kpi-card__hint">Unauthorized / failed attempts</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-0">
        <div class="card-header d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div>
                <h4 class="card-title mb-1">Immutable event logbook</h4>
                <p class="text-muted mb-0 fs-13">
                    Tamper-evident OCBO audit trail — who did what to which record, when, and from where.
                </p>
            </div>
            <button type="button" class="btn btn-soft-secondary btn-sm" data-audit-refresh>
                <i class="ri-refresh-line align-middle"></i> Refresh
            </button>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3 apics-audit-console__range" data-audit-range-bar>
                <div class="col-12 col-md-3">
                    <label class="form-label fs-12 mb-1" for="audit-range">Date range</label>
                    <select id="audit-range" class="form-select form-select-sm" data-audit-range>
                        <option value="">All time</option>
                        <option value="today">Today</option>
                        <option value="24h" selected>Last 24 hours</option>
                        <option value="7d">Last 7 days</option>
                        <option value="custom">Custom range</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-none" data-audit-custom-dates>
                    <label class="form-label fs-12 mb-1" for="audit-date-from">From</label>
                    <input type="date" id="audit-date-from" class="form-control form-control-sm" data-audit-date-from>
                </div>
                <div class="col-6 col-md-3 d-none" data-audit-custom-dates>
                    <label class="form-label fs-12 mb-1" for="audit-date-to">To</label>
                    <input type="date" id="audit-date-to" class="form-control form-control-sm" data-audit-date-to>
                </div>
            </div>

            <div class="apics-dt-shell">
                <table id="audit-table" class="table table-hover align-middle w-100 apics-datatable apics-audit-table">
                    <thead class="table-light"></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Inspect / Diff drawer --}}
<div class="offcanvas offcanvas-end apics-audit-offcanvas" tabindex="-1" id="audit-inspect-drawer" aria-labelledby="audit-inspect-title">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-0" id="audit-inspect-title">Inspect event</h5>
            <p class="text-muted fs-12 mb-0 mt-1" data-audit-inspect-event>—</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" data-audit-inspect-body>
        <div class="text-center text-muted py-5">
            <div class="spinner-border text-primary avatar-sm" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
        </div>
    </div>
</div>
@endsection
