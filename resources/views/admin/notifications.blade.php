@extends('layouts.velzon.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h4 class="card-title mb-0 flex-grow-1">Inbox</h4>
                <button type="button" class="btn btn-soft-primary btn-sm" id="btn-mark-all-read">Mark all read</button>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-send-notification">Send</button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="notifications-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h4 class="card-title mb-0 flex-grow-1">Templates</h4>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-add-template">Add</button>
            </div>
            <div class="card-body">
                <div class="apics-dt-shell">
                    <table id="notif-templates-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-send-notification" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send status notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-send-notification">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="notif-user-uuid">Recipient user UUID</label>
                        <input id="notif-user-uuid" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notif-channel">Channel</label>
                        <select id="notif-channel" class="form-select">
                            <option value="in_app">In-app</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="notif-message">Message</label>
                        <textarea id="notif-message" class="form-control" rows="3" placeholder="Optional custom message for @{{message}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
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
