{{ $title }}

{{ $body }}

@if (! empty($actionUrl))
{{ $actionLabel ?? 'Open in APICS' }}: {{ $actionUrl }}
@endif

{{ config('app.name') }}
