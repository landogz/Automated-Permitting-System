import { escapeHtml } from '../../../utils/bootstrap-modal';

export type EvalChecklistItem = {
    code?: string;
    label?: string;
    item?: string;
    status?: string;
    remarks?: string;
};

function statusSelectHtml(value = 'na'): string {
    return `<select class="form-select form-select-sm" data-status-field="status">
        <option value="ok" ${value === 'ok' ? 'selected' : ''}>OK</option>
        <option value="fail" ${value === 'fail' ? 'selected' : ''}>Fail</option>
        <option value="na" ${value === 'na' ? 'selected' : ''}>N/A</option>
    </select>`;
}

function bindStatusPaint(container: HTMLElement): void {
    container.querySelectorAll<HTMLSelectElement>('[data-status-field="status"]').forEach((select) => {
        const paint = (): void => {
            const card = select.closest('.apics-insp-check-card') as HTMLElement | null;
            if (card) card.dataset.status = select.value;
        };
        paint();
        select.addEventListener('change', paint);
    });
}

export function renderEvalChecklist(container: HTMLElement | null, items: EvalChecklistItem[]): void {
    if (!container) return;
    const cards = items
        .map((item, index) => {
            const code = item.code || `ITEM_${index + 1}`;
            const label = item.label || item.item || code;
            const status = item.status || 'na';
            const remarks = item.remarks || '';
            return `<div class="col-12 col-md-6">
                <div class="apics-insp-check-card" data-eval-item data-status="${escapeHtml(status)}" data-code="${escapeHtml(code)}" data-label="${escapeHtml(label)}">
                    <div class="apics-insp-check-card__top">
                        <div class="min-w-0 flex-grow-1">
                            <span class="apics-insp-check-card__code">${escapeHtml(code)}</span>
                            <div class="apics-insp-check-card__label">${escapeHtml(label)}</div>
                        </div>
                        <div class="apics-insp-check-card__status">${statusSelectHtml(status)}</div>
                    </div>
                    <input type="text" class="form-control form-control-sm mt-2" data-eval-remarks value="${escapeHtml(remarks)}" placeholder="Remarks (optional)" maxlength="1000">
                </div>
            </div>`;
        })
        .join('');
    container.innerHTML = `<div class="row g-2 g-md-3">${cards}</div>`;
    bindStatusPaint(container);
}

export function readEvalChecklist(container: HTMLElement | null): EvalChecklistItem[] {
    if (!container) return [];
    return Array.from(container.querySelectorAll('[data-eval-item]')).map((row) => ({
        code: (row as HTMLElement).dataset.code || '',
        label: (row as HTMLElement).dataset.label || '',
        status: (row.querySelector('[data-status-field="status"]') as HTMLSelectElement | null)?.value || 'na',
        remarks: (row.querySelector('[data-eval-remarks]') as HTMLInputElement | null)?.value.trim() || '',
    }));
}

export function formatDuration(seconds: number): string {
    const s = Math.max(0, Math.floor(seconds));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    if (h > 0) return `${h}h ${String(m).padStart(2, '0')}m`;
    return `${m}m ${String(s % 60).padStart(2, '0')}s`;
}

let cachedTemplates: {
    qms63_default_items?: EvalChecklistItem[];
    qms64_default_items?: EvalChecklistItem[];
} | null = null;

export async function loadEvaluationTemplates(): Promise<{
    qms63_default_items?: EvalChecklistItem[];
    qms64_default_items?: EvalChecklistItem[];
}> {
    if (cachedTemplates) return cachedTemplates;
    const { data } = await window.axios.get('/api/v1/staff/evaluation-form-templates');
    cachedTemplates = data.data || {};
    return cachedTemplates;
}
