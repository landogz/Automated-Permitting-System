import { escapeHtml, hideModal } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi, type ApicsColumn, type ApicsRowAction } from '../../utils/datatable';
import { openOpsApplicationDetail } from '../ops/application-detail/index';
import { toastSuccessAndGoNext } from '../../utils/operations-next-step';
import { bindOpsQueueTabs, setOpsActiveCount, setOpsCompletedCount } from '../../utils/ops-completed';
import {
    formatScheduleBlock,
    inspectionTypeLabel,
    inspectorAvatarHtml,
    resultPendingHtml,
    statusBadgeHtml,
} from '../../utils/ops-ui';
import { initApplicationSelects } from '../application-select/application-select';
import { createLocationPicker, type LocationPickerApi } from '../location-map/location-picker';
import { confirmAction, confirmWithReason, toastError, toastSuccess } from '../../utils/toast';

type InspectionRow = {
    uuid: string;
    inspection_no: string;
    type: string;
    status: string;
    result?: string | null;
    scheduled_at?: string | null;
    location?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    application?: {
        uuid?: string;
        application_no?: string;
        project_title?: string;
        project_location?: string;
        latitude?: number | null;
        longitude?: number | null;
    };
    inspector?: { name?: string };
};

function isSameDay(a: Date, b: Date): boolean {
    return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
}

function isJointType(type: string | null | undefined): boolean {
    const key = String(type || '').toLowerCase();
    return key === 'joint' || key.startsWith('joint_');
}

function paintInspStats(active: InspectionRow[], completed: InspectionRow[]): void {
    const now = new Date();
    const today = active.filter((r) => r.scheduled_at && isSameDay(new Date(r.scheduled_at), now)).length;
    const joint = active.filter((r) => isJointType(r.type)).length;
    const failed = completed.filter((r) => r.result === 'failed').length;
    const passed = completed.filter((r) => r.result === 'passed').length;
    const set = (key: string, value: number): void => {
        document.querySelectorAll(`[data-insp-stat="${key}"]`).forEach((el) => {
            el.textContent = String(value);
        });
    };
    set('today', today);
    set('joint', joint);
    set('failed', failed);
    set('passed', passed);
}

function renderCalendar(rows: InspectionRow[]): void {
    const pane = document.getElementById('insp-calendar-pane');
    if (!pane) return;

    const sorted = [...rows].sort((a, b) => {
        const ta = a.scheduled_at ? new Date(a.scheduled_at).getTime() : 0;
        const tb = b.scheduled_at ? new Date(b.scheduled_at).getTime() : 0;
        return ta - tb;
    });

    if (!sorted.length) {
        pane.innerHTML = `<div class="text-center text-muted py-5">No active inspections scheduled.</div>`;
        return;
    }

    const groups = new Map<string, InspectionRow[]>();
    sorted.forEach((row) => {
        const key = row.scheduled_at
            ? new Date(row.scheduled_at).toLocaleDateString('en-US', {
                  weekday: 'short',
                  month: 'short',
                  day: 'numeric',
                  year: 'numeric',
              })
            : 'Unscheduled';
        const list = groups.get(key) || [];
        list.push(row);
        groups.set(key, list);
    });

    pane.innerHTML = Array.from(groups.entries())
        .map(([day, items]) => {
            const body = items
                .map((row) => {
                    const time = row.scheduled_at
                        ? new Date(row.scheduled_at).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
                        : '—';
                    const resultHtml = row.result
                        ? statusBadgeHtml(String(row.result))
                        : resultPendingHtml('Not Recorded');
                    return `<div class="apics-insp-calendar__item">
                        <div>
                            <div class="fw-semibold">${escapeHtml(time)} · ${escapeHtml(inspectionTypeLabel(row.type))}</div>
                            <div class="text-muted">${escapeHtml(row.application?.application_no || row.inspection_no)} — ${escapeHtml(row.application?.project_title || '—')}</div>
                        </div>
                        <div>${resultHtml}</div>
                    </div>`;
                })
                .join('');
            return `<div class="apics-insp-calendar__day"><div class="apics-insp-calendar__day-title">${escapeHtml(day)}</div>${body}</div>`;
        })
        .join('');
}

