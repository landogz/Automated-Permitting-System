<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'APICS Print')</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --rule: #cbd5e1;
            --band: #f1f5f9;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            font-family: "Public Sans", "Segoe UI", system-ui, -apple-system, sans-serif;
            font-size: 12.5px;
            line-height: 1.45;
            background: #e2e8f0;
        }
        .sheet {
            width: 210mm;
            max-width: 100%;
            min-height: 297mm;
            margin: 16px auto;
            padding: 18mm 16mm 16mm;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        }
        .btn-print {
            appearance: none;
            border: 0;
            border-radius: 6px;
            background: #405189;
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 14px;
            cursor: pointer;
        }
        .gov-letterhead {
            display: grid;
            grid-template-columns: 56px 1fr;
            gap: 12px;
            align-items: center;
            border-bottom: 2px solid var(--ink);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .gov-letterhead img { width: 56px; height: 56px; object-fit: contain; }
        .gov-letterhead__org {
            font-size: 11px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
            margin: 0 0 2px;
        }
        .gov-letterhead__office { font-size: 15px; font-weight: 700; margin: 0 0 2px; }
        .gov-letterhead__system { font-size: 11px; color: var(--muted); margin: 0; }
        .gov-doc-title { text-align: center; margin: 0 0 14px; }
        .gov-doc-title__code {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: var(--band);
            border: 1px solid var(--rule);
            border-radius: 999px;
            padding: 3px 10px;
            margin-bottom: 6px;
        }
        .gov-doc-title h1 { font-size: 18px; margin: 0 0 4px; }
        .gov-doc-title__no {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            font-size: 13px;
            margin: 0;
        }
        .gov-doc-title__sub { color: var(--muted); margin: 4px 0 0; font-size: 12px; }
        .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 16px;
            margin: 0 0 16px;
            padding: 10px 12px;
            background: var(--band);
            border: 1px solid var(--rule);
            border-radius: 6px;
        }
        .meta__label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 2px;
        }
        .meta__value { font-weight: 600; word-break: break-word; }
        .notes {
            border: 1px solid var(--rule);
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 18px;
            white-space: pre-wrap;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 28px;
        }
        .sig { text-align: center; min-height: 72px; }
        .sig__line { border-top: 1px solid var(--ink); margin: 42px 8px 6px; }
        .sig__role {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
        }
        .sig__name { font-weight: 600; font-size: 12px; }
        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid var(--rule);
            font-size: 10px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        @media print {
            body { background: #fff; }
            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 12mm 12mm 10mm;
                box-shadow: none;
            }
            .no-print { display: none !important; }
        }
        @media (max-width: 720px) {
            .meta, .signatures { grid-template-columns: 1fr; }
            .sheet { margin: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="no-print" style="text-align:center;padding:12px">
    <button type="button" class="btn-print" onclick="window.print()">Print / Save PDF</button>
</div>
<article class="sheet">
    @yield('content')
</article>
</body>
</html>
