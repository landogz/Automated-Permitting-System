import { toastError } from '../../../utils/toast';
import type { InspectionPrintUrls, InspectionRow } from './types';

const DOC_LABELS: Record<string, string> = {
    'qms-38': 'Print QMS-38 · Schedule',
    'qms-39': 'Print QMS-39 · Team',
    'o-03': 'Print O-03 · Notes',
    'qms-65': 'Print QMS-65 · Compliance',
    'dpwh-77-006-e': 'Print DPWH 77-006-E',
};

export function openInspectionPrint(url: string | undefined | null): void {
    if (!url) {
        toastError('Print link unavailable. Reload the list and try again.');
        return;
    }
    window.open(url, '_blank', 'noopener,noreferrer');
}

export function printActionsForRow(
    row: InspectionRow,
): Array<{ id: string; label: string; onClick: () => void }> {
    const urls = (row.print_urls || {}) as InspectionPrintUrls;
    const docs: Array<keyof InspectionPrintUrls> = [
        'qms-38',
        'qms-39',
        'o-03',
        'qms-65',
        'dpwh-77-006-e',
    ];

    return docs
        .filter((doc) => Boolean(urls[doc]))
        .filter((doc) => {
            if (doc === 'dpwh-77-006-e') {
                return row.requires_electrical_form || Boolean(row.electrical_form?.result);
            }
            return true;
        })
        .map((doc) => ({
            id: `print-${doc}`,
            label: DOC_LABELS[doc] || `Print ${doc}`,
            onClick: () => openInspectionPrint(urls[doc]),
        }));
}
