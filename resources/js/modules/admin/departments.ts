import { escapeHtml, hideModal } from '../../utils/bootstrap-modal';
import { badgeYesNo, createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type DepartmentRow = {
    uuid: string;
    code: string;
    name: string;
    description?: string | null;
    is_active: boolean;
};

export function initDepartmentsPage(): void {
    const tableEl = document.getElementById('departments-table');
    const addForm = document.getElementById('form-add-department') as HTMLFormElement | null;
    const importForm = document.getElementById('form-import-departments') as HTMLFormElement | null;
    const exportBtn = document.getElementById('btn-export-departments') as HTMLAnchorElement | null;

    if (!tableEl || !addForm) {
        return;
    }

    let table: ApicsDataTableApi<DepartmentRow> | null = null;

    const importSample = JSON.stringify(
        [{ code: 'ENG', name: 'Engineering', description: 'Imported', is_active: true }],
        null,
        2
    );

    const importTextarea = document.getElementById('dept-import-json') as HTMLTextAreaElement | null;
    if (importTextarea && !importTextarea.value) {
        importTextarea.value = importSample;
    }

    if (exportBtn) {
        exportBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const res = await window.axios.get('/api/v1/admin/departments/export', { responseType: 'blob' });
                const url = window.URL.createObjectURL(res.data);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'departments.csv';
                a.click();
                window.URL.revokeObjectURL(url);
                toastSuccess('Export started');
            } catch (error: any) {
                toastError(error?.response?.data?.message || 'Export failed');
            }
        });
    }

    void (async () => {
        try {
            table = await createApicsDataTable<DepartmentRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'departments',
                rowId: 'uuid',
                order: [[0, 'asc']],
                filters: [
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
                        render: (data) => `<span class="fw-medium">${escapeHtml(String(data ?? ''))}</span>`,
                    },
                    { data: 'name', title: 'Name', responsivePriority: 1 },
                    {
                        data: 'description',
                        title: 'Description',
                        responsivePriority: 3,
                        render: (data) => escapeHtml(String(data ?? '—')),
                    },
                    {
                        data: 'is_active',
                        title: 'Active',
                        responsivePriority: 2,
                        render: (data) => badgeYesNo(Boolean(data)),
                    },
                ],
                actions: [
                    {
                        id: 'delete',
                        label: 'Delete',
                        danger: true,
                        onClick: async (row) => {
                            if (!(await confirmAction('Delete department?', 'This soft-deletes the record.'))) {
                                return;
                            }
                            try {
                                await window.axios.delete(`/api/v1/admin/departments/${row.uuid}`);
                                toastSuccess('Department deleted');
                                await table?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Delete failed');
                            }
                        },
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/admin/departments', {
                        params: { per_page: 100 },
                    });
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Sign in as admin to manage departments.');
        }
    })();

    addForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const code = (document.getElementById('dept-code') as HTMLInputElement).value.trim();
        const name = (document.getElementById('dept-name') as HTMLInputElement).value.trim();
        const description = (document.getElementById('dept-description') as HTMLTextAreaElement).value.trim();
        const isActive = (document.getElementById('dept-active') as HTMLInputElement).checked;
        const saveBtn = document.getElementById('btn-save-department') as HTMLButtonElement | null;

        if (!code || !name) {
            toastError('Code and name are required.');
            return;
        }

        if (saveBtn) saveBtn.disabled = true;

        try {
            const { data } = await window.axios.post('/api/v1/admin/departments', {
                code,
                name,
                description: description || null,
                is_active: isActive,
            });
            toastSuccess(data.message || 'Created');
            addForm.reset();
            (document.getElementById('dept-active') as HTMLInputElement).checked = true;
            hideModal('modal-add-department');
            await table?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Create failed');
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });

    importForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const raw = (document.getElementById('dept-import-json') as HTMLTextAreaElement).value.trim();
        const runBtn = document.getElementById('btn-run-import') as HTMLButtonElement | null;

        if (!raw) {
            toastError('Paste a JSON array to import.');
            return;
        }

        if (runBtn) runBtn.disabled = true;

        try {
            const rows = JSON.parse(raw);
            if (!Array.isArray(rows)) {
                toastError('JSON must be an array of department objects.');
                return;
            }

            const dry = await window.axios.post('/api/v1/admin/departments/import', { rows, dry_run: true });
            const summary = `Dry-run: +${dry.data.data.created} created / ~${dry.data.data.updated} updated`;

            if (!(await confirmAction('Commit import?', summary))) {
                return;
            }

            const committed = await window.axios.post('/api/v1/admin/departments/import', { rows, dry_run: false });
            toastSuccess(committed.data.message || 'Import committed');
            hideModal('modal-import-departments');
            await table?.reload(false);
        } catch (error: any) {
            if (error instanceof SyntaxError) {
                toastError('Invalid JSON. Check the array syntax.');
            } else {
                toastError(error?.response?.data?.message || 'Import failed');
            }
        } finally {
            if (runBtn) runBtn.disabled = false;
        }
    });
}
