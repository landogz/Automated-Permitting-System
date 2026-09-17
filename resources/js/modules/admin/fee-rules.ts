import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { badgeYesNo, createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type FeeCondition = { field?: string; operator?: string; value?: string | number | null };

type FeeRuleRow = {
    uuid: string;
    code: string;
    name: string;
    agency: string;
    basis: string;
    amount: string | number;
    rate?: string | number | null;
    priority: number;
    is_active: boolean;
    conditions?: FeeCondition[] | null;
    condition_count?: number;
    created_at?: string | null;
    updated_at?: string | null;
};

type FeeSummary = {
    active?: number;
    inactive?: number;
    lgu?: number;
    bfp?: number;
    dpwh?: number;
    cto?: number;
    total?: number;
};

function formatPhp(amount: string | number | null | undefined): string {
    const n = Number(amount ?? 0);
    if (Number.isNaN(n)) return '₱0.00';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(n);
}

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

function agencyBadge(agency: string): string {
    const map: Record<string, string> = {
        lgu: 'bg-primary-subtle text-primary',
        bfp: 'bg-danger-subtle text-danger',
        dpwh: 'bg-warning-subtle text-warning',
        cto: 'bg-success-subtle text-success',
    };
    const cls = map[agency] || 'bg-secondary-subtle text-secondary';
    return `<span class="badge ${cls}">${escapeHtml(agency.toUpperCase())}</span>`;
}

function paintSummary(summary?: FeeSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-fr-stat="${key}"]`).forEach((el) => {
            el.textContent = value;
        });
    };
    if (!summary) {
        ['active', 'inactive', 'lgu', 'bfp', 'dpwh', 'cto', 'total'].forEach((k) => set(k, '—'));
        return;
    }
    set('active', String(summary.active ?? 0));
    set('inactive', String(summary.inactive ?? 0));
    set('lgu', String(summary.lgu ?? 0));
    set('bfp', String(summary.bfp ?? 0));
    set('dpwh', String(summary.dpwh ?? 0));
    set('cto', String(summary.cto ?? 0));
    set('total', String(summary.total ?? 0));
}

function conditionsHtml(conditions?: FeeCondition[] | null): string {
    const list = conditions || [];
    if (!list.length) {
        return `<p class="text-muted mb-0 fs-13">No conditions — applies to all matching applications by priority.</p>`;
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

function renderDetail(row: FeeRuleRow): string {
    return `
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Code</p>
                <p class="fw-semibold mb-0">${escapeHtml(row.code)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Agency</p>
                <div>${agencyBadge(row.agency)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Basis</p>
                <p class="mb-0 text-capitalize">${escapeHtml(humanize(row.basis))}</p>
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
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Amount</p>
                <p class="fw-semibold text-primary mb-0">${escapeHtml(formatPhp(row.amount))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Rate</p>
                <p class="fw-medium mb-0">${row.rate != null && row.rate !== '' ? escapeHtml(String(row.rate)) : '—'}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Priority</p>
                <p class="fw-medium mb-0">${escapeHtml(String(row.priority))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Updated</p>
                <p class="mb-0 small">${escapeHtml(formatDate(row.updated_at))}</p>
            </div>
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Conditions (${row.condition_count ?? (row.conditions || []).length})</h6>
        ${conditionsHtml(row.conditions)}
    `;
}

function collectPayload(): Record<string, unknown> {
    return {
        code: (document.getElementById('fee-code') as HTMLInputElement).value.trim(),
        name: (document.getElementById('fee-name') as HTMLInputElement).value.trim(),
        agency: (document.getElementById('fee-agency') as HTMLSelectElement).value,
        basis: (document.getElementById('fee-basis') as HTMLSelectElement).value,
        amount: Number((document.getElementById('fee-amount') as HTMLInputElement).value || 0),
        rate: Number((document.getElementById('fee-rate') as HTMLInputElement).value || 0) || null,
        priority: Number((document.getElementById('fee-priority') as HTMLInputElement).value || 100),
        is_active: (document.getElementById('fee-active') as HTMLSelectElement).value === '1',
    };
}

export function initFeeRulesPage(): void {
    const tableEl = document.getElementById('fee-rules-table');
    const form = document.getElementById('form-add-fee-rule') as HTMLFormElement | null;
    if (!tableEl || !form) return;

    let table: ApicsDataTableApi<FeeRuleRow> | null = null;
    let viewing: FeeRuleRow | null = null;
    const detailBody = document.getElementById('fee-detail-body');
    const detailTitle = document.getElementById('modal-fee-detail-label');
    const formTitle = document.getElementById('modal-add-fee-rule-label');
    const saveBtn = document.getElementById('btn-save-fee-rule');
    const uuidInput = document.getElementById('fee-uuid') as HTMLInputElement | null;

    const resetCreateForm = (): void => {
        form.reset();
        if (uuidInput) uuidInput.value = '';
        (document.getElementById('fee-priority') as HTMLInputElement).value = '100';
        (document.getElementById('fee-amount') as HTMLInputElement).value = '0';
        (document.getElementById('fee-rate') as HTMLInputElement).value = '0';
        (document.getElementById('fee-active') as HTMLSelectElement).value = '1';
        if (formTitle) formTitle.textContent = 'Add fee rule';
        if (saveBtn) saveBtn.textContent = 'Save rule';
    };

    const openCreate = (): void => {
        resetCreateForm();
        showModal('modal-add-fee-rule');
    };

    const openEdit = (row: FeeRuleRow): void => {
        viewing = row;
        if (uuidInput) uuidInput.value = row.uuid;
        (document.getElementById('fee-code') as HTMLInputElement).value = row.code;
        (document.getElementById('fee-name') as HTMLInputElement).value = row.name;
        (document.getElementById('fee-agency') as HTMLSelectElement).value = row.agency;
        (document.getElementById('fee-basis') as HTMLSelectElement).value = row.basis;
        (document.getElementById('fee-priority') as HTMLInputElement).value = String(row.priority ?? 100);
        (document.getElementById('fee-amount') as HTMLInputElement).value = String(row.amount ?? 0);
        (document.getElementById('fee-rate') as HTMLInputElement).value =
            row.rate != null && row.rate !== '' ? String(row.rate) : '0';
        (document.getElementById('fee-active') as HTMLSelectElement).value = row.is_active ? '1' : '0';
        if (formTitle) formTitle.textContent = `Edit fee rule · ${row.code}`;
        if (saveBtn) saveBtn.textContent = 'Update rule';
        hideModal('modal-fee-detail');
        showModal('modal-add-fee-rule');
    };

    const openDetail = (row: FeeRuleRow): void => {
        viewing = row;
        if (detailTitle) detailTitle.textContent = `Fee rule · ${row.code}`;
        if (detailBody) detailBody.innerHTML = renderDetail(row);
        showModal('modal-fee-detail');
    };

    const deleteRule = async (row: FeeRuleRow): Promise<void> => {
        if (!(await confirmAction('Delete fee rule?', `${row.code} will be soft-deleted from the fee engine.`))) return;
        try {
            await window.axios.delete(`/api/v1/admin/fee-rules/${row.uuid}`);
            hideModal('modal-fee-detail');
            toastSuccess('Fee rule deleted');
            await table?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Delete failed');
        }
    };

    document.getElementById('btn-open-add-fee-rule')?.addEventListener('click', () => openCreate());
    document.getElementById('btn-fee-edit')?.addEventListener('click', () => {
        if (!viewing) return;
        openEdit(viewing);
    });
    document.getElementById('btn-fee-delete')?.addEventListener('click', () => {
        if (!viewing) return;
        void deleteRule(viewing);
    });

    void (async () => {
        try {
            table = await createApicsDataTable<FeeRuleRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'fee-rules',
                rowId: 'uuid',
                order: [[5, 'asc']],
                filters: [
                    {
                        id: 'agency',
                        label: 'Agency',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'lgu', label: 'LGU' },
                            { value: 'bfp', label: 'BFP' },
                            { value: 'dpwh', label: 'DPWH' },
                            { value: 'cto', label: 'CTO' },
                        ],
                        match: (row, value) => row.agency === value,
                    },
                    {
                        id: 'basis',
                        label: 'Basis',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'fixed', label: 'Fixed' },
                            { value: 'area_rate', label: 'Area × rate' },
                        ],
                        match: (row, value) => row.basis === value,
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
                        render: (d) => `<span class="fw-semibold">${escapeHtml(String(d ?? ''))}</span>`,
                    },
                    {
                        data: 'name',
                        title: 'Name',
                        responsivePriority: 1,
                        render: (d) =>
                            `<div class="fw-medium text-truncate" style="max-width:14rem">${escapeHtml(String(d ?? ''))}</div>`,
                    },
                    {
                        data: 'agency',
                        title: 'Agency',
                        responsivePriority: 1,
                        render: (d) => agencyBadge(String(d ?? '')),
                    },
                    {
                        data: 'basis',
                        title: 'Basis',
                        responsivePriority: 2,
                        render: (d) => escapeHtml(humanize(String(d ?? ''))),
                    },
                    {
                        data: 'amount',
                        title: 'Amount',
                        responsivePriority: 2,
                        className: 'text-end',
                        render: (d) => escapeHtml(formatPhp(d as string | number)),
                    },
                    { data: 'priority', title: 'Priority', responsivePriority: 3 },
                    {
                        data: 'is_active',
                        title: 'Active',
                        responsivePriority: 2,
                        render: (d) => badgeYesNo(Boolean(d)),
                    },
                ],
                actions: [
                    { id: 'view', label: 'View rule', onClick: (row) => openDetail(row) },
                    { id: 'edit', label: 'Edit', onClick: (row) => openEdit(row) },
                    {
                        id: 'delete',
                        label: 'Delete',
                        danger: true,
                        dividerBefore: true,
                        onClick: (row) => void deleteRule(row),
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/admin/fee-rules', { params: { per_page: 100 } });
                    paintSummary(data.data?.summary || null);
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load fee rules');
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
                await window.axios.put(`/api/v1/admin/fee-rules/${uuid}`, payload);
                toastSuccess('Fee rule updated');
            } else {
                await window.axios.post('/api/v1/admin/fee-rules', payload);
                toastSuccess('Fee rule created');
            }
            resetCreateForm();
            hideModal('modal-add-fee-rule');
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
