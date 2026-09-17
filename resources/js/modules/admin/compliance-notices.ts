import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi, type ApicsColumn, type ApicsRowAction } from '../../utils/datatable';
import { openOpsApplicationDetail } from '../ops/application-detail/index';
import { toastSuccessAndGoNext } from '../../utils/operations-next-step';
import { bindOpsQueueTabs, setOpsActiveCount, setOpsCompletedCount } from '../../utils/ops-completed';
import { appealCountdownHtml, statusBadgeHtml } from '../../utils/ops-ui';
import { initApplicationSelects } from '../application-select/application-select';
import { confirmWithReason, toastError, toastSuccess } from '../../utils/toast';

type NoticeAppeal = {
    uuid: string;
    status: string;
    grounds?: string | null;
    resolution_notes?: string | null;
    filed_at?: string | null;
    resolved_at?: string | null;
    filed_by?: { name?: string } | null;
    resolved_by?: { name?: string } | null;
};

type NoticeRow = {
    uuid: string;
    notice_no: string;
    type: string;
    status: string;
    title: string;
    body?: string;
    issued_at?: string | null;
    due_at?: string | null;
    appeal_count?: number;
    pending_appeal_uuid?: string | null;
    application?: {
        uuid?: string;
        application_no?: string;
        project_title?: string;
        project_location?: string;
        status?: string;
        classification?: string | null;
        owner_name?: string | null;
        occupancy?: string | null;
    };
    inspection?: {
        uuid?: string;
        inspection_no?: string;
        type?: string;
        result?: string | null;
        status?: string | null;
        notes?: string | null;
        completed_at?: string | null;
    };
    issued_by?: { name?: string };
    appeals?: NoticeAppeal[];
};

