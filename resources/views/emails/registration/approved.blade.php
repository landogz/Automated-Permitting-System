@extends('emails.layouts.apics', [
    'preheader' => 'Your APICS applicant account has been approved. You may now sign in.',
    'eyebrow' => 'Registration · Approved',
    'title' => 'Account approved',
])

@section('content')
    <p style="margin:0 0 14px 0;">Hello {{ $applicantName }},</p>
    <p style="margin:0 0 14px 0;">
        Your APICS applicant account has been <strong style="color:#15803D;">approved</strong>.
        You may now sign in and submit building permit applications with the Office of the City Building Official.
    </p>
    @include('emails.partials.panel', [
        'tone' => 'success',
        'heading' => 'Next step',
        'body' => 'Sign in to open the Citizens Portal and start a new application when you are ready.',
    ])
    @include('emails.partials.button', [
        'url' => $loginUrl,
        'label' => 'Sign in to APICS',
    ])
    <p style="margin:8px 0 0 0;color:#64748B;font-size:14px;">
        Thank you,<br>
        {{ config('app.name') }}
    </p>
@endsection
