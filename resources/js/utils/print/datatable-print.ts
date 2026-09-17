/**
 * Government-format print for DataTables Export → Print (filtered list dumps).
 */

import { openGovernmentPrint, type GovernmentPrintColumn, type GovernmentPrintRow } from './government-print';

function stripHtml(value: unknown): string {
    const raw = String(value ?? '');
    if (!raw.includes('<')) {
        return raw.replace(/\s+/g, ' ').trim();
    }
    const tmp = document.createElement('div');
    tmp.innerHTML = raw;
    return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
}

function humanizeFileName(name: string): string {
    return name
        .replace(/[-_]+/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase())
        .trim();
}

export type DataTableGovernmentPrintOptions = {
    /** Export indexes (exclude Actions). */
    exportColumnIndexes: number[];
    /** Prefer page title from DOM when present. */
    exportFileName?: string;
    formCode?: string;
    formTitle?: string;
};

/**
 * Print the current filtered / ordered DataTable rows with OCBO letterhead.
 */
export function printDataTableGovernment(
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    dt: any,
    options: DataTableGovernmentPrintOptions,
): boolean {
    const indexes = options.exportColumnIndexes || [];
    if (!indexes.length) {
        return openGovernmentPrint({
            formCode: options.formCode || 'LIST',
            formTitle: options.formTitle || 'Records listing',
            documentNo: `Export · ${new Date().toISOString().slice(0, 10)}`,
            bodyHtml: '<p>No exportable columns.</p>',
            signatures: [{ role: 'Prepared by' }, { role: 'Checked by' }, { role: 'Noted by' }],
        });
    }

    const columns: GovernmentPrintColumn[] = indexes.map((colIdx, i) => {
        const header = dt.column(colIdx).header() as HTMLElement | null;
        const label = stripHtml(header?.textContent || `Column ${i + 1}`);
        return { key: `c${i}`, label };
    });

    const rowIndexes: number[] = dt.rows({ search: 'applied', order: 'applied' }).indexes().toArray();
    const rows: GovernmentPrintRow[] = rowIndexes.map((rowIdx) => {
        const row: GovernmentPrintRow = {};
        indexes.forEach((colIdx, i) => {
            let display: unknown;
            try {
                display = dt.cell(rowIdx, colIdx).render('display');
            } catch {
                display = dt.cell(rowIdx, colIdx).data();
            }
            row[`c${i}`] = stripHtml(display) || '—';
        });
        return row;
    });

    const pageTitle =
        document.querySelector('.page-title')?.textContent?.trim() ||
        document.querySelector('h4.card-title')?.textContent?.trim() ||
        humanizeFileName(options.exportFileName || 'records');

    const formTitle = options.formTitle || pageTitle;
    const printedDate = new Date().toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });

    return openGovernmentPrint({
        formCode: options.formCode || 'LIST',
        formTitle,
        documentNo: `Listing · ${printedDate}`,
        subtitle: `${rows.length} record${rows.length === 1 ? '' : 's'} (current filters)`,
        meta: [
            { label: 'Module', value: formTitle },
            { label: 'Records', value: String(rows.length) },
            { label: 'Generated', value: new Date().toLocaleString('en-PH') },
            { label: 'System', value: 'APICS · CSFP OCBO' },
        ],
        columns,
        rows,
        signatures: [
            { role: 'Prepared by' },
            { role: 'Checked by' },
            { role: 'City Building Official' },
        ],
        windowTitle: `${formTitle} · Print`,
    });
}
