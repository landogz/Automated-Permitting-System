import { hasPermission } from './auth';
import { toastSuccess } from './toast';

export type OperationsNextStep = {
    step: string;
    label: string;
    path: string;
    status: string;
};

const PATH_PERMISSIONS: Record<string, string> = {
    '/admin/evaluation-queue': 'evaluations.manage',
    '/admin/inspections': 'inspections.manage',
    '/admin/orders-of-payment': 'fees.manage',
    '/admin/compliance-notices': 'compliance.manage',
    '/admin/logbooks': 'records.manage',
};

/**
 * Toast success and navigate to the next Operations step when the user can access it.
 * Cross-role handoffs only toast (no redirect).
 */
export function toastSuccessAndGoNext(
    message: string,
    nextStep?: OperationsNextStep | null,
    delayMs = 900,
): void {
    const path = nextStep?.path?.trim() || '';
    const label = nextStep?.label?.trim() || '';
    const samePage = !path || path === window.location.pathname;
    const required = PATH_PERMISSIONS[path];
    const canOpen = !required || hasPermission(required);

    if (samePage || !label) {
        toastSuccess(message);
        return;
    }

    if (!canOpen) {
        toastSuccess(`${message} → handoff to ${label}`);
        return;
    }

    toastSuccess(`${message} → ${label}`);
    window.setTimeout(() => {
        window.location.assign(path);
    }, delayMs);
}
