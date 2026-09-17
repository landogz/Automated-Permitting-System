import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { badgeYesNo, createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type RuleCondition = { field?: string; operator?: string; value?: string | number | null };

type RuleRow = {
    uuid: string;
    priority: number;
    code: string;
    name: string;
    classification: string;
    sla_hours: number;
    is_active: boolean;
    conditions?: RuleCondition[] | null;
    condition_count?: number;
    created_at?: string | null;
    updated_at?: string | null;
};

type RuleSummary = {
    active?: number;
    inactive?: number;
    simple?: number;
    complex?: number;
    highly_technical?: number;
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

function classBadge(classification: string): string {
    const map: Record<string, string> = {
        simple: 'bg-success-subtle text-success',
        complex: 'bg-warning-subtle text-warning',
        highly_technical: 'bg-danger-subtle text-danger',
    };
    const cls = map[classification] || 'bg-info-subtle text-info';
    return `<span class="badge ${cls}">${escapeHtml(humanize(classification))}</span>`;
}

function paintSummary(summary?: RuleSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-cr-stat="${key}"]`).forEach((el) => {
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

function conditionsHtml(conditions?: RuleCondition[] | null): string {
    const list = conditions || [];
    if (!list.length) {
        return `<p class="text-muted mb-0 fs-13">No conditions — unconditional match (default / catch-all).</p>`;
    }
    const rows = list
        .map(
            (c, i) => `<tr>
                <td class="text-muted">${i + 1}</td>
                <td><code>${escapeHtml(String(c.field || ''))}</code></td>
                <td>${escapeHtml(String(c.operator || ''))}</td>
                <td>${escapeHtml(String(c.value ?? ''))}</td>
            </tr>`,
        )
        .join('');
    return `<div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 align-middle">
            <thead class="table-light"><tr><th>#</th><th>Field</th><th>Op</th><th>Value</th></tr></thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

function renderDetail(row: RuleRow): string {
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
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Priority</p>
                <p class="fw-semibold mb-0">${escapeHtml(String(row.priority))}</p>
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
            <div class="col-md-4">
                <p class="text-muted fs-11 text-uppercase mb-1">SLA hours</p>
                <p class="fw-medium mb-0">${escapeHtml(String(row.sla_hours))} h</p>
            </div>
            <div class="col-md-4">
                <p class="text-muted fs-11 text-uppercase mb-1">Created</p>
                <p class="mb-0 small">${escapeHtml(formatDate(row.created_at))}</p>
            </div>
            <div class="col-md-4">
                <p class="text-muted fs-11 text-uppercase mb-1">Updated</p>
                <p class="mb-0 small">${escapeHtml(formatDate(row.updated_at))}</p>
            </div>
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Conditions (${row.condition_count ?? (row.conditions || []).length})</h6>
        ${conditionsHtml(row.conditions)}
    `;
}

function collectPayload(): Record<string, unknown> {
    const field = (document.getElementById('rule-field') as HTMLInputElement).value.trim();
    const operator = (document.getElementById('rule-operator') as HTMLSelectElement).value;
    let value: string | number = (document.getElementById('rule-value') as HTMLInputElement).value.trim();
    if (value !== '' && !Number.isNaN(Number(value)) && operator !== 'contains') {
        value = Number(value);
    }

    return {
        code: (document.getElementById('rule-code') as HTMLInputElement).value.trim(),
        name: (document.getElementById('rule-name') as HTMLInputElement).value.trim(),
        classification: (document.getElementById('rule-classification') as HTMLSelectElement).value,
        priority: Number((document.getElementById('rule-priority') as HTMLInputElement).value || 100),
        sla_hours: Number((document.getElementById('rule-sla') as HTMLInputElement).value || 72),
        is_active: (document.getElementById('rule-active') as HTMLSelectElement).value === '1',
        conditions: field && value !== '' ? [{ field, operator, value }] : [],
    };
}

export function initClassificationRulesPage(): void {
    const tableEl = document.getElementById('rules-table');
    const form = document.getElementById('form-add-rule') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    let table: ApicsDataTableApi<RuleRow> | null = null;
    let viewing: RuleRow | null = null;
    const detailBody = document.getElementById('rule-detail-body');
    const detailTitle = document.getElementById('modal-rule-detail-label');
    const formTitle = document.getElementById('modal-add-rule-label');
    const saveBtn = document.getElementById('btn-save-rule');
    const uuidInput = document.getElementById('rule-uuid') as HTMLInputElement | null;

    const resetCreateForm = (): void => {
        form.reset();
        if (uuidInput) uuidInput.value = '';
        (document.getElementById('rule-priority') as HTMLInputElement).value = '100';
        (document.getElementById('rule-sla') as HTMLInputElement).value = '72';
        (document.getElementById('rule-field') as HTMLInputElement).value = 'lot_area';
        (document.getElementById('rule-active') as HTMLSelectElement).value = '1';
        if (formTitle) formTitle.textContent = 'Add classification rule';
        if (saveBtn) saveBtn.textContent = 'Save rule';
    };

    const openCreate = (): void => {
        resetCreateForm();
        showModal('modal-add-rule');
    };

    const openEdit = (row: RuleRow): void => {
        viewing = row;
        if (uuidInput) uuidInput.value = row.uuid;
        (document.getElementById('rule-code') as HTMLInputElement).value = row.code;
        (document.getElementById('rule-name') as HTMLInputElement).value = row.name;
        (document.getElementById('rule-classification') as HTMLSelectElement).value = row.classification;
        (document.getElementById('rule-priority') as HTMLInputElement).value = String(row.priority ?? 100);
        (document.getElementById('rule-sla') as HTMLInputElement).value = String(row.sla_hours ?? 72);
        (document.getElementById('rule-active') as HTMLSelectElement).value = row.is_active ? '1' : '0';
        const first = (row.conditions || [])[0];
        (document.getElementById('rule-field') as HTMLInputElement).value = first?.field || '';
        (document.getElementById('rule-operator') as HTMLSelectElement).value = first?.operator || '>=';
        (document.getElementById('rule-value') as HTMLInputElement).value =
            first?.value != null && first.value !== '' ? String(first.value) : '';
        if (formTitle) formTitle.textContent = `Edit rule · ${row.code}`;
        if (saveBtn) saveBtn.textContent = 'Update rule';
        hideModal('modal-rule-detail');
        showModal('modal-add-rule');
    };

    const openDetail = (row: RuleRow): void => {
        viewing = row;
        if (detailTitle) detailTitle.textContent = `Rule · ${row.code}`;
        if (detailBody) detailBody.innerHTML = renderDetail(row);
        const toggleBtn = document.getElementById('btn-rule-toggle');
        if (toggleBtn) toggleBtn.textContent = row.is_active ? 'Deactivate' : 'Activate';
        showModal('modal-rule-detail');
    };

    const deleteRule = async (row: RuleRow): Promise<void> => {
        if (!(await confirmAction('Delete rule?', `${row.code} will be soft-deleted from the classifier.`))) return;
        try {
            await window.axios.delete(`/api/v1/admin/classification-rules/${row.uuid}`);
            hideModal('modal-rule-detail');
            toastSuccess('Rule deleted');
            await table?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Delete failed');
        }
    };

    document.getElementById('btn-open-add-rule')?.addEventListener('click', () => openCreate());
    document.getElementById('btn-rule-edit')?.addEventListener('click', () => {
        if (!viewing) return;
        openEdit(viewing);
    });

    document.getElementById('btn-rule-toggle')?.addEventListener('click', async () => {
        if (!viewing) return;
        try {
            await window.axios.put(`/api/v1/admin/classification-rules/${viewing.uuid}`, {
                is_active: !viewing.is_active,
            });
            toastSuccess(viewing.is_active ? 'Rule deactivated' : 'Rule activated');
            hideModal('modal-rule-detail');
            await table?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Update failed');
        }
    });

    document.getElementById('btn-rule-delete')?.addEventListener('click', () => {
        if (!viewing) return;
        void deleteRule(viewing);
    });

    void (async () => {
        try {
            table = await createApicsDataTable<RuleRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'classification-rules',
                rowId: 'uuid',
                order: [[0, 'asc']],
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
                    { data: 'priority', title: 'Priority', responsivePriority: 2 },
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
                             <div class="text-muted small">${row.condition_count ?? 0} condition(s)</div>`,
                    },
                    {
                        data: 'classification',
                        title: 'Classification',
                        responsivePriority: 1,
                        render: (data) => classBadge(String(data ?? '')),
                    },
                    {
                        data: 'sla_hours',
                        title: 'SLA (h)',
                        responsivePriority: 3,
                        render: (d) => escapeHtml(String(d ?? '')),
                    },
                    {
                        data: 'is_active',
                        title: 'Active',
                        responsivePriority: 2,
                        render: (data) => badgeYesNo(Boolean(data)),
                    },
                ],
                actions: [
                    { id: 'view', label: 'View rule', onClick: (row) => openDetail(row) },
                    { id: 'edit', label: 'Edit', onClick: (row) => openEdit(row) },
                    {
                        id: 'toggle',
                        label: 'Toggle active',
                        onClick: async (row) => {
                            try {
                                await window.axios.put(`/api/v1/admin/classification-rules/${row.uuid}`, {
                                    is_active: !row.is_active,
                                });
                                toastSuccess(row.is_active ? 'Rule deactivated' : 'Rule activated');
                                await table?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Update failed');
                            }
                        },
                    },
                    {
                        id: 'delete',
                        label: 'Delete',
                        danger: true,
                        dividerBefore: true,
                        onClick: (row) => void deleteRule(row),
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/admin/classification-rules', {
                        params: { per_page: 100 },
                    });
                    paintSummary(data.data?.summary || null);
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load rules');
            paintSummary(null);
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const uuid = uuidInput?.value.trim() || '';
        const payload = collectPayload();
        const btn = saveBtn as HTMLButtonElement | null;
        if (btn) btn.disabled = true;
        try {
            if (uuid) {
                await window.axios.put(`/api/v1/admin/classification-rules/${uuid}`, payload);
                toastSuccess('Rule updated');
            } else {
                await window.axios.post('/api/v1/admin/classification-rules', payload);
                toastSuccess('Rule created');
            }
            resetCreateForm();
            hideModal('modal-add-rule');
            await table?.reload(false);
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || (uuid ? 'Update failed' : 'Create failed')));
        } finally {
            if (btn) btn.disabled = false;
        }
    });
}
