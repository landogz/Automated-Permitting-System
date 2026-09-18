@extends('layouts.velzon.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Operations Console')

@section('page-actions')
    <button type="button" class="btn btn-soft-primary btn-sm" id="btn-dashboard-refresh" aria-label="Refresh dashboard">
        <i class="ri-refresh-line align-bottom me-1"></i> Refresh
    </button>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div id="apics-admin-dashboard" class="apics-dashboard" aria-busy="true">
    {{-- Hero --}}
    <div class="card apics-dashboard__hero mb-3 border-0 overflow-hidden">
        <div class="card-body py-3 py-md-4">
            <div class="d-flex flex-wrap align-items-center gap-3 gap-lg-4">
                <x-branding.logo :height="56" class="rounded-circle shadow-sm flex-shrink-0" />
                <div class="flex-grow-1 min-w-0">
                    <p class="text-uppercase fw-semibold text-muted mb-1 fs-11 letter-spacing-wide">
                        City of San Fernando, Pampanga · OCBO
                    </p>
                    <h2 class="fs-4 fw-semibold mb-1 text-truncate">APICS Operations Console</h2>
                    <p class="text-muted mb-0 fs-13">
                        Live permitting pipeline — evaluation, inspection, payment, releasing, and compliance.
                        <span class="d-none d-sm-inline">Data refreshes via Axios (no page reload).</span>
                    </p>
                </div>
                <div class="text-sm-end flex-shrink-0">
                    <p class="text-muted mb-0 fs-12 text-uppercase fw-semibold">As of</p>
                    <p class="mb-0 fw-semibold font-monospace fs-13" data-dash-generated>—</p>
                    <span class="badge bg-success-subtle text-success mt-1" data-dash-live>
                        <span class="apics-status__dot me-1" aria-hidden="true"></span> Live
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Primary attention KPIs --}}
    <div class="row g-3 mb-3" id="ops-stats">
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.evaluation-queue') }}" class="card apics-kpi-card apics-kpi-card--amber mb-0 text-decoration-none apics-dashboard__kpi-link" data-nav-permissions="evaluations.manage">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <p class="apics-kpi-card__label mb-0">Evaluation queue</p>
                        <span class="avatar-xs"><span class="avatar-title bg-warning-subtle text-warning rounded-circle"><i class="ri-clipboard-line"></i></span></span>
                    </div>
                    <p class="apics-kpi-card__value" data-dash="evaluation_queue">—</p>
                    <p class="apics-kpi-card__hint">Submitted + under evaluation</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.inspections') }}" class="card apics-kpi-card apics-kpi-card--blue mb-0 text-decoration-none apics-dashboard__kpi-link" data-nav-permissions="inspections.manage">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <p class="apics-kpi-card__label mb-0">Inspections due</p>
                        <span class="avatar-xs"><span class="avatar-title bg-info-subtle text-info rounded-circle"><i class="ri-search-eye-line"></i></span></span>
                    </div>
                    <p class="apics-kpi-card__value" data-dash="inspections_scheduled">—</p>
                    <p class="apics-kpi-card__hint">Scheduled field visits</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.orders-of-payment') }}" class="card apics-kpi-card apics-kpi-card--indigo mb-0 text-decoration-none apics-dashboard__kpi-link" data-nav-permissions="fees.manage">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <p class="apics-kpi-card__label mb-0">For payment</p>
                        <span class="avatar-xs"><span class="avatar-title bg-primary-subtle text-primary rounded-circle"><i class="ri-bill-line"></i></span></span>
                    </div>
                    <p class="apics-kpi-card__value" data-dash="for_payment">—</p>
                    <p class="apics-kpi-card__hint">Ready for G-02 / mark paid</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.logbooks') }}" class="card apics-kpi-card apics-kpi-card--emerald mb-0 text-decoration-none apics-dashboard__kpi-link" data-nav-permissions="records.manage">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <p class="apics-kpi-card__label mb-0">For releasing</p>
                        <span class="avatar-xs"><span class="avatar-title bg-success-subtle text-success rounded-circle"><i class="ri-hand-coin-line"></i></span></span>
                    </div>
                    <p class="apics-kpi-card__value" data-dash="for_releasing">—</p>
                    <p class="apics-kpi-card__hint">Paid — awaiting G-01</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Secondary metrics --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card apics-kpi-card apics-kpi-card--slate mb-0">
                <div class="card-body py-3">
                    <p class="apics-kpi-card__label">Total filings</p>
                    <p class="apics-kpi-card__value fs-4" data-dash="apps_total">—</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card apics-kpi-card apics-kpi-card--emerald mb-0">
                <div class="card-body py-3">
                    <p class="apics-kpi-card__label">Released</p>
                    <p class="apics-kpi-card__value fs-4" data-dash="released">—</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.compliance-notices') }}" class="card apics-kpi-card apics-kpi-card--rose mb-0 text-decoration-none apics-dashboard__kpi-link" data-nav-permissions="compliance.manage">
                <div class="card-body py-3">
                    <p class="apics-kpi-card__label">Compliance open</p>
                    <p class="apics-kpi-card__value fs-4" data-dash="compliance_open">—</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.registrations') }}" class="card apics-kpi-card apics-kpi-card--amber mb-0 text-decoration-none apics-dashboard__kpi-link" data-nav-permissions="users.manage">
                <div class="card-body py-3">
                    <p class="apics-kpi-card__label">Pending regs</p>
                    <p class="apics-kpi-card__value fs-4" data-dash="pending_reg">—</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card apics-kpi-card apics-kpi-card--slate mb-0">
                <div class="card-body py-3">
                    <p class="apics-kpi-card__label">Logbook entries</p>
                    <p class="apics-kpi-card__value fs-4" data-dash="logbooks">—</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.notifications') }}" class="card apics-kpi-card apics-kpi-card--blue mb-0 text-decoration-none apics-dashboard__kpi-link" data-nav-permissions="applications.manage">
                <div class="card-body py-3">
                    <p class="apics-kpi-card__label">Unread notifs</p>
                    <p class="apics-kpi-card__value fs-4" data-dash="unread_notif">—</p>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Pipeline --}}
        <div class="col-lg-7">
            <div class="card h-100 mb-0">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Permit pipeline</h4>
                    <span class="text-muted fs-12" data-dash="pipeline_total"></span>
                </div>
                <div class="card-body">
                    <div class="apics-dashboard__pipeline" data-dash-pipeline aria-live="polite">
                        <div class="placeholder-glow">
                            @for ($i = 0; $i < 6; $i++)
                                <div class="placeholder col-12 mb-3 rounded" style="height: 2.25rem;"></div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attention queue --}}
        <div class="col-lg-5">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h4 class="card-title mb-0">Needs attention</h4>
                    <p class="text-muted fs-12 mb-0 mt-1">Queues with work waiting — open the matching desk.</p>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush apics-dashboard__attention" data-dash-attention>
                        <div class="list-group-item text-muted fs-13 py-4 text-center">Loading queues…</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Recent filings --}}
        <div class="col-12 col-xl-7" data-dash-recent-col>
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                    <h4 class="card-title mb-0 flex-grow-1">Recent filings</h4>
                    <a href="{{ route('admin.evaluation-queue') }}" class="btn btn-sm btn-soft-primary" data-nav-permissions="evaluations.manage">Evaluation queue</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 apics-dashboard__table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Application</th>
                                    <th scope="col" class="d-none d-md-table-cell">Project</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="d-none d-lg-table-cell">Updated</th>
                                </tr>
                            </thead>
                            <tbody data-dash-recent-apps>
                                <tr><td colspan="4" class="text-muted text-center py-4 fs-13">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Audit activity (admin Spatie role only) --}}
        <div class="col-xl-5 d-none" data-nav-roles="admin" data-nav-permissions="audit.view" data-nav-require-role="admin">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                    <h4 class="card-title mb-0 flex-grow-1">Recent activity</h4>
                    <a href="{{ route('admin.audit') }}" class="btn btn-sm btn-soft-danger">Audit trail</a>
                </div>
                <div class="card-body">
                    <ul class="apics-dashboard__activity list-unstyled mb-0" data-dash-activity>
                        <li class="text-muted fs-13 text-center py-3">Loading…</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick access --}}
    <div class="card mb-0">
        <div class="card-header">
            <h4 class="card-title mb-0">Quick access</h4>
            <p class="text-muted fs-12 mb-0 mt-1">Operations desks and administrative tools.</p>
        </div>
        <div class="card-body">
            <div class="row g-3 apics-dashboard__quick">
                @foreach ([
                    ['route' => 'admin.evaluation-queue', 'icon' => 'ri-clipboard-line', 'tone' => 'warning', 'title' => 'Evaluation', 'sub' => 'Classify & decide', 'perm' => 'evaluations.manage'],
                    ['route' => 'admin.inspections', 'icon' => 'ri-search-line', 'tone' => 'info', 'title' => 'Inspections', 'sub' => 'Schedule & complete', 'perm' => 'inspections.manage'],
                    ['route' => 'admin.orders-of-payment', 'icon' => 'ri-bill-line', 'tone' => 'primary', 'title' => 'Orders of Payment', 'sub' => 'G-02 fees', 'perm' => 'fees.manage'],
                    ['route' => 'admin.compliance-notices', 'icon' => 'ri-notification-3-line', 'tone' => 'danger', 'title' => 'Compliance', 'sub' => 'G-03 / G-04', 'perm' => 'compliance.manage'],
                    ['route' => 'admin.logbooks', 'icon' => 'ri-book-open-line', 'tone' => 'success', 'title' => 'Logbooks', 'sub' => 'G-01 releasing', 'perm' => 'records.manage'],
                    ['route' => 'admin.registrations', 'icon' => 'ri-user-follow-line', 'tone' => 'warning', 'title' => 'Registrations', 'sub' => 'Approve applicants', 'perm' => 'users.manage'],
                    ['route' => 'admin.forms', 'icon' => 'ri-survey-line', 'tone' => 'success', 'title' => 'Forms', 'sub' => 'QMS schemas', 'perm' => 'forms.manage'],
                    ['route' => 'admin.departments', 'icon' => 'ri-building-line', 'tone' => 'primary', 'title' => 'Departments', 'sub' => 'Offices & routing', 'perm' => 'departments.manage'],
                    ['route' => 'admin.users', 'icon' => 'ri-team-line', 'tone' => 'info', 'title' => 'Users & roles', 'sub' => 'Staff accounts', 'perm' => 'users.manage'],
                    ['route' => 'admin.audit', 'icon' => 'ri-history-line', 'tone' => 'danger', 'title' => 'Audit trail', 'sub' => 'Mutation log', 'perm' => 'audit.view', 'requireRole' => 'admin'],
                    ['route' => 'admin.project-plan', 'icon' => 'ri-roadmap-line', 'tone' => 'info', 'title' => 'Project plan', 'sub' => 'Phases 0–8', 'perm' => 'audit.view'],
                    ['route' => 'admin.archives', 'icon' => 'ri-archive-line', 'tone' => 'secondary', 'title' => 'Archives', 'sub' => 'Records vault', 'perm' => 'records.manage'],
                ] as $item)
                    <div class="col-6 col-md-4 col-xl-3" data-nav-permissions="{{ $item['perm'] }}" @if (!empty($item['requireRole'])) data-nav-roles="admin" data-nav-require-role="{{ $item['requireRole'] }}" @else data-nav-roles="admin" @endif>
                        <a href="{{ route($item['route']) }}" class="apics-dashboard__quick-card">
                            <span class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-{{ $item['tone'] }}-subtle text-{{ $item['tone'] }} rounded-2 fs-18">
                                    <i class="{{ $item['icon'] }}"></i>
                                </span>
                            </span>
                            <span class="min-w-0">
                                <span class="d-block fw-semibold text-body text-truncate">{{ $item['title'] }}</span>
                                <span class="d-block text-muted fs-12 text-truncate">{{ $item['sub'] }}</span>
                            </span>
                            <i class="ri-arrow-right-s-line text-muted ms-auto flex-shrink-0" aria-hidden="true"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
