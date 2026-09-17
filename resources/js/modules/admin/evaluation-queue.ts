import { escapeHtml } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi, type ApicsColumn, type ApicsRowAction } from '../../utils/datatable';
import { openOpsApplicationDetail } from '../ops/application-detail/index';
import { toastSuccessAndGoNext } from '../../utils/operations-next-step';
import { bindOpsQueueTabs, setOpsActiveCount, setOpsCompletedCount } from '../../utils/ops-completed';
import {
    applicationNoLinkHtml,
    classificationBadgeHtml,
    classificationSlaTagHtml,
    formatElapsed,
    slaCountdownHtml,
    slaRiskFromSubmitted,
    statusBadgeHtml,
} from '../../utils/ops-ui';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type QueueRow = {
    uuid: string;
    application_no?: string;
    project_title?: string;
    project_location?: string;
    status?: string;
    classification?: string | null;
    submitted_at?: string | null;
    updated_at?: string | null;
    payload?: Record<string, unknown> | null;
    applicant?: { name?: string; email?: string };
    user?: { name?: string };
    form?: { code?: string; title?: string };
};

function payloadVal(row: QueueRow, key: string): string {
    const value = row.payload?.[key];
    if (value === null || value === undefined || value === '') {
        return '—';
    }
    return String(value);
}

function paintEvalStats(active: QueueRow[], completed: QueueRow[]): void {
    const pending = active.filter((r) => r.status === 'submitted').length;
    const under = active.filter((r) => r.status === 'under_evaluation').length;
    const slaWarn = active.filter((r) => {
        const risk = slaRiskFromSubmitted(r.submitted_at, r.classification);
        return risk === 'warn' || risk === 'over';
    }).length;
    const startToday = new Date();
    startToday.setHours(0, 0, 0, 0);
    const completedToday = completed.filter((r) => {
        if (!r.updated_at) return false;
        return new Date(r.updated_at).getTime() >= startToday.getTime();
    }).length;

    const set = (key: string, value: number): void => {
        document.querySelectorAll(`[data-eval-stat="${key}"]`).forEach((el) => {
            el.textContent = String(value);
        });
    };
    set('pending', pending);
    set('under_evaluation', under);
    set('sla_warn', slaWarn);
    set('completed_today', completedToday);
}

function queueColumns(): ApicsColumn<QueueRow>[] {
    return [
        {
            data: 'application_no',
            title: 'Application No.',
            responsivePriority: 1,
            render: (_data, _type, row) => `
                <div>${applicationNoLinkHtml(row.application_no, row.uuid)}</div>
                ${classificationSlaTagHtml(row.classification)}
                ${slaCountdownHtml(row.submitted_at, row.classification)}`,
        },
        {
            data: 'project_title',
            title: 'Project',
            responsivePriority: 1,
            render: (_data, _type, row) => `
                <div>${escapeHtml(row.project_title || '—')}</div>
                <div class="text-muted small">${escapeHtml(row.project_location || '')}</div>
                <div class="text-muted small">${escapeHtml(row.form?.code || '')}</div>`,
        },
        {
            data: 'payload',
            title: 'Owner / Area',
            orderable: false,
            responsivePriority: 2,
            render: (_data, _type, row) => `
                <div>${escapeHtml(payloadVal(row, 'owner_name'))}</div>
                <div class="text-muted small">Lot ${escapeHtml(payloadVal(row, 'lot_area'))} · Floor ${escapeHtml(payloadVal(row, 'floor_area'))} sqm</div>`,
        },
        {
            data: 'status',
            title: 'Status',
            responsivePriority: 1,
            render: (data) => statusBadgeHtml(String(data ?? ''), { pulse: true }),
        },
        {
            data: 'classification',
            title: 'Classification',
            responsivePriority: 2,
            render: (data) => classificationBadgeHtml(data ? String(data) : null),
        },
        {
            data: 'applicant',
            title: 'Applicant',
            responsivePriority: 3,
            render: (_data, _type, row) =>
                `<div>${escapeHtml(row.applicant?.name || row.user?.name || '—')}</div>
                 <div class="text-muted small">${escapeHtml(row.applicant?.email || '')}</div>`,
        },
    ];
}

