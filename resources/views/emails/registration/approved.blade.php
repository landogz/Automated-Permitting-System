<x-mail::message>
# Account approved

Hello {{ $applicantName }},

Your APICS applicant account has been **approved**. You may now sign in and submit permit applications.

<x-mail::button :url="$loginUrl">
Sign in
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
