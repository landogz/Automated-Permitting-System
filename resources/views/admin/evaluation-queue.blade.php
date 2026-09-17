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
@endsection
