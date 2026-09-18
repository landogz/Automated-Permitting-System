import { escapeHtml } from '../../../utils/bootstrap-modal';
import { userAvatarHtml } from '../../../utils/user-avatar';

export type ManagedUserRow = {
    uuid: string;
    name: string;
    email: string;
    phone?: string | null;
    avatar_url?: string | null;
    is_active: boolean;
    approval_status: string;
    role?: string | null;
    roles?: string[];
    department?: { uuid: string; code: string; name: string } | null;
};

export type UsersSummary = {
    total?: number;
    active?: number;
    inactive?: number;
    staff?: number;
    applicants?: number;
    pending?: number;
};

const ROLE_LABELS: Record<string, string> = {
    admin: 'System Administrator',
    building_official: 'Building Official',
    evaluator: 'Technical Evaluator',
    inspector: 'Inspector',
    assessor: 'Fee Assessor',
    compliance: 'Compliance Officer',
    records: 'Records Officer',
    receiving: 'Receiving Clerk',
    staff: 'OCBO Staff',
    applicant: 'Applicant',
};

export function roleLabel(role: string | null | undefined): string {
    if (!role) return '—';
    return ROLE_LABELS[role] || role.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export function roleBadge(role: string | null | undefined): string {
    const raw = role || '';
    const label = roleLabel(raw || null);
    const tone =
        raw === 'admin' || raw === 'building_official'
            ? 'bg-danger-subtle text-danger'
            : raw === 'applicant'
              ? 'bg-info-subtle text-info'
              : 'bg-primary-subtle text-primary';

    return `<span class="badge ${tone} text-wrap text-start">${escapeHtml(label)}</span>`;
}

export function approvalBadge(status: string): string {
    const map: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        declined: 'bg-danger-subtle text-danger',
    };
    const cls = map[status] || 'bg-secondary-subtle text-secondary';
    const label = status ? status.charAt(0).toUpperCase() + status.slice(1) : '—';
    return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
}

export function activeBadge(isActive: boolean): string {
    return isActive
        ? '<span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line align-middle me-1"></i>Active</span>'
        : '<span class="badge bg-secondary-subtle text-secondary"><i class="ri-forbid-line align-middle me-1"></i>Inactive</span>';
}

export function nameCell(row: ManagedUserRow): string {
    const phone = row.phone ? `<span class="d-block text-muted fs-11 text-truncate">${escapeHtml(row.phone)}</span>` : '';
    return `
        <span class="apics-users-name d-inline-flex align-items-center gap-2 min-w-0">
            ${userAvatarHtml(row.name, row.avatar_url, 'apics-user-avatar')}
            <span class="min-w-0">
                <span class="fw-semibold d-block text-truncate">${escapeHtml(row.name || '')}</span>
                ${phone}
            </span>
        </span>`;
}

export function emailCell(row: ManagedUserRow): string {
    const email = row.email || '';
    if (!email) return '<span class="text-muted">—</span>';
    return `
        <a href="mailto:${escapeHtml(email)}" class="apics-users-email text-decoration-none">
            <i class="ri-mail-line align-middle me-1 text-muted" aria-hidden="true"></i>
            <span class="text-truncate">${escapeHtml(email)}</span>
        </a>`;
}

export function departmentCell(row: ManagedUserRow): string {
    if (!row.department) {
        return '<span class="text-muted">—</span>';
    }
    return `
        <span class="apics-users-dept" title="${escapeHtml(row.department.name)}">
            <span class="badge bg-light text-body border">${escapeHtml(row.department.code)}</span>
            <span class="d-none d-xl-inline text-muted fs-12 ms-1 text-truncate">${escapeHtml(row.department.name)}</span>
        </span>`;
}

export function paintUsersSummary(summary?: UsersSummary | null): void {
    const set = (key: string, value: string): void => {
        document.querySelectorAll(`[data-users-stat="${key}"]`).forEach((el) => {
            el.textContent = value;
        });
    };

    if (!summary) {
        ['total', 'staff', 'applicants', 'pending', 'active', 'inactive'].forEach((k) => set(k, '—'));
        return;
    }

    set('total', String(summary.total ?? 0));
    set('staff', String(summary.staff ?? 0));
    set('applicants', String(summary.applicants ?? 0));
    set('pending', String(summary.pending ?? 0));
    set('active', String(summary.active ?? 0));
    set('inactive', String(summary.inactive ?? 0));
}
