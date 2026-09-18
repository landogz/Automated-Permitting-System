@extends('layouts.velzon.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div id="apics-notif-inbox" class="apics-notif-inbox" aria-busy="true">
    <div class="email-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
        {{-- Sidebar filters --}}
        <div class="email-menu-sidebar minimal-border">
            <div class="p-4 d-flex flex-column h-100">
                <div class="pb-4 border-bottom border-bottom-dashed">
                    <div class="text-center">
                        <div class="avatar-md mx-auto mb-2">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-22">
                                <i class="ri-notification-3-line"></i>
                            </div>
                        </div>
                        <h5 class="fs-15 mb-1">Notification inbox</h5>
                        <p class="text-muted fs-12 mb-0">Same feed as the topbar bell — full history here.</p>
                    </div>
                </div>

                <div class="mx-n4 px-4 email-menu-sidebar-scroll flex-grow-1">
                    <div class="mail-list mt-3" role="navigation" aria-label="Notification folders">
                        <a href="#" class="active" data-notif-filter="all">
                            <i class="ri-mail-fill me-3 align-middle fw-medium"></i>
                            <span class="mail-list-link">All</span>
                            <span class="badge bg-primary-subtle text-primary ms-auto" data-notif-count="total">0</span>
                        </a>
                        <a href="#" data-notif-filter="unread">
                            <i class="ri-mail-unread-fill me-3 align-middle fw-medium"></i>
                            <span class="mail-list-link">Unread</span>
                            <span class="badge bg-danger-subtle text-danger ms-auto" data-notif-count="unread">0</span>
                        </a>
                        <a href="#" data-notif-filter="read">
                            <i class="ri-mail-open-fill me-3 align-middle fw-medium"></i>
                            <span class="mail-list-link">Read</span>
                            <span class="badge bg-success-subtle text-success ms-auto" data-notif-count="read">0</span>
                        </a>
                    </div>

                    <div class="mt-4 d-none" data-notif-admin-tools>
                        <h5 class="fs-12 text-uppercase text-muted">Office tools</h5>
                        <div class="mail-list mt-1">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#modal-templates">
                                <i class="ri-file-list-3-line me-3 align-middle fw-medium"></i>
                                <span class="mail-list-link">Templates</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-top border-top-dashed">
                    <p class="text-muted fs-12 mb-0">
                        <i class="ri-information-line align-middle me-1"></i>
                        Showing your personal OCBO notifications.
                    </p>
                </div>
            </div>
        </div>

        {{-- Message list --}}
        <div class="email-content minimal-border">
            <div class="p-4 pb-0">
                <div class="border-bottom border-bottom-dashed">
                    <div class="row mt-n2 mb-3 mb-sm-0 align-items-center g-2">
                        <div class="col col-sm-auto order-1 d-block d-lg-none">
                            <button type="button" class="btn btn-soft-success btn-icon btn-sm fs-16 email-menu-btn material-shadow-none" aria-label="Open folders">
                                <i class="ri-menu-2-fill align-bottom"></i>
                            </button>
                        </div>
                        <div class="col-sm order-3 order-sm-2">
                            <div class="hstack gap-sm-1 align-items-center flex-wrap email-topbar-link">
                                <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm fs-16 material-shadow-none" data-notif-refresh aria-label="Refresh">
                                    <i class="ri-refresh-line align-bottom"></i>
                                </button>
                                <button type="button" class="btn btn-soft-primary btn-sm" data-notif-mark-all>
                                    <i class="ri-check-double-line align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Mark all read</span>
                                    <span class="d-sm-none">Read all</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 col-md order-4">
                            <div class="search-box">
                                <input type="search"
                                    class="form-control bg-light border-light"
                                    placeholder="Search notifications…"
                                    data-notif-search
                                    autocomplete="off"
                                    aria-label="Search notifications">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center mt-3">
                        <div class="col">
                            <h5 class="fs-15 mb-0" data-notif-folder-label>All notifications</h5>
                            <p class="text-muted fs-12 mb-0 mt-1" data-notif-range>Loading…</p>
                        </div>
                    </div>
                </div>

                <div class="message-list-content mx-n4 px-4 message-list-scroll apics-notif-inbox__list-scroll">
                    <div class="text-center py-5" data-notif-loader>
                        <div class="spinner-border text-primary avatar-sm" role="status">
                            <span class="visually-hidden">Loading…</span>
                        </div>
                    </div>
                    <ul class="message-list" data-notif-mail-list role="list" aria-label="Notification messages"></ul>
                    <div class="text-center text-muted py-5 px-3 d-none" data-notif-empty>
                        <div class="avatar-md mx-auto mb-3">
                            <div class="avatar-title bg-light text-muted rounded-circle fs-24">
                                <i class="ri-inbox-line"></i>
                            </div>
                        </div>
                        <h5 class="fs-15">No notifications</h5>
                        <p class="fs-13 mb-0">When OCBO sends status updates, they will appear here and in the bell.</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 px-1 d-none" data-notif-pager>
                        <button type="button" class="btn btn-soft-secondary btn-sm" data-notif-prev disabled>Previous</button>
                        <span class="text-muted fs-12" data-notif-page-label></span>
                        <button type="button" class="btn btn-soft-secondary btn-sm" data-notif-next disabled>Next</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail pane --}}
        <div class="email-detail-content minimal-border">
            <div class="p-4 d-flex flex-column h-100">
                <div class="pb-3 border-bottom border-bottom-dashed">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <button type="button" class="btn btn-soft-danger btn-icon btn-sm fs-16 close-btn-email material-shadow-none" data-notif-close-detail aria-label="Close notification">
                                <i class="ri-close-fill align-bottom"></i>
                            </button>
                        </div>
                        <div class="col-auto">
                            <div class="hstack gap-1">
                                <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm fs-16 material-shadow-none d-none" data-notif-open-related aria-label="Open related page">
                                    <i class="ri-external-link-line align-bottom"></i>
                                </button>
                                <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm fs-16 material-shadow-none d-none" data-notif-mark-one aria-label="Mark as read">
                                    <i class="ri-mail-open-line align-bottom"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mx-n4 px-4 email-detail-content-scroll flex-grow-1 apics-notif-inbox__detail-scroll" data-notif-detail-body>
                    <div class="text-center text-muted py-5">
                        <p class="fs-13 mb-0">Select a notification to read it.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Templates manager --}}
<div class="modal fade" id="modal-templates" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <p class="text-muted fs-13 mb-0">Manage reusable in-app / email templates.</p>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-template">
                        <i class="ri-add-line align-middle"></i> Add template
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" data-notif-templates-table>
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Channel</th>
                                <th>Active</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-template" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add notification template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-template">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="tpl-code">Code</label>
                        <input id="tpl-code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tpl-name">Name</label>
                        <input id="tpl-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tpl-channel">Channel</label>
                        <select id="tpl-channel" class="form-select">
                            <option value="in_app">In-app</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="tpl-subject">Subject</label>
                        <input id="tpl-subject" class="form-control" placeholder="Hello @{{name}}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="tpl-body">Body template</label>
                        <textarea id="tpl-body" class="form-control" rows="4" required placeholder="Use @{{name}} and @{{message}} tokens"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
