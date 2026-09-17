type BootstrapFocusTrap = {
    activate?: () => void;
    deactivate?: () => void;
};

type BootstrapModalInstance = {
    show: () => void;
    hide: () => void;
    _focustrap?: BootstrapFocusTrap;
};

type BootstrapModalStatic = {
    getInstance?: (element: Element) => BootstrapModalInstance | null;
    getOrCreateInstance: (element: Element, options?: object) => BootstrapModalInstance;
};

declare global {
    interface Window {
        bootstrap?: {
            Modal: BootstrapModalStatic;
        };
    }
}

export function getBootstrapModal(element: HTMLElement | null): BootstrapModalInstance | null {
    if (!element || !window.bootstrap?.Modal) {
        return null;
    }

    return window.bootstrap.Modal.getOrCreateInstance(element);
}

/**
 * Pause Bootstrap modal focus traps so stacked dialogs (e.g. SweetAlert2)
 * can receive keyboard focus. Call the returned restore function when done.
 */
export function pauseOpenModalFocusTraps(): () => void {
    const restores: Array<() => void> = [];

    const active = document.activeElement;
    if (active instanceof HTMLElement && typeof active.blur === 'function') {
        active.blur();
    }

    document.querySelectorAll<HTMLElement>('.modal.show').forEach((modalEl) => {
        const Modal = window.bootstrap?.Modal;
        if (!Modal) {
            return;
        }

        const instance = Modal.getInstance?.(modalEl) ?? Modal.getOrCreateInstance(modalEl);
        const trap = instance?._focustrap;
        if (trap && typeof trap.deactivate === 'function') {
            trap.deactivate();
            restores.push(() => {
                if (modalEl.classList.contains('show') && typeof trap.activate === 'function') {
                    trap.activate();
                }
            });
        }
    });

    return () => {
        restores.forEach((restore) => restore());
    };
}

export function showModal(id: string): void {
    const el = document.getElementById(id);
    getBootstrapModal(el)?.show();
}

export function hideModal(id: string): void {
    const el = document.getElementById(id);
    getBootstrapModal(el)?.hide();
}

export function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
