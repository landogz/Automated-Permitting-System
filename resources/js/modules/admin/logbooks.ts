import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { openOpsApplicationDetail } from '../ops/application-detail/index';
import { initApplicationSelects } from '../application-select/application-select';
import { toastError, toastSuccess } from '../../utils/toast';

type LogbookRow = {
    uuid: string;
    entry_no: string;
    book_type: string;
    subject: string;
    recipient_name?: string | null;
    recipient_contact?: string | null;
    notes?: string | null;
    meta?: Record<string, unknown> | null;
    recorded_at?: string | null;
    print_url?: string;
    application?: {
        uuid?: string;
        application_no?: string;
        project_title?: string;
        project_location?: string;
        status?: string;
    };
    recorded_by?: { name?: string };
};

type LogbookSummary = {
    g01_releasing?: number;
    o02_occupancy?: number;
    e_series?: number;
    g05?: number;
    g06?: number;
    total?: number;
};

const BOOK_LABELS: Record<string, string> = {
    g01_releasing: 'G-01 Releasing',
    o02_occupancy: 'O-02 Occupancy',
    e_series: 'E-series',
    g05: 'G-05',
    g06: 'G-06',
};

function formatDate(value?: string | null): string {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleString();
    } catch {
        return escapeHtml(value);
    }
}

