const STATUS_LABELS: Record<string, string> = {
    draft: 'Draft',
    submitted: 'Submitted',
    under_evaluation: 'Under Evaluation',
    for_inspection: 'For Inspection',
    for_compliance: 'For Compliance',
    for_payment: 'For Payment',
    released: 'Released',
    disapproved: 'Disapproved',
};

/**
 * Canonical status chip markup shared by Applications DataTable and any SPA lists.
 */
export function statusBadge(status: string): string {
    const key = status.trim().toLowerCase().replace(/\s+/g, '_');
    const label = STATUS_LABELS[key] || status.replace(/_/g, ' ');
    const safeKey = key.replace(/[^a-z0-9_]/g, '') || 'draft';
    const escaped = label
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    return `<span class="apics-status apics-status--${safeKey}">${escaped}</span>`;
}

export function statusLabel(status: string): string {
    const key = status.trim().toLowerCase().replace(/\s+/g, '_');
    return STATUS_LABELS[key] || status.replace(/_/g, ' ');
}
