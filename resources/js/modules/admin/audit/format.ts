import { escapeHtml } from '../../../utils/bootstrap-modal';

export type AuditResource = {
    type: string;
    label: string;
    href?: string | null;
};

export type AuditRow = {
    uuid: string;
    event: string;
    category?: string;
    category_label?: string;
    severity?: string;
    badge_tone?: string;
    actor_name?: string | null;
    actor_roles?: string[];
    actor_role_label?: string | null;
    ip_address?: string | null;
    user_agent?: string | null;
    client_label?: string | null;
    resource?: AuditResource | null;
    request_url?: string | null;
    session_id?: string | null;
    old_values?: Record<string, unknown> | null;
    new_values?: Record<string, unknown> | null;
    has_diff?: boolean;
    meta?: Record<string, unknown> | null;
    created_at?: string | null;
};

const PHT: Intl.DateTimeFormatOptions = {
    timeZone: 'Asia/Manila',
};

export function formatWhenStack(iso?: string | null): string {
    if (!iso) {
        return '<span class="text-muted">—</span>';
    }

    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) {
        return `<span class="text-muted">${escapeHtml(iso)}</span>`;
    }

    const date = d.toLocaleDateString('en-US', {
        ...PHT,
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
    const time = d.toLocaleTimeString('en-US', {
        ...PHT,
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    });

    return `
        <span class="apics-audit-when">
            <span class="apics-audit-when__date">${escapeHtml(date)}</span>
            <span class="apics-audit-when__time">${escapeHtml(time)} PHT</span>
        </span>
    `;
}

export function eventBadge(event: string, tone?: string): string {
    const safeTone = ['auth', 'create', 'modify', 'danger'].includes(tone || '')
        ? tone
        : 'auth';

    return `<span class="apics-audit-badge apics-audit-badge--${safeTone}">${escapeHtml(event)}</span>`;
}

export function actorCell(row: AuditRow): string {
    const name = row.actor_name || '—';
    const role = row.actor_role_label || '';
    const initials = name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || '')
        .join('') || '?';

    return `
        <span class="apics-audit-actor">
            <span class="apics-audit-actor__avatar" aria-hidden="true">${escapeHtml(initials)}</span>
            <span class="min-w-0">
                <span class="apics-audit-actor__name d-block text-truncate">${escapeHtml(name)}</span>
                ${role ? `<span class="apics-audit-actor__role d-block text-truncate">${escapeHtml(role)}</span>` : ''}
            </span>
        </span>
    `;
}

export function resourceCell(row: AuditRow): string {
    const resource = row.resource;
    if (!resource?.label) {
        return '<span class="text-muted">—</span>';
    }

    const label = escapeHtml(resource.label);
    const type = escapeHtml(resource.type || 'Record');
    const body = resource.href
        ? `<a href="${escapeHtml(resource.href)}" class="link-primary text-decoration-none">${label}</a>`
        : label;

    return `
        <span class="apics-audit-resource">
            <span class="apics-audit-resource__type">${type}</span>
            ${body}
        </span>
    `;
}

export function clientCell(row: AuditRow): string {
    return `
        <span class="apics-audit-client">
            <span class="apics-audit-client__ip d-block">${escapeHtml(row.ip_address || '—')}</span>
            <span class="apics-audit-client__ua d-block text-truncate" title="${escapeHtml(row.user_agent || '')}">
                ${escapeHtml(row.client_label || '—')}
            </span>
        </span>
    `;
}

export function prettyJson(value: unknown): string {
    try {
        return JSON.stringify(value ?? {}, null, 2);
    } catch {
        return String(value ?? '');
    }
}
