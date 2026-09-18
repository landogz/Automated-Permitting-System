import { escapeHtml } from '../../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi } from '../../../utils/datatable';
import { toastError, toastSuccess } from '../../../utils/toast';
import { userAvatarHtml } from '../../../utils/user-avatar';
import {
    actorCell,
    clientCell,
    eventBadge,
    formatWhenStack,
    prettyJson,
    resourceCell,
    type AuditRow,
} from './format';

declare const bootstrap: {
    Offcanvas: {
        getOrCreateInstance: (el: HTMLElement) => { show: () => void };
    };
};

export function initAuditPage(): void {
    const root = document.getElementById('apics-audit-console');
    const tableEl = document.getElementById('audit-table');
    if (!root || !tableEl) {
        return;
    }

    const rangeSelect = root.querySelector<HTMLSelectElement>('[data-audit-range]');
    const dateFrom = root.querySelector<HTMLInputElement>('[data-audit-date-from]');
    const dateTo = root.querySelector<HTMLInputElement>('[data-audit-date-to]');
    const customDateEls = root.querySelectorAll<HTMLElement>('[data-audit-custom-dates]');
    const drawer = document.getElementById('audit-inspect-drawer');
    const drawerBody = document.querySelector<HTMLElement>('[data-audit-inspect-body]');
    const drawerEvent = document.querySelector<HTMLElement>('[data-audit-inspect-event]');

    let table: ApicsDataTableApi<AuditRow> | null = null;
    let actorOptions: Array<{ value: string; label: string }> = [{ value: '', label: 'All actors' }];
    let categoryOptions: Array<{ value: string; label: string }> = [
        { value: '', label: 'All events' },
    ];

    const paintStats = (summary?: Record<string, number> | null): void => {
        if (!summary) return;
        (['events_24h', 'active_sessions', 'fee_overrides_24h', 'security_anomalies_24h'] as const).forEach((key) => {
            const el = root.querySelector<HTMLElement>(`[data-audit-stat="${key}"]`);
            if (el) {
                el.textContent = String(summary[key] ?? 0);
            }
        });

        const securityEl = root.querySelector<HTMLElement>('[data-audit-stat="security_anomalies_24h"]');
        const securityCard = securityEl?.closest('.apics-kpi-card');
        if (securityCard) {
            const count = Number(summary.security_anomalies_24h ?? 0);
            securityCard.classList.toggle('apics-kpi-card--emerald', count === 0);
            securityCard.classList.toggle('apics-kpi-card--rose', count > 0);
        }
    };

    const syncCustomDates = (): void => {
        const isCustom = rangeSelect?.value === 'custom';
        customDateEls.forEach((el) => el.classList.toggle('d-none', !isCustom));
    };

    const rangeParams = (): Record<string, string | undefined> => {
        const range = rangeSelect?.value || '';
        return {
            range: range || undefined,
            date_from: range === 'custom' ? (dateFrom?.value || undefined) : undefined,
            date_to: range === 'custom' ? (dateTo?.value || undefined) : undefined,
        };
    };

    const openInspect = async (row: AuditRow): Promise<void> => {
        if (!drawer || !drawerBody) return;

        if (drawerEvent) {
            drawerEvent.textContent = row.event;
        }
        drawerBody.innerHTML = `
            <div class="text-center text-muted py-5">
                <div class="spinner-border text-primary avatar-sm" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>
        `;

        bootstrap.Offcanvas.getOrCreateInstance(drawer).show();

        try {
            const { data } = await window.axios.get(`/api/v1/admin/audit-logs/${row.uuid}`);
            if (!data?.status) {
                throw new Error(data?.message || 'Unable to load event');
            }

            const item = data.data as AuditRow;
            const oldJson = prettyJson(item.old_values ?? {});
            const newJson = prettyJson(item.new_values ?? item.meta ?? {});
            const hasStructuredDiff = Boolean(item.old_values || item.new_values);

            drawerBody.innerHTML = `
                <dl class="apics-audit-meta-grid">
                    <div>
                        <dt>When (PHT)</dt>
                        <dd>${formatWhenStack(item.created_at)}</dd>
                    </div>
                    <div>
                        <dt>Actor</dt>
                        <dd>
                            <span class="apics-audit-actor">
                                ${userAvatarHtml(
                                    item.actor_name || 'User',
                                    item.actor_avatar_url || item.user?.avatar_url || null,
                                    'apics-audit-actor__avatar',
                                )}
                                <span class="min-w-0">
                                    <strong class="d-block">${escapeHtml(item.actor_name || '—')}</strong>
                                    ${item.actor_role_label ? `<div class="text-muted fs-12">${escapeHtml(item.actor_role_label)}</div>` : ''}
                                </span>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt>Target resource</dt>
                        <dd>${resourceCell(item)}</dd>
                    </div>
                    <div>
                        <dt>IP &amp; client</dt>
                        <dd>${clientCell(item)}</dd>
                    </div>
                    <div>
                        <dt>Request URL</dt>
                        <dd><code class="fs-12">${escapeHtml(item.request_url || '—')}</code></dd>
                    </div>
                    <div>
                        <dt>Session ID</dt>
                        <dd><code class="fs-12">${escapeHtml(item.session_id || '—')}</code></dd>
                    </div>
                    <div>
                        <dt>User agent</dt>
                        <dd class="fs-12">${escapeHtml(item.user_agent || '—')}</dd>
                    </div>
                    <div>
                        <dt>Severity</dt>
                        <dd><span class="badge bg-secondary-subtle text-secondary text-uppercase">${escapeHtml(item.severity || 'success')}</span></dd>
                    </div>
                </dl>

                <h6 class="fs-13 fw-semibold mb-2">${hasStructuredDiff ? 'Payload delta' : 'Event payload'}</h6>
                <div class="apics-audit-diff">
                    ${hasStructuredDiff
                        ? `
                            <div class="apics-audit-diff__pane apics-audit-diff__pane--old">
                                <div class="apics-audit-diff__pane-title">Old values</div>
                                <pre>${escapeHtml(oldJson)}</pre>
                            </div>
                            <div class="apics-audit-diff__pane apics-audit-diff__pane--new">
                                <div class="apics-audit-diff__pane-title">New values</div>
                                <pre>${escapeHtml(newJson)}</pre>
                            </div>
                        `
                        : `
                            <div class="apics-audit-diff__pane apics-audit-diff__pane--new" style="grid-column: 1 / -1;">
                                <div class="apics-audit-diff__pane-title">Meta</div>
                                <pre>${escapeHtml(prettyJson(item.meta ?? {}))}</pre>
                            </div>
                        `}
                </div>
            `;
        } catch (error: any) {
            drawerBody.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(error?.response?.data?.message || 'Unable to inspect event')}</div>`;
            toastError(error?.response?.data?.message || 'Unable to inspect event');
        }
    };

    const buildTable = async (): Promise<void> => {
        if (table) {
            table.destroy();
            table = null;
            // Recreate tbody shell after destroy
            tableEl.innerHTML = '<thead class="table-light"></thead><tbody></tbody>';
        }

        table = await createApicsDataTable<AuditRow>({
            table: tableEl as HTMLTableElement,
            exportFileName: 'audit-logs',
            searchMode: 'server',
            pageLength: 25,
            rowId: 'uuid',
            order: [[0, 'desc']],
            emptyMessage: 'No audit events recorded yet.',
            noResultsMessage: 'No events match these investigative filters.',
            filters: [
                {
                    id: 'category',
                    label: 'Event category',
                    options: categoryOptions,
                },
                {
                    id: 'actor',
                    label: 'Actor',
                    options: actorOptions,
                },
                {
                    id: 'severity',
                    label: 'Severity',
                    options: [
                        { value: '', label: 'All' },
                        { value: 'success', label: 'Success' },
                        { value: 'warning', label: 'Warnings' },
                        { value: 'failure', label: 'Security / failed' },
                    ],
                },
            ],
            columns: [
                {
                    data: 'created_at',
                    title: 'Timestamp',
                    className: 'apics-audit-col-when',
                    responsivePriority: 1,
                    render: (d) => formatWhenStack(d ? String(d) : null),
                },
                {
                    data: 'event',
                    title: 'Event',
                    className: 'apics-audit-col-event',
                    responsivePriority: 1,
                    render: (_d, _t, row) => eventBadge(row.event, row.badge_tone),
                },
                {
                    data: 'actor_name',
                    title: 'Actor',
                    className: 'apics-audit-col-actor',
                    responsivePriority: 2,
                    render: (_d, _t, row) => actorCell(row),
                },
                {
                    data: 'resource',
                    title: 'Target resource',
                    className: 'apics-audit-col-resource',
                    responsivePriority: 2,
                    orderable: false,
                    render: (_d, _t, row) => resourceCell(row),
                },
                {
                    data: 'ip_address',
                    title: 'IP & client',
                    className: 'apics-audit-col-client',
                    responsivePriority: 3,
                    render: (_d, _t, row) => clientCell(row),
                },
            ],
            actions: [
                {
                    id: 'inspect',
                    label: 'View Diff',
                    icon: 'ri-code-s-slash-line',
                    onClick: (row) => {
                        void openInspect(row);
                    },
                },
            ],
            fetchData: async (ctx) => {
                const { data } = await window.axios.get('/api/v1/admin/audit-logs', {
                    params: {
                        per_page: 100,
                        search: ctx.search || undefined,
                        category: ctx.filters.category || undefined,
                        actor: ctx.filters.actor || undefined,
                        severity: ctx.filters.severity || undefined,
                        ...rangeParams(),
                    },
                });

                if (!data?.status) {
                    throw new Error(data?.message || 'Unable to load audit logs');
                }

                paintStats(data.data?.summary);

                const filters = data.data?.filters;
                if (filters?.actors?.length) {
                    actorOptions = [
                        { value: '', label: 'All actors' },
                        ...filters.actors.map((a: { value: string; label: string }) => ({
                            value: a.value,
                            label: a.label,
                        })),
                    ];
                }
                if (filters?.categories?.length) {
                    categoryOptions = [
                        { value: '', label: 'All events' },
                        ...filters.categories.map((c: { value: string; label: string }) => ({
                            value: c.value,
                            label: c.label,
                        })),
                    ];
                }

                return (data.data?.items || []) as AuditRow[];
            },
        });
    };

    syncCustomDates();

    rangeSelect?.addEventListener('change', () => {
        syncCustomDates();
        void table?.reload(true);
    });
    dateFrom?.addEventListener('change', () => {
        if (rangeSelect?.value === 'custom') {
            void table?.reload(true);
        }
    });
    dateTo?.addEventListener('change', () => {
        if (rangeSelect?.value === 'custom') {
            void table?.reload(true);
        }
    });

    root.querySelector('[data-audit-refresh]')?.addEventListener('click', () => {
        void (async () => {
            await table?.reload(false);
            toastSuccess('Audit log refreshed');
        })();
    });

    void (async () => {
        try {
            // Warm filter option lists once
            const { data } = await window.axios.get('/api/v1/admin/audit-logs', {
                params: { per_page: 1, range: '24h' },
            });
            paintStats(data.data?.summary);
            const filters = data.data?.filters;
            if (filters?.actors?.length) {
                actorOptions = [
                    { value: '', label: 'All actors' },
                    ...filters.actors.map((a: { value: string; label: string }) => ({
                        value: a.value,
                        label: a.label,
                    })),
                ];
            }
            if (filters?.categories?.length) {
                categoryOptions = [
                    { value: '', label: 'All events' },
                    ...filters.categories.map((c: { value: string; label: string }) => ({
                        value: c.value,
                        label: c.label,
                    })),
                ];
            }

            await buildTable();
            root.setAttribute('aria-busy', 'false');
        } catch (error: any) {
            root.setAttribute('aria-busy', 'false');
            toastError(error?.response?.data?.message || 'Sign in as admin to view audit logs.');
        }
    })();
}
