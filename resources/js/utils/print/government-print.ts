/**
 * Shared government / LGU print helpers for APICS official slips.
 * See `.cursor/rules/government-print-format.mdc`.
 */

import { escapeHtml } from '../bootstrap-modal';
import { toastError } from '../toast';

export type GovernmentPrintMeta = {
    label: string;
    value: string;
};

export type GovernmentPrintColumn = {
    key: string;
    label: string;
    align?: 'left' | 'right' | 'center';
};

export type GovernmentPrintRow = Record<string, string>;

export type GovernmentPrintOptions = {
    /** Form code, e.g. G-02 */
    formCode: string;
    /** Form title, e.g. Order of Payment */
    formTitle: string;
    /** Primary control number shown under the title */
    documentNo: string;
    /** Optional subtitle under the document number */
    subtitle?: string;
    /** Key facts in a 2-column meta grid */
    meta?: GovernmentPrintMeta[];
    /** Optional HTML block rendered before the table (already escaped by caller) */
    bodyHtml?: string;
    columns?: GovernmentPrintColumn[];
    rows?: GovernmentPrintRow[];
    /** Right-aligned totals row label + value */
    totalLabel?: string;
    totalValue?: string;
    /** Signature lines */
    signatures?: Array<{ role: string; name?: string }>;
    /** Auto-open browser print dialog (default true) */
    autoPrint?: boolean;
    windowTitle?: string;
};

const LOGO_URL = '/images/branding/apics-logo.png';

function printStyles(): string {
    return `
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
    .no-print { margin-bottom: 12px; }
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
    .letterhead {
      display: grid;
      grid-template-columns: 56px 1fr;
      gap: 12px;
      align-items: center;
      border-bottom: 2px solid var(--ink);
      padding-bottom: 12px;
      margin-bottom: 14px;
    }
    .letterhead img {
      width: 56px;
      height: 56px;
      object-fit: contain;
    }
    .letterhead__org {
      font-size: 11px;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: var(--muted);
      margin: 0 0 2px;
    }
    .letterhead__office {
      font-size: 15px;
      font-weight: 700;
      margin: 0 0 2px;
    }
    .letterhead__system {
      font-size: 11px;
      color: var(--muted);
      margin: 0;
    }
    .doc-title {
      text-align: center;
      margin: 0 0 14px;
    }
    .doc-title__code {
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
    .doc-title h1 {
      font-size: 18px;
      margin: 0 0 4px;
      letter-spacing: 0.02em;
    }
    .doc-title__no {
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-weight: 700;
      font-size: 13px;
      margin: 0;
    }
    .doc-title__sub {
      color: var(--muted);
      margin: 4px 0 0;
      font-size: 12px;
    }
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
    .meta__item { min-width: 0; }
    .meta__label {
      display: block;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--muted);
      margin-bottom: 2px;
    }
    .meta__value {
      font-weight: 600;
      word-break: break-word;
    }
    table.data {
      width: 100%;
      border-collapse: collapse;
      margin: 0 0 18px;
    }
    table.data th,
    table.data td {
      border: 1px solid var(--rule);
      padding: 7px 8px;
      vertical-align: top;
    }
    table.data th {
      background: #f8fafc;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--muted);
      text-align: left;
    }
    table.data td.num,
    table.data th.num { text-align: right; font-variant-numeric: tabular-nums; }
    table.data tfoot th {
      background: #fff;
      color: var(--ink);
      font-size: 12px;
      text-transform: none;
      letter-spacing: 0;
    }
    .body-block { margin: 0 0 16px; }
    .signatures {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 18px;
      margin-top: 28px;
    }
    .sig {
      text-align: center;
      min-height: 72px;
    }
    .sig__line {
      border-top: 1px solid var(--ink);
      margin: 42px 8px 6px;
    }
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
      a { color: inherit; text-decoration: none; }
    }
    @media (max-width: 720px) {
      .sheet { margin: 0; border-radius: 0; min-height: auto; }
      .meta, .signatures { grid-template-columns: 1fr; }
    }
  `;
}