function inspectionColumns(): ApicsColumn<InspectionRow>[] {
    return [
        {
            data: 'inspection_no',
            title: 'Inspection No.',
            responsivePriority: 1,
            render: (d) => `<span class="fw-medium font-monospace">${escapeHtml(String(d ?? ''))}</span>`,
        },
        {
            data: 'application',
            title: 'Application',
            responsivePriority: 1,
            render: (_d, _t, row) =>
                `<div class="apics-app-no">${escapeHtml(row.application?.application_no || '—')}</div>
                 <div class="text-muted small">${escapeHtml(row.application?.project_title || '')}</div>`,
        },
        {
            data: 'type',
            title: 'Type',
            responsivePriority: 2,
            render: (d) => escapeHtml(inspectionTypeLabel(String(d ?? ''))),
        },
        {
            data: 'status',
            title: 'Status',
            responsivePriority: 1,
            render: (d) => statusBadgeHtml(String(d ?? ''), { pulse: true }),
        },
        {
            data: 'result',
            title: 'Result',
            responsivePriority: 2,
            render: (d, _t, row) => {
                if (d) {
                    return statusBadgeHtml(String(d));
                }
                if (row.status === 'scheduled' || row.status === 'in_progress') {
                    return resultPendingHtml(
                        row.status === 'in_progress' ? 'Pending Verification' : 'Not Recorded',
                    );
                }
                return '<span class="text-muted">—</span>';
            },
        },
        {
            data: 'scheduled_at',
            title: 'Scheduled',
            responsivePriority: 2,
            render: (d) => formatScheduleBlock(d ? String(d) : null),
        },
        {
            data: 'inspector',
            title: 'Inspector',
            responsivePriority: 3,
            render: (_d, _t, row) => inspectorAvatarHtml(row.inspector?.name),
        },
    ];
}

