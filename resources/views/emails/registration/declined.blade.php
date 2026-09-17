<x-mail::message>
# Registration declined

Hello {{ $applicantName }},

Your APICS registration request was **declined**. You cannot sign in with this account.

**Reason:** {{ $reason }}

If you believe this was an error, contact the Office of the City Building Official (OCBO).

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
