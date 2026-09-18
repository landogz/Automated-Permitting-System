import { escapeHtml } from '../../../utils/bootstrap-modal';
import type { ComplianceItem, InspectionTemplatesPayload } from './types';

const DISCIPLINES = [
    { value: 'structural', label: 'Structural' },
    { value: 'architectural', label: 'Architectural' },
    { value: 'electrical', label: 'Electrical' },
    { value: 'sanitary', label: 'Sanitary' },
    { value: 'mechanical', label: 'Mechanical' },
    { value: 'fire_safety', label: 'Fire safety' },
] as const;

export function renderDisciplineCheckboxes(container: HTMLElement | null, selected: string[] = []): void {
    if (!container) return;
    const selectedSet = new Set(selected.map((d) => d.toLowerCase()));
    container.innerHTML = DISCIPLINES.map(
        (d) => `<label class="form-check form-check-inline me-2 mb-1">
            <input class="form-check-input" type="checkbox" name="insp-discipline" value="${d.value}" ${
                selectedSet.has(d.value) ? 'checked' : ''
            }>
            <span class="form-check-label">${escapeHtml(d.label)}</span>
        </label>`,
    ).join('');
}

export function readSelectedDisciplines(container: HTMLElement | null): string[] {
    if (!container) return [];
    return Array.from(container.querySelectorAll<HTMLInputElement>('input[name="insp-discipline"]:checked')).map(
        (el) => el.value,
    );
}

export function renderTeamRows(container: HTMLElement | null, rows: Array<{ name?: string; role?: string; discipline?: string }> = [{}]): void {
    if (!container) return;
    const list = rows.length ? rows : [{}];
    container.innerHTML = list
        .map(
            (row, index) => `<div class="row g-2 align-items-end mb-2" data-team-row>
                <div class="col-12 col-md-4">
                    <label class="form-label ${index === 0 ? '' : 'visually-hidden'}">Name</label>
                    <input type="text" class="form-control" data-team-name value="${escapeHtml(row.name || '')}" placeholder="Inspector name" maxlength="120">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label ${index === 0 ? '' : 'visually-hidden'}">Role</label>
                    <input type="text" class="form-control" data-team-role value="${escapeHtml(row.role || '')}" placeholder="Lead / Member" maxlength="80">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label ${index === 0 ? '' : 'visually-hidden'}">Discipline</label>
                    <select class="form-select" data-team-discipline>
                        <option value="">—</option>
                        ${DISCIPLINES.map(
                            (d) =>
                                `<option value="${d.value}" ${
                                    (row.discipline || '') === d.value ? 'selected' : ''
                                }>${escapeHtml(d.label)}</option>`,
                        ).join('')}
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="button" class="btn btn-soft-danger w-100" data-team-remove aria-label="Remove team member">
                        <i class="ri-delete-bin-line" aria-hidden="true"></i>
                    </button>
                </div>
            </div>`,
        )
        .join('');
}

export function readTeamRows(container: HTMLElement | null): Array<{ name: string; role: string; discipline: string }> {
    if (!container) return [];
    return Array.from(container.querySelectorAll('[data-team-row]'))
        .map((row) => {
            const name = (row.querySelector('[data-team-name]') as HTMLInputElement | null)?.value.trim() || '';
            const role = (row.querySelector('[data-team-role]') as HTMLInputElement | null)?.value.trim() || 'Member';
            const discipline =
                (row.querySelector('[data-team-discipline]') as HTMLSelectElement | null)?.value.trim() || '';
            return { name, role, discipline };
        })
        .filter((row) => row.name !== '');
}

export function bindTeamEditor(container: HTMLElement | null, addBtn: HTMLElement | null): void {
    if (!container) return;
    addBtn?.addEventListener('click', () => {
        const current = readTeamRows(container);
        renderTeamRows(container, [...current, { name: '', role: 'Member', discipline: '' }]);
    });
    container.addEventListener('click', (event) => {
        const target = event.target as HTMLElement | null;
        const btn = target?.closest('[data-team-remove]');
        if (!btn) return;
        const row = btn.closest('[data-team-row]');
        row?.remove();
        if (!container.querySelector('[data-team-row]')) {
            renderTeamRows(container, [{}]);
        }
    });
}

