<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('home') }}" class="logo logo-dark">
                        <span class="logo-sm"><x-branding.logo :height="28" class="rounded-circle" /></span>
                        {{-- No Bootstrap display utilities on logo-lg/sm — Velzon toggles them on sidebar collapse --}}
                        <span class="logo-lg">
                            <span class="logo-text d-inline-flex align-items-center gap-2">
                                <x-branding.logo :height="28" class="rounded-circle" />
                                <span class="fs-16 fw-semibold">APICS</span>
                            </span>
                        </span>
                    </a>
                    <a href="{{ route('home') }}" class="logo logo-light">
                        <span class="logo-sm"><x-branding.logo :height="28" class="rounded-circle" /></span>
                        <span class="logo-lg">
                            <span class="logo-text d-inline-flex align-items-center gap-2">
                                <x-branding.logo :height="28" class="rounded-circle" />
                                <span class="fs-16 fw-semibold text-white">APICS</span>
                            </span>
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown topbar-head-dropdown ms-1 header-item d-none" id="notificationDropdown" data-apics-notif-bell>
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle position-relative"
                        id="page-header-notifications-dropdown"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-label="Notifications">
                        <i class="bx bx-bell fs-22"></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger d-none" data-notif-badge>
                            0<span class="visually-hidden">unread notifications</span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white">Notifications</h6>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-light text-body fs-13" data-notif-new-count>0 New</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="apics-notif-scroll py-2" style="max-height: min(380px, 55vh); overflow-y: auto; -webkit-overflow-scrolling: touch;">
                            <div class="apics-notif-list" data-notif-list>
                                <div class="text-center text-muted py-4 px-3 fs-13">
                                    No notifications yet.
                                </div>
                            </div>
                            <div class="d-none" data-notif-detail></div>
                        </div>
                        <div class="p-2 border-top d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-soft-primary flex-grow-1" data-notif-mark-all>
                                Mark all read
                            </button>
                            <a class="btn btn-sm btn-soft-secondary flex-grow-1 d-none" data-notif-view-all href="{{ route('admin.notifications') }}">
                                Manage
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="{{ asset('images/branding/apics-logo.png') }}" alt="APICS user">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">Guest</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Not signed in</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Account</h6>
                        <a class="dropdown-item d-none" data-nav-roles="applicant" href="{{ route('applications.index') }}"><i class="mdi mdi-file-document-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">My Applications</span></a>
                        <a class="dropdown-item d-none" data-nav-roles="admin" data-nav-permissions="applications.manage,audit.view,departments.manage,forms.manage,users.manage,workflow.manage" href="{{ route('admin.dashboard') }}"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Admin Dashboard</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" id="btn-signin-link" href="{{ route('login') }}"><i class="mdi mdi-login text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Sign in</span></a>
                        <a class="dropdown-item d-none" href="#" id="btn-logout"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Sign out</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
