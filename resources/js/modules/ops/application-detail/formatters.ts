import { escapeHtml } from '../../../utils/bootstrap-modal';
import { humanizeKey } from '../../../utils/ops-ui';
import type { FormFieldDef } from '../../applications/dynamic-fields';
import type { TimelineEvent } from './types';

const CURRENCY_FIELDS = new Set(['estimated_cost', 'project_cost', 'amount', 'total_amount']);
const AREA_FIELDS = new Set(['lot_area', 'floor_area', 'built_up_area']);
const LENGTH_FIELDS = new Set(['building_height']);
const ENUMISH_FIELDS = new Set(['scope_of_work', 'occupancy', 'character_of_occupancy']);

export function formatDateShort(value?: string | null): string {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    } catch {
        return escapeHtml(value);
    }
}

export function formatDateTime(value?: string | null): string {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleString();
    } catch {
        return escapeHtml(value);
    }
}

export function formatPhpCurrency(value: unknown): string {
    const num = typeof value === 'number' ? value : Number(String(value).replace(/,/g, ''));
    if (!Number.isFinite(num)) return escapeHtml(String(value ?? '—'));
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export function formatAreaSqm(value: unknown): string {
    const num = typeof value === 'number' ? value : Number(String(value).replace(/,/g, ''));
    if (!Number.isFinite(num)) return escapeHtml(String(value ?? '—'));
    return `${num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} sq.m`;
}

export function formatLengthM(value: unknown): string {
    const num = typeof value === 'number' ? value : Number(String(value).replace(/,/g, ''));
    if (!Number.isFinite(num)) return escapeHtml(String(value ?? '—'));
    return `${num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} m`;
}

export function isUnverifiedEmail(email?: string | null): boolean {
    if (!email) return false;
    const lower = email.toLowerCase();
    return (
        lower.endsWith('.local') ||
        lower.includes('@example.') ||
        lower.includes('test@') ||
        lower.includes('demo@')
    );
}

export function emailDisplayHtml(email?: string | null): string {
    if (!email) return '—';
    const base = `<span class="text-break">${escapeHtml(email)}</span>`;
    if (!isUnverifiedEmail(email)) return base;
    return `${base} <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1">Unverified / test data</span>`;
}

export function formatFieldValue(field: FormFieldDef | { name: string; type?: string }, raw: unknown): string {
    if (raw === null || raw === undefined || raw === '') return '—';

    const name = field.name;
    const type = field.type || '';

    if (CURRENCY_FIELDS.has(name) || name.includes('cost') || name.includes('amount')) {
        return `<span class="font-monospace fw-semibold text-body">${formatPhpCurrency(raw)}</span>`;
    }
    if (AREA_FIELDS.has(name) || (type === 'number' && name.includes('area'))) {
        return `<span class="font-monospace">${formatAreaSqm(raw)}</span>`;
    }
    if (LENGTH_FIELDS.has(name) || name.includes('height')) {
        return `<span class="font-monospace">${formatLengthM(raw)}</span>`;
    }
    if (ENUMISH_FIELDS.has(name) || type === 'select') {
        return escapeHtml(humanizeKey(String(raw)));
    }
    if (type === 'email' || name.includes('email')) {
        return emailDisplayHtml(String(raw));
    }
    if (typeof raw === 'string' && /^[a-z0-9]+(?:_[a-z0-9]+)+$/i.test(raw)) {
        return escapeHtml(humanizeKey(raw));
    }

    return escapeHtml(String(raw));
}

export function buildTimelineEvents(app: {
    created_at?: string | null;
    submitted_at?: string | null;
    classified_at?: string | null;
    updated_at?: string | null;
    status?: string;
    evaluations?: Array<{ decided_at?: string | null; status?: string | null; result?: string | null }>;
    inspections?: Array<{
        scheduled_at?: string | null;
        completed_at?: string | null;
        status?: string | null;
        result?: string | null;
    }>;
    orders_of_payment?: Array<{
        issued_at?: string | null;
        paid_at?: string | null;
        status?: string | null;
    }>;
    compliance_notices?: Array<{ issued_at?: string | null; status?: string | null; type?: string | null }>;
}): TimelineEvent[] {
    const status = (app.status || 'draft').toLowerCase();
    const onComplianceBranch =
        status === 'for_compliance' ||
        status === 'disapproved' ||
        (Array.isArray(app.compliance_notices) &&
            app.compliance_notices.length > 0 &&
            !['for_payment', 'for_releasing', 'released'].includes(status));

    const createdMs = app.created_at ? new Date(app.created_at).getTime() : NaN;
    const submittedMs = app.submitted_at ? new Date(app.submitted_at).getTime() : NaN;
    let draftedAt = app.created_at || null;
    if (Number.isFinite(createdMs) && Number.isFinite(submittedMs) && createdMs > submittedMs) {
        draftedAt = new Date(submittedMs - 3600000).toISOString();
    }

    const decidedEval = (app.evaluations || []).find((row) => row.decided_at) || (app.evaluations || [])[0];
    const latestInspection =
        (app.inspections || []).find((row) => row.completed_at) || (app.inspections || [])[0];
    const latestOop = (app.orders_of_payment || [])[0];
    const latestNotice = (app.compliance_notices || [])[0];

    const evaluatedAt = decidedEval?.decided_at || null;
    const inspectedAt = latestInspection?.completed_at || latestInspection?.scheduled_at || null;
    const paymentAt = onComplianceBranch
        ? latestNotice?.issued_at || null
        : latestOop?.paid_at || latestOop?.issued_at || null;
    const releasingAt =
        status === 'for_releasing' || status === 'released'
            ? latestOop?.paid_at || app.updated_at || null
            : null;
    const releasedAt =
        status === 'released'
            ? app.updated_at || latestOop?.paid_at || inspectedAt || evaluatedAt
            : status === 'disapproved'
              ? latestNotice?.issued_at || app.updated_at || null
              : null;

    const midLabel = onComplianceBranch ? 'Compliance' : 'Payment';
    const endLabel = status === 'disapproved' ? 'Disapproved' : 'Released';

    const steps: Array<{ key: string; label: string; at: string | null }> = [
        { key: 'drafted', label: 'Drafted', at: draftedAt },
        { key: 'submitted', label: 'Submitted', at: app.submitted_at || null },
        { key: 'classified', label: 'Classified', at: app.classified_at || null },
        { key: 'evaluation', label: 'Evaluation', at: evaluatedAt },
        { key: 'inspection', label: 'Inspection', at: inspectedAt },
        { key: 'payment', label: midLabel, at: paymentAt },
        { key: 'releasing', label: 'For Releasing', at: releasingAt },
        { key: 'released', label: endLabel, at: releasedAt },
    ];

    const currentIndex = (() => {
        switch (status) {
            case 'draft':
                return 0;
            case 'submitted':
                return 2;
            case 'under_evaluation':
                return 3;
            case 'for_inspection':
                return 4;
            case 'for_payment':
            case 'for_compliance':
                return 5;
            case 'for_releasing':
                return 6;
            case 'released':
            case 'disapproved':
                return 7;
            default:
                return 0;
        }
    })();

    const terminal = status === 'released' || status === 'disapproved';

    return steps.map((step, index) => {
        let state: TimelineEvent['state'] = 'pending';
        if (terminal) {
            state = index <= currentIndex ? 'done' : 'pending';
        } else if (index < currentIndex) {
            state = 'done';
        } else if (index === currentIndex) {
            state = 'current';
        }

        // Status can advance ahead of timestamps (e.g. under_evaluation without classified_at stamp).
        if (state !== 'pending' && !step.at && index < currentIndex) {
            // keep done without date
        }

        return {
            key: step.key,
            label: step.label,
            at: step.at,
            state,
        };
    });
}

export function timelineStepperHtml(events: TimelineEvent[]): string {
    if (!events.length) {
        return `<p class="text-muted small mb-0">No timeline events yet.</p>`;
    }

    return `<ol class="apics-timeline-stepper list-unstyled mb-0" aria-label="Application timeline">
        ${events
            .map((event) => {
                const dateText =
                    event.at && event.state !== 'pending'
                        ? formatDateShort(event.at)
                        : event.state === 'pending'
                          ? 'Pending'
                          : event.state === 'current'
                            ? 'In progress'
                            : '—';
                return `<li class="apics-timeline-stepper__item is-${event.state}">
                    <span class="apics-timeline-stepper__dot" aria-hidden="true"></span>
                    <span class="apics-timeline-stepper__label">${escapeHtml(event.label)}</span>
                    <span class="apics-timeline-stepper__date">${escapeHtml(dateText)}</span>
                </li>`;
            })
            .join('')}
    </ol>`;
}

export function formatBytes(size?: number): string {
    if (!size || size <= 0) return '';
    if (size < 1024) return `${size} B`;
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}
