import { escapeHtml, hideModal, showModal } from '../../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi, type ApicsColumn, type ApicsRowAction } from '../../../utils/datatable';
import { openOpsApplicationDetail } from '../../ops/application-detail/index';
import { toastSuccessAndGoNext } from '../../../utils/operations-next-step';
import { bindOpsQueueTabs, setOpsActiveCount, setOpsCompletedCount } from '../../../utils/ops-completed';
import {
    formatScheduleBlock,
    inspectionTypeLabel,
    inspectorAvatarHtml,
    resultPendingHtml,
    statusBadgeHtml,
} from '../../../utils/ops-ui';
import { initApplicationSelects } from '../../application-select/application-select';
import { createLocationPicker, type LocationPickerApi } from '../../location-map/location-picker';
import { toastError, toastSuccess } from '../../../utils/toast';
import {
    bindTeamEditor,
    loadInspectionTemplates,
    readComplianceChecklist,
    readElectricalFields,
    readSelectedDisciplines,
    readTeamRows,
    renderComplianceChecklist,
    renderDisciplineCheckboxes,
    renderElectricalFields,
    renderTeamRows,
} from './form-helpers';
import { printActionsForRow } from './print';
import type { InspectionRow } from './types';

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
                        ? new Date(row.scheduled_at).toLocaleTimeString('en-US', {
                              hour: 'numeric',
                              minute: '2-digit',
                          })
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
            render: (_d, _t, row) => {
                const teamCount = row.team_inspectors?.length || 0;
                const lead = inspectorAvatarHtml(row.inspector?.name);
                if (teamCount > 1) {
                    return `${lead}<div class="text-muted small mt-1">+${teamCount - 1} team</div>`;
                }
                if (teamCount === 1 && !row.inspector?.name) {
                    return inspectorAvatarHtml(row.team_inspectors?.[0]?.name);
                }
                return lead;
            },
        },
    ];
}