function humanize(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function bookBadge(bookType: string): string {
    const map: Record<string, string> = {
        g01_releasing: 'bg-success-subtle text-success',
        o02_occupancy: 'bg-primary-subtle text-primary',
        e_series: 'bg-info-subtle text-info',
        g05: 'bg-warning-subtle text-warning',
        g06: 'bg-secondary-subtle text-secondary',
    };
    const cls = map[bookType] || 'bg-info-subtle text-info';
    return `<span class="badge ${cls}">${escapeHtml(BOOK_LABELS[bookType] || humanize(bookType))}</span>`;
}

function paintSummary(summary?: LogbookSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-lb-stat="${key}"]`).forEach((el) => {
            el.textContent = value;
        });
    };
    if (!summary) {
        ['g01_releasing', 'o02_occupancy', 'e_series', 'g05', 'g06', 'total'].forEach((k) => set(k, '—'));
        return;
    }
    set('g01_releasing', String(summary.g01_releasing ?? 0));
    set('o02_occupancy', String(summary.o02_occupancy ?? 0));
    set('e_series', String(summary.e_series ?? 0));
    set('g05', String(summary.g05 ?? 0));
    set('g06', String(summary.g06 ?? 0));
    set('total', String(summary.total ?? 0));
}

function renderLogbookDetailHtml(row: LogbookRow): string {
    const app = row.application || {};

    return `
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Entry No.</p>
                <p class="fw-semibold mb-0">${escapeHtml(row.entry_no)}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Book</p>
                <div>${bookBadge(row.book_type)}</div>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Recorded</p>
                <p class="mb-0">${escapeHtml(formatDate(row.recorded_at))}</p>
            </div>
        </div>

        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Subject</h6>
            <p class="fw-semibold mb-0">${escapeHtml(row.subject)}</p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Recipient</h6>
                    <p class="mb-2"><span class="text-muted">Name:</span> ${escapeHtml(row.recipient_name || '—')}</p>
                    <p class="mb-0"><span class="text-muted">Contact:</span> ${escapeHtml(row.recipient_contact || '—')}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Recorder</h6>
                    <p class="mb-2"><span class="text-muted">By:</span> ${escapeHtml(row.recorded_by?.name || '—')}</p>
                    <p class="mb-0"><span class="text-muted">At:</span> ${escapeHtml(formatDate(row.recorded_at))}</p>
                </div>
            </div>
        </div>

        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-3">Linked application</h6>
            ${
                app.application_no
                    ? `<p class="fw-medium mb-1">${escapeHtml(app.application_no)}</p>
                       <p class="mb-1">${escapeHtml(app.project_title || '—')}</p>
                       <p class="text-muted small mb-1">${escapeHtml(app.project_location || '')}</p>
                       <p class="mb-0 text-capitalize"><span class="text-muted">Status:</span> ${escapeHtml(humanize(String(app.status || '—')))}</p>`
                    : `<p class="text-muted mb-0">No application linked to this entry.</p>`
            }
        </div>

        <div class="border rounded p-3">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Notes</h6>
            <div class="fs-13 mb-0" style="white-space:pre-wrap">${escapeHtml(row.notes || '—')}</div>
        </div>
    `;
}

function openPrint(row: LogbookRow): void {
    const url = row.print_url || `/admin/logbooks/${row.uuid}/print`;
    window.open(url, '_blank', 'noopener,noreferrer');
}

export function initLogbooksPage(): void {
    const tableEl = document.getElementById('logbooks-table');
    const form = document.getElementById('form-add-logbook') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    const appSelects = initApplicationSelects(form);
    let table: ApicsDataTableApi<LogbookRow> | null = null;
    let viewingRow: LogbookRow | null = null;

    const detailBody = document.getElementById('logbook-detail-body');
    const detailTitle = document.getElementById('modal-logbook-detail-label');
    const viewAppBtn = document.getElementById('btn-lb-view-app') as HTMLButtonElement | null;
    const printBtn = document.getElementById('btn-lb-print') as HTMLButtonElement | null;

    const openDetail = (row: LogbookRow): void => {
        viewingRow = row;
        if (detailTitle) {
            detailTitle.textContent = `Logbook · ${row.entry_no}`;
        }
        if (detailBody) {
            detailBody.innerHTML = renderLogbookDetailHtml(row);
        }
        viewAppBtn?.classList.toggle('d-none', !row.application?.uuid);
        printBtn?.classList.remove('d-none');
        showModal('modal-logbook-detail');
    };

    viewAppBtn?.addEventListener('click', () => {
        const uuid = viewingRow?.application?.uuid;
        if (!uuid) return;
        hideModal('modal-logbook-detail');
        void openOpsApplicationDetail(uuid);
    });

    printBtn?.addEventListener('click', () => {
        if (!viewingRow) return;
        openPrint(viewingRow);
    });

    void (async () => {
        try {
            table = await createApicsDataTable<LogbookRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'logbook-entries',
                rowId: 'uuid',
                searchMode: 'server',
                order: [[0, 'desc']],
                filters: [
                    {
                        id: 'book_type',
                        label: 'Book',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'g01_releasing', label: 'G-01 Releasing' },
                            { value: 'o02_occupancy', label: 'O-02 Occupancy' },
                            { value: 'e_series', label: 'E-series' },
                            { value: 'g05', label: 'G-05' },
                            { value: 'g06', label: 'G-06' },
                        ],
                    },
                ],
                columns: [
                    {
                        data: 'entry_no',
                        title: 'Entry No.',
                        responsivePriority: 1,
                        render: (d) => `<span class="fw-semibold">${escapeHtml(String(d ?? ''))}</span>`,
                    },
                    {
                        data: 'book_type',
                        title: 'Book',
                        responsivePriority: 1,
                        render: (d) => bookBadge(String(d ?? '')),
                    },
                    {
                        data: 'subject',
                        title: 'Subject',
                        responsivePriority: 1,
                        render: (d, _t, row) =>
                            `<div class="fw-medium text-truncate" style="max-width:16rem">${escapeHtml(String(d ?? ''))}</div>
                             <div class="text-muted small">${escapeHtml(row.recipient_name || 'No recipient')}</div>`,
                    },
                    {
                        data: 'application',
                        title: 'Application',
                        responsivePriority: 2,
                        render: (_d, _t, row) =>
                            `<div class="fw-medium">${escapeHtml(row.application?.application_no || '—')}</div>
                             <div class="text-muted small text-truncate" style="max-width:12rem">${escapeHtml(row.application?.project_title || '')}</div>`,
                    },
                    {
                        data: 'recorded_by',
                        title: 'Recorder',
                        responsivePriority: 3,
                        render: (_d, _t, row) => escapeHtml(row.recorded_by?.name || '—'),
                    },
                    {
                        data: 'recorded_at',
                        title: 'Recorded',
                        responsivePriority: 2,
                        render: (d) => `<span class="small">${escapeHtml(formatDate(d as string | null))}</span>`,
                    },
                ],
                actions: [
                    {
                        id: 'view',
                        label: 'View entry',
                        onClick: (row) => openDetail(row),
                    },
                    {
                        id: 'view-app',
                        label: 'View application details',
                        visible: (row) => Boolean(row.application?.uuid),
                        onClick: (row) => {
                            if (row.application?.uuid) {
                                void openOpsApplicationDetail(row.application.uuid);
                            }
                        },
                    },
                    {
                        id: 'print',
                        label: 'Print / PDF',
                        dividerBefore: true,
                        onClick: (row) => openPrint(row),
                    },
                ],
                fetchData: async ({ search, filters }) => {
                    const { data } = await window.axios.get('/api/v1/staff/logbook-entries', {
                        params: {
                            search: search || undefined,
                            book_type: filters.book_type || undefined,
                            per_page: 100,
                        },
                    });
                    paintSummary(data.data?.summary || null);
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load logbooks');
            paintSummary(null);
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const saveBtn = document.getElementById('btn-save-logbook') as HTMLButtonElement | null;
        if (saveBtn) saveBtn.disabled = true;
        try {
            await window.axios.post('/api/v1/staff/logbook-entries', {
                book_type: (document.getElementById('lb-book-type') as HTMLSelectElement).value,
                subject: (document.getElementById('lb-subject') as HTMLInputElement).value.trim(),
                application_uuid: appSelects.get('lb-app-uuid')?.getValue() || undefined,
                recipient_name: (document.getElementById('lb-recipient') as HTMLInputElement).value.trim() || undefined,
                recipient_contact: (document.getElementById('lb-contact') as HTMLInputElement).value.trim() || undefined,
                notes: (document.getElementById('lb-notes') as HTMLTextAreaElement).value.trim() || undefined,
            });
            toastSuccess('Logbook entry created');
            form.reset();
            appSelects.get('lb-app-uuid')?.clear();
            hideModal('modal-add-logbook');
            await table?.reload(false);
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || 'Create failed'));
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });
}
