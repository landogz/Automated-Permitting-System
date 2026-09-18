import { hasPermission } from '../../../utils/auth';
import { escapeHtml } from '../../../utils/bootstrap-modal';
import { statusBadge } from '../../../utils/status-badge';
import { toastError, toastSuccess } from '../../../utils/toast';
import { userAvatarHtml } from '../../../utils/user-avatar';

type PipelineStage = {
    key: string;
    label: string;
    count: number;
    tone: string;
};

type AttentionItem = {
    key: string;
    label: string;
    hint: string;
    count: number;
    path: string;
    tone: string;
    permission: string;
};

type RecentApp = {
    uuid: string;
    application_no: string;
    project_title?: string | null;
    status: string;
    classification?: string | null;
    applicant_name?: string | null;
    updated_at?: string | null;
};

type ActivityItem = {
    uuid: string;
    event: string;
    actor_name?: string | null;
    actor_avatar_url?: string | null;
    meta?: Record<string, unknown>;
    created_at?: string | null;
};

type DashboardStats = {
    applications?: Record<string, number> & { total?: number; by_status?: Record<string, number> };
    inspections?: { scheduled?: number; completed?: number };
    orders_of_payment?: { issued?: number; paid_stub?: number };
    compliance?: { open_notices?: number };
    evaluation_queue?: number;
    records?: { logbook_entries?: number; archives?: number };
    notifications?: { unread?: number };
    pending_registrations?: number;
    pipeline?: PipelineStage[];
    attention?: AttentionItem[];
    recent_applications?: RecentApp[];
    recent_activity?: ActivityItem[];
    meta?: { generated_at?: string; can_view_audit?: boolean };
};

function setDash(key: string, value: string | number): void {
    document.querySelectorAll<HTMLElement>(`[data-dash="${key}"]`).forEach((el) => {
        el.textContent = String(value);
    });
}

