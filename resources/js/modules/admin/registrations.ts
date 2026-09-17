import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type RegistrationRow = {
    uuid: string;
    name: string;
    email: string;
    phone?: string | null;
    approval_status: string;
    registered_at?: string | null;
};

function statusBadge(status: string): string {
    const map: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        declined: 'bg-danger-subtle text-danger',
    };
    const cls = map[status] || 'bg-secondary-subtle text-secondary';
    return `<span class="badge ${cls}">${escapeHtml(status)}</span>`;
}

export function initRegistrationsPage(): void {
    const tableEl = document.getElementById('registrations-table');
    const declineForm = document.getElementById('form-decline-registration') as HTMLFormElement | null;

    if (!tableEl) {
        return;
    }

    let table: ApicsDataTableApi<RegistrationRow> | null = null;

    const openDecline = (row: RegistrationRow): void => {
        (document.getElementById('decline-user-uuid') as HTMLInputElement).value = row.uuid;
        (document.getElementById('decline-reason') as HTMLTextAreaElement).value = '';
        const summary = document.getElementById('decline-user-summary');
        if (summary) {
            summary.textContent = `Decline ${row.name} (${row.email}). The applicant will be emailed and cannot sign in.`;
        }
        showModal('modal-decline-registration');
    };

    void (async () => {
        try {
            table = await createApicsDataTable<RegistrationRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'registrations',
                rowId: 'uuid',
                searchMode: 'server',
                order: [[4, 'desc']],
                filters: [
                    {
                        id: 'status',
                        label: 'Status',
                        value: 'pending',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'pending', label: 'Pending' },
                            { value: 'approved', label: 'Approved' },
                            { value: 'declined', label: 'Declined' },
                        ],
                    },
                ],
                columns: [
                    {
                        data: 'name',
                        title: 'Name',
                        responsivePriority: 1,
                        render: (data) => `<span class="fw-medium">${escapeHtml(String(data ?? ''))}</span>`,
                    },
                    { data: 'email', title: 'Email', responsivePriority: 1 },
                    {
                        data: 'phone',
                        title: 'Phone',
                        responsivePriority: 3,
                        render: (data) => escapeHtml(String(data ?? '—')),
                    },
                    {
                        data: 'approval_status',
                        title: 'Status',
                        responsivePriority: 1,
                        render: (data) => statusBadge(String(data ?? '')),
                    },
                    {
                        data: 'registered_at',
                        title: 'Registered',
                        responsivePriority: 2,
                        render: (data) =>
                            data ? escapeHtml(new Date(String(data)).toLocaleString()) : '—',
                    },
                ],
                actions: [
                    {
                        id: 'approve',
                        label: 'Approve',
                        visible: (row) => row.approval_status === 'pending',
                        onClick: async (row) => {
                            if (
                                !(await confirmAction(
                                    'Approve registration?',
                                    'The applicant will receive an email and can sign in.'
                                ))
                            ) {
                                return;
                            }
                            try {
                                await window.axios.post(`/api/v1/admin/registrations/${row.uuid}/approve`);
                                toastSuccess('Registration approved');
                                await table?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Approve failed');
                            }
                        },
                    },
                    {
                        id: 'decline',
                        label: 'Decline',
                        danger: true,
                        dividerBefore: true,
                        visible: (row) => row.approval_status === 'pending',
                        onClick: (row) => openDecline(row),
                    },
                ],
                fetchData: async ({ search, filters }) => {
                    const { data } = await window.axios.get('/api/v1/admin/registrations', {
                        params: {
                            status: filters.status || 'all',
                            search: search || undefined,
                            per_page: 100,
                        },
                    });
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load registrations.');
        }
    })();

    declineForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const uuid = (document.getElementById('decline-user-uuid') as HTMLInputElement).value;
        const reason = (document.getElementById('decline-reason') as HTMLTextAreaElement).value.trim();
        const submitBtn = document.getElementById('btn-confirm-decline') as HTMLButtonElement | null;

        if (!uuid) {
            toastError('Missing registration selection.');
            return;
        }

        if (reason.length < 5) {
            toastError('Please provide a reason (at least 5 characters).');
            return;
        }

        if (!(await confirmAction('Decline registration?', 'This cannot be undone from this screen.'))) {
            return;
        }

        if (submitBtn) submitBtn.disabled = true;

        try {
            await window.axios.post(`/api/v1/admin/registrations/${uuid}/decline`, { reason });
            toastSuccess('Registration declined');
            hideModal('modal-decline-registration');
            await table?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Decline failed');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}
