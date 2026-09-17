import { escapeHtml } from './bootstrap-modal';

const STATUS_LABELS: Record<string, string> = {
    draft: 'Draft',
    submitted: 'Submitted',
    under_evaluation: 'Under Evaluation',
    for_inspection: 'For Inspection',
    for_compliance: 'For Compliance',
    for_payment: 'For Payment',
    released: 'Released',
    disapproved: 'Disapproved',
    scheduled: 'Scheduled',
    in_progress: 'In Progress',
    completed: 'Completed',
    cancelled: 'Cancelled',
    issued: 'Issued',
    paid_stub: 'Paid',
    appealed: 'Appealed',
    closed: 'Closed',
};

const CLASSIFICATION_SLA: Record<string, { label: string; days: number; tone: string }> = {
    simple: { label: 'Simple', days: 3, tone: 'success' },
    complex: { label: 'Complex', days: 7, tone: 'purple' },
    highly_technical: { label: 'Highly Technical', days: 20, tone: 'danger' },
};

export function humanizeKey(value: string | null | undefined): string {
    if (!value) return '—';
    const key = String(value).toLowerCase();
    if (STATUS_LABELS[key]) return STATUS_LABELS[key];
    return key.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export function statusBadgeHtml(status: string | null | undefined, opts?: { pulse?: boolean }): string {
    const key = String(status || '')
        .toLowerCase()
        .replaceAll(' ', '_')
        .replaceAll('-', '_');
    const label = humanizeKey(key);
    const pulse =
        opts?.pulse && (key === 'under_evaluation' || key === 'submitted' || key === 'issued')
            ? '<span class="apics-status__dot" aria-hidden="true"></span>'
            : '';
    return `<span class="apics-status apics-status--${escapeHtml(key)}">${pulse}${escapeHtml(label)}</span>`;
}

export function classificationBadgeHtml(classification: string | null | undefined): string {
    if (!classification) {
        return '<span class="text-muted small">Unclassified</span>';
    }
    const key = String(classification).toLowerCase().replaceAll(' ', '_').replaceAll('-', '_');
    const meta = CLASSIFICATION_SLA[key];
    if (!meta) {
        return `<span class="badge bg-info-subtle text-info">${escapeHtml(humanizeKey(key))}</span>`;
    }
    return `<span class="apics-class-badge apics-class-badge--${meta.tone}">${escapeHtml(meta.label)}</span>`;
}

export function classificationSlaTagHtml(classification: string | null | undefined): string {
    if (!classification) return '';
    const key = String(classification).toLowerCase().replaceAll(' ', '_').replaceAll('-', '_');
    const meta = CLASSIFICATION_SLA[key];
    if (!meta) return '';
    return `<span class="apics-sla-tag">RA 11032 · ${meta.days} day${meta.days === 1 ? '' : 's'}</span>`;
}

/** Remaining statutory processing time with visual health (RA 11032). */
export function slaCountdownHtml(
    submittedAt?: string | null,
    classification?: string | null,
): string {
    if (!submittedAt) return '';
    const key = String(classification || 'simple').toLowerCase().replaceAll(' ', '_').replaceAll('-', '_');
    const days = CLASSIFICATION_SLA[key]?.days ?? 3;
    const start = new Date(submittedAt).getTime();
    if (Number.isNaN(start)) return '';

    const deadline = start + days * 86400000;
    const remainingMs = deadline - Date.now();
    const remainingHours = remainingMs / 3600000;

    let label: string;
    if (remainingMs <= 0) {
        const overdueH = Math.abs(remainingHours);
        label = overdueH >= 24 ? `${Math.ceil(overdueH / 24)}d overdue` : `${Math.ceil(overdueH)}h overdue`;
    } else if (remainingHours < 24) {
        label = `${Math.max(1, Math.ceil(remainingHours))}h left`;
    } else {
        label = `${Math.ceil(remainingHours / 24)}d left`;
    }

    const tone = remainingMs <= 0 || remainingHours <= 24 ? 'critical' : remainingHours <= 48 ? 'warn' : 'ok';

    return `<span class="apics-sla-health apics-sla-health--${tone}"><span class="apics-sla-health__dot" aria-hidden="true"></span>${escapeHtml(label)}</span>`;
}

export function applicationNoLinkHtml(applicationNo: string | null | undefined, uuid?: string | null): string {
    const no = applicationNo || '—';
    if (!uuid) {
        return `<span class="apics-app-no">${escapeHtml(no)}</span>`;
    }
    return `<button type="button" class="btn btn-link p-0 apics-app-no" data-ops-open-app="${escapeHtml(uuid)}">${escapeHtml(no)}</button>`;
}

const INSPECTION_TYPE_LABELS: Record<string, string> = {
    joint: 'Joint',
    joint_structural: 'Joint · Structural',
    joint_architectural: 'Joint · Architectural',
    joint_electrical: 'Joint · Electrical',
    joint_sanitary: 'Joint · Sanitary',
    joint_mechanical: 'Joint · Mechanical',
    joint_fire_safety: 'Joint · Fire Safety',
    electrical: 'Electrical (DPWH 77-006-E)',
    final: 'Final',
};

export function inspectionTypeLabel(type: string | null | undefined): string {
    if (!type) return '—';
    const key = String(type).toLowerCase().replaceAll(' ', '_').replaceAll('-', '_');
    if (INSPECTION_TYPE_LABELS[key]) return INSPECTION_TYPE_LABELS[key];
    if (key.startsWith('joint_')) {
        return `Joint · ${humanizeKey(key.replace(/^joint_/, ''))}`;
    }
    return humanizeKey(key);
}

export function inspectorAvatarHtml(name?: string | null): string {
    const display = (name || '').trim();
    if (!display) {
        return `<span class="text-muted">Unassigned</span>`;
    }
    const parts = display.split(/\s+/).filter(Boolean);
    const initials =
        parts.length >= 2
            ? `${parts[0]![0] || ''}${parts[parts.length - 1]![0] || ''}`.toUpperCase()
            : display.slice(0, 2).toUpperCase();
    const short =
        /administrator/i.test(display) || /^admin\b/i.test(display)
            ? 'Admin'
            : parts.length > 2
              ? `${parts[0]} ${parts[parts.length - 1]![0]}.`
              : display;

    return `<span class="apics-inspector"><span class="apics-avatar" aria-hidden="true">${escapeHtml(initials)}</span><span>${escapeHtml(short)}</span></span>`;
}

export function resultPendingHtml(label = 'Pending Verification'): string {
    return `<span class="apics-result-pending">${escapeHtml(label)}</span>`;
}

export function formatPhpMono(
    amount: string | number | null | undefined,
    opts?: { total?: boolean },
): string {
    const n = Number(amount ?? 0);
    const formatted = Number.isNaN(n)
        ? '₱0.00'
        : new Intl.NumberFormat('en-PH', {
              style: 'currency',
              currency: 'PHP',
              minimumFractionDigits: 2,
          }).format(n);
    const cls = opts?.total ? 'apics-money apics-money--total' : 'apics-money';
    return `<span class="${cls}">${escapeHtml(formatted)}</span>`;
}

export function formatScheduleBlock(
    value?: string | null,
    opts?: { relative?: boolean; padHour?: boolean },
): string {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return escapeHtml(value);

    const dateLine = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });

    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    if (hours === 0) hours = 12;
    const hourStr = opts?.padHour ? String(hours).padStart(2, '0') : String(hours);
    const timeLine = `${hourStr}:${minutes} ${ampm}`;

    let relative = '';
    if (opts?.relative !== false) {
        const now = new Date();
        const startToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const startTarget = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const dayDiff = Math.round((startTarget.getTime() - startToday.getTime()) / 86400000);
        if (dayDiff === 0) relative = 'Today';
        else if (dayDiff === 1) relative = 'Tomorrow';
        else if (dayDiff === -1) relative = 'Yesterday';
        else if (dayDiff > 1 && dayDiff < 7) relative = `In ${dayDiff} days`;
        else if (dayDiff < -1 && dayDiff > -7) relative = `${Math.abs(dayDiff)} days ago`;
    }

    return `<div class="apics-datetime">
        <div class="apics-datetime__date">${escapeHtml(dateLine)}</div>
        <div class="apics-datetime__meta">${escapeHtml(timeLine)}${relative ? ` · ${escapeHtml(relative)}` : ''}</div>
    </div>`;
}

