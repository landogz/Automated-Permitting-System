import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { openOpsApplicationDetail } from '../ops/application-detail/index';
import { initApplicationSelects } from '../application-select/application-select';
import { toastError, toastSuccess } from '../../utils/toast';

type ArchiveRow = {
    uuid: string;
    archive_no: string;
    title: string;
    storage_location?: string | null;
    media_type: string;
    checksum?: string | null;
    notes?: string | null;
    meta?: Record<string, unknown> | null;
    archived_at?: string | null;
    application?: {
        uuid?: string;
        application_no?: string;
        project_title?: string;
        project_location?: string;
        status?: string;
        classification?: string | null;
    };
    archived_by?: { name?: string };
};

type ArchiveSummary = {
    digital?: number;
    physical?: number;
    hybrid?: number;
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

function mediaBadge(media: string): string {
    const map: Record<string, string> = {
        digital: 'bg-primary-subtle text-primary',
        physical: 'bg-secondary-subtle text-secondary',
        hybrid: 'bg-info-subtle text-info',
    };
    const cls = map[media] || 'bg-secondary-subtle text-secondary';
    return `<span class="badge ${cls}">${escapeHtml(humanize(media))}</span>`;
}

function paintSummary(summary?: ArchiveSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-arc-stat="${key}"]`).forEach((el) => {
            el.textContent = value;
        });
    };
    if (!summary) {
        ['digital', 'physical', 'hybrid', 'total'].forEach((k) => set(k, '—'));
        return;
    }
    set('digital', String(summary.digital ?? 0));
    set('physical', String(summary.physical ?? 0));
    set('hybrid', String(summary.hybrid ?? 0));
    set('total', String(summary.total ?? 0));
}

function renderArchiveDetailHtml(row: ArchiveRow): string {
    const app = row.application || {};
    const metaJson =
        row.meta && Object.keys(row.meta).length
            ? escapeHtml(JSON.stringify(row.meta, null, 2))
            : null;

    return `
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Archive No.</p>
                <p class="fw-semibold mb-0">${escapeHtml(row.archive_no)}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Media</p>
                <div>${mediaBadge(row.media_type)}</div>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Archived</p>
                <p class="mb-0">${escapeHtml(formatDate(row.archived_at))}</p>
            </div>
        </div>

        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Title</h6>
            <p class="fw-semibold mb-0">${escapeHtml(row.title)}</p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Storage</h6>
                    <p class="mb-2"><span class="text-muted">Location:</span> ${escapeHtml(row.storage_location || '—')}</p>
                    <p class="mb-0"><span class="text-muted">Archived by:</span> ${escapeHtml(row.archived_by?.name || '—')}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Linked application</h6>
                    ${
                        app.application_no
                            ? `<p class="fw-medium mb-1">${escapeHtml(app.application_no)}</p>
                               <p class="mb-1">${escapeHtml(app.project_title || '—')}</p>
                               <p class="text-muted small mb-1">${escapeHtml(app.project_location || '')}</p>
                               <p class="mb-0 text-capitalize"><span class="text-muted">Status:</span> ${escapeHtml(humanize(String(app.status || '—')))}</p>`
                            : `<p class="text-muted mb-0">No application linked.</p>`
                    }
                </div>
            </div>
        </div>

        <div class="border rounded p-3 mb-3">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Integrity checksum (SHA-256)</h6>
            <code class="fs-12 text-break d-block">${escapeHtml(row.checksum || '—')}</code>
        </div>

        ${
            metaJson
                ? `<div class="border rounded p-3 mb-3 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-2">CICTO / meta stub</h6>
                    <pre class="mb-0 small font-monospace" style="white-space:pre-wrap">${metaJson}</pre>
                   </div>`
                : ''
        }

        <div class="border rounded p-3">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Notes</h6>
            <div class="fs-13 mb-0" style="white-space:pre-wrap">${escapeHtml(row.notes || '—')}</div>
        </div>
    `;
}

export function initArchivesPage(): void {
    const tableEl = document.getElementById('archives-table');
    const form = document.getElementById('form-add-archive') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    const appSelects = initApplicationSelects(form);
    let table: ApicsDataTableApi<ArchiveRow> | null = null;
    let viewingRow: ArchiveRow | null = null;

    const detailBody = document.getElementById('archive-detail-body');
    const detailTitle = document.getElementById('modal-archive-detail-label');
    const viewAppBtn = document.getElementById('btn-arc-view-app') as HTMLButtonElement | null;

    const openDetail = (row: ArchiveRow): void => {
        viewingRow = row;
        if (detailTitle) {
            detailTitle.textContent = `Archive · ${row.archive_no}`;
        }
        if (detailBody) {
            detailBody.innerHTML = renderArchiveDetailHtml(row);
        }
        viewAppBtn?.classList.toggle('d-none', !row.application?.uuid);
        showModal('modal-archive-detail');
    };

    viewAppBtn?.addEventListener('click', () => {
        const uuid = viewingRow?.application?.uuid;
        if (!uuid) return;
        hideModal('modal-archive-detail');
        void openOpsApplicationDetail(uuid);
    });

    void (async () => {
        try {
            table = await createApicsDataTable<ArchiveRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'archive-records',
                rowId: 'uuid',
                searchMode: 'server',
                order: [[0, 'desc']],
                filters: [
                    {
                        id: 'media_type',
                        label: 'Media',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'digital', label: 'Digital' },
                            { value: 'physical', label: 'Physical' },
                            { value: 'hybrid', label: 'Hybrid' },
                        ],
                    },
                ],
                columns: [
                    {
                        data: 'archive_no',
                        title: 'Archive No.',
                        responsivePriority: 1,
                        render: (d) => `<span class="fw-semibold">${escapeHtml(String(d ?? ''))}</span>`,
                    },
                    {
                        data: 'title',
                        title: 'Title',
                        responsivePriority: 1,
                        render: (d, _t, row) =>
                            `<div class="fw-medium text-truncate" style="max-width:16rem">${escapeHtml(String(d ?? ''))}</div>
                             <div class="text-muted small text-truncate" style="max-width:16rem">${escapeHtml(row.storage_location || '')}</div>`,
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
                        data: 'media_type',
                        title: 'Media',
                        responsivePriority: 1,
                        render: (d) => mediaBadge(String(d ?? '')),
                    },
                    {
                        data: 'checksum',
                        title: 'Checksum',
                        responsivePriority: 3,
                        render: (d) => {
                            if (!d) return '—';
                            const hash = String(d);
                            return `<span class="small font-monospace" title="${escapeHtml(hash)}">${escapeHtml(hash.slice(0, 12))}…</span>`;
                        },
                    },
                    {
                        data: 'archived_by',
                        title: 'Archivist',
                        responsivePriority: 4,
                        render: (_d, _t, row) => escapeHtml(row.archived_by?.name || '—'),
                    },
                    {
                        data: 'archived_at',
                        title: 'Archived',
                        responsivePriority: 2,
                        render: (d) => `<span class="small">${escapeHtml(formatDate(d as string | null))}</span>`,
                    },
                ],
                actions: [
                    {
                        id: 'view',
                        label: 'View archive',
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
                ],
                fetchData: async ({ search, filters }) => {
                    const { data } = await window.axios.get('/api/v1/staff/archive-records', {
                        params: {
                            search: search || undefined,
                            media_type: filters.media_type || undefined,
                            per_page: 100,
                        },
                    });
                    paintSummary(data.data?.summary || null);
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load archives');
            paintSummary(null);
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const saveBtn = document.getElementById('btn-save-archive') as HTMLButtonElement | null;
        if (saveBtn) saveBtn.disabled = true;
        try {
            await window.axios.post('/api/v1/staff/archive-records', {
                title: (document.getElementById('arc-title') as HTMLInputElement).value.trim(),
                application_uuid: appSelects.get('arc-app-uuid')?.getValue() || undefined,
                storage_location: (document.getElementById('arc-location') as HTMLInputElement).value.trim() || undefined,
                media_type: (document.getElementById('arc-media') as HTMLSelectElement).value,
                notes: (document.getElementById('arc-notes') as HTMLTextAreaElement).value.trim() || undefined,
            });
            toastSuccess('Archive record created');
            form.reset();
            appSelects.get('arc-app-uuid')?.clear();
            hideModal('modal-add-archive');
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
