import { toastError } from '../../utils/toast';

function setText(id: string, value: string | number): void {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = String(value);
    }
}

export function initDashboardStats(): void {
    const root = document.getElementById('ops-stats');
    if (!root) {
        return;
    }

    void (async () => {
        try {
            const { data } = await window.axios.get('/api/v1/staff/dashboard-stats');
            const stats = data.data || {};
            setText('stat-apps-total', stats.applications?.total ?? 0);
            setText('stat-under-eval', stats.applications?.under_evaluation ?? 0);
            setText('stat-inspections', stats.inspections?.scheduled ?? 0);
            setText('stat-payment', stats.applications?.for_payment ?? 0);
            setText('stat-released', stats.applications?.released ?? 0);
            setText('stat-pending-reg', stats.pending_registrations ?? 0);
            setText('stat-logbooks', stats.records?.logbook_entries ?? 0);
            setText('stat-unread-notif', stats.notifications?.unread ?? 0);
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load operational stats');
        }
    })();
}
