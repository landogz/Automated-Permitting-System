import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi, type ApicsColumn, type ApicsRowAction } from '../../utils/datatable';
import { openOpsApplicationDetail } from '../ops/application-detail/index';
import { toastSuccessAndGoNext } from '../../utils/operations-next-step';
import { bindOpsQueueTabs, setOpsActiveCount, setOpsCompletedCount } from '../../utils/ops-completed';
import { formatPhpMono, formatScheduleBlock, statusBadgeHtml } from '../../utils/ops-ui';
import { openGovernmentPrint } from '../../utils/print';
import { initApplicationSelects } from '../application-select/application-select';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type OopLine = {
    uuid?: string;
    agency: string;
    description: string;
    amount: string | number;
    external_stub_reference?: string | null;
    line_order?: number;
};

type OopRow = {
    uuid: string;
    oop_no: string;
    status: string;
    total_amount: string | number;
    issued_at?: string | null;
    paid_at?: string | null;
    payment_reference?: string | null;
    cto_stub_reference?: string | null;
    override_reason?: string | null;
    line_count?: number;
    application?: {
        uuid?: string;
        application_no?: string;
        project_title?: string;
        project_location?: string;
        status?: string;
        classification?: string | null;
        lot_area?: string | number | null;
        floor_area?: string | number | null;
        occupancy?: string | null;
        owner_name?: string | null;
    };
    lines?: OopLine[];
    assessed_by?: { name?: string };
};

type OopSummary = {
    issued?: number;
    paid_stub?: number;
    cancelled?: number;
    total?: number;
    issued_amount?: number;
    paid_amount?: number;
};

function formatPhp(amount: string | number | null | undefined): string {
    const n = Number(amount ?? 0);
    if (Number.isNaN(n)) return '₱0.00';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(n);
}

function formatDate(value?: string | null): string {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleString();
    } catch {
        return escapeHtml(value);
    }
}

function formatIssuedColumn(value?: string | null): string {
    return formatScheduleBlock(value, { relative: false, padHour: true });
}