export function formatElapsed(seconds: number): string {
    const s = Math.max(0, Math.floor(seconds));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const rem = s % 60;
    if (h > 0) return `${h}h ${m}m ${rem}s`;
    return `${m}m ${String(rem).padStart(2, '0')}s`;
}

/** Statutory appeal window remaining for G-04 (default 15 days from issued_at). */
export function appealCountdownHtml(opts: {
    type: string;
    status: string;
    issuedAt?: string | null;
    dueAt?: string | null;
}): string {
    if (opts.type !== 'g04_disapproval' || opts.status === 'closed') {
        return '<span class="text-muted">—</span>';
    }
    const end = opts.dueAt
        ? new Date(opts.dueAt)
        : opts.issuedAt
          ? new Date(new Date(opts.issuedAt).getTime() + 15 * 86400000)
          : null;
    if (!end || Number.isNaN(end.getTime())) {
        return '<span class="text-muted">—</span>';
    }
    const daysLeft = Math.ceil((end.getTime() - Date.now()) / 86400000);
    if (daysLeft < 0) {
        return `<span class="badge bg-danger-subtle text-danger">Appeal window closed</span>`;
    }
    if (daysLeft === 0) {
        return `<span class="badge bg-warning-subtle text-warning">Last day to appeal</span>`;
    }
    return `<span class="badge bg-info-subtle text-info">${daysLeft} day${daysLeft === 1 ? '' : 's'} left to appeal</span>`;
}

export function slaRiskFromSubmitted(submittedAt?: string | null, classification?: string | null): 'ok' | 'warn' | 'over' {
    if (!submittedAt) return 'ok';
    const key = String(classification || 'simple').toLowerCase().replaceAll(' ', '_');
    const days = CLASSIFICATION_SLA[key]?.days ?? 3;
    const start = new Date(submittedAt).getTime();
    if (Number.isNaN(start)) return 'ok';
    const elapsedDays = (Date.now() - start) / 86400000;
    if (elapsedDays >= days) return 'over';
    if (elapsedDays >= days - 2) return 'warn';
    return 'ok';
}
