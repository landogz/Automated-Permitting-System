@extends('layouts.velzon.landing')

@section('title', $title ?? 'Information')

@section('content')
<div class="container py-4 py-lg-5 gwt-policy-main">
    <nav aria-label="{{ __('Breadcrumb') }}" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
        </ol>
    </nav>
    <article class="gwt-policy-article">
        <h1 class="h2 fw-semibold mb-3">{{ $title }}</h1>
        @yield('policy-body')
    </article>
</div>
@endsection
