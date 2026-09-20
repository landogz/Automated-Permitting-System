{{-- Soft info / reason panel. Pass $body (escaped string) and optional $heading, $tone. --}}
@php
    $tone = $tone ?? 'info'; // info | success | danger | warning
    $borders = [
        'info' => '#BAE6FD',
        'success' => '#BBF7D0',
        'danger' => '#FECACA',
        'warning' => '#FDE68A',
    ];
    $bgs = [
        'info' => '#F0F9FF',
        'success' => '#F0FDF4',
        'danger' => '#FEF2F2',
        'warning' => '#FFFBEB',
    ];
    $border = $borders[$tone] ?? $borders['info'];
    $bg = $bgs[$tone] ?? $bgs['info'];
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="apics-email-panel" style="margin:16px 0;background-color:{{ $bg }};border:1px solid {{ $border }};border-radius:8px;">
    <tr>
        <td style="padding:14px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.55;color:#334155;">
            @isset($heading)
                <p style="margin:0 0 6px 0;font-size:12px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#475569;">
                    {{ $heading }}
                </p>
            @endisset
            <p style="margin:0;">{{ $body }}</p>
        </td>
    </tr>
</table>
