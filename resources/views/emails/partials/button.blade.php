{{--
  Email CTA button (Outlook-safe).
  @var string $url
  @var string $label
  @var string|null $variant  primary|secondary
--}}
@php
    $variant = $variant ?? 'primary';
    $bg = $variant === 'secondary' ? '#0F172A' : '#FF2222';
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" class="apics-email-btn" style="margin:24px 0 8px 0;">
    <tr>
        <td align="center" bgcolor="{{ $bg }}" style="border-radius:8px;background-color:{{ $bg }};">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="17%" stroke="f" fillcolor="{{ $bg }}">
                <w:anchorlock/>
                <center style="color:#ffffff;font-family:Segoe UI,sans-serif;font-size:15px;font-weight:600;">{{ $label }}</center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-- -->
            <a href="{{ $url }}" target="_blank" rel="noopener" style="display:inline-block;padding:14px 28px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;font-weight:600;line-height:1.2;color:#FFFFFF;text-decoration:none;border-radius:8px;background-color:{{ $bg }};min-height:20px;">
                {{ $label }}
            </a>
            <!--<![endif]-->
        </td>
    </tr>
</table>
<p style="margin:0 0 16px 0;font-size:12px;line-height:1.5;color:#64748B;word-break:break-all;">
    Or open: <a href="{{ $url }}" style="color:#0284C7;text-decoration:underline;">{{ $url }}</a>
</p>