export function initEvaluationQueuePage(): void {
    const tableEl = document.getElementById('queue-table');
    const completedEl = document.getElementById('queue-completed-table');
    if (!tableEl) return;

    let activeTable: ApicsDataTableApi<QueueRow> | null = null;
    let completedTable: ApicsDataTableApi<QueueRow> | null = null;
    let activeRows: QueueRow[] = [];
    let completedRows: QueueRow[] = [];
    let timerTick: ReturnType<typeof setInterval> | null = null;
    let timerStartedAt: number | null = null;

    const timerPill = document.getElementById('eval-timer-pill');
    const timerAppEl = timerPill?.querySelector('[data-timer-app]');
    const timerElapsedEl = timerPill?.querySelector('[data-timer-elapsed]');

    const clearTimerUi = (): void => {
        if (timerTick) {
            clearInterval(timerTick);
            timerTick = null;
        }
        timerStartedAt = null;
        timerPill?.classList.add('is-idle');
    };

    const paintTimerElapsed = (): void => {
        if (!timerStartedAt || !timerElapsedEl) return;
        const seconds = Math.floor((Date.now() - timerStartedAt) / 1000);
        timerElapsedEl.textContent = formatElapsed(seconds);
    };

    const showTimerUi = (applicationNo: string, startedAtIso?: string | null, elapsedSeconds?: number): void => {
        if (!timerPill || !timerAppEl) return;
        timerAppEl.textContent = applicationNo || '—';
        timerPill.classList.remove('is-idle');
        if (startedAtIso) {
            timerStartedAt = new Date(startedAtIso).getTime();
        } else if (typeof elapsedSeconds === 'number') {
            timerStartedAt = Date.now() - elapsedSeconds * 1000;
        } else {
            timerStartedAt = Date.now();
        }
        paintTimerElapsed();
        if (timerTick) clearInterval(timerTick);
        timerTick = setInterval(paintTimerElapsed, 1000);
    };

    const reloadBoth = async (): Promise<void> => {
        await Promise.all([activeTable?.reload(false), completedTable?.reload(false)]);
    };

    document.getElementById('btn-stop-timer')?.addEventListener('click', async () => {
        try {
            const { data } = await window.axios.post('/api/v1/staff/timer/stop');
            clearTimerUi();
            toastSuccess(`Timer stopped (${data.data?.duration_seconds ?? 0}s)`);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'No open timer');
        }
    });

    document.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement | null)?.closest<HTMLElement>('[data-ops-open-app]');
        if (!target) return;
        const uuid = target.dataset.opsOpenApp;
        if (uuid) void openOpsApplicationDetail(uuid);
    });

    const activeActions: ApicsRowAction<QueueRow>[] = [
        {
            id: 'view',
            label: 'Review',
            primary: true,
            onClick: (row) => void openOpsApplicationDetail(row.uuid),
        },
        {
            id: 'classify',
            label: 'Classify',
            onClick: async (row) => {
                if (!(await confirmAction('Auto-classify application?', 'Uses active classification rules by priority.'))) {
                    return;
                }
                try {
                    const { data: res } = await window.axios.post(`/api/v1/staff/applications/${row.uuid}/classify`, {
                        auto: true,
                    });
                    toastSuccess(`Classified as ${res.data?.classification || 'done'}`);
                    await reloadBoth();
                } catch (error: any) {
                    toastError(error?.response?.data?.message || 'Classify failed');
                }
            },
        },
        {
            id: 'route',
            label: 'Generate routing slip',
            disabled: (row) => !row.classification,
            onClick: async (row) => {
                if (!(await confirmAction('Generate routing slip?', 'Creates steps from the matching routing template.'))) {
                    return;
                }
                try {
                    const { data: res } = await window.axios.post(`/api/v1/staff/applications/${row.uuid}/routing-slip`);
                    toastSuccess(`Routing slip ${res.data?.slip_no || ''} generated`);
                    await reloadBoth();
                } catch (error: any) {
                    toastError(error?.response?.data?.message || 'Routing failed');
                }
            },
        },
        {
            id: 'timer',
            label: 'Start timer',
            dividerBefore: true,
            onClick: async (row) => {
                try {
                    const { data } = await window.axios.post(`/api/v1/staff/applications/${row.uuid}/timer/start`);
                    showTimerUi(
                        data.data?.application_no || row.application_no || '—',
                        data.data?.started_at,
                    );
                    toastSuccess('Timer started');
                } catch (error: any) {
                    toastError(error?.response?.data?.message || 'Timer start failed');
                }
            },
        },
        {
            id: 'evaluate',
            label: 'Evaluate (compliant)',
            onClick: async (row) => {
                try {
                    const { data: created } = await window.axios.post(`/api/v1/staff/applications/${row.uuid}/evaluations`, {
                        findings: [{ item: 'Completeness', status: 'ok' }],
                        remarks: 'Initial evaluation sheet',
                    });
                    const evaluationUuid = created.data?.uuid;
                    if (!evaluationUuid) {
                        toastError('Evaluation create failed');
                        return;
                    }
                    if (!(await confirmAction('Mark compliant?', 'Decide this evaluation as compliant.'))) {
                        return;
                    }
                    const { data: decided } = await window.axios.post(`/api/v1/staff/evaluations/${evaluationUuid}/decide`, {
                        result: 'compliant',
                        remarks: 'Compliant per initial review',
                    });
                    toastSuccessAndGoNext('Evaluation decided: compliant', decided.data?.next_step);
                    await reloadBoth();
                } catch (error: any) {
                    toastError(error?.response?.data?.message || 'Evaluation failed');
                }
            },
        },
    ];

    void (async () => {
        try {
            try {
                const { data } = await window.axios.get('/api/v1/staff/timer/current');
                if (data?.data?.uuid) {
                    showTimerUi(
                        data.data.application_no || '—',
                        data.data.started_at,
                        data.data.elapsed_seconds,
                    );
                }
            } catch {
                // optional
            }

            activeTable = await createApicsDataTable<QueueRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'evaluation-queue-active',
                rowId: 'uuid',
                searchMode: 'server',
                filters: [
                    {
                        id: 'classified',
                        label: 'Classification',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'yes', label: 'Classified' },
                            { value: 'no', label: 'Unclassified' },
                        ],
                        match: (row, value) => {
                            const has = Boolean(row.classification);
                            return value === 'yes' ? has : !has;
                        },
                    },
                ],
                columns: queueColumns(),
                actions: activeActions,
                fetchData: async ({ search }) => {
                    const { data } = await window.axios.get('/api/v1/staff/queue', {
                        params: { search: search || undefined, bucket: 'active', per_page: 100 },
                    });
                    activeRows = data.data?.items || [];
                    setOpsActiveCount('eval-queue', data.data?.meta?.total ?? activeRows.length);
                    paintEvalStats(activeRows, completedRows);
                    return activeRows;
                },
            });

            if (completedEl) {
                completedTable = await createApicsDataTable<QueueRow>({
                    table: completedEl as HTMLTableElement,
                    exportFileName: 'evaluation-queue-completed',
                    rowId: 'uuid',
                    searchMode: 'server',
                    columns: queueColumns(),
                    actions: [
                        {
                            id: 'view',
                            label: 'View details',
                            primary: true,
                            onClick: (row) => void openOpsApplicationDetail(row.uuid),
                        },
                    ],
                    fetchData: async ({ search }) => {
                        const { data } = await window.axios.get('/api/v1/staff/queue', {
                            params: { search: search || undefined, bucket: 'completed', per_page: 100 },
                        });
                        completedRows = data.data?.items || [];
                        setOpsCompletedCount('eval-queue', data.data?.meta?.total ?? completedRows.length);
                        paintEvalStats(activeRows, completedRows);
                        return completedRows;
                    },
                });
            }

            bindOpsQueueTabs(
                'eval-queue',
                () => completedTable?.raw as any,
                () => activeTable?.raw as any,
            );
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Sign in as staff/admin to view queue.');
        }
    })();
}
