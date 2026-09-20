{{-- Label / value meta row inside a panel or body --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="apics-email-meta" style="margin:0 0 8px 0;">
    <tr>
        <td width="120" valign="top" style="padding:4px 12px 4px 0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748B;white-space:nowrap;">
            {{ $label }}
        </td>
        <td valign="top" style="padding:4px 0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;color:#0F172A;word-break:break-word;">
            {{ $value }}
        </td>
    </tr>
</table>
