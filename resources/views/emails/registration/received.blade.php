@extends('emails.layouts.apics', [
    'preheader' => 'We received your APICS registration. Your account is pending admin approval.',
    'eyebrow' => 'Registration · Received',
    'title' => 'Registration received',
])

@section('content')
    <p style="margin:0 0 14px 0;">Hello {{ $applicantName }},</p>
    <p style="margin:0 0 14px 0;">
        Thank you for registering with <strong>APICS</strong> (Automated Permitting, Inspection, and Compliance System)
        for the Office of the City Building Official, City of San Fernando, Pampanga.
    </p>
    @include('emails.partials.panel', [
        'tone' => 'info',
        'heading' => 'Pending approval',
        'body' => 'Your account is pending admin approval. You will receive another email when your registration is approved or declined. You cannot sign in until an administrator approves your account.',
    ])
    <p style="margin:8px 0 0 0;color:#64748B;font-size:14px;">
        Thank you,<br>
        {{ config('app.name') }}
    </p>
@endsection
