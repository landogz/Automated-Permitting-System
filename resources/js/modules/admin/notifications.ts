import { escapeHtml, hideModal } from '../../utils/bootstrap-modal';
import { badgeYesNo, createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type NotifRow = {
    uuid: string;
    title: string;
    channel: string;
    status: string;
    is_read: boolean;
    created_at?: string | null;
    body?: string;
};

type TemplateRow = {
    uuid: string;
    code: string;
    name: string;
    channel: string;
    is_active: boolean;
};

export function initNotificationsPage(): void {
    const inboxEl = document.getElementById('notifications-table');
    const templatesEl = document.getElementById('notif-templates-table');
    const sendForm = document.getElementById('form-send-notification') as HTMLFormElement | null;
    const templateForm = document.getElementById('form-add-template') as HTMLFormElement | null;
    if (!inboxEl || !templatesEl) return;

    let inbox: ApicsDataTableApi<NotifRow> | null = null;
    let templates: ApicsDataTableApi<TemplateRow> | null = null;

    void (async () => {
        try {
            inbox = await createApicsDataTable<NotifRow>({
                table: inboxEl as HTMLTableElement,
                exportFileName: 'notifications',
                rowId: 'uuid',
                filters: [
                    {
                        id: 'unread',
                        label: 'Read',
                        options: [
                            { value: '', label: 'All' },
                            { value: '0', label: 'Unread' },
                            { value: '1', label: 'Read' },
                        ],
                        match: (row, value) => String(Number(row.is_read)) === value,
                    },
                ],
                columns: [
                    {
                        data: 'title',
                        title: 'Title',
                        responsivePriority: 1,
                        render: (d, _t, row) =>
                            `<span class="${row.is_read ? '' : 'fw-semibold'}">${escapeHtml(String(d ?? ''))}</span>`,
                    },
                    { data: 'channel', title: 'Channel', responsivePriority: 2 },
                    { data: 'status', title: 'Delivery', responsivePriority: 3 },
                    {
                        data: 'is_read',
                        title: 'Read',
                        responsivePriority: 1,
                        render: (d) => (d ? 'Yes' : '<span class="badge bg-warning-subtle text-warning">No</span>'),
                    },
                    {
                        data: 'created_at',
                        title: 'When',
                        responsivePriority: 2,
                        render: (d) => (d ? escapeHtml(new Date(String(d)).toLocaleString()) : '—'),
                    },
                ],
                actions: [
                    {
                        id: 'mark-read',
                        label: 'Mark read',
                        visible: (row) => !row.is_read,
                        onClick: async (row) => {
                            try {
                                await window.axios.post(`/api/v1/notifications/${row.uuid}/read`);
                                toastSuccess('Marked read');
                                await inbox?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Update failed');
                            }
                        },
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/notifications', { params: { per_page: 100 } });
                    return data.data?.items || [];
                },
            });

            templates = await createApicsDataTable<TemplateRow>({
                table: templatesEl as HTMLTableElement,
                exportFileName: 'notification-templates',
                rowId: 'uuid',
                columns: [
                    {
                        data: 'code',
                        title: 'Code',
                        responsivePriority: 1,
                        render: (d) => `<span class="fw-medium">${escapeHtml(String(d ?? ''))}</span>`,
                    },
                    { data: 'name', title: 'Name', responsivePriority: 1 },
                    { data: 'channel', title: 'Channel', responsivePriority: 2 },
                    {
                        data: 'is_active',
                        title: 'Active',
                        responsivePriority: 2,
                        render: (d) => badgeYesNo(Boolean(d)),
                    },
                ],
                actions: [
                    {
                        id: 'delete',
                        label: 'Delete',
                        danger: true,
                        onClick: async (row) => {
                            if (!(await confirmAction('Delete template?', 'This soft-deletes the template.'))) return;
                            try {
                                await window.axios.delete(`/api/v1/admin/notification-templates/${row.uuid}`);
                                toastSuccess('Template deleted');
                                await templates?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Delete failed');
                            }
                        },
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/admin/notification-templates', {
                        params: { per_page: 100 },
                    });
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load notifications');
        }
    })();

    document.getElementById('btn-mark-all-read')?.addEventListener('click', async () => {
        try {
            await window.axios.post('/api/v1/notifications/read-all');
            toastSuccess('All marked read');
            await inbox?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Update failed');
        }
    });

    sendForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await window.axios.post('/api/v1/staff/notifications/send', {
                user_uuid: (document.getElementById('notif-user-uuid') as HTMLInputElement).value.trim(),
                channel: (document.getElementById('notif-channel') as HTMLSelectElement).value,
                message: (document.getElementById('notif-message') as HTMLTextAreaElement).value.trim() || undefined,
                template_code: 'status.update',
            });
            toastSuccess('Notification sent');
            sendForm.reset();
            hideModal('modal-send-notification');
            await inbox?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Send failed');
        }
    });

    templateForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await window.axios.post('/api/v1/admin/notification-templates', {
                code: (document.getElementById('tpl-code') as HTMLInputElement).value.trim(),
                name: (document.getElementById('tpl-name') as HTMLInputElement).value.trim(),
                channel: (document.getElementById('tpl-channel') as HTMLSelectElement).value,
                subject: (document.getElementById('tpl-subject') as HTMLInputElement).value.trim() || undefined,
                body_template: (document.getElementById('tpl-body') as HTMLTextAreaElement).value.trim(),
                is_active: true,
            });
            toastSuccess('Template created');
            templateForm.reset();
            hideModal('modal-add-template');
            await templates?.reload(false);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Create failed');
        }
    });
}
