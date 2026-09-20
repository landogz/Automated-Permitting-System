{{--
  APICS transactional email shell — table-based, mobile-first, multi-client safe.
  Expects optional: $preheader, $eyebrow, $title (or @section('title'))
--}}
@php
    $agency = config('apics_public.agency_name', 'Office of the City Building Official');
    $lgu = config('apics_public.lgu_name', 'City Government of San Fernando, Pampanga');
    $preheader = $preheader ?? '';
    $eyebrow = $eyebrow ?? '';
    $title = $title ?? trim($__env->yieldContent('title'));
    $sealUrl = rtrim((string) config('app.url'), '/').'/images/branding/csfp-seal.png';
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $title !== '' ? $title.' · APICS' : 'APICS' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    @include('emails.partials.styles')
</head>
<body style="margin:0;padding:0;width:100% !important;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#F1F5F9;">
    @if ($preheader !== '')
        <div class="apics-email-preheader" style="display:none !important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;max-height:0;max-width:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;">
            {{ $preheader }}
        </div>
    @endif

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;background-color:#F1F5F9;margin:0;padding:0;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" class="apics-email-container" style="width:100%;max-width:600px;background-color:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;overflow:hidden;">
                    @include('emails.partials.header', ['sealUrl' => $sealUrl, 'agency' => $agency, 'lgu' => $lgu])

                    <tr>
                        <td class="apics-email-body" style="padding:28px 28px 8px 28px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#334155;font-size:16px;line-height:1.6;">
                            @if ($eyebrow !== '')
                                <p style="margin:0 0 8px 0;font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#0284C7;">
                                    {{ $eyebrow }}
                                </p>
                            @endif
                            @if ($title !== '')
                                <h1 style="margin:0 0 16px 0;font-size:22px;line-height:1.3;font-weight:700;color:#0F172A;">
                                    {{ $title }}
                                </h1>
                            @endif

                            @yield('content')
                        </td>
                    </tr>

                    @include('emails.partials.footer', ['agency' => $agency, 'lgu' => $lgu])
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
