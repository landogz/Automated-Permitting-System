import { hideModal, showModal } from '../../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi } from '../../../utils/datatable';
import { bindPasswordToggles } from '../../../utils/password-toggle';
import { confirmAction, toastError, toastSuccess } from '../../../utils/toast';
import {
    activeBadge,
    approvalBadge,
    departmentCell,
    emailCell,
    nameCell,
    paintUsersSummary,
    roleBadge,
    roleLabel,
    type ManagedUserRow,
    type UsersSummary,
} from './format';

type FormMeta = {
    roles: string[];
    staff_roles: string[];
    departments: Array<{ uuid: string; code: string; name: string }>;
};

function fillSelect(
    select: HTMLSelectElement,
    options: Array<{ value: string; label: string }>,
    includeBlank = false,
): void {
    select.innerHTML = '';
    if (includeBlank) {
        const blank = document.createElement('option');
        blank.value = '';
        blank.textContent = '— None —';
        select.appendChild(blank);
    }
    options.forEach((opt) => {
        const el = document.createElement('option');
        el.value = opt.value;
        el.textContent = opt.label;
        select.appendChild(el);
    });
}

export function initUsersPage(): void {
    const tableEl = document.getElementById('users-table');
    const form = document.getElementById('form-user') as HTMLFormElement | null;
    bindPasswordToggles(form ?? document);
    const addBtn = document.getElementById('btn-add-user');

    if (!tableEl || !form) {
        return;
    }

    let table: ApicsDataTableApi<ManagedUserRow> | null = null;
    let meta: FormMeta = { roles: [], staff_roles: [], departments: [] };
    let editingUuid: string | null = null;

    const roleSelect = document.getElementById('user-role') as HTMLSelectElement;
    const deptSelect = document.getElementById('user-department') as HTMLSelectElement;
    const approvalWrap = document.getElementById('user-approval-wrap') as HTMLElement | null;
    const passwordRequired = document.getElementById('user-password-required');
    const passwordConfirmRequired = document.getElementById('user-password-confirm-required');
    const passwordHint = document.getElementById('user-password-hint');
    const modalLabel = document.getElementById('modal-user-form-label');
    const modalLede = document.getElementById('modal-user-form-lede');
    const modalIcon = document.getElementById('modal-user-form-icon');
    const saveBtnLabel = document.getElementById('btn-save-user');

    const resetForm = (mode: 'create' | 'edit', row?: ManagedUserRow): void => {
        editingUuid = mode === 'edit' && row ? row.uuid : null;
        (document.getElementById('user-uuid') as HTMLInputElement).value = editingUuid || '';
        (document.getElementById('user-name') as HTMLInputElement).value = row?.name || '';
        (document.getElementById('user-email') as HTMLInputElement).value = row?.email || '';
        (document.getElementById('user-phone') as HTMLInputElement).value = row?.phone || '';
        (document.getElementById('user-password') as HTMLInputElement).value = '';
        (document.getElementById('user-password-confirmation') as HTMLInputElement).value = '';
        (document.getElementById('user-active') as HTMLInputElement).checked = row ? Boolean(row.is_active) : true;

        const roleOptions = (mode === 'create' ? meta.staff_roles : meta.roles).map((role) => ({
            value: role,
            label: roleLabel(role),
        }));
        fillSelect(roleSelect, roleOptions);
        fillSelect(
            deptSelect,
            meta.departments.map((d) => ({ value: d.uuid, label: `${d.code} — ${d.name}` })),
            true,
        );

        roleSelect.value = row?.role || meta.staff_roles[0] || '';
        deptSelect.value = row?.department?.uuid || '';

        if (approvalWrap) {
            const showApproval = mode === 'edit' && (row?.role === 'applicant' || row?.roles?.includes('applicant'));
            approvalWrap.hidden = !showApproval;
            if (showApproval) {
                (document.getElementById('user-approval') as HTMLSelectElement).value = row?.approval_status || 'approved';
            }
        }

        const requirePassword = mode === 'create';
        if (passwordRequired) passwordRequired.hidden = !requirePassword;
        if (passwordConfirmRequired) passwordConfirmRequired.hidden = !requirePassword;
        if (passwordHint) {
            passwordHint.textContent = requirePassword
                ? 'Min 8 chars with upper, lower, number, and symbol.'
                : 'Leave blank to keep the current password.';
        }
        if (modalLabel) {
            modalLabel.textContent = mode === 'create' ? 'Add staff user' : 'Edit user';
        }
        if (modalLede) {
            modalLede.textContent =
                mode === 'create'
                    ? 'Create an OCBO office account with role, department, and sign-in credentials.'
                    : `Update access for ${row?.name || 'this user'}. Password is optional.`;
        }
        if (modalIcon) {
            modalIcon.className = mode === 'create' ? 'ri-user-add-line' : 'ri-user-settings-line';
        }
        if (saveBtnLabel) {
            const icon = mode === 'create' ? 'ri-user-add-line' : 'ri-save-line';
            const label = mode === 'create' ? 'Create user' : 'Save changes';
            saveBtnLabel.innerHTML = `<i class="${icon}" aria-hidden="true"></i><span class="btn-label">${label}</span>`;
        }
    };

    const openCreate = (): void => {
        resetForm('create');
        showModal('modal-user-form');
    };

    const openEdit = (row: ManagedUserRow): void => {
        resetForm('edit', row);
        showModal('modal-user-form');
    };

    addBtn?.addEventListener('click', () => openCreate());

    void (async () => {
        try {
            const metaRes = await window.axios.get('/api/v1/admin/users/meta');
            meta = metaRes.data.data || meta;
            resetForm('create');

            table = await createApicsDataTable<ManagedUserRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'users-roles',
                rowId: 'uuid',
                searchMode: 'server',
                order: [[0, 'asc']],
                emptyMessage: 'No users match the current filters. Clear filters or add a staff account.',
                filters: [
                    {
                        id: 'role',
                        label: 'Role',
                        options: [
                            { value: '', label: 'All roles' },
                            ...meta.roles.map((role) => ({ value: role, label: roleLabel(role) })),
                        ],
                    },
                    {
                        id: 'is_active',
                        label: 'Status',
                        options: [
                            { value: '', label: 'All statuses' },
                            { value: '1', label: 'Active' },
                            { value: '0', label: 'Inactive' },
                        ],
                    },
                    {
                        id: 'approval_status',
                        label: 'Approval',
                        options: [
                            { value: '', label: 'All approvals' },
                            { value: 'pending', label: 'Pending' },
                            { value: 'approved', label: 'Approved' },
                            { value: 'declined', label: 'Declined' },
                        ],
                    },
                ],
                columns: [
                    {
                        data: 'name',
                        title: 'Person',
                        responsivePriority: 1,
                        render: (_data, _type, row) => nameCell(row),
                    },
                    {
                        data: 'email',
                        title: 'Email',
                        responsivePriority: 1,
                        render: (_data, _type, row) => emailCell(row),
                    },
                    {
                        data: 'role',
                        title: 'Role',
                        responsivePriority: 1,
                        render: (_data, _type, row) => roleBadge(row.role || row.roles?.[0]),
                    },
                    {
                        data: 'department',
                        title: 'Department',
                        responsivePriority: 3,
                        render: (_data, _type, row) => departmentCell(row),
                    },
                    {
                        data: 'approval_status',
                        title: 'Approval',
                        responsivePriority: 2,
                        render: (data) => approvalBadge(String(data ?? '')),
                    },
                    {
                        data: 'is_active',
                        title: 'Access',
                        responsivePriority: 2,
                        render: (data) => activeBadge(Boolean(data)),
                    },
                ],
                actions: [
                    {
                        id: 'edit',
                        label: 'Edit',
                        onClick: (row) => openEdit(row),
                    },
                    {
                        id: 'deactivate',
                        label: 'Deactivate',
                        danger: true,
                        visible: (row) => row.is_active,
                        onClick: async (row) => {
                            if (
                                !(await confirmAction(
                                    'Deactivate user?',
                                    `${row.name} will not be able to sign in until reactivated.`,
                                ))
                            ) {
                                return;
                            }
                            try {
                                await window.axios.delete(`/api/v1/admin/users/${row.uuid}`);
                                toastSuccess('User deactivated');
                                await table?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Deactivate failed');
                            }
                        },
                    },
                    {
                        id: 'activate',
                        label: 'Activate',
                        visible: (row) => !row.is_active,
                        onClick: async (row) => {
                            try {
                                await window.axios.put(`/api/v1/admin/users/${row.uuid}`, { is_active: true });
                                toastSuccess('User activated');
                                await table?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Activate failed');
                            }
                        },
                    },
                ],
                fetchData: async (ctx) => {
                    const { data } = await window.axios.get('/api/v1/admin/users', {
                        params: {
                            per_page: 200,
                            search: ctx.search || '',
                            role: ctx.filters.role || '',
                            is_active: ctx.filters.is_active || '',
                            approval_status: ctx.filters.approval_status || '',
                        },
                    });
                    paintUsersSummary((data.data?.summary || null) as UsersSummary | null);
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Sign in as admin to manage users.');
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const saveBtn = document.getElementById('btn-save-user') as HTMLButtonElement | null;
        const name = (document.getElementById('user-name') as HTMLInputElement).value.trim();
        const email = (document.getElementById('user-email') as HTMLInputElement).value.trim();
        const phone = (document.getElementById('user-phone') as HTMLInputElement).value.trim();
        const role = roleSelect.value;
        const departmentUuid = deptSelect.value || null;
        const isActive = (document.getElementById('user-active') as HTMLInputElement).checked;
        const password = (document.getElementById('user-password') as HTMLInputElement).value;
        const passwordConfirmation = (document.getElementById('user-password-confirmation') as HTMLInputElement)
            .value;

        if (!name || !email || !role) {
            toastError('Name, email, and role are required.');
            return;
        }

        if (!editingUuid && !password) {
            toastError('Password is required for new staff users.');
            return;
        }

        if (password && password !== passwordConfirmation) {
            toastError('Password confirmation does not match.');
            return;
        }

        const payload: Record<string, unknown> = {
            name,
            email,
            phone: phone || null,
            role,
            department_uuid: departmentUuid,
            is_active: isActive,
        };

        if (password) {
            payload.password = password;
            payload.password_confirmation = passwordConfirmation;
        }

        if (editingUuid && approvalWrap && !approvalWrap.hidden) {
            payload.approval_status = (document.getElementById('user-approval') as HTMLSelectElement).value;
        }

        if (saveBtn) saveBtn.disabled = true;

        try {
            if (editingUuid) {
                const { data } = await window.axios.put(`/api/v1/admin/users/${editingUuid}`, payload);
                toastSuccess(data.message || 'User updated');
            } else {
                const { data } = await window.axios.post('/api/v1/admin/users', payload);
                toastSuccess(data.message || 'User created');
            }
            hideModal('modal-user-form');
            resetForm('create');
            await table?.reload(false);
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first =
                errors?.email?.[0] ||
                errors?.password?.[0] ||
                errors?.role?.[0] ||
                errors?.is_active?.[0] ||
                error?.response?.data?.message ||
                'Save failed';
            toastError(first);
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });
}
