/**
 * Shared helpers for Operations pages: Active | Completed tabs + counts.
 */

export function setOpsCompletedCount(queueId: string, count: number): void {
    const safe = Math.max(0, count);
    document.querySelectorAll(`[data-ops-completed-count="${queueId}"]`).forEach((el) => {
        el.textContent = String(safe);
    });
}

export function setOpsActiveCount(queueId: string, count: number): void {
    const safe = Math.max(0, count);
    document.querySelectorAll(`[data-ops-active-count="${queueId}"]`).forEach((el) => {
        el.textContent = String(safe);
    });
}

type AdjustableTable = {
    columns: { adjust: () => unknown };
    responsive?: { recalc?: () => unknown };
} | null | undefined;

/**
 * Bind Active / Completed tab switching and recalculate DataTables on show.
 */
export function bindOpsQueueTabs(
    queueId: string,
    getCompletedTable?: () => AdjustableTable,
    getActiveTable?: () => AdjustableTable,
): void {
    const root = document.querySelector<HTMLElement>(`[data-ops-queue="${queueId}"]`);
    if (!root || root.dataset.opsTabsBound === '1') {
        return;
    }
    root.dataset.opsTabsBound = '1';

    const tabs = root.querySelectorAll<HTMLButtonElement>('[data-ops-tab]');
    const panes = {
        active: root.querySelector<HTMLElement>('[data-ops-pane="active"]'),
        completed: root.querySelector<HTMLElement>('[data-ops-pane="completed"]'),
    };

    const show = (which: 'active' | 'completed'): void => {
        tabs.forEach((tab) => {
            const on = tab.dataset.opsTab === which;
            tab.classList.toggle('active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panes.active?.classList.toggle('d-none', which !== 'active');
        panes.active?.classList.toggle('is-active', which === 'active');
        panes.completed?.classList.toggle('d-none', which !== 'completed');
        panes.completed?.classList.toggle('is-active', which === 'completed');

        requestAnimationFrame(() => {
            const dt = which === 'completed' ? getCompletedTable?.() : getActiveTable?.();
            if (!dt) return;
            try {
                dt.columns.adjust();
                dt.responsive?.recalc?.();
            } catch {
                // ignore layout race
            }
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const which = tab.dataset.opsTab === 'completed' ? 'completed' : 'active';
            show(which);
        });
    });
}

/** @deprecated Prefer bindOpsQueueTabs — kept for any leftover accordion markup */
export function bindOpsCompletedAccordionLayout(
    accordionId: string,
    getRawDataTable: () => AdjustableTable,
): void {
    bindOpsQueueTabs(accordionId, getRawDataTable);
}

export function isCompletedInspectionStatus(status: string): boolean {
    return status === 'completed' || status === 'cancelled';
}

export function isCompletedOopStatus(status: string): boolean {
    return status === 'paid_stub' || status === 'cancelled';
}

export function isCompletedNoticeStatus(status: string): boolean {
    return status === 'closed';
}

export function isCompletedEvaluationStatus(status: string): boolean {
    return ['for_inspection', 'for_payment', 'for_releasing', 'for_compliance', 'released', 'disapproved'].includes(status);
}