export function buildGovernmentPrintHtml(options: GovernmentPrintOptions): string {
    const printedAt = new Date().toLocaleString('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });

    const metaHtml = (options.meta || [])
        .map(
            (item) => `<div class="meta__item">
          <span class="meta__label">${escapeHtml(item.label)}</span>
          <span class="meta__value">${escapeHtml(item.value || '—')}</span>
        </div>`,
        )
        .join('');

    const columns = options.columns || [];
    const rows = options.rows || [];
    const tableHtml =
        columns.length > 0
            ? `<table class="data">
        <thead>
          <tr>${columns
              .map(
                  (col) =>
                      `<th class="${col.align === 'right' ? 'num' : ''}">${escapeHtml(col.label)}</th>`,
              )
              .join('')}</tr>
        </thead>
        <tbody>
          ${
              rows.length
                  ? rows
                        .map(
                            (row) => `<tr>${columns
                                .map(
                                    (col) =>
                                        `<td class="${col.align === 'right' ? 'num' : ''}">${escapeHtml(row[col.key] || '—')}</td>`,
                                )
                                .join('')}</tr>`,
                        )
                        .join('')
                  : `<tr><td colspan="${columns.length}">No records</td></tr>`
          }
        </tbody>
        ${
            options.totalLabel != null && options.totalValue != null
                ? `<tfoot><tr><th colspan="${Math.max(columns.length - 1, 1)}">${escapeHtml(options.totalLabel)}</th><th class="num">${escapeHtml(options.totalValue)}</th></tr></tfoot>`
                : ''
        }
      </table>`
            : '';

    const signatures = options.signatures || [
        { role: 'Prepared by' },
        { role: 'Checked by' },
        { role: 'Noted by' },
    ];
    const sigHtml = signatures
        .map(
            (sig) => `<div class="sig">
        <div class="sig__line"></div>
        ${sig.name ? `<div class="sig__name">${escapeHtml(sig.name)}</div>` : ''}
        <div class="sig__role">${escapeHtml(sig.role)}</div>
      </div>`,
        )
        .join('');

    const title = options.windowTitle || `${options.formCode} · ${options.documentNo}`;

    return `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${escapeHtml(title)}</title>
  <style>${printStyles()}</style>
</head>
<body>
  <div class="no-print" style="text-align:center;padding:12px">
    <button type="button" class="btn-print" onclick="window.print()">Print / Save PDF</button>
  </div>
  <article class="sheet">
    <header class="letterhead">
      <img src="${escapeHtml(LOGO_URL)}" alt="APICS" width="56" height="56" onerror="this.style.display='none'">
      <div>
        <p class="letterhead__org">Republic of the Philippines · City of San Fernando, Pampanga</p>
        <p class="letterhead__office">Office of the City Building Official (OCBO)</p>
        <p class="letterhead__system">APICS — Automated Permitting, Inspection, and Compliance System</p>
      </div>
    </header>

    <div class="doc-title">
      <div class="doc-title__code">${escapeHtml(options.formCode)}</div>
      <h1>${escapeHtml(options.formTitle)}</h1>
      <p class="doc-title__no">${escapeHtml(options.documentNo)}</p>
      ${options.subtitle ? `<p class="doc-title__sub">${escapeHtml(options.subtitle)}</p>` : ''}
    </div>

    ${metaHtml ? `<section class="meta">${metaHtml}</section>` : ''}
    ${options.bodyHtml ? `<div class="body-block">${options.bodyHtml}</div>` : ''}
    ${tableHtml}

    <section class="signatures">${sigHtml}</section>

    <footer class="footer">
      <span>System-generated document · APICS · CSFP OCBO · Not valid without authorized signature when required.</span>
      <span>Printed ${escapeHtml(printedAt)}</span>
    </footer>
  </article>
</body>
</html>`;
}

/**
 * Open a print window. Do **not** pass `noopener` in features — browsers return null.
 */
export function openGovernmentPrint(options: GovernmentPrintOptions): boolean {
    const html = buildGovernmentPrintHtml(options);
    const win = window.open('', '_blank', 'width=960,height=780');
    if (!win) {
        toastError('Pop-up blocked — allow pop-ups to print this document');
        return false;
    }
    try {
        win.opener = null;
    } catch {
        // ignore
    }
    win.document.open();
    win.document.write(html);
    win.document.close();

    if (options.autoPrint === false) {
        return true;
    }

    const triggerPrint = (): void => {
        try {
            win.focus();
            win.print();
        } catch {
            toastError('Unable to open the print dialog');
        }
    };

    if (win.document.readyState === 'complete') {
        window.setTimeout(triggerPrint, 80);
    } else {
        win.addEventListener('load', () => window.setTimeout(triggerPrint, 80));
        window.setTimeout(triggerPrint, 400);
    }
    return true;
}
