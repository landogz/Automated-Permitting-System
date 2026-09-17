import { escapeHtml, showModal } from '../../utils/bootstrap-modal';
import { toastError } from '../../utils/toast';

type TemplateStep = {
    step_order?: number;
    label?: string;
    sla_hours?: number | null;
    department?: { code?: string; name?: string } | null;
};

type TemplateRow = {
    uuid: string;
    code: string;
    name: string;
    classification: string;
    is_active: boolean;
    steps?: TemplateStep[];
};

function humanize(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function classBadge(classification: string): string {
    const map: Record<string, string> = {
        simple: 'bg-success-subtle text-success',
        complex: 'bg-warning-subtle text-warning',
        highly_technical: 'bg-danger-subtle text-danger',
    };
    const cls = map[classification] || 'bg-info-subtle text-info';
    return `<span class="badge ${cls}">${escapeHtml(humanize(classification))}</span>`;
}

function renderTemplates(items: TemplateRow[]): string {
    if (!items.length) {
        return `<p class="text-muted mb-0">No routing templates configured yet. An admin with Workflow Config access can add them.</p>`;
    }

    return items
        .map((tpl) => {
            const steps = [...(tpl.steps || [])].sort((a, b) => (a.step_order || 0) - (b.step_order || 0));
            const path = steps.length
                ? steps
                      .map(
                          (s) =>
                              `<li class="mb-1">
                                <span class="badge bg-secondary-subtle text-secondary me-1">${escapeHtml(String(s.step_order ?? ''))}</span>
                                <span class="fw-medium">${escapeHtml(s.label || 'Step')}</span>
                                <span class="text-muted"> — ${escapeHtml(s.department?.name || s.department?.code || 'Dept')}</span>
                                <span class="text-muted small">(${escapeHtml(String(s.sla_hours ?? 24))}h)</span>
                            </li>`,
                      )
                      .join('')
                : '<li class="text-muted">No steps</li>';

            return `<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="fw-semibold">${escapeHtml(tpl.code)}</span>
                    ${classBadge(tpl.classification)}
                    ${
                        tpl.is_active
                            ? '<span class="badge bg-success-subtle text-success">Active</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>'
                    }
                </div>
                <p class="mb-2">${escapeHtml(tpl.name)}</p>
                <ol class="list-unstyled mb-0 ps-1">${path}</ol>
            </div>`;
        })
        .join('');
}

export function initOpsRoutingTemplatesBrowse(): void {
    const trigger = document.getElementById('btn-browse-routing-templates');
    const body = document.getElementById('ops-routing-templates-body');
    if (!trigger || !body) {
        return;
    }

    trigger.addEventListener('click', () => {
        void (async () => {
            body.innerHTML = `<div class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading templates…
            </div>`;
            showModal('modal-ops-routing-templates');
            try {
                const { data } = await window.axios.get('/api/v1/staff/routing-templates', {
                    params: { per_page: 50 },
                });
                body.innerHTML = renderTemplates(data.data?.items || []);
                const manageBtn = document.getElementById('btn-ops-manage-routing-templates');
                if (manageBtn && data.data?.can_manage) {
                    manageBtn.classList.remove('d-none');
                }
            } catch (error: any) {
                body.innerHTML = `<p class="text-danger mb-0">${escapeHtml(
                    error?.response?.data?.message || 'Unable to load routing templates.',
                )}</p>`;
                toastError(error?.response?.data?.message || 'Unable to load routing templates');
            }
        })();
    });
}
