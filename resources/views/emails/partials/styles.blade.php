{{-- Inline + media-query styles for APICS HTML emails --}}
<style type="text/css">
    :root { color-scheme: light dark; supported-color-schemes: light dark; }
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; }
    img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
    a { color: #0284C7; }

    @media only screen and (max-width: 620px) {
        .apics-email-container { width: 100% !important; max-width: 100% !important; border-radius: 0 !important; }
        .apics-email-body,
        .apics-email-footer,
        .apics-email-header { padding-left: 18px !important; padding-right: 18px !important; }
        .apics-email-title { font-size: 20px !important; }
        .apics-email-btn a {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            text-align: center !important;
        }
        .apics-email-meta td {
            display: block !important;
            width: 100% !important;
            padding-bottom: 4px !important;
        }
        .apics-email-seal { width: 48px !important; height: 48px !important; }
    }

    @media (prefers-color-scheme: dark) {
        body, .apics-email-canvas { background-color: #0F172A !important; }
        .apics-email-container { background-color: #1E293B !important; border-color: #334155 !important; }
        .apics-email-body, .apics-email-body p, .apics-email-body li { color: #E2E8F0 !important; }
        .apics-email-body h1 { color: #F8FAFC !important; }
        .apics-email-footer { background-color: #0F172A !important; color: #94A3B8 !important; border-top-color: #334155 !important; }
        .apics-email-panel { background-color: #0F172A !important; border-color: #334155 !important; }
        .apics-email-header { background-color: #0F172A !important; border-bottom-color: #334155 !important; }
        .apics-email-header p { color: #CBD5E1 !important; }
        .apics-email-header strong { color: #F8FAFC !important; }
    }
</style>
