import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { badgeYesNo, createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type Dept = { uuid: string; code: string; name: string };

type TemplateStep = {
    uuid?: string;
    step_order: number;
    label: string;
    sla_hours?: number | null;
    department?: { uuid?: string; code?: string; name?: string } | null;
};

type TemplateRow = {
    uuid: string;
    code: string;
    name: string;
    classification: string;
    is_active: boolean;
    step_count?: number;
    steps?: TemplateStep[];
    created_at?: string | null;
    updated_at?: string | null;
};

type TemplateSummary = {
    active?: number;
    inactive?: number;
    simple?: number;
    complex?: number;
    highly_technical?: number;
    total?: number;
};

type StepPrefill = {
    department_uuid?: string;
    label?: string;
    sla_hours?: number | null;
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

function classBadge(classification: string): string {
    const map: Record<string, string> = {
        simple: 'bg-success-subtle text-success',
        complex: 'bg-warning-subtle text-warning',
        highly_technical: 'bg-danger-subtle text-danger',
    };
    const cls = map[classification] || 'bg-info-subtle text-info';
    return `<span class="badge ${cls}">${escapeHtml(humanize(classification))}</span>`;
}

function paintSummary(summary?: TemplateSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-rt-stat="${key}"]`).forEach((el) => {
            el.textContent = value;
        });
    };
    if (!summary) {
        ['active', 'inactive', 'simple', 'complex', 'highly_technical', 'total'].forEach((k) => set(k, '—'));
        return;
    }
    set('active', String(summary.active ?? 0));
    set('inactive', String(summary.inactive ?? 0));
    set('simple', String(summary.simple ?? 0));
    set('complex', String(summary.complex ?? 0));
    set('highly_technical', String(summary.highly_technical ?? 0));
    set('total', String(summary.total ?? 0));
}

function stepsTableHtml(steps: TemplateStep[]): string {
    if (!steps.length) {
        return `<p class="text-muted mb-0">No steps defined.</p>`;
    }
    const sorted = [...steps].sort((a, b) => a.step_order - b.step_order);
    const rows = sorted
        .map(
            (step) => `<tr>
                <td class="text-muted">${escapeHtml(String(step.step_order))}</td>
                <td>
                    <div class="fw-medium">${escapeHtml(step.label)}</div>
                    <div class="text-muted small">${escapeHtml(
                        step.department
                            ? `${step.department.code || ''} — ${step.department.name || ''}`
                            : 'No department',
                    )}</div>
                </td>
                <td class="text-end">${escapeHtml(String(step.sla_hours ?? 24))} h</td>
            </tr>`,
        )
        .join('');
    return `<div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr><th style="width:3rem">#</th><th>Step / Department</th><th class="text-end">SLA</th></tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

function renderDetail(row: TemplateRow): string {
    const steps = row.steps || [];
    return `
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Code</p>
                <p class="fw-semibold mb-0">${escapeHtml(row.code)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Classification</p>
                <div>${classBadge(row.classification)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Steps</p>
                <p class="fw-semibold mb-0">${escapeHtml(String(row.step_count ?? steps.length))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Active</p>
                <div>${badgeYesNo(Boolean(row.is_active))}</div>
            </div>
        </div>
        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Name</h6>
            <p class="fw-semibold mb-0">${escapeHtml(row.name)}</p>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Created</p>
                <p class="mb-0 small">${escapeHtml(formatDate(row.created_at))}</p>
            </div>
            <div class="col-md-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Updated</p>
                <p class="mb-0 small">${escapeHtml(formatDate(row.updated_at))}</p>
            </div>
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Routing path</h6>
        ${stepsTableHtml(steps)}
    `;
}

export function initRoutingTemplatesPage(): void {
    const tableEl = document.getElementById('templates-table');
    const form = document.getElementById('form-add-template') as HTMLFormElement | null;
    const stepsBox = document.getElementById('tpl-steps');
    const addStepBtn = document.getElementById('btn-add-step');
    if (!tableEl || !form || !stepsBox) return;

    let departments: Dept[] = [];
    let table: ApicsDataTableApi<TemplateRow> | null = null;
    let viewing: TemplateRow | null = null;
    const detailBody = document.getElementById('tpl-detail-body');
    const detailTitle = document.getElementById('modal-tpl-detail-label');
    const formTitle = document.getElementById('modal-add-template-label');
    const saveBtn = document.getElementById('btn-save-template');
    const uuidInput = document.getElementById('tpl-uuid') as HTMLInputElement | null;

    const renderStepRow = (prefill?: StepPrefill): void => {
        const options = departments
            .map(
                (d) =>
                    `<option value="${escapeHtml(d.uuid)}"${
                        prefill?.department_uuid === d.uuid ? ' selected' : ''
                    }>${escapeHtml(d.code)} — ${escapeHtml(d.name)}</option>`,
            )
            .join('');
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end tpl-step-row border rounded p-2 bg-light-subtle';
        row.innerHTML = `
            <div class="col-md-5">
                <label class="form-label">Department</label>
                <select class="form-select step-dept" required>${options}</select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Label</label>
                <input class="form-control step-label" required placeholder="Step label" maxlength="255" value="${escapeHtml(
                    prefill?.label || '',
                )}">
            </div>
            <div class="col-md-2">
                <label class="form-label">SLA (h)</label>
                <input type="number" class="form-control step-sla" value="${escapeHtml(
                    String(prefill?.sla_hours ?? 24),
                )}" min="1" max="8760">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-soft-danger w-100 btn-remove-step" aria-label="Remove step">
                    <i class="ri-close-line"></i>
                </button>
            </div>`;
        row.querySelector('.btn-remove-step')?.addEventListener('click', () => row.remove());
        stepsBox.appendChild(row);
    };

    const loadDepartments = async (): Promise<void> => {
        const { data } = await window.axios.get('/api/v1/admin/departments', { params: { per_page: 100 } });
        departments = data.data?.items || [];
    };

    const resetCreateForm = (): void => {
        form.reset();
        if (uuidInput) uuidInput.value = '';
        (document.getElementById('tpl-active') as HTMLSelectElement).value = '1';
        stepsBox.innerHTML = '';
        renderStepRow();
        if (formTitle) formTitle.textContent = 'Add routing template';
        if (saveBtn) saveBtn.textContent = 'Save template';
    };

    const openCreate = (): void => {
        resetCreateForm();
        showModal('modal-add-template');
    };

    const openEdit = (row: TemplateRow): void => {
        viewing = row;
        if (uuidInput) uuidInput.value = row.uuid;
        (document.getElementById('tpl-code') as HTMLInputElement).value = row.code;
        (document.getElementById('tpl-name') as HTMLInputElement).value = row.name;
        (document.getElementById('tpl-classification') as HTMLSelectElement).value = row.classification;
        (document.getElementById('tpl-active') as HTMLSelectElement).value = row.is_active ? '1' : '0';
        stepsBox.innerHTML = '';
        const steps = [...(row.steps || [])].sort((a, b) => a.step_order - b.step_order);
        if (steps.length) {
            steps.forEach((step) =>
                renderStepRow({
                    department_uuid: step.department?.uuid,
                    label: step.label,
                    sla_hours: step.sla_hours ?? 24,
                }),
            );
        } else {
            renderStepRow();
        }
        if (formTitle) formTitle.textContent = `Edit template · ${row.code}`;
        if (saveBtn) saveBtn.textContent = 'Update template';
        hideModal('modal-tpl-detail');
        showModal('modal-add-template');
    };

    const openDetail = (row: TemplateRow): void => {
        viewing = row;
        if (detailTitle) detailTitle.textContent = `Template · ${row.code}`;
        if (detailBody) detailBody.innerHTML = renderDetail(row);
        showModal('modal-tpl-detail');
    };

    const deleteTemplate = async (row: TemplateRow): Promise<void> => {
        if (!(await confirmAction('Delete template?', `${row.code} will be soft-deleted.`))) return;
        try {
            await window.axios.delete(`/api/v1/admin/routing-templates/${row.uuid}`);
            hideModal('modal-tpl-detail');
            toastSuccess('Template deleted');
            await table?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Delete failed');
        }
    };

    addStepBtn?.addEventListener('click', () => renderStepRow());
    document.getElementById('btn-open-add-template')?.addEventListener('click', () => openCreate());
    document.getElementById('btn-tpl-edit')?.addEventListener('click', () => {
        if (!viewing) return;
        openEdit(viewing);
    });
    document.getElementById('btn-tpl-delete')?.addEventListener('click', () => {
        if (!viewing) return;
        void deleteTemplate(viewing);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const rows = Array.from(stepsBox.querySelectorAll('.tpl-step-row'));
        if (!rows.length) {
            toastError('Add at least one routing step');
            return;
        }
        const steps = rows.map((row, index) => ({
            department_uuid: (row.querySelector('.step-dept') as HTMLSelectElement).value,
            label: (row.querySelector('.step-label') as HTMLInputElement).value.trim(),
            step_order: index + 1,
            sla_hours: Number((row.querySelector('.step-sla') as HTMLInputElement).value || 24),
        }));
        const uuid = uuidInput?.value.trim() || '';
        const payload = {
            code: (document.getElementById('tpl-code') as HTMLInputElement).value.trim(),
            name: (document.getElementById('tpl-name') as HTMLInputElement).value.trim(),
            classification: (document.getElementById('tpl-classification') as HTMLSelectElement).value,
            is_active: (document.getElementById('tpl-active') as HTMLSelectElement).value === '1',
            steps,
        };
        const btn = saveBtn as HTMLButtonElement | null;
        if (btn) btn.disabled = true;
        try {
            if (uuid) {
                await window.axios.put(`/api/v1/admin/routing-templates/${uuid}`, payload);
                toastSuccess('Template updated');
            } else {
                await window.axios.post('/api/v1/admin/routing-templates', payload);
                toastSuccess('Template created');
            }
            resetCreateForm();
            hideModal('modal-add-template');
            await table?.reload(false);
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || (uuid ? 'Update failed' : 'Create failed')));
        } finally {
            if (btn) btn.disabled = false;
        }
    });

    void (async () => {
        try {
            await loadDepartments();
            if (!stepsBox.children.length) {
                renderStepRow();
            }
            table = await createApicsDataTable<TemplateRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'routing-templates',
                rowId: 'uuid',
                filters: [
                    {
                        id: 'classification',
                        label: 'Classification',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'simple', label: 'Simple' },
                            { value: 'complex', label: 'Complex' },
                            { value: 'highly_technical', label: 'Highly technical' },
                        ],
                        match: (row, value) => row.classification === value,
                    },
                    {
                        id: 'active',
                        label: 'Active',
                        options: [
                            { value: '', label: 'All' },
                            { value: '1', label: 'Active' },
                            { value: '0', label: 'Inactive' },
                        ],
                        match: (row, value) => String(Number(row.is_active)) === value,
                    },
                ],
                columns: [
                    {
                        data: 'code',
                        title: 'Code',
                        responsivePriority: 1,
                        render: (data) => `<span class="fw-semibold">${escapeHtml(String(data ?? ''))}</span>`,
                    },
                    {
                        data: 'name',
                        title: 'Name',
                        responsivePriority: 1,
                        render: (d, _t, row) =>
                            `<div class="fw-medium text-truncate" style="max-width:14rem">${escapeHtml(String(d ?? ''))}</div>
                             <div class="text-muted small">${row.step_count ?? (row.steps || []).length} step(s)</div>`,
                    },
                    {
                        data: 'classification',
                        title: 'Classification',
                        responsivePriority: 1,
                        render: (data) => classBadge(String(data ?? '')),
                    },
                    {
                        data: 'steps',
                        title: 'Path preview',
                        responsivePriority: 3,
                        orderable: false,
                        render: (_data, _type, row) => {
                            const steps = [...(row.steps || [])].sort((a, b) => a.step_order - b.step_order);
                            if (!steps.length) return '—';
                            const preview = steps
                                .slice(0, 3)
                                .map((s) => escapeHtml(s.department?.code || s.label))
                                .join(' → ');
                            const more = steps.length > 3 ? ` +${steps.length - 3}` : '';
                            return `<span class="small">${preview}${more}</span>`;
                        },
                    },
                    {
                        data: 'is_active',
                        title: 'Active',
                        responsivePriority: 2,
                        render: (data) => badgeYesNo(Boolean(data)),
                    },
                ],
                actions: [
                    { id: 'view', label: 'View template', onClick: (row) => openDetail(row) },
                    { id: 'edit', label: 'Edit', onClick: (row) => openEdit(row) },
                    {
                        id: 'delete',
                        label: 'Delete',
                        danger: true,
                        dividerBefore: true,
                        onClick: (row) => void deleteTemplate(row),
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/admin/routing-templates', {
                        params: { per_page: 100 },
                    });
                    paintSummary(data.data?.summary || null);
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to initialize routing templates');
            paintSummary(null);
        }
    })();
}
