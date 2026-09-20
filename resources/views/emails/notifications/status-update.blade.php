@extends('emails.layouts.apics', [
    'preheader' => \Illuminate\Support\Str::limit(strip_tags((string) $body), 120),
    'eyebrow' => 'Notification',
    'title' => $title,
])

@section('content')
    <p style="margin:0 0 14px 0;white-space:pre-wrap;">{{ $body }}</p>
    @if (! empty($actionUrl))
        @include('emails.partials.button', [
            'url' => $actionUrl,
            'label' => $actionLabel ?? 'Open in APICS',
        ])
    @endif
    <p style="margin:8px 0 0 0;color:#64748B;font-size:14px;">
        {{ config('app.name') }}
    </p>
@endsection
