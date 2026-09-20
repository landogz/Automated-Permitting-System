@extends('emails.layouts.apics', [
    'preheader' => 'A new applicant registration requires review in APICS.',
    'eyebrow' => 'Admin · Registrations',
    'title' => 'New registration pending',
])

@section('content')
    <p style="margin:0 0 14px 0;">
        A new applicant registration requires review before the account can sign in.
    </p>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="apics-email-panel" style="margin:16px 0;background-color:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;">
        <tr>
            <td style="padding:14px 16px;">
                @include('emails.partials.meta-row', ['label' => 'Name', 'value' => $applicantName])
                @include('emails.partials.meta-row', ['label' => 'Email', 'value' => $applicantEmail])
                @include('emails.partials.meta-row', ['label' => 'Phone', 'value' => $applicantPhone ?: '—'])
            </td>
        </tr>
    </table>
    @include('emails.partials.button', [
        'url' => $reviewUrl,
        'label' => 'Review registrations',
        'variant' => 'secondary',
    ])
    <p style="margin:8px 0 0 0;color:#64748B;font-size:14px;">
        Thank you,<br>
        {{ config('app.name') }}
    </p>
@endsection