export function initInspectionsPage(): void {
    const tableEl = document.getElementById('inspections-table');
    const completedEl = document.getElementById('inspections-completed-table');
    const form = document.getElementById('form-schedule-inspection') as HTMLFormElement | null;
    const completeForm = document.getElementById('form-complete-inspection') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    const appSelects = initApplicationSelects(form);
    let activeTable: ApicsDataTableApi<InspectionRow> | null = null;
    let completedTable: ApicsDataTableApi<InspectionRow> | null = null;
    let activeRows: InspectionRow[] = [];
    let completedRows: InspectionRow[] = [];
    let completingRow: InspectionRow | null = null;

    const locationRoot = document.querySelector<HTMLElement>(
        '[data-location-picker][data-address-input="insp-location"]',
    );
    let locationPicker: LocationPickerApi | null = null;

    const teamContainer = document.getElementById('insp-team-rows');
    const disciplineContainer = document.getElementById('insp-disciplines');
    const complianceContainer = document.getElementById('insp-complete-compliance');
    const electricalContainer = document.getElementById('insp-complete-electrical');
    const electricalSection = document.getElementById('insp-complete-electrical-section');

    renderDisciplineCheckboxes(disciplineContainer, ['structural']);
    renderTeamRows(teamContainer, [{ name: '', role: 'Lead', discipline: 'structural' }]);
    bindTeamEditor(teamContainer, document.getElementById('btn-insp-add-team'));

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

    const openCompleteModal = async (row: InspectionRow): Promise<void> => {
        completingRow = row;
        const title = document.getElementById('modal-complete-inspection-label');
        if (title) {
            title.textContent = `Record forms · ${row.inspection_no}`;
        }

        const resultEl = document.getElementById('insp-complete-result') as HTMLSelectElement | null;
        const notesEl = document.getElementById('insp-complete-notes') as HTMLTextAreaElement | null;
        const weatherEl = document.getElementById('insp-complete-weather') as HTMLInputElement | null;
        const siteEl = document.getElementById('insp-complete-site') as HTMLTextAreaElement | null;
        const findingsEl = document.getElementById('insp-complete-findings') as HTMLTextAreaElement | null;
        const defectsEl = document.getElementById('insp-complete-defects') as HTMLTextAreaElement | null;
        const recoEl = document.getElementById('insp-complete-recommendations') as HTMLTextAreaElement | null;
        const overallEl = document.getElementById('insp-complete-overall') as HTMLTextAreaElement | null;
        const elecResultEl = document.getElementById('insp-complete-elec-result') as HTMLSelectElement | null;
        const elecRemarksEl = document.getElementById('insp-complete-elec-remarks') as HTMLTextAreaElement | null;

        if (resultEl) resultEl.value = 'passed';
        if (notesEl) notesEl.value = '';
        if (weatherEl) weatherEl.value = row.inspector_notes?.weather || '';
        if (siteEl) siteEl.value = row.inspector_notes?.site_conditions || '';
        if (findingsEl) findingsEl.value = row.inspector_notes?.findings || row.notes || '';
        if (defectsEl) defectsEl.value = row.inspector_notes?.observed_defects || '';
        if (recoEl) recoEl.value = row.inspector_notes?.recommendations || '';

        let items =
            (Array.isArray(row.compliance_sheet)
                ? row.compliance_sheet
                : row.compliance_sheet?.items) || [];
        if (!items.length) {
            try {
                const templates = await loadInspectionTemplates();
                items = templates.qms65_default_items || [];
            } catch {
                items = [];
            }
        }
        renderComplianceChecklist(complianceContainer, items);
        if (overallEl) {
            overallEl.value = Array.isArray(row.compliance_sheet)
                ? ''
                : row.compliance_sheet?.overall_remarks || '';
        }

        const showElectrical = Boolean(row.requires_electrical_form);
        electricalSection?.classList.toggle('d-none', !showElectrical);
        if (showElectrical) {
            const elec = row.electrical_form || {};
            renderElectricalFields(electricalContainer, {
                service_entrance: elec.service_entrance || 'na',
                grounding: elec.grounding || 'na',
                panel_boards: elec.panel_boards || 'na',
                wiring_methods: elec.wiring_methods || 'na',
                fixtures_devices: elec.fixtures_devices || 'na',
                load_schedule: elec.load_schedule || 'na',
            });
            if (elecResultEl) elecResultEl.value = elec.result || elec.status || 'na';
            if (elecRemarksEl) elecRemarksEl.value = elec.remarks || '';
        }

        showModal('modal-complete-inspection');
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
                void openOpsApplicationDetail(row.application.uuid, { tab: 'inspection' });
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
                        id: 'complete',
                        label: 'Record / complete forms',
                        onClick: (row) => void openCompleteModal(row),
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
                    actions: [
                        viewAction,
                        ...(['qms-38', 'qms-39', 'o-03', 'qms-65', 'dpwh-77-006-e'] as const).map((doc) => ({
                            id: `print-${doc}`,
                            label:
                                {
                                    'qms-38': 'Print QMS-38 · Schedule',
                                    'qms-39': 'Print QMS-39 · Team',
                                    'o-03': 'Print O-03 · Notes',
                                    'qms-65': 'Print QMS-65 · Compliance',
                                    'dpwh-77-006-e': 'Print DPWH 77-006-E',
                                }[doc],
                            visible: (row: InspectionRow) => {
                                if (!row.print_urls?.[doc]) return false;
                                if (doc === 'dpwh-77-006-e') {
                                    return Boolean(
                                        row.requires_electrical_form || row.electrical_form?.result,
                                    );
                                }
                                return true;
                            },
                            onClick: (row: InspectionRow) => {
                                const action = printActionsForRow(row).find((a) => a.id === `print-${doc}`);
                                action?.onClick();
                            },
                        })),
                    ],
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
        const team = readTeamRows(teamContainer);
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
                schedule_sheet: {
                    purpose: (document.getElementById('insp-purpose') as HTMLTextAreaElement | null)?.value.trim() || undefined,
                    meeting_point:
                        (document.getElementById('insp-meeting-point') as HTMLInputElement | null)?.value.trim() ||
                        undefined,
                    disciplines: readSelectedDisciplines(disciplineContainer),
                    remarks:
                        (document.getElementById('insp-schedule-remarks') as HTMLTextAreaElement | null)?.value.trim() ||
                        undefined,
                    coordination_notes:
                        (document.getElementById('insp-coordination') as HTMLTextAreaElement | null)?.value.trim() ||
                        undefined,
                },
                team_inspectors: team.length ? team : undefined,
            });
            toastSuccess('Inspection scheduled (QMS-38/39)');
            form.reset();
            renderDisciplineCheckboxes(disciplineContainer, ['structural']);
            renderTeamRows(teamContainer, [{ name: '', role: 'Lead', discipline: 'structural' }]);
            ensureLocationPicker()?.setValue({ address: '', latitude: null, longitude: null });
            appSelects.get('insp-app-uuid')?.clear();
            hideModal('modal-schedule-inspection');
            await reloadBoth();
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Schedule failed');
        }
    });

    completeForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        await submitInspectionForms('complete');
    });

    document.getElementById('btn-insp-save-only')?.addEventListener('click', () => {
        void submitInspectionForms('save');
    });

    const buildFormsPayload = (mode: 'save' | 'complete'): Record<string, unknown> | null => {
        if (!completingRow) return null;

        const result = (document.getElementById('insp-complete-result') as HTMLSelectElement).value as
            | 'passed'
            | 'failed'
            | 'conditional';
        const findings =
            (document.getElementById('insp-complete-findings') as HTMLTextAreaElement).value.trim() || '';
        const notes =
            (document.getElementById('insp-complete-notes') as HTMLTextAreaElement).value.trim() ||
            findings ||
            (mode === 'complete' && result === 'passed' ? 'Inspection passed' : '');

        if (mode === 'complete' && result === 'failed' && notes.length < 10) {
            toastError('Describe what failed (at least 10 characters) in Notes or Findings.');
            return null;
        }

        const payload: Record<string, unknown> = {
            notes: notes || undefined,
            inspector_notes: {
                weather: (document.getElementById('insp-complete-weather') as HTMLInputElement).value.trim(),
                site_conditions: (document.getElementById('insp-complete-site') as HTMLTextAreaElement).value.trim(),
                findings,
                observed_defects: (document.getElementById('insp-complete-defects') as HTMLTextAreaElement).value.trim(),
                recommendations: (
                    document.getElementById('insp-complete-recommendations') as HTMLTextAreaElement
                ).value.trim(),
            },
            compliance_sheet: {
                form_code: 'QMS-65',
                items: readComplianceChecklist(complianceContainer),
                overall_remarks: (document.getElementById('insp-complete-overall') as HTMLTextAreaElement).value.trim(),
            },
        };

        if (mode === 'complete') {
            payload.result = result;
            if (!payload.notes) {
                payload.notes = result === 'passed' ? 'Inspection passed' : notes;
            }
        }

        if (completingRow.requires_electrical_form) {
            payload.electrical_form = {
                form_code: '77-006-E',
                ...readElectricalFields(electricalContainer),
                result: (document.getElementById('insp-complete-elec-result') as HTMLSelectElement).value,
                remarks: (document.getElementById('insp-complete-elec-remarks') as HTMLTextAreaElement).value.trim(),
            };
        }

        return payload;
    };

    const submitInspectionForms = async (mode: 'save' | 'complete'): Promise<void> => {
        if (!completingRow) return;
        const payload = buildFormsPayload(mode);
        if (!payload) return;

        const saveBtn = document.getElementById('btn-insp-save-only') as HTMLButtonElement | null;
        const completeBtn = document.getElementById('btn-insp-save-complete') as HTMLButtonElement | null;
        saveBtn && (saveBtn.disabled = true);
        completeBtn && (completeBtn.disabled = true);

        try {
            if (mode === 'save') {
                await window.axios.post(`/api/v1/staff/inspections/${completingRow.uuid}/forms`, payload);
                toastSuccess('Inspection forms saved (not completed)');
                // Keep modal open so staff can continue, but refresh row state in tables.
                await reloadBoth();
                const refreshed =
                    activeRows.find((r) => r.uuid === completingRow?.uuid) ||
                    completedRows.find((r) => r.uuid === completingRow?.uuid);
                if (refreshed) {
                    completingRow = refreshed;
                }
                return;
            }

            const { data: res } = await window.axios.post(
                `/api/v1/staff/inspections/${completingRow.uuid}/complete`,
                payload,
            );
            hideModal('modal-complete-inspection');
            const result = String(payload.result || '');
            toastSuccessAndGoNext(
                result === 'failed' ? 'Inspection completed (failed)' : 'Inspection completed',
                res.data?.next_step,
            );
            completingRow = null;
            await reloadBoth();
        } catch (error: any) {
            toastError(
                error?.response?.data?.message ||
                    (mode === 'save' ? 'Save failed' : 'Complete failed'),
            );
        } finally {
            saveBtn && (saveBtn.disabled = false);
            completeBtn && (completeBtn.disabled = false);
        }
    };
}