type NoticeSummary = {
    issued?: number;
    appealed?: number;
    closed?: number;
    g03?: number;
    g04?: number;
    g04_active?: number;
    g04_archived?: number;
    total?: number;
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

function typeBadge(type: string): string {
    const isG04 = type === 'g04_disapproval';
    const cls = isG04 ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning';
    const label = type === 'g03_compliance' ? 'G-03 Compliance' : type === 'g04_disapproval' ? 'G-04 Disapproval' : humanize(type);
    return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
}

function statusBadge(status: string): string {
    return statusBadgeHtml(status, { pulse: status === 'issued' || status === 'appealed' });
}

function paintSummary(summary?: NoticeSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-notice-stat="${key}"]`).forEach((el) => {
            el.textContent = value;
        });
    };
    if (!summary) {
        ['issued', 'appealed', 'closed', 'g03', 'g04', 'g04_active', 'g04_archived', 'total'].forEach((k) =>
            set(k, '—'),
        );
        return;
    }
    const g04Active = summary.g04_active ?? summary.g04 ?? 0;
    const g04Archived = summary.g04_archived ?? 0;
    set('issued', String(summary.issued ?? 0));
    set('appealed', String(summary.appealed ?? 0));
    set('closed', String(summary.closed ?? 0));
    set('g03', String(summary.g03 ?? 0));
    set('g04', String(g04Active));
    set('g04_active', String(g04Active));
    set('g04_archived', String(g04Archived));
    set('total', String(summary.total ?? 0));
}

function appealsHtml(appeals: NoticeAppeal[]): string {
    if (!appeals.length) {
        return `<p class="text-muted mb-0 fs-13">No appeals filed for this notice.</p>`;
    }
    return appeals
        .map(
            (appeal) => `<div class="border rounded p-3 mb-2 bg-light-subtle">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>${statusBadge(appeal.status)}</div>
                    <div class="text-muted small">Filed ${escapeHtml(formatDate(appeal.filed_at))} · ${escapeHtml(appeal.filed_by?.name || '—')}</div>
                </div>
                <p class="mb-1 fs-13"><span class="text-muted">Grounds:</span> ${escapeHtml(appeal.grounds || '—')}</p>
                ${
                    appeal.resolution_notes
                        ? `<p class="mb-1 fs-13"><span class="text-muted">Resolution:</span> ${escapeHtml(appeal.resolution_notes)}</p>`
                        : ''
                }
                ${
                    appeal.resolved_at
                        ? `<p class="mb-0 text-muted small">Resolved ${escapeHtml(formatDate(appeal.resolved_at))} · ${escapeHtml(appeal.resolved_by?.name || '—')}</p>`
                        : ''
                }
            </div>`,
        )
        .join('');
}

function renderNoticeDetailHtml(row: NoticeRow): string {
    const app = row.application || {};
    const inspection = row.inspection || {};
    const appeals = row.appeals || [];

    return `
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Notice No.</p>
                <p class="fw-semibold mb-0">${escapeHtml(row.notice_no)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Type</p>
                <div>${typeBadge(row.type)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Status</p>
                <div>${statusBadge(row.status)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Issued by</p>
                <p class="mb-0">${escapeHtml(row.issued_by?.name || '—')}</p>
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
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Timeline</h6>
                    <p class="mb-2"><span class="text-muted">Issued:</span> ${escapeHtml(formatDate(row.issued_at))}</p>
                    <p class="mb-2"><span class="text-muted">Due:</span> ${escapeHtml(formatDate(row.due_at))}</p>
                    <p class="mb-2"><span class="text-muted">Linked inspection:</span> ${escapeHtml(inspection.inspection_no || '—')}</p>
                    <p class="mb-2"><span class="text-muted">Result:</span> ${escapeHtml(inspection.result ? humanize(String(inspection.result)) : '—')}</p>
                    ${
                        inspection.notes
                            ? `<p class="mb-0"><span class="text-muted">Inspector findings:</span></p>
                               <p class="mb-0 fs-13 mt-1" style="white-space:pre-wrap">${escapeHtml(inspection.notes)}</p>`
                            : ''
                    }
                </div>
            </div>
        </div>

        <div class="border rounded p-3 mb-4">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Notice content</h6>
            <p class="fw-semibold mb-2">${escapeHtml(row.title)}</p>
            <div class="fs-13 mb-0" style="white-space:pre-wrap">${escapeHtml(row.body || '—')}</div>
        </div>

        <h6 class="fs-13 text-uppercase text-muted mb-2">Appeals (${appeals.length})</h6>
        ${appealsHtml(appeals)}
    `;
}

function noticeColumns(): ApicsColumn<NoticeRow>[] {
    return [
        {
            data: 'notice_no',
            title: 'Notice No.',
            responsivePriority: 1,
            render: (d) => `<span class="fw-semibold">${escapeHtml(String(d ?? ''))}</span>`,
        },
        {
            data: 'type',
            title: 'Type',
            responsivePriority: 1,
            render: (d) => typeBadge(String(d ?? '')),
        },
        {
            data: 'status',
            title: 'Status',
            responsivePriority: 1,
            render: (d) => statusBadge(String(d ?? '')),
        },
        {
            data: 'title',
            title: 'Title',
            responsivePriority: 2,
            render: (d, _t, row) =>
                `<div class="fw-medium text-truncate" style="max-width:16rem">${escapeHtml(String(d ?? ''))}</div>
                 <div class="text-muted small">${row.appeal_count ? `${row.appeal_count} appeal(s)` : 'No appeals'}</div>`,
        },
        {
            data: 'application',
            title: 'Application',
            responsivePriority: 1,
            render: (_d, _t, row) =>
                `<div class="fw-medium">${escapeHtml(row.application?.application_no || '—')}</div>
                 <div class="text-muted small text-truncate" style="max-width:14rem">${escapeHtml(row.application?.project_title || '')}</div>`,
        },
        {
            data: 'due_at',
            title: 'Appeal window',
            responsivePriority: 2,
            render: (_d, _t, row) =>
                appealCountdownHtml({
                    type: row.type,
                    status: row.status,
                    issuedAt: row.issued_at,
                    dueAt: row.due_at,
                }),
        },
        {
            data: 'issued_by',
            title: 'Issuer',
            responsivePriority: 4,
            render: (_d, _t, row) => escapeHtml(row.issued_by?.name || '—'),
        },
    ];
}

export function initComplianceNoticesPage(): void {
    const tableEl = document.getElementById('notices-table');
    const completedEl = document.getElementById('notices-completed-table');
    const form = document.getElementById('form-issue-notice') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    const appSelects = initApplicationSelects(form);
    let activeTable: ApicsDataTableApi<NoticeRow> | null = null;
    let completedTable: ApicsDataTableApi<NoticeRow> | null = null;
    let viewingRow: NoticeRow | null = null;
    let linkedFailedInspectionUuid: string | null = null;

    const prefillFromFailedInspection = async (): Promise<void> => {
        const appUuid = appSelects.get('notice-app-uuid')?.getValue() || '';
        const bodyEl = document.getElementById('notice-body') as HTMLTextAreaElement | null;
        const titleEl = document.getElementById('notice-title') as HTMLInputElement | null;
        linkedFailedInspectionUuid = null;
        if (!appUuid || !bodyEl) return;

        try {
            const { data } = await window.axios.get(`/api/v1/staff/applications/${appUuid}`);
            const inspections = (data.data?.inspections || []) as Array<{
                uuid?: string;
                inspection_no?: string;
                result?: string | null;
                notes?: string | null;
            }>;
            const failed = inspections.find((row) => row.result === 'failed' && row.notes);
            if (!failed) return;

            linkedFailedInspectionUuid = failed.uuid || null;
            if (titleEl && !titleEl.value.trim()) {
                titleEl.value = 'Notice of Compliance — inspection deficiencies';
            }
            if (!bodyEl.value.trim()) {
                const inspLabel = failed.inspection_no || 'site inspection';
                bodyEl.value =
                    `Inspection findings (${inspLabel}):\n\n${failed.notes}\n\n` +
                    'Required corrective actions:\n1. \n2. \n\n' +
                    'Please comply within the appeal window stated on this notice.';
            }
        } catch {
            // Prefill is best-effort; staff can still type the notice body.
        }
    };

    document.getElementById('notice-app-uuid')?.addEventListener('change', () => {
        void prefillFromFailedInspection();
    });

    const reloadBoth = async (): Promise<void> => {
        await Promise.all([activeTable?.reload(false), completedTable?.reload(false)]);
    };

    const detailBody = document.getElementById('notice-detail-body');
    const detailTitle = document.getElementById('modal-notice-detail-label');
    const viewAppBtn = document.getElementById('btn-notice-view-app') as HTMLButtonElement | null;
    const fileAppealBtn = document.getElementById('btn-notice-file-appeal') as HTMLButtonElement | null;
    const resolveAppealBtn = document.getElementById('btn-notice-resolve-appeal') as HTMLButtonElement | null;
    const resolveForm = document.getElementById('form-resolve-appeal') as HTMLFormElement | null;

    const openDetail = (row: NoticeRow): void => {
        viewingRow = row;
        if (detailTitle) detailTitle.textContent = `Notice · ${row.notice_no}`;
        if (detailBody) detailBody.innerHTML = renderNoticeDetailHtml(row);
        viewAppBtn?.classList.toggle('d-none', !row.application?.uuid);
        fileAppealBtn?.classList.toggle('d-none', row.status !== 'issued');
        resolveAppealBtn?.classList.toggle('d-none', !(row.status === 'appealed' && row.pending_appeal_uuid));
        showModal('modal-notice-detail');
    };

    const fileAppeal = async (row: NoticeRow): Promise<void> => {
        const grounds = await confirmWithReason({
            title: 'File appeal?',
            text: `Explain the grounds for appealing ${row.notice_no}.`,
            inputLabel: 'Appeal grounds',
            inputPlaceholder: 'Cite why the findings should be re-evaluated…',
            confirmButtonText: 'File appeal',
            minLength: 10,
        });
        if (!grounds) return;
        try {
            await window.axios.post(`/api/v1/staff/compliance-notices/${row.uuid}/appeals`, {
                grounds,
            });
            hideModal('modal-notice-detail');
            toastSuccess('Appeal filed');
            await reloadBoth();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Appeal failed');
        }
    };

    viewAppBtn?.addEventListener('click', () => {
        const uuid = viewingRow?.application?.uuid;
        if (!uuid) return;
        hideModal('modal-notice-detail');
        void openOpsApplicationDetail(uuid);
    });

    fileAppealBtn?.addEventListener('click', () => {
        if (!viewingRow) return;
        void fileAppeal(viewingRow);
    });

    resolveAppealBtn?.addEventListener('click', () => {
        if (!viewingRow?.pending_appeal_uuid) return;
        const hidden = document.getElementById('resolve-appeal-uuid') as HTMLInputElement | null;
        if (hidden) hidden.value = viewingRow.pending_appeal_uuid;
        const notes = document.getElementById('resolve-appeal-notes') as HTMLTextAreaElement | null;
        if (notes) notes.value = '';
        hideModal('modal-notice-detail');
        showModal('modal-resolve-appeal');
    });

    resolveForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const appealUuid = (document.getElementById('resolve-appeal-uuid') as HTMLInputElement).value.trim();
        if (!appealUuid) {
            toastError('No pending appeal selected');
            return;
        }
        try {
            await window.axios.post(`/api/v1/staff/compliance-appeals/${appealUuid}/resolve`, {
                status: (document.getElementById('resolve-appeal-status') as HTMLSelectElement).value,
                resolution_notes:
                    (document.getElementById('resolve-appeal-notes') as HTMLTextAreaElement).value.trim() || undefined,
            });
            toastSuccess('Appeal resolved');
            hideModal('modal-resolve-appeal');
            await reloadBoth();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Resolve failed');
        }
    });

    const activeActions: ApicsRowAction<NoticeRow>[] = [
        { id: 'view-notice', label: 'View Notice', primary: true, onClick: (row) => openDetail(row) },
        {
            id: 'view-app',
            label: 'View application details',
            visible: (row) => Boolean(row.application?.uuid),
            onClick: (row) => {
                if (row.application?.uuid) void openOpsApplicationDetail(row.application.uuid);
            },
        },
        {
            id: 'appeal',
            label: 'File appeal',
            dividerBefore: true,
            visible: (row) => row.status === 'issued',
            onClick: (row) => void fileAppeal(row),
        },
        {
            id: 'resolve',
            label: 'Resolve appeal',
            visible: (row) => row.status === 'appealed' && Boolean(row.pending_appeal_uuid),
            onClick: (row) => {
                viewingRow = row;
                const hidden = document.getElementById('resolve-appeal-uuid') as HTMLInputElement | null;
                if (hidden && row.pending_appeal_uuid) hidden.value = row.pending_appeal_uuid;
                showModal('modal-resolve-appeal');
            },
        },
    ];

    const emptyStateHtml =
        'All filings fully compliant — no pending G-03/G-04 notices. Use Issue notice to create one manually.';

    void (async () => {
        try {
            activeTable = await createApicsDataTable<NoticeRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'compliance-notices-active',
                rowId: 'uuid',
                searchMode: 'server',
                order: [[0, 'desc']],
                emptyMessage: emptyStateHtml,
                noResultsMessage: emptyStateHtml,
                filters: [
                    {
                        id: 'type',
                        label: 'Type',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'g03_compliance', label: 'G-03 Compliance' },
                            { value: 'g04_disapproval', label: 'G-04 Disapproval' },
                        ],
                    },
                ],
                columns: noticeColumns(),
                actions: activeActions,
                fetchData: async ({ search, filters }) => {
                    const { data } = await window.axios.get('/api/v1/staff/compliance-notices', {
                        params: {
                            search: search || undefined,
                            type: filters.type || undefined,
                            bucket: 'active',
                            per_page: 100,
                        },
                    });
                    paintSummary(data.data?.summary || null);
                    const items = data.data?.items || [];
                    setOpsActiveCount('notice-queue', data.data?.meta?.total ?? items.length);

                    // Purpose-built empty state: hide table chrome (thead/footer) entirely.
                    const shell = tableEl.closest('.apics-dt-shell');
                    let emptyEl = shell?.querySelector<HTMLElement>('.apics-empty-state');
                    if (!items.length && shell) {
                        if (!emptyEl) {
                            emptyEl = document.createElement('div');
                            emptyEl.className = 'apics-empty-state';
                            emptyEl.innerHTML = `
                                <div class="apics-empty-state__icon" aria-hidden="true"><i class="ri-checkbox-circle-line"></i></div>
                                <h4 class="apics-empty-state__title">All Filings Fully Compliant</h4>
                                <p class="apics-empty-state__copy">No pending G-03 (Compliance) or G-04 (Disapproval) notices required. All applications are clear of deficiencies.</p>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-issue-notice">+ Issue Notice Manually</button>`;
                            shell.appendChild(emptyEl);
                        }
                        emptyEl.classList.remove('d-none');
                        shell.classList.add('is-empty');
                    } else {
                        emptyEl?.classList.add('d-none');
                        shell?.classList.remove('is-empty');
                    }

                    return items;
                },
            });

            if (completedEl) {
                completedTable = await createApicsDataTable<NoticeRow>({
                    table: completedEl as HTMLTableElement,
                    exportFileName: 'compliance-notices-completed',
                    rowId: 'uuid',
                    searchMode: 'server',
                    order: [[0, 'desc']],
                    filters: [
                        {
                            id: 'type',
                            label: 'Type',
                            options: [
                                { value: '', label: 'All' },
                                { value: 'g03_compliance', label: 'G-03 Compliance' },
                                { value: 'g04_disapproval', label: 'G-04 Disapproval' },
                            ],
                        },
                    ],
                    columns: noticeColumns(),
                    actions: [
                        { id: 'view-notice', label: 'View Notice', primary: true, onClick: (row) => openDetail(row) },
                        {
                            id: 'view-app',
                            label: 'View application details',
                            visible: (row) => Boolean(row.application?.uuid),
                            onClick: (row) => {
                                if (row.application?.uuid) void openOpsApplicationDetail(row.application.uuid);
                            },
                        },
                    ],
                    fetchData: async ({ search, filters }) => {
                        const { data } = await window.axios.get('/api/v1/staff/compliance-notices', {
                            params: {
                                search: search || undefined,
                                type: filters.type || undefined,
                                bucket: 'completed',
                                per_page: 100,
                            },
                        });
                        paintSummary(data.data?.summary || null);
                        const items = data.data?.items || [];
                        setOpsCompletedCount('notice-queue', data.data?.meta?.total ?? items.length);
                        return items;
                    },
                });
            }

            bindOpsQueueTabs(
                'notice-queue',
                () => completedTable?.raw as any,
                () => activeTable?.raw as any,
            );
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load notices');
            paintSummary(null);
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const appUuid =
            appSelects.get('notice-app-uuid')?.getValue() ||
            (document.getElementById('notice-app-uuid') as HTMLInputElement).value.trim();
        if (!appUuid) {
            toastError('Please select an application');
            return;
        }
        const saveBtn = document.getElementById('btn-issue-notice') as HTMLButtonElement | null;
        if (saveBtn) saveBtn.disabled = true;
        try {
            const { data: res } = await window.axios.post(`/api/v1/staff/applications/${appUuid}/compliance-notices`, {
                type: (document.getElementById('notice-type') as HTMLSelectElement).value,
                title: (document.getElementById('notice-title') as HTMLInputElement).value.trim(),
                body: (document.getElementById('notice-body') as HTMLTextAreaElement).value.trim(),
                inspection_uuid: linkedFailedInspectionUuid || undefined,
            });
            toastSuccessAndGoNext('Notice issued', res.data?.next_step);
            form.reset();
            linkedFailedInspectionUuid = null;
            appSelects.get('notice-app-uuid')?.clear();
            hideModal('modal-issue-notice');
            await reloadBoth();
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || 'Issue failed'));
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });
}
