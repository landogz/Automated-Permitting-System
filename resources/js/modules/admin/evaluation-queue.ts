import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
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
import {
    formatDuration,
    loadEvaluationTemplates,
    readEvalChecklist,
    renderEvalChecklist,
} from './evaluation-queue/form-helpers';

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
    let evaluatingRow: QueueRow | null = null;
    let draftEvaluationUuid: string | null = null;

    const timerPill = document.getElementById('eval-timer-pill');
    const timerAppEl = timerPill?.querySelector('[data-timer-app]');
    const timerElapsedEl = timerPill?.querySelector('[data-timer-elapsed]');
    const qms63Box = document.getElementById('eval-qms63-checklist');
    const qms64Box = document.getElementById('eval-qms64-checklist');

    const paintTimeSummary = async (): Promise<void> => {
        const host = document.getElementById('eval-time-summary');
        if (!host) return;
        try {
            const { data } = await window.axios.get('/api/v1/staff/evaluation-time-summary');
            const summary = data.data || {};
            const depts = (summary.by_department || []) as Array<{
                department?: { name?: string; code?: string };
                total_seconds?: number;
                sessions?: number;
            }>;
            const staff = (summary.by_staff || []) as Array<{
                user?: { name?: string };
                total_seconds?: number;
                sessions?: number;
            }>;
            host.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-2">By department</p>
                        ${
                            depts.length
                                ? depts
                                      .map(
                                          (row) => `<div class="d-flex justify-content-between border-bottom py-1 small">
                                            <span>${escapeHtml(row.department?.name || row.department?.code || 'Unassigned')}</span>
                                            <span class="font-monospace">${escapeHtml(formatDuration(row.total_seconds || 0))} · ${row.sessions || 0} sess.</span>
                                          </div>`,
                                      )
                                      .join('')
                                : '<p class="text-muted small mb-0">No closed time logs yet.</p>'
                        }
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-2">By staff</p>
                        ${
                            staff.length
                                ? staff
                                      .map(
                                          (row) => `<div class="d-flex justify-content-between border-bottom py-1 small">
                                            <span>${escapeHtml(row.user?.name || '—')}</span>
                                            <span class="font-monospace">${escapeHtml(formatDuration(row.total_seconds || 0))} · ${row.sessions || 0} sess.</span>
                                          </div>`,
                                      )
                                      .join('')
                                : '<p class="text-muted small mb-0">No closed time logs yet.</p>'
                        }
                    </div>
                    <div class="col-12">
                        <p class="small text-muted mb-0">Total tracked: <strong class="font-monospace">${escapeHtml(formatDuration(summary.total_seconds || 0))}</strong></p>
                    </div>
                </div>`;
        } catch {
            host.innerHTML = `<p class="text-muted small mb-0">Unable to load time summary.</p>`;
        }
    };

    const openEvaluateModal = async (row: QueueRow): Promise<void> => {
        evaluatingRow = row;
        draftEvaluationUuid = null;
        const title = document.getElementById('modal-evaluate-label');
        if (title) title.textContent = `Evaluate · ${row.application_no || ''}`;
        const meta = document.getElementById('eval-modal-meta');
        if (meta) meta.textContent = `${row.project_title || '—'} · ${row.classification || 'Unclassified'}`;

        const resultEl = document.getElementById('eval-result') as HTMLSelectElement | null;
        const remarksEl = document.getElementById('eval-remarks') as HTMLTextAreaElement | null;
        const overallEl = document.getElementById('eval-overall') as HTMLTextAreaElement | null;
        const discEl = document.getElementById('eval-discipline') as HTMLTextAreaElement | null;
        if (resultEl) resultEl.value = 'compliant';
        if (remarksEl) remarksEl.value = '';
        if (overallEl) overallEl.value = '';
        if (discEl) discEl.value = '';

        let completenessDefaults: ReturnType<typeof readEvalChecklist> = [];
        let technicalDefaults: ReturnType<typeof readEvalChecklist> = [];
        try {
            const templates = await loadEvaluationTemplates();
            completenessDefaults = templates.qms63_default_items || [];
            technicalDefaults = templates.qms64_default_items || [];
        } catch {
            completenessDefaults = [];
            technicalDefaults = [];
        }

        try {
            const { data } = await window.axios.get(`/api/v1/staff/applications/${row.uuid}`);
            const evaluations = (data.data?.evaluations || []) as Array<{
                uuid?: string;
                status?: string;
                result?: string | null;
                remarks?: string | null;
                findings?: {
                    completeness?: ReturnType<typeof readEvalChecklist>;
                    technical?: ReturnType<typeof readEvalChecklist>;
                    overall_remarks?: string;
                    discipline_remarks?: string;
                } | null;
            }>;
            const draft = evaluations.find((rowEval) => String(rowEval.status || '') === 'draft');
            const decided = evaluations.find((rowEval) => String(rowEval.status || '') === 'decided');

            if (draft?.uuid) {
                draftEvaluationUuid = draft.uuid;
                renderEvalChecklist(qms63Box, draft.findings?.completeness?.length
                    ? draft.findings.completeness
                    : completenessDefaults);
                renderEvalChecklist(qms64Box, draft.findings?.technical?.length
                    ? draft.findings.technical
                    : technicalDefaults);
                if (remarksEl) remarksEl.value = draft.remarks || '';
                if (overallEl) overallEl.value = draft.findings?.overall_remarks || '';
                if (discEl) discEl.value = draft.findings?.discipline_remarks || '';
            } else {
                renderEvalChecklist(qms63Box, completenessDefaults);
                renderEvalChecklist(qms64Box, technicalDefaults);
                if (decided && resultEl && decided.result) {
                    resultEl.value = decided.result;
                }
            }
        } catch {
            renderEvalChecklist(qms63Box, completenessDefaults);
            renderEvalChecklist(qms64Box, technicalDefaults);
        }

        showModal('modal-evaluate-application');
    };

    const buildFindingsPayload = (): Record<string, unknown> => ({
        form_code: 'QMS-63',
        completeness: readEvalChecklist(qms63Box),
        technical: readEvalChecklist(qms64Box),
        overall_remarks: (document.getElementById('eval-overall') as HTMLTextAreaElement | null)?.value.trim() || '',
        discipline_remarks:
            (document.getElementById('eval-discipline') as HTMLTextAreaElement | null)?.value.trim() || '',
    });

    const ensureDraftEvaluation = async (): Promise<string | null> => {
        if (!evaluatingRow) return null;
        if (draftEvaluationUuid) return draftEvaluationUuid;
        const { data } = await window.axios.post(`/api/v1/staff/applications/${evaluatingRow.uuid}/evaluations`, {
            findings: buildFindingsPayload(),
            remarks: (document.getElementById('eval-remarks') as HTMLTextAreaElement | null)?.value.trim() || undefined,
        });
        draftEvaluationUuid = data.data?.uuid || null;
        return draftEvaluationUuid;
    };

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
            void paintTimeSummary();
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
            label: 'Evaluate (QMS-63/64)',
            onClick: (row) => void openEvaluateModal(row),
        },
    ];

    document.getElementById('btn-eval-save-only')?.addEventListener('click', async () => {
        if (!evaluatingRow) return;
        const btn = document.getElementById('btn-eval-save-only') as HTMLButtonElement | null;
        if (btn) btn.disabled = true;
        try {
            const findings = buildFindingsPayload();
            const remarks =
                (document.getElementById('eval-remarks') as HTMLTextAreaElement | null)?.value.trim() || undefined;
            if (draftEvaluationUuid) {
                await window.axios.post(`/api/v1/staff/evaluations/${draftEvaluationUuid}/forms`, {
                    findings,
                    remarks,
                });
            } else {
                const { data } = await window.axios.post(
                    `/api/v1/staff/applications/${evaluatingRow.uuid}/evaluations`,
                    { findings, remarks },
                );
                draftEvaluationUuid = data.data?.uuid || null;
            }
            toastSuccess('Evaluation sheet saved (draft)');
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Save failed');
        } finally {
            if (btn) btn.disabled = false;
        }
    });

    document.getElementById('form-evaluate-application')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!evaluatingRow) return;
        const result =
            (document.getElementById('eval-result') as HTMLSelectElement | null)?.value || 'compliant';
        const resultLabel =
            result === 'compliant' ? 'compliant' : result === 'non_compliant' ? 'non-compliant' : 'needs info';
        if (
            !(await confirmAction(
                `Decide as ${resultLabel}?`,
                'This finalizes QMS-63/64 and advances the application status.',
            ))
        ) {
            return;
        }
        try {
            const findings = buildFindingsPayload();
            const remarks =
                (document.getElementById('eval-remarks') as HTMLTextAreaElement | null)?.value.trim() || undefined;
            const uuid = await ensureDraftEvaluation();
            if (!uuid) {
                toastError('Evaluation create failed');
                return;
            }
            await window.axios.post(`/api/v1/staff/evaluations/${uuid}/forms`, { findings, remarks });
            const { data: decided } = await window.axios.post(`/api/v1/staff/evaluations/${uuid}/decide`, {
                result,
                findings,
                remarks,
            });
            hideModal('modal-evaluate-application');
            toastSuccessAndGoNext(`Evaluation decided: ${resultLabel}`, decided.data?.next_step);
            draftEvaluationUuid = null;
            evaluatingRow = null;
            await reloadBoth();
            void paintTimeSummary();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Evaluation failed');
        }
    });

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

            void paintTimeSummary();

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