function formatWhen(iso?: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function humanizeEvent(event: string): string {
    return event
        .replace(/\./g, ' · ')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

function toneClass(tone: string): string {
    const map: Record<string, string> = {
        amber: 'bg-warning',
        blue: 'bg-info',
        indigo: 'bg-primary',
        emerald: 'bg-success',
        rose: 'bg-danger',
        slate: 'bg-secondary',
        primary: 'bg-primary',
        warning: 'bg-warning',
        info: 'bg-info',
        success: 'bg-success',
        danger: 'bg-danger',
    };
    return map[tone] || 'bg-primary';
}

function renderPipeline(stages: PipelineStage[]): void {
    const host = document.querySelector<HTMLElement>('[data-dash-pipeline]');
    if (!host) return;

    const max = Math.max(1, ...stages.map((s) => s.count));
    const total = stages.reduce((sum, s) => sum + s.count, 0);
    setDash('pipeline_total', `${total} in pipeline view`);

    if (!stages.length) {
        host.innerHTML = '<p class="text-muted mb-0 fs-13">No pipeline data.</p>';
        return;
    }

    host.innerHTML = stages
        .map((stage) => {
            const pct = Math.round((stage.count / max) * 100);
            return `
            <div class="apics-dashboard__pipe-row">
                <div class="apics-dashboard__pipe-meta">
                    <span class="apics-dashboard__pipe-label">${escapeHtml(stage.label)}</span>
                    <span class="apics-dashboard__pipe-count font-monospace">${stage.count}</span>
                </div>
                <div class="progress apics-dashboard__pipe-bar" role="progressbar" aria-valuenow="${stage.count}" aria-valuemin="0" aria-valuemax="${max}" aria-label="${escapeHtml(stage.label)}">
                    <div class="progress-bar ${toneClass(stage.tone)}" style="width:${pct}%"></div>
                </div>
            </div>`;
        })
        .join('');
}

function renderAttention(items: AttentionItem[]): void {
    const host = document.querySelector<HTMLElement>('[data-dash-attention]');
    if (!host) return;

    const visible = items.filter((item) => !item.permission || hasPermission(item.permission));
    if (!visible.length) {
        host.innerHTML = '<div class="list-group-item text-muted fs-13 py-4 text-center">No queues available for your role.</div>';
        return;
    }

    host.innerHTML = visible
        .map((item) => {
            const hot = item.count > 0;
            return `
            <a href="${escapeHtml(item.path)}" class="list-group-item list-group-item-action apics-dashboard__attention-item ${hot ? 'is-hot' : ''}">
                <span class="apics-dashboard__attention-body">
                    <span class="fw-semibold d-block">${escapeHtml(item.label)}</span>
                    <span class="text-muted fs-12">${escapeHtml(item.hint)}</span>
                </span>
                <span class="badge ${hot ? 'bg-danger' : 'bg-secondary-subtle text-secondary'} rounded-pill font-monospace">${item.count}</span>
                <i class="ri-arrow-right-s-line text-muted" aria-hidden="true"></i>
            </a>`;
        })
        .join('');
}

function renderRecentApps(rows: RecentApp[]): void {
    const host = document.querySelector<HTMLElement>('[data-dash-recent-apps]');
    if (!host) return;

    if (!rows.length) {
        host.innerHTML = '<tr><td colspan="4" class="text-muted text-center py-4 fs-13">No applications yet.</td></tr>';
        return;
    }

    host.innerHTML = rows
        .map((row) => `
        <tr>
            <td>
                <div class="fw-semibold font-monospace fs-13">${escapeHtml(row.application_no || '—')}</div>
                <div class="text-muted fs-11 d-md-none text-truncate" style="max-width:10rem">${escapeHtml(row.project_title || '')}</div>
            </td>
            <td class="d-none d-md-table-cell">
                <div class="text-truncate" style="max-width:16rem">${escapeHtml(row.project_title || '—')}</div>
                <div class="text-muted fs-11">${escapeHtml(row.applicant_name || '')}</div>
            </td>
            <td>${statusBadge(row.status || 'draft')}</td>
            <td class="d-none d-lg-table-cell text-muted fs-12 text-nowrap">${escapeHtml(formatWhen(row.updated_at))}</td>
        </tr>`)
        .join('');
}

function renderActivity(rows: ActivityItem[]): void {
    const host = document.querySelector<HTMLElement>('[data-dash-activity]');
    if (!host) return;

    if (!rows.length) {
        host.innerHTML = '<li class="text-muted fs-13 text-center py-3">No recent audit events.</li>';
        return;
    }

    host.innerHTML = rows
        .map((row) => {
            const metaBits = Object.values(row.meta || {})
                .filter((v) => v != null && String(v).trim() !== '')
                .slice(0, 2)
                .map((v) => escapeHtml(String(v)));
            return `
            <li class="apics-dashboard__activity-item">
                ${userAvatarHtml(row.actor_name || 'System', row.actor_avatar_url, 'apics-user-avatar apics-user-avatar--sm')}
                <span class="min-w-0 flex-grow-1">
                    <span class="d-block fw-semibold fs-13 text-truncate">${escapeHtml(humanizeEvent(row.event))}</span>
                    <span class="d-block text-muted fs-11">
                        ${escapeHtml(row.actor_name || 'System')}
                        ${metaBits.length ? ' · ' + metaBits.join(' · ') : ''}
                    </span>
                </span>
                <span class="text-muted fs-11 text-nowrap ms-2">${escapeHtml(formatWhen(row.created_at))}</span>
            </li>`;
        })
        .join('');
}

function paintStats(stats: DashboardStats): void {
    const apps = stats.applications || {};
    setDash('evaluation_queue', stats.evaluation_queue ?? 0);
    setDash('inspections_scheduled', stats.inspections?.scheduled ?? 0);
    setDash('for_payment', apps.for_payment ?? 0);
    setDash('for_releasing', apps.for_releasing ?? 0);
    setDash('apps_total', apps.total ?? 0);
    setDash('released', apps.released ?? 0);
    setDash('compliance_open', stats.compliance?.open_notices ?? 0);
    setDash('pending_reg', stats.pending_registrations ?? 0);
    setDash('logbooks', stats.records?.logbook_entries ?? 0);
    setDash('unread_notif', stats.notifications?.unread ?? 0);

    // Legacy IDs kept for any external hooks
    const legacy: Record<string, string | number> = {
        'stat-apps-total': apps.total ?? 0,
        'stat-under-eval': apps.under_evaluation ?? 0,
        'stat-inspections': stats.inspections?.scheduled ?? 0,
        'stat-payment': apps.for_payment ?? 0,
        'stat-released': apps.released ?? 0,
        'stat-pending-reg': stats.pending_registrations ?? 0,
        'stat-logbooks': stats.records?.logbook_entries ?? 0,
        'stat-unread-notif': stats.notifications?.unread ?? 0,
    };
    Object.entries(legacy).forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = String(value);
    });

    const generated = document.querySelector<HTMLElement>('[data-dash-generated]');
    if (generated) {
        generated.textContent = formatWhen(stats.meta?.generated_at) || new Date().toLocaleString();
    }

    const recentCol = document.querySelector<HTMLElement>('[data-dash-recent-col]');
    if (recentCol) {
        const canViewAudit = Boolean(stats.meta?.can_view_audit);
        recentCol.classList.toggle('col-xl-7', canViewAudit);
        recentCol.classList.toggle('col-xl-12', !canViewAudit);
    }

    renderPipeline(stats.pipeline || []);
    renderAttention(stats.attention || []);
    renderRecentApps(stats.recent_applications || []);
    renderActivity(stats.recent_activity || []);
}

async function loadDashboard(showToast = false): Promise<void> {
    const root = document.getElementById('apics-admin-dashboard');
    if (!root) return;

    root.setAttribute('aria-busy', 'true');
    root.classList.add('is-loading');

    try {
        const { data } = await window.axios.get('/api/v1/staff/dashboard-stats');
        if (!data?.status) {
            throw new Error(data?.message || 'Unable to load dashboard');
        }
        paintStats((data.data || {}) as DashboardStats);
        if (showToast) {
            toastSuccess('Dashboard refreshed');
        }
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Unable to load operational stats');
    } finally {
        root.setAttribute('aria-busy', 'false');
        root.classList.remove('is-loading');
    }
}

export function initDashboardStats(): void {
    const root = document.getElementById('apics-admin-dashboard') || document.getElementById('ops-stats');
    if (!root) {
        return;
    }

    document.getElementById('btn-dashboard-refresh')?.addEventListener('click', () => {
        void loadDashboard(true);
    });

    void loadDashboard(false);
}