export function statusSelectHtml(name: string, value = 'na'): string {
    const options = [
        { value: 'ok', label: 'OK' },
        { value: 'fail', label: 'Fail' },
        { value: 'na', label: 'N/A' },
    ];
    return `<select class="form-select form-select-sm" data-status-field="${escapeHtml(name)}">
        ${options
            .map(
                (opt) =>
                    `<option value="${opt.value}" ${value === opt.value ? 'selected' : ''}>${opt.label}</option>`,
            )
            .join('')}
    </select>`;
}

function bindComplianceStatusPaint(container: HTMLElement): void {
    container.querySelectorAll<HTMLSelectElement>('[data-status-field="status"]').forEach((select) => {
        const paint = (): void => {
            const card = select.closest('.apics-insp-check-card') as HTMLElement | null;
            if (card) card.dataset.status = select.value;
        };
        paint();
        select.addEventListener('change', paint);
    });
}

export function renderComplianceChecklist(
    container: HTMLElement | null,
    items: ComplianceItem[],
): void {
    if (!container) return;
    const cards = items
        .map((item, index) => {
            const code = item.code || `ITEM_${index + 1}`;
            const label = item.label || item.item || code;
            const status = item.status || 'na';
            const remarks = item.remarks || item.notes || '';
            return `<div class="col-12 col-md-6">
                <div class="apics-insp-check-card" data-compliance-row data-status="${escapeHtml(status)}" data-code="${escapeHtml(code)}" data-label="${escapeHtml(label)}">
                    <div class="apics-insp-check-card__top">
                        <div class="min-w-0 flex-grow-1">
                            <span class="apics-insp-check-card__code">${escapeHtml(code)}</span>
                            <div class="apics-insp-check-card__label">${escapeHtml(label)}</div>
                        </div>
                        <div class="apics-insp-check-card__status">${statusSelectHtml('status', status)}</div>
                    </div>
                    <input type="text" class="form-control form-control-sm mt-2" data-compliance-remarks value="${escapeHtml(remarks)}" placeholder="Remarks (optional)" maxlength="1000">
                </div>
            </div>`;
        })
        .join('');
    container.innerHTML = `<div class="row g-2 g-md-3">${cards}</div>`;
    bindComplianceStatusPaint(container);
}

export function readComplianceChecklist(container: HTMLElement | null): ComplianceItem[] {
    if (!container) return [];
    return Array.from(container.querySelectorAll('[data-compliance-row]')).map((row) => ({
        code: (row as HTMLElement).dataset.code || '',
        label: (row as HTMLElement).dataset.label || '',
        status: (row.querySelector('[data-status-field="status"]') as HTMLSelectElement | null)?.value || 'na',
        remarks: (row.querySelector('[data-compliance-remarks]') as HTMLInputElement | null)?.value.trim() || '',
    }));
}

export function renderElectricalFields(container: HTMLElement | null, values: Record<string, string> = {}): void {
    if (!container) return;
    const fields = [
        { key: 'service_entrance', label: 'Service entrance' },
        { key: 'grounding', label: 'Grounding / bonding' },
        { key: 'panel_boards', label: 'Panel boards / overcurrent' },
        { key: 'wiring_methods', label: 'Wiring methods / raceways' },
        { key: 'fixtures_devices', label: 'Fixtures / devices' },
        { key: 'load_schedule', label: 'Load schedule conformance' },
    ];
    container.innerHTML = `<div class="row g-2">
        ${fields
            .map(
                (field) => `<div class="col-12 col-md-6">
                    <div class="apics-insp-elec-row h-100">
                        <span class="apics-insp-elec-row__label">${escapeHtml(field.label)}</span>
                        <div style="min-width:5.75rem">${statusSelectHtml(field.key, values[field.key] || 'na')}</div>
                    </div>
                </div>`,
            )
            .join('')}
    </div>`;
}

export function readElectricalFields(container: HTMLElement | null): Record<string, string> {
    if (!container) return {};
    const out: Record<string, string> = {};
    container.querySelectorAll<HTMLSelectElement>('[data-status-field]').forEach((el) => {
        const key = el.getAttribute('data-status-field');
        if (key) out[key] = el.value;
    });
    return out;
}

let cachedTemplates: InspectionTemplatesPayload | null = null;

export async function loadInspectionTemplates(): Promise<InspectionTemplatesPayload> {
    if (cachedTemplates) return cachedTemplates;
    const { data } = await window.axios.get('/api/v1/staff/inspection-form-templates');
    cachedTemplates = (data.data || {}) as InspectionTemplatesPayload;
    return cachedTemplates;
}
