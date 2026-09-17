@extends('layouts.velzon.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@php
    $summary = $projectPlan['summary'];
    $percent = $summary['percent'];
@endphp

@section('content')
{{-- Operational KPIs --}}
<div class="row" id="ops-stats">
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Applications</p>
                <h4 class="mb-0" id="stat-apps-total">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Under evaluation</p>
                <h4 class="mb-0 text-warning" id="stat-under-eval">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Inspections due</p>
                <h4 class="mb-0 text-info" id="stat-inspections">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">For payment</p>
                <h4 class="mb-0 text-primary" id="stat-payment">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Released</p>
                <h4 class="mb-0 text-success" id="stat-released">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Pending registrations</p>
                <h4 class="mb-0" id="stat-pending-reg">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Logbook entries</p>
                <h4 class="mb-0" id="stat-logbooks">—</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-1 fs-12">Unread notifications</p>
                <h4 class="mb-0 text-danger" id="stat-unread-notif">—</h4>
            </div>
        </div>
    </div>
</div>

{{-- Project plan progress --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">APICS Project Plan — Delivery Phases 0–8</h5>
                        <p class="text-muted mb-0 small">{{ $projectPlan['project'] }}</p>
                        @if (! empty($projectPlan['source']))
                            <p class="text-muted mb-0 small mt-1">Source: <code>{{ $projectPlan['source'] }}</code></p>
                        @endif
                    </div>
                    <div class="text-md-end">
                        <span class="badge bg-primary-subtle text-primary">Updated {{ $projectPlan['updated_at'] }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Overall</p>
                            <h4 class="mb-0">{{ $percent }}%</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Phases done</p>
                            <h4 class="mb-0 text-success">{{ $summary['completed'] }}/{{ $summary['total'] }}</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-1">In progress</p>
                            <h4 class="mb-0 text-warning">{{ $summary['in_progress'] }}</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Pending</p>
                            <h4 class="mb-0 text-muted">{{ $summary['pending'] }}</h4>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-medium">Delivery phase progress (0–8)</span>
                        <span class="text-muted small">{{ $percent }}% done</span>
                    </div>
                    <div class="progress progress-lg" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Project plan completion">
                        <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                    </div>
                </div>

                <div class="alert alert-primary border-0 mb-4" role="status">
                    <strong>Current focus:</strong> {{ $projectPlan['current_focus'] }}
                </div>

                <div class="accordion" id="projectPlanAccordion">
                    @foreach ($projectPlan['phases'] as $phase)
                        @php
                            $phaseStatus = $phase['status'];
                            $badgeClass = match ($phaseStatus) {
                                'completed' => 'bg-success-subtle text-success',
                                'in_progress' => 'bg-warning-subtle text-warning',
                                default => 'bg-secondary-subtle text-secondary',
                            };
                            $badgeLabel = match ($phaseStatus) {
                                'completed' => 'Completed',
                                'in_progress' => 'In progress',
                                default => 'Pending',
                            };
                            $collapseId = 'phase-'.$phase['id'];
                            $isOpen = $phaseStatus === 'in_progress';
                        @endphp
                        <div class="accordion-item border mb-2 rounded overflow-hidden">
                            <h2 class="accordion-header" id="heading-{{ $phase['id'] }}">
                                <button
                                    class="accordion-button {{ $isOpen ? '' : 'collapsed' }} py-3"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                                    aria-controls="{{ $collapseId }}"
                                >
                                    <span class="d-flex flex-wrap align-items-center gap-2 w-100 pe-3">
                                        <span class="fw-semibold">{{ $phase['title'] }}</span>
                                        @if (! empty($phase['weeks']))
                                            <span class="badge bg-info-subtle text-info">{{ $phase['weeks'] }}</span>
                                        @endif
                                        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                        <span class="ms-auto text-muted small">{{ $phase['progress_percent'] }}%</span>
                                    </span>
                                </button>
                            </h2>
                            <div
                                id="{{ $collapseId }}"
                                class="accordion-collapse collapse {{ $isOpen ? 'show' : '' }}"
                                aria-labelledby="heading-{{ $phase['id'] }}"
                                data-bs-parent="#projectPlanAccordion"
                            >
                                <div class="accordion-body pt-0">
                                    <div class="progress mb-3" style="height: 6px;" role="progressbar" aria-valuenow="{{ $phase['progress_percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $phase['title'] }} progress">
                                        <div class="progress-bar {{ $phaseStatus === 'completed' ? 'bg-success' : ($phaseStatus === 'in_progress' ? 'bg-warning' : 'bg-secondary') }}" style="width: {{ $phase['progress_percent'] }}%"></div>
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        @foreach ($phase['items'] as $item)
                                            @php
                                                $itemDone = $item['status'] === 'completed';
                                                $itemActive = $item['status'] === 'in_progress';
                                            @endphp
                                            <li class="list-group-item d-flex align-items-start gap-2 px-0">
                                                @if ($itemDone)
                                                    <i class="ri-checkbox-circle-fill text-success fs-5 mt-0" aria-hidden="true"></i>
                                                    <span class="visually-hidden">Completed:</span>
                                                @elseif ($itemActive)
                                                    <i class="ri-loader-4-line text-warning fs-5 mt-0" aria-hidden="true"></i>
                                                    <span class="visually-hidden">In progress:</span>
                                                @else
                                                    <i class="ri-checkbox-blank-circle-line text-muted fs-5 mt-0" aria-hidden="true"></i>
                                                    <span class="visually-hidden">Pending:</span>
                                                @endif
                                                <span class="{{ $itemDone ? 'text-muted text-decoration-line-through' : '' }}">{{ $item['label'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (! empty($projectPlan['roadmap']))
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-3">Beyond Phase I (roadmap)</h6>
                        <div class="row g-3">
                            @foreach ($projectPlan['roadmap'] as $road)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-secondary-subtle text-secondary">Later</span>
                                            <span class="fw-semibold">{{ $road['title'] }}</span>
                                        </div>
                                        <p class="text-muted mb-0 small">{{ $road['label'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Quick links --}}
<div class="row">
    <div class="col-12">
        <div class="alert alert-info border-0 mb-4" role="alert">
            Manage master data, forms, and audit logs. All actions are API-driven (Axios). Sign in as admin to load data.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle text-warning rounded-2 fs-2">
                            <i class="ri-user-follow-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden ms-3">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Registrations</p>
                        <h4 class="fs-5 mb-0">Approve / decline</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.registrations') }}" class="btn btn-sm btn-soft-warning">Open</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">
                            <i class="ri-building-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden ms-3">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Departments</p>
                        <h4 class="fs-5 mb-0">CRUD + import/export</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.departments') }}" class="btn btn-sm btn-soft-primary">Open</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded-2 fs-2">
                            <i class="ri-survey-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden ms-3">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Form definitions</p>
                        <h4 class="fs-5 mb-0">QMS schemas</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.forms') }}" class="btn btn-sm btn-soft-success">Open</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-2">
                            <i class="ri-history-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden ms-3">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Audit trail</p>
                        <h4 class="fs-5 mb-0">Mutation events</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.audit') }}" class="btn btn-sm btn-soft-danger">Open</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