export function initInspectionsPage(): void {
    const tableEl = document.getElementById('inspections-table');
    const completedEl = document.getElementById('inspections-completed-table');
    const form = document.getElementById('form-schedule-inspection') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    const appSelects = initApplicationSelects(form);
    let activeTable: ApicsDataTableApi<InspectionRow> | null = null;
    let completedTable: ApicsDataTableApi<InspectionRow> | null = null;
    let activeRows: InspectionRow[] = [];
    let completedRows: InspectionRow[] = [];

    const locationRoot = document.querySelector<HTMLElement>(
        '[data-location-picker][data-address-input="insp-location"]',
    );
    let locationPicker: LocationPickerApi | null = null;

    const ensureLocationPicker = (): LocationPickerApi | null => {
        if (locationPicker) return locationPicker;
        const addressInput = document.getElementById('insp-location') as HTMLInputElement | null;
        if (!locationRoot || !addressInput) return null;
        try {
            locationPicker = createLocationPicker({
                root: locationRoot,
                addressInput,
                latInput: document.getElementById('insp-location-lat') as HTMLInputElement | null,
                lngInput: document.getElementById('insp-location-lng') as HTMLInputElement | null,
            });
            locationRoot.dataset.locationMounted = '1';
        } catch {
            locationPicker = null;
        }
        return locationPicker;
    };

    document.getElementById('modal-schedule-inspection')?.addEventListener('shown.bs.modal', () => {
        ensureLocationPicker()?.invalidateSize();
    });

    document.getElementById('insp-app-uuid')?.addEventListener('change', () => {
        const uuid = (document.getElementById('insp-app-uuid') as HTMLInputElement).value.trim();
        if (!uuid) return;
        void (async () => {
            try {
                const { data } = await window.axios.get(`/api/v1/staff/applications/${uuid}`);
                const app = data.data;
                ensureLocationPicker()?.setValue({
                    address: app?.project_location || '',
                    latitude: app?.latitude ?? null,
                    longitude: app?.longitude ?? null,
                });
            } catch {
                // keep manual entry
            }
        })();
    });

    const reloadBoth = async (): Promise<void> => {
        await Promise.all([activeTable?.reload(false), completedTable?.reload(false)]);
    };

    const completeInspection = async (row: InspectionRow, result: 'passed' | 'failed'): Promise<void> => {
        let notes = 'Inspection passed';

        if (result === 'failed') {
            const reason = await confirmWithReason({
                title: 'Mark failed?',
                text: 'Describe what failed so Compliance and the applicant know the problem.',
                inputLabel: 'Failure reason',
                inputPlaceholder:
                    'e.g. Incomplete firewall, missing electrical grounding, setback encroachment…',
                confirmButtonText: 'Mark as failed',
                minLength: 10,
            });
            if (!reason) return;
            notes = reason;
        } else {
            const ok = await confirmAction(
                'Mark passed?',
                'Completes this inspection as passed. Confirm findings in the Inspect drawer when available.',
            );
            if (!ok) return;
        }

        try {
            const { data: res } = await window.axios.post(`/api/v1/staff/inspections/${row.uuid}/complete`, {
                result,
                notes,
                compliance_sheet:
                    result === 'passed'
                        ? [{ item: 'Site conditions', status: 'ok' }]
                        : [{ item: 'Deficiencies', status: 'fail', notes }],
                electrical_form: result === 'passed' ? { form: '77-006-E', status: 'ok' } : undefined,
            });
            toastSuccessAndGoNext(
                result === 'passed' ? 'Inspection completed (passed)' : 'Inspection completed (failed)',
                res.data?.next_step,
            );
            await reloadBoth();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Complete failed');
        }
    };

    const setView = (mode: 'table' | 'calendar'): void => {
        const tablePane = document.getElementById('insp-table-pane');
        const calPane = document.getElementById('insp-calendar-pane');
        const btnTable = document.getElementById('btn-insp-view-table');
        const btnCal = document.getElementById('btn-insp-view-calendar');
        const isCal = mode === 'calendar';
        tablePane?.classList.toggle('d-none', isCal);
        calPane?.classList.toggle('is-visible', isCal);
        btnTable?.classList.toggle('btn-primary', !isCal);
        btnTable?.classList.toggle('btn-soft-secondary', isCal);
        btnCal?.classList.toggle('btn-primary', isCal);
        btnCal?.classList.toggle('btn-soft-secondary', !isCal);
        if (isCal) {
            renderCalendar(activeRows);
        } else {
            requestAnimationFrame(() => {
                try {
                    (activeTable?.raw as any)?.columns?.adjust?.();
                } catch {
                    /* ignore */
                }
            });
        }
    };

    document.getElementById('btn-insp-view-table')?.addEventListener('click', () => setView('table'));
    document.getElementById('btn-insp-view-calendar')?.addEventListener('click', () => setView('calendar'));

    const viewAction: ApicsRowAction<InspectionRow> = {
        id: 'view',
        label: 'Inspect',
        primary: true,
        visible: (row) => Boolean(row.application?.uuid),
        onClick: (row) => {
            if (row.application?.uuid) {
                void openOpsApplicationDetail(row.application.uuid);
            }
        },
    };

    void (async () => {
        try {
            activeTable = await createApicsDataTable<InspectionRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'inspections-active',
                rowId: 'uuid',
                searchMode: 'server',
                order: [[5, 'desc']],
                columns: inspectionColumns(),
                actions: [
                    viewAction,
                    {
                        id: 'complete-pass',
                        label: 'Complete — Passed',
                        onClick: (row) => void completeInspection(row, 'passed'),
                    },
                    {
                        id: 'complete-fail',
                        label: 'Complete — Failed',
                        danger: true,
                        onClick: (row) => void completeInspection(row, 'failed'),
                    },
                ],
                fetchData: async ({ search }) => {
                    const { data } = await window.axios.get('/api/v1/staff/inspections', {
                        params: {
                            search: search || undefined,
                            bucket: 'active',
                            per_page: 100,
                        },
                    });
                    activeRows = data.data?.items || [];
                    setOpsActiveCount('insp-queue', data.data?.meta?.total ?? activeRows.length);
                    paintInspStats(activeRows, completedRows);
                    if (document.getElementById('insp-calendar-pane')?.classList.contains('is-visible')) {
                        renderCalendar(activeRows);
                    }
                    return activeRows;
                },
            });

            if (completedEl) {
                completedTable = await createApicsDataTable<InspectionRow>({
                    table: completedEl as HTMLTableElement,
                    exportFileName: 'inspections-completed',
                    rowId: 'uuid',
                    searchMode: 'server',
                    order: [[5, 'desc']],
                    columns: inspectionColumns(),
                    actions: [viewAction],
                    fetchData: async ({ search }) => {
                        const { data } = await window.axios.get('/api/v1/staff/inspections', {
                            params: {
                                search: search || undefined,
                                bucket: 'completed',
                                per_page: 100,
                            },
                        });
                        completedRows = data.data?.items || [];
                        setOpsCompletedCount('insp-queue', data.data?.meta?.total ?? completedRows.length);
                        paintInspStats(activeRows, completedRows);
                        return completedRows;
                    },
                });
            }

            bindOpsQueueTabs(
                'insp-queue',
                () => completedTable?.raw as any,
                () => activeTable?.raw as any,
            );
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load inspections');
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const appUuid =
            appSelects.get('insp-app-uuid')?.getValue() ||
            (document.getElementById('insp-app-uuid') as HTMLInputElement).value.trim();
        if (!appUuid) {
            toastError('Please select an application');
            return;
        }
        const scheduledLocal = (document.getElementById('insp-scheduled-at') as HTMLInputElement).value;
        try {
            await window.axios.post(`/api/v1/staff/applications/${appUuid}/inspections`, {
                type: (document.getElementById('insp-type') as HTMLSelectElement).value,
                scheduled_at: scheduledLocal ? new Date(scheduledLocal).toISOString() : undefined,
                location: (document.getElementById('insp-location') as HTMLInputElement).value.trim() || undefined,
                latitude: (() => {
                    const raw = (document.getElementById('insp-location-lat') as HTMLInputElement | null)?.value;
                    const n = raw ? Number(raw) : NaN;
                    return Number.isFinite(n) ? n : undefined;
                })(),
                longitude: (() => {
                    const raw = (document.getElementById('insp-location-lng') as HTMLInputElement | null)?.value;
                    const n = raw ? Number(raw) : NaN;
                    return Number.isFinite(n) ? n : undefined;
                })(),
            });
            toastSuccess('Inspection scheduled');
            form.reset();
            ensureLocationPicker()?.setValue({ address: '', latitude: null, longitude: null });
            appSelects.get('insp-app-uuid')?.clear();
            hideModal('modal-schedule-inspection');
            await reloadBoth();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Schedule failed');
        }
    });
}