function humanize(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function agencyBadge(agency: string, amount?: string | number | null, description?: string | null): string {
    const key = agency.toLowerCase();
    const map: Record<string, string> = {
        lgu: 'bg-primary-subtle text-primary',
        bfp: 'bg-danger-subtle text-danger',
        dpwh: 'bg-warning-subtle text-warning',
        cto: 'bg-success-subtle text-success',
    };
    const cls = map[key] || 'bg-secondary-subtle text-secondary';
    const tip =
        amount != null
            ? `${agency.toUpperCase()} share: ${formatPhp(amount)}${description ? ` — ${description}` : ''}`
            : `${agency.toUpperCase()} fee agency`;
    return `<span class="badge ${cls} apics-agency-tip" title="${escapeHtml(tip)}" data-bs-toggle="tooltip">${escapeHtml(agency.toUpperCase())}</span>`;
}

function statusBadge(status: string): string {
    return statusBadgeHtml(status);
}

/**
 * Print G-02 in the shared government / LGU letterhead format.
 */
function printOop(row: OopRow): void {
    openGovernmentPrint({
        formCode: 'G-02',
        formTitle: 'Order of Payment',
        documentNo: row.oop_no,
        subtitle: row.application?.project_title || undefined,
        meta: [
            { label: 'Application No.', value: row.application?.application_no || '—' },
            { label: 'Status', value: humanize(row.status) },
            { label: 'Project', value: row.application?.project_title || '—' },
            { label: 'Location', value: row.application?.project_location || '—' },
            { label: 'Issued', value: formatDate(row.issued_at) },
            { label: 'Assessed by', value: row.assessed_by?.name || '—' },
        ],
        columns: [
            { key: 'agency', label: 'Agency' },
            { key: 'description', label: 'Description' },
            { key: 'amount', label: 'Amount', align: 'right' },
        ],
        rows: (row.lines || []).map((line) => ({
            agency: String(line.agency || '').toUpperCase(),
            description: line.description || '—',
            amount: formatPhp(line.amount),
        })),
        totalLabel: 'Total amount due',
        totalValue: formatPhp(row.total_amount),
        signatures: [
            { role: 'Assessed by', name: row.assessed_by?.name },
            { role: 'Reviewed by' },
            { role: 'City Building Official' },
        ],
        windowTitle: `G-02 ${row.oop_no}`,
    });
}

function paintSummary(summary?: OopSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-oop-stat="${key}"]`).forEach((el) => {
            el.textContent = value;
        });
    };
    if (!summary) {
        set('issued', '—');
        set('paid_stub', '—');
        set('cancelled', '—');
        set('total', '—');
        set('issued_amount', '₱0.00');
        set('paid_amount', '₱0.00');
        return;
    }
    set('issued', String(summary.issued ?? 0));
    set('paid_stub', String(summary.paid_stub ?? 0));
    set('cancelled', String(summary.cancelled ?? 0));
    set('total', String(summary.total ?? 0));
    set('issued_amount', formatPhp(summary.issued_amount));
    set('paid_amount', formatPhp(summary.paid_amount));
}

function linesTableHtml(lines: OopLine[]): string {
    if (!lines.length) {
        return `<p class="text-muted mb-0">No fee lines on this order.</p>`;
    }
    const rows = lines
        .map(
            (line) => `<tr>
                <td class="text-muted">${escapeHtml(String(line.line_order ?? ''))}</td>
                <td>${agencyBadge(String(line.agency || ''), line.amount, line.description)}</td>
                <td>${escapeHtml(line.description || '—')}</td>
                <td class="text-end">${formatPhpMono(line.amount)}</td>
                <td class="small text-muted">${escapeHtml(line.external_stub_reference || '—')}</td>
            </tr>`,
        )
        .join('');
    return `<div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">#</th>
                    <th>Agency</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th>Stub ref</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

function renderOopDetailHtml(row: OopRow): string {
    const app = row.application || {};
    const lines = row.lines || [];

    return `
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">OoP number</p>
                <p class="fw-semibold mb-0">${escapeHtml(row.oop_no)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Status</p>
                <div>${statusBadge(row.status)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Total due</p>
                <p class="fw-semibold fs-18 mb-0 text-primary">${escapeHtml(formatPhp(row.total_amount))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Assessed by</p>
                <p class="mb-0">${escapeHtml(row.assessed_by?.name || '—')}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Application</h6>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">Number</p>
                            <p class="fw-medium mb-0">${escapeHtml(app.application_no || '—')}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">App status</p>
                            <p class="mb-0 text-capitalize">${escapeHtml(humanize(String(app.status || '—')))}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Project</p>
                            <p class="fw-medium mb-0">${escapeHtml(app.project_title || '—')}</p>
                            <p class="text-muted small mb-0">${escapeHtml(app.project_location || '')}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Owner</p>
                            <p class="mb-0">${escapeHtml(String(app.owner_name || '—'))}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Classification</p>
                            <p class="mb-0 text-capitalize">${escapeHtml(app.classification ? humanize(String(app.classification)) : '—')}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Occupancy</p>
                            <p class="mb-0">${escapeHtml(String(app.occupancy || '—'))}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">Lot area</p>
                            <p class="mb-0">${app.lot_area != null && app.lot_area !== '' ? `${escapeHtml(String(app.lot_area))} sqm` : '—'}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">Floor area</p>
                            <p class="mb-0">${app.floor_area != null && app.floor_area !== '' ? `${escapeHtml(String(app.floor_area))} sqm` : '—'}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Payment trail</h6>
                    <p class="mb-2"><span class="text-muted">Issued:</span> ${escapeHtml(formatDate(row.issued_at))}</p>
                    <p class="mb-2"><span class="text-muted">Paid:</span> ${escapeHtml(formatDate(row.paid_at))}</p>
                    <p class="mb-2"><span class="text-muted">CTO stub:</span> <code class="fs-12">${escapeHtml(row.cto_stub_reference || '—')}</code></p>
                    <p class="mb-2"><span class="text-muted">Payment ref:</span> <code class="fs-12">${escapeHtml(row.payment_reference || '—')}</code></p>
                    ${
                        row.override_reason
                            ? `<div class="alert alert-warning border-0 mb-0 mt-2 py-2 fs-13"><strong>Override:</strong> ${escapeHtml(row.override_reason)}</div>`
                            : '<p class="text-muted mb-0 fs-13">No total override applied.</p>'
                    }
                </div>
            </div>
        </div>

        <h6 class="fs-13 text-uppercase text-muted mb-2">Fee line items (${lines.length})</h6>
        ${linesTableHtml(lines)}
    `;
}

function renderFeePreviewHtml(preview: {
    lines?: OopLine[];
    total?: number;
    lot_area?: number | null;
    floor_area?: number | null;
    classification?: string | null;
    application?: { application_no?: string; project_title?: string };
}): string {
    const lines = preview.lines || [];
    if (!lines.length) {
        return `<div class="text-warning mb-0 fs-13"><i class="ri-error-warning-line me-1"></i>No active fee rules matched this application. Update Fee Rules before issuing.</div>`;
    }

    return `
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <div>
                <p class="fw-medium mb-0">${escapeHtml(preview.application?.application_no || 'Application')}</p>
                <p class="text-muted small mb-0">${escapeHtml(preview.application?.project_title || '')}</p>
            </div>
            <div class="text-md-end">
                <p class="text-muted fs-11 text-uppercase mb-0">Computed total</p>
                <p class="fw-semibold text-primary fs-16 mb-0">${escapeHtml(formatPhp(preview.total))}</p>
            </div>
        </div>
        <p class="text-muted fs-12 mb-2">
            Basis · Lot ${preview.lot_area != null ? escapeHtml(String(preview.lot_area)) : '—'} sqm ·
            Floor ${preview.floor_area != null ? escapeHtml(String(preview.floor_area)) : '—'} sqm ·
            ${preview.classification ? escapeHtml(humanize(preview.classification)) : 'Unclassified'}
        </p>
        ${linesTableHtml(lines.map((line, i) => ({ ...line, line_order: i + 1 })))}
    `;
}

function oopColumns(): ApicsColumn<OopRow>[] {
    return [
        {
            data: 'oop_no',
            title: 'OoP No.',
            responsivePriority: 1,
            render: (d) => `<span class="fw-semibold">${escapeHtml(String(d ?? ''))}</span>`,
        },
        {
            data: 'application',
            title: 'Application',
            responsivePriority: 1,
            render: (_d, _t, row) =>
                `<div class="apics-app-no">${escapeHtml(row.application?.application_no || '—')}</div>
                 <div class="text-muted small text-truncate" style="max-width:14rem">${escapeHtml(row.application?.project_title || '')}</div>
                 <div class="text-muted small">${
                     row.application?.lot_area != null ? `Lot ${escapeHtml(String(row.application.lot_area))} sqm` : ''
                 }</div>`,
        },
        {
            data: 'status',
            title: 'Status',
            responsivePriority: 1,
            render: (d) => statusBadge(String(d ?? '')),
        },
        {
            data: 'total_amount',
            title: 'Total',
            responsivePriority: 1,
            className: 'text-end',
            render: (d) => formatPhpMono(d as string | number, { total: true }),
        },
        {
            data: 'lines',
            title: 'Agencies',
            responsivePriority: 2,
            orderable: false,
            render: (_d, _t, row) => {
                const lines = row.lines || [];
                if (!lines.length) return '—';
                const byAgency = new Map<string, OopLine>();
                lines.forEach((line) => {
                    const key = String(line.agency || '').toLowerCase();
                    const prev = byAgency.get(key);
                    if (!prev) {
                        byAgency.set(key, { ...line });
                        return;
                    }
                    byAgency.set(key, {
                        ...prev,
                        amount: Number(prev.amount || 0) + Number(line.amount || 0),
                        description: `${prev.description}; ${line.description}`,
                    });
                });
                return Array.from(byAgency.values())
                    .map((line) => agencyBadge(String(line.agency || ''), line.amount, line.description))
                    .join(' ');
            },
        },
        {
            data: 'issued_at',
            title: 'Issued',
            responsivePriority: 3,
            render: (d) => formatIssuedColumn(d as string | null),
        },
        {
            data: 'assessed_by',
            title: 'Assessor',
            responsivePriority: 4,
            render: (_d, _t, row) => escapeHtml(row.assessed_by?.name || '—'),
        },
    ];
}

export function initOrdersOfPaymentPage(): void {
    const tableEl = document.getElementById('oop-table');
    const completedEl = document.getElementById('oop-completed-table');
    const form = document.getElementById('form-generate-oop') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    const appSelects = initApplicationSelects(form);
    let activeTable: ApicsDataTableApi<OopRow> | null = null;
    let completedTable: ApicsDataTableApi<OopRow> | null = null;
    let viewingRow: OopRow | null = null;

    const reloadBoth = async (): Promise<void> => {
        await Promise.all([activeTable?.reload(false), completedTable?.reload(false)]);
    };

    const previewBox = document.getElementById('oop-fee-preview');
    const detailBody = document.getElementById('oop-detail-body');
    const detailTitle = document.getElementById('modal-oop-detail-label');
    const viewAppBtn = document.getElementById('btn-oop-view-app') as HTMLButtonElement | null;
    const markPaidBtn = document.getElementById('btn-oop-mark-paid') as HTMLButtonElement | null;
    const printBtn = document.getElementById('btn-oop-print') as HTMLButtonElement | null;

    const openDetail = (row: OopRow): void => {
        viewingRow = row;
        if (detailTitle) detailTitle.textContent = `Order of Payment · ${row.oop_no}`;
        if (detailBody) detailBody.innerHTML = renderOopDetailHtml(row);
        viewAppBtn?.classList.toggle('d-none', !row.application?.uuid);
        markPaidBtn?.classList.toggle('d-none', row.status !== 'issued');
        showModal('modal-oop-detail');
    };

    const markPaid = async (row: OopRow): Promise<void> => {
        if (!(await confirmAction('Mark this OoP as paid?', `${row.oop_no} will be recorded as paid via CTO stub and the application may be released.`))) {
            return;
        }
        try {
            const { data: res } = await window.axios.post(`/api/v1/staff/orders-of-payment/${row.uuid}/mark-paid`);
            hideModal('modal-oop-detail');
            toastSuccessAndGoNext('Marked paid (CTO stub)', res.data?.next_step);
            await reloadBoth();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Update failed');
        }
    };

    const loadFeePreview = async (appUuid: string): Promise<void> => {
        if (!previewBox || !appUuid) {
            if (previewBox) {
                previewBox.innerHTML = `<p class="text-muted fs-13 mb-0">Select an application to preview the fee assessment.</p>`;
            }
            return;
        }
        previewBox.innerHTML = `<div class="text-muted fs-13"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Computing fees…</div>`;
        try {
            const { data } = await window.axios.get(`/api/v1/staff/applications/${appUuid}/orders-of-payment/preview`);
            previewBox.innerHTML = renderFeePreviewHtml(data.data || {});
        } catch (error: any) {
            previewBox.innerHTML = `<p class="text-danger fs-13 mb-0">${escapeHtml(error?.response?.data?.message || 'Unable to preview fees')}</p>`;
        }
    };

    viewAppBtn?.addEventListener('click', () => {
        const uuid = viewingRow?.application?.uuid;
        if (!uuid) return;
        hideModal('modal-oop-detail');
        void openOpsApplicationDetail(uuid);
    });

    markPaidBtn?.addEventListener('click', () => {
        if (!viewingRow) return;
        void markPaid(viewingRow);
    });

    printBtn?.addEventListener('click', () => {
        if (!viewingRow) {
            toastError('No order selected to print');
            return;
        }
        printOop(viewingRow);
    });

    const appHidden = document.getElementById('oop-app-uuid') as HTMLInputElement | null;
    let lastPreviewUuid = '';
    const syncFeePreview = (): void => {
        const uuid = appSelects.get('oop-app-uuid')?.getValue() || appHidden?.value.trim() || '';
        if (uuid === lastPreviewUuid) return;
        lastPreviewUuid = uuid;
        void loadFeePreview(uuid);
    };
    appHidden?.addEventListener('change', syncFeePreview);
    document.getElementById('modal-generate-oop')?.addEventListener('shown.bs.modal', () => {
        syncFeePreview();
    });

    const sharedActions = (includeMarkPaid: boolean): ApicsRowAction<OopRow>[] => {
        const actions: ApicsRowAction<OopRow>[] = [
            { id: 'view-order', label: 'View Order', primary: true, onClick: (row) => openDetail(row) },
            {
                id: 'print',
                label: 'Print G-02',
                onClick: (row) => printOop(row),
            },
            {
                id: 'view-app',
                label: 'View application details',
                visible: (row) => Boolean(row.application?.uuid),
                onClick: (row) => {
                    if (row.application?.uuid) void openOpsApplicationDetail(row.application.uuid);
                },
            },
        ];
        if (includeMarkPaid) {
            actions.push({
                id: 'mark-paid',
                label: 'Mark paid (CTO stub)',
                dividerBefore: true,
                visible: (row) => row.status === 'issued',
                onClick: (row) => void markPaid(row),
            });
        }
        return actions;
    };

    void (async () => {
        try {
            activeTable = await createApicsDataTable<OopRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'orders-of-payment-active',
                rowId: 'uuid',
                searchMode: 'server',
                order: [[0, 'desc']],
                columns: oopColumns(),
                actions: sharedActions(true),
                fetchData: async ({ search }) => {
                    const { data } = await window.axios.get('/api/v1/staff/orders-of-payment', {
                        params: { search: search || undefined, bucket: 'active', per_page: 100 },
                    });
                    paintSummary((data.data?.summary || null) as OopSummary | null);
                    const items = data.data?.items || [];
                    setOpsActiveCount('oop-queue', data.data?.meta?.total ?? items.length);
                    return items;
                },
            });

            if (completedEl) {
                completedTable = await createApicsDataTable<OopRow>({
                    table: completedEl as HTMLTableElement,
                    exportFileName: 'orders-of-payment-completed',
                    rowId: 'uuid',
                    searchMode: 'server',
                    order: [[0, 'desc']],
                    columns: oopColumns(),
                    actions: sharedActions(false),
                    fetchData: async ({ search }) => {
                        const { data } = await window.axios.get('/api/v1/staff/orders-of-payment', {
                            params: { search: search || undefined, bucket: 'completed', per_page: 100 },
                        });
                        paintSummary((data.data?.summary || null) as OopSummary | null);
                        const items = data.data?.items || [];
                        setOpsCompletedCount('oop-queue', data.data?.meta?.total ?? items.length);
                        return items;
                    },
                });
            }

            bindOpsQueueTabs(
                'oop-queue',
                () => completedTable?.raw as any,
                () => activeTable?.raw as any,
            );
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load orders');
            paintSummary(null);
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const appUuid =
            appSelects.get('oop-app-uuid')?.getValue() ||
            (document.getElementById('oop-app-uuid') as HTMLInputElement).value.trim();
        if (!appUuid) {
            toastError('Please select an application');
            return;
        }
        const overrideTotal = (document.getElementById('oop-override-total') as HTMLInputElement).value;
        const overrideReason = (document.getElementById('oop-override-reason') as HTMLTextAreaElement).value.trim();
        const saveBtn = document.getElementById('btn-issue-oop') as HTMLButtonElement | null;
        if (saveBtn) saveBtn.disabled = true;

        try {
            await window.axios.post(`/api/v1/staff/applications/${appUuid}/orders-of-payment`, {
                override_total: overrideTotal !== '' ? Number(overrideTotal) : undefined,
                override_reason: overrideReason || undefined,
            });
            toastSuccess('Order of payment issued');
            form.reset();
            appSelects.get('oop-app-uuid')?.clear();
            lastPreviewUuid = '';
            if (previewBox) {
                previewBox.innerHTML = `<p class="text-muted fs-13 mb-0">Select an application to preview the fee assessment.</p>`;
            }
            hideModal('modal-generate-oop');
            await reloadBoth();
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || 'Generate failed'));
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });
}
