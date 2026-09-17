import { escapeHtml } from '../../utils/bootstrap-modal';
import { createApicsDataTable } from '../../utils/datatable';
import { toastError } from '../../utils/toast';

type AuditRow = {
    uuid?: string;
    id?: number;
    created_at: string;
    event: string;
    actor_name?: string | null;
    ip_address?: string | null;
};

export function initAuditPage(): void {
    const tableEl = document.getElementById('audit-table');
    if (!tableEl) {
        return;
    }

    void (async () => {
        try {
            await createApicsDataTable<AuditRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'audit-logs',
                rowId: (row) => String(row.uuid || row.id || `${row.event}-${row.created_at}`),
                order: [[0, 'desc']],
                columns: [
                    {
                        data: 'created_at',
                        title: 'When',
                        responsivePriority: 1,
                        render: (data) => `<span class="text-nowrap">${escapeHtml(String(data ?? ''))}</span>`,
                    },
                    {
                        data: 'event',
                        title: 'Event',
                        responsivePriority: 1,
                        render: (data) => `<span class="fw-medium">${escapeHtml(String(data ?? ''))}</span>`,
                    },
                    {
                        data: 'actor_name',
                        title: 'Actor',
                        responsivePriority: 2,
                        render: (data) => escapeHtml(String(data ?? '—')),
                    },
                    {
                        data: 'ip_address',
                        title: 'IP',
                        responsivePriority: 3,
                        render: (data) => escapeHtml(String(data ?? '—')),
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/admin/audit-logs', {
                        params: { per_page: 100 },
                    });
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Sign in as admin to view audit logs.');
        }
    })();
}
