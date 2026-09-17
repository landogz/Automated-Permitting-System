<x-mail::message>
# New registration pending

A new applicant registration requires review.

- **Name:** {{ $applicantName }}
- **Email:** {{ $applicantEmail }}
- **Phone:** {{ $applicantPhone ?? '—' }}

<x-mail::button :url="$reviewUrl">
Review registrations
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
