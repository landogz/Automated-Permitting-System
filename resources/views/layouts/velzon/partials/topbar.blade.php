<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('home') }}" class="logo logo-dark">
                        <span class="logo-sm"><x-branding.logo :height="30" class="rounded-circle" /></span>
                        {{-- No Bootstrap display utilities on logo-lg/sm — Velzon toggles them on sidebar collapse --}}
                        <span class="logo-lg">
                            <span class="logo-text d-inline-flex align-items-center gap-2">
                                <x-branding.logo :height="30" class="rounded-circle" />
                                <span class="fs-16 fw-semibold">APICS</span>
                            </span>
                        </span>
                    </a>
                    <a href="{{ route('home') }}" class="logo logo-light">
                        <span class="logo-sm"><x-branding.logo :height="30" class="rounded-circle" /></span>
                        <span class="logo-lg">
                            <span class="logo-text d-inline-flex align-items-center gap-2">
                                <x-branding.logo :height="30" class="rounded-circle" />
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
                                View all
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user apics-topbar-user">
                    <button type="button"
                        class="btn material-shadow-none apics-topbar-user__trigger"
                        id="page-header-user-dropdown"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-label="Account menu">
                        <span class="d-flex align-items-center gap-2">
                            <span class="apics-topbar-user__avatar" data-user-avatar aria-hidden="true">AD</span>
                            <span class="text-start d-none d-xl-block min-w-0">
                                <span class="d-block fw-semibold user-name-text text-truncate apics-topbar-user__name">Guest</span>
                                <span class="d-block fs-11 text-muted user-name-sub-text text-truncate apics-topbar-user__role">Not signed in</span>
                            </span>
                            <i class="ri-arrow-down-s-line apics-topbar-user__caret d-none d-xl-inline-block text-muted" aria-hidden="true"></i>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end apics-account-menu p-0">
                        <div class="apics-account-menu__head">
                            <div class="d-flex align-items-center gap-3">
                                <span class="apics-topbar-user__avatar apics-topbar-user__avatar--lg" data-user-avatar-menu aria-hidden="true">AD</span>
                                <div class="min-w-0">
                                    <p class="mb-0 fw-semibold text-truncate user-name-text">Guest</p>
                                    <p class="mb-1 fs-12 text-muted text-truncate" data-user-email>Sign in to continue</p>
                                    <span class="badge apics-account-menu__role-badge user-name-sub-text">Guest</span>
                                </div>
                            </div>
                        </div>
                        <div class="apics-account-menu__body py-2 d-none" data-account-menu-body>
                            <a class="dropdown-item d-none apics-account-menu__item" data-nav-roles="applicant" href="{{ route('applications.index') }}">
                                <span class="apics-account-menu__icon"><i class="ri-file-list-3-line"></i></span>
                                <span>My Applications</span>
                            </a>
                            <a class="dropdown-item d-none apics-account-menu__item" data-nav-roles="admin" data-nav-permissions="applications.manage,audit.view,departments.manage,forms.manage,users.manage,workflow.manage" href="{{ route('admin.dashboard') }}">
                                <span class="apics-account-menu__icon"><i class="ri-dashboard-2-line"></i></span>
                                <span>Admin Dashboard</span>
                            </a>
                            <div class="dropdown-divider my-1 d-none" data-nav-roles="applicant,admin"></div>
                            <a class="dropdown-item d-none apics-account-menu__item" href="#" data-nav-roles="applicant,admin" data-action="edit-profile">
                                <span class="apics-account-menu__icon"><i class="ri-user-settings-line"></i></span>
                                <span>Edit Profile</span>
                            </a>
                            <a class="dropdown-item d-none apics-account-menu__item" href="#" data-nav-roles="applicant,admin" data-action="change-password">
                                <span class="apics-account-menu__icon"><i class="ri-lock-password-line"></i></span>
                                <span>Change Password</span>
                            </a>
                        </div>
                        <div class="apics-account-menu__footer border-top py-2">
                            <a class="dropdown-item d-none apics-account-menu__item" id="btn-signin-link" data-nav-roles="guest" href="{{ route('login') }}">
                                <span class="apics-account-menu__icon"><i class="ri-login-circle-line"></i></span>
                                <span>Sign in</span>
                            </a>
                            <a class="dropdown-item d-none apics-account-menu__item apics-account-menu__item--danger" href="#" id="btn-logout">
                                <span class="apics-account-menu__icon"><i class="ri-logout-box-r-line"></i></span>
                                <span>Sign out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
