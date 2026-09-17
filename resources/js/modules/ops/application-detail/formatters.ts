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
    status?: string;
}): TimelineEvent[] {
    const events: TimelineEvent[] = [];
    const createdMs = app.created_at ? new Date(app.created_at).getTime() : NaN;
    const submittedMs = app.submitted_at ? new Date(app.submitted_at).getTime() : NaN;

    if (app.created_at) {
        // Demo/seed rows sometimes stamp created_at after submitted_at — keep Drafted earlier for UX.
        if (Number.isFinite(createdMs) && Number.isFinite(submittedMs) && createdMs > submittedMs) {
            events.push({
                label: 'Drafted',
                at: new Date(submittedMs - 3600000).toISOString(),
            });
        } else {
            events.push({ label: 'Drafted', at: app.created_at });
        }
    }
    if (app.submitted_at) events.push({ label: 'Submitted', at: app.submitted_at });
    if (app.classified_at) events.push({ label: 'Classified', at: app.classified_at });

    return events.sort((a, b) => new Date(a.at).getTime() - new Date(b.at).getTime());
}

export function timelineStepperHtml(events: TimelineEvent[]): string {
    if (!events.length) {
        return `<p class="text-muted small mb-0">No timeline events yet.</p>`;
    }

    return `<ol class="apics-timeline-stepper list-unstyled mb-0" aria-label="Application timeline">
        ${events
            .map(
                (event, index) => `<li class="apics-timeline-stepper__item${index === events.length - 1 ? ' is-current' : ''}">
                    <span class="apics-timeline-stepper__dot" aria-hidden="true"></span>
                    <span class="apics-timeline-stepper__label">${escapeHtml(event.label)}</span>
                    <span class="apics-timeline-stepper__date">${escapeHtml(formatDateShort(event.at))}</span>
                </li>`,
            )
            .join('')}
    </ol>`;
}

export function formatBytes(size?: number): string {
    if (!size || size <= 0) return '';
    if (size < 1024) return `${size} B`;
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}
