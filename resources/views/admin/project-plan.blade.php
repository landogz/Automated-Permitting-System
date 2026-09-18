@extends('layouts.velzon.app')

@section('title', 'APICS Project Plan')
@section('page-title', 'APICS Project Plan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Project Plan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body" id="project-plan-root" data-project-plan-page>
                <div class="placeholder-glow" aria-busy="true" aria-live="polite">
                    <span class="placeholder col-7 mb-2 d-block"></span>
                    <span class="placeholder col-4 mb-4 d-block"></span>
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3"><span class="placeholder col-12 rounded" style="height: 4.5rem;"></span></div>
                        <div class="col-6 col-md-3"><span class="placeholder col-12 rounded" style="height: 4.5rem;"></span></div>
                        <div class="col-6 col-md-3"><span class="placeholder col-12 rounded" style="height: 4.5rem;"></span></div>
                        <div class="col-6 col-md-3"><span class="placeholder col-12 rounded" style="height: 4.5rem;"></span></div>
                    </div>
                    <span class="placeholder col-12 rounded" style="height: 0.75rem;"></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
