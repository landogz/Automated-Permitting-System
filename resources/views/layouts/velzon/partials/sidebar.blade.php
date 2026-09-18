@php
    // Role-aware + permission-aware nav.
    // data-nav-roles="guest|applicant|admin" (comma-separated). Office staff use "admin".
    // data-nav-permissions="perm.a,perm.b" — show if user has ANY listed Spatie permission.
    // Wired by resources/js/modules/layout/shell.ts
@endphp
<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ route('home') }}" class="logo logo-dark">
            <span class="logo-sm"><x-branding.logo :height="34" class="rounded-circle" /></span>
            {{-- No Bootstrap display utilities on logo-lg/sm — Velzon toggles them on sidebar collapse --}}
            <span class="logo-lg">
                <span class="logo-text apics-brand-lockup">
                    <x-branding.logo :height="40" class="rounded-circle" />
                    <span class="apics-brand-lockup__name">APICS</span>
                </span>
            </span>
        </a>
        <a href="{{ route('home') }}" class="logo logo-light">
            <span class="logo-sm"><x-branding.logo :height="34" class="rounded-circle" /></span>
            <span class="logo-lg">
                <span class="logo-text apics-brand-lockup">
                    <x-branding.logo :height="40" class="rounded-circle" />
                    <span class="apics-brand-lockup__name apics-brand-lockup__name--light">APICS</span>
                </span>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                {{-- Public / applicant --}}
                <li class="menu-title d-none" data-nav-roles="guest,applicant"><span>Menu</span></li>

                <li class="nav-item d-none" data-nav-roles="guest,applicant">
                    <a class="nav-link menu-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="ri-home-4-line"></i> <span>Home</span>
                    </a>
                </li>

                <li class="nav-item d-none" data-nav-roles="applicant">
                    <a class="nav-link menu-link {{ request()->routeIs('applications.*') ? 'active' : '' }}" href="{{ route('applications.index') }}">
                        <i class="ri-file-list-3-line"></i> <span>My Applications</span>
                    </a>
                </li>

                <li class="nav-item d-none" data-nav-roles="guest">
                    <a class="nav-link menu-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                        <i class="ri-login-box-line"></i> <span>Sign In</span>
                    </a>
                </li>

                <li class="nav-item d-none" data-nav-roles="guest">
                    <a class="nav-link menu-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
                        <i class="ri-user-add-line"></i> <span>Register</span>
                    </a>
                </li>

                {{-- Overview --}}
                <li class="menu-title d-none" data-nav-roles="admin"><span>Overview</span></li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="applications.manage,audit.view,departments.manage,forms.manage,users.manage,workflow.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                {{-- Access & accounts --}}
                <li class="menu-title d-none" data-nav-roles="admin" data-nav-permissions="users.manage"><span>Access &amp; Accounts</span></li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="users.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.registrations') ? 'active' : '' }}" href="{{ route('admin.registrations') }}">
                        <i class="ri-user-follow-line"></i> <span>Registrations</span>
                        <span class="apics-nav-badge apics-nav-badge--amber is-empty" data-nav-badge="pending_registrations" aria-label="Pending registrations"></span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="users.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                        <i class="ri-user-settings-line"></i> <span>Users &amp; Roles</span>
                    </a>
                </li>

                {{-- Master data --}}
                <li class="menu-title d-none" data-nav-roles="admin" data-nav-permissions="departments.manage,forms.manage"><span>Master Data</span></li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="departments.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.departments') ? 'active' : '' }}" href="{{ route('admin.departments') }}">
                        <i class="ri-building-line"></i> <span>Departments</span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="forms.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.forms') ? 'active' : '' }}" href="{{ route('admin.forms') }}">
                        <i class="ri-survey-line"></i> <span>Form Definitions</span>
                    </a>
                </li>

                {{-- Workflow configuration --}}
                <li class="menu-title d-none" data-nav-roles="admin" data-nav-permissions="workflow.manage"><span>Workflow Config</span></li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="workflow.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.classification-rules') ? 'active' : '' }}" href="{{ route('admin.classification-rules') }}">
                        <i class="ri-organization-chart"></i> <span>Classifier Rules</span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="workflow.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.routing-templates') ? 'active' : '' }}" href="{{ route('admin.routing-templates') }}">
                        <i class="ri-route-line"></i> <span>Routing Templates</span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="workflow.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.fee-rules') ? 'active' : '' }}" href="{{ route('admin.fee-rules') }}">
                        <i class="ri-money-dollar-circle-line"></i> <span>Fee Rules</span>
                    </a>
                </li>

                {{-- Day-to-day operations (icons aligned with the rest of the nav) --}}
                <li class="menu-title d-none" data-nav-roles="admin" data-nav-permissions="applications.manage"><span>Operations</span></li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="applications.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.evaluation-queue') ? 'active' : '' }}" href="{{ route('admin.evaluation-queue') }}" data-ops-permission="evaluations.manage">
                        <i class="ri-clipboard-line"></i> <span>Evaluation Queue</span>
                        <span class="apics-nav-badge apics-nav-badge--blue is-empty" data-nav-badge="evaluation_queue" aria-label="Applications in evaluation queue"></span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="applications.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.inspections') ? 'active' : '' }}" href="{{ route('admin.inspections') }}" data-ops-permission="inspections.manage">
                        <i class="ri-search-line"></i> <span>Inspections</span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="applications.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.orders-of-payment') ? 'active' : '' }}" href="{{ route('admin.orders-of-payment') }}" data-ops-permission="fees.manage">
                        <i class="ri-bill-line"></i> <span>Orders of Payment</span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="applications.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.compliance-notices') ? 'active' : '' }}" href="{{ route('admin.compliance-notices') }}" data-ops-permission="compliance.manage">
                        <i class="ri-notification-3-line"></i> <span>Compliance Notices</span>
                        <span class="apics-nav-badge apics-nav-badge--rose is-empty" data-nav-badge="open_compliance" aria-label="Open compliance notices"></span>
                    </a>
                </li>

                {{-- Records & communications --}}
                <li class="menu-title d-none" data-nav-roles="admin" data-nav-permissions="records.manage,applications.manage"><span>Records &amp; Comms</span></li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="records.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.logbooks') || request()->routeIs('admin.logbooks.print') ? 'active' : '' }}" href="{{ route('admin.logbooks') }}">
                        <i class="ri-book-2-line"></i> <span>Logbooks</span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="records.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.archives') ? 'active' : '' }}" href="{{ route('admin.archives') }}">
                        <i class="ri-archive-drawer-line"></i> <span>Archives</span>
                    </a>
                </li>
                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="applications.manage">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.notifications') ? 'active' : '' }}" href="{{ route('admin.notifications') }}">
                        <i class="ri-mail-send-line"></i> <span>Notifications</span>
                    </a>
                </li>

                {{-- Governance --}}
                <li class="menu-title d-none" data-nav-roles="admin" data-nav-permissions="audit.view"><span>Governance</span></li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="audit.view">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.project-plan') ? 'active' : '' }}" href="{{ route('admin.project-plan') }}">
                        <i class="ri-roadmap-line"></i> <span>Project Plan</span>
                    </a>
                </li>

                <li class="nav-item d-none" data-nav-roles="admin" data-nav-permissions="audit.view" data-nav-require-role="admin">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}" href="{{ route('admin.audit') }}">
                        <i class="ri-history-line"></i> <span>Audit Trail</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
