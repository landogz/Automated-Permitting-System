@extends('layouts.velzon.app')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Audit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Event log</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">Immutable events for mutations and authentication.</p>
                <div class="apics-dt-shell">
                    <table id="audit-table" class="table table-bordered table-nowrap align-middle w-100 apics-datatable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
