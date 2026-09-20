@php
    $phone = config('apics_public.phone');
    $email = config('apics_public.email');
    $hours = config('apics_public.office_hours');
@endphp
<tr>
    <td class="apics-email-footer" style="padding:20px 28px 24px 28px;background-color:#F8FAFC;border-top:1px solid #E2E8F0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.55;color:#64748B;">
        <p style="margin:0 0 8px 0;font-weight:600;color:#475569;">
            {{ $agency }} · {{ $lgu }}
        </p>
        @if ($phone || $email || $hours)
            <p style="margin:0 0 8px 0;">
                @if ($phone) Tel: {{ $phone }}@endif
                @if ($phone && $email) · @endif
                @if ($email) {{ $email }}@endif
                @if (($phone || $email) && $hours)<br>@endif
                @if ($hours){{ $hours }}@endif
            </p>
        @endif
        <p style="margin:0;">
            This is a system-generated message from APICS · CSFP OCBO. Please do not reply to this email unless instructed.
        </p>
    </td>
</tr>
