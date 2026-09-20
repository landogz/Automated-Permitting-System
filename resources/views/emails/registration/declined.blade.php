@extends('emails.layouts.apics', [
    'preheader' => 'Your APICS registration request was declined.',
    'eyebrow' => 'Registration · Declined',
    'title' => 'Registration declined',
])

@section('content')
    <p style="margin:0 0 14px 0;">Hello {{ $applicantName }},</p>
    <p style="margin:0 0 14px 0;">
        Your APICS registration request was <strong style="color:#B91C1C;">declined</strong>.
        You cannot sign in with this account.
    </p>
    @include('emails.partials.panel', [
        'tone' => 'danger',
        'heading' => 'Reason',
        'body' => $reason,
    ])
    <p style="margin:0 0 14px 0;">
        If you believe this was an error, please contact the Office of the City Building Official (OCBO).
    </p>
    <p style="margin:8px 0 0 0;color:#64748B;font-size:14px;">
        Thank you,<br>
        {{ config('app.name') }}
    </p>
@endsection
