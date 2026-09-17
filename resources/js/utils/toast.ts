import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../../css/apics-toast.css';
import { pauseOpenModalFocusTraps } from './bootstrap-modal';

const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 2800,
    timerProgressBar: false,
    width: 'auto',
    padding: '0.85rem 1.15rem',
    customClass: {
        container: 'apics-toast-container',
        popup: 'apics-toast',
        title: 'apics-toast-title',
        htmlContainer: 'apics-toast-html',
        icon: 'apics-toast-icon',
    },
});

const CONFIRM_CONTAINER_CLASS = 'apics-confirm-container';

function focusSwalInput(): void {
    const container = Swal.getContainer();
    if (container) {
        container.style.zIndex = '2000';
    }

    // Swal sets aria-hidden on body siblings; clear it from focused ancestors first.
    const layout = document.getElementById('layout-wrapper');
    if (layout?.getAttribute('aria-hidden') === 'true') {
        layout.removeAttribute('aria-hidden');
    }

    const input = Swal.getInput();
    if (input) {
        input.removeAttribute('readonly');
        input.removeAttribute('disabled');
        input.setAttribute('data-gramm', 'false');
        input.setAttribute('data-gramm_editor', 'false');
        input.setAttribute('data-enable-grammarly', 'false');
        requestAnimationFrame(() => {
            input.focus({ preventScroll: true });
        });
    }
}

export function toastSuccess(message: string): void {
    void Toast.fire({ icon: 'success', title: message });
}

export function toastError(message: string): void {
    void Toast.fire({ icon: 'error', title: message });
}

export async function confirmAction(title: string, text: string): Promise<boolean> {
    const restoreFocusTraps = pauseOpenModalFocusTraps();

    try {
        const result = await Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            heightAuto: false,
            returnFocus: false,
            focusConfirm: true,
            allowOutsideClick: false,
            customClass: {
                container: CONFIRM_CONTAINER_CLASS,
                popup: 'apics-confirm',
            },
            didOpen: () => {
                const container = Swal.getContainer();
                if (container) {
                    container.style.zIndex = '2000';
                }
                const layout = document.getElementById('layout-wrapper');
                if (layout?.getAttribute('aria-hidden') === 'true') {
                    layout.removeAttribute('aria-hidden');
                }
            },
        });

        return result.isConfirmed;
    } finally {
        restoreFocusTraps();
    }
}

/**
 * Confirm dialog with a required textarea (e.g. inspection failure reason, appeal grounds).
 * Returns trimmed text, or null if cancelled.
 */
export async function confirmWithReason(options: {
    title: string;
    text: string;
    inputLabel?: string;
    inputPlaceholder?: string;
    confirmButtonText?: string;
    minLength?: number;
}): Promise<string | null> {
    const minLength = options.minLength ?? 10;
    const restoreFocusTraps = pauseOpenModalFocusTraps();

    try {
        const result = await Swal.fire({
            title: options.title,
            text: options.text,
            icon: 'warning',
            input: 'textarea',
            inputLabel: options.inputLabel || 'Reason',
            inputPlaceholder: options.inputPlaceholder || 'Describe the findings…',
            inputAttributes: {
                'aria-label': options.inputLabel || 'Reason',
                'data-gramm': 'false',
                'data-gramm_editor': 'false',
                'data-enable-grammarly': 'false',
            },
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || 'Yes, continue',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            heightAuto: false,
            returnFocus: false,
            focusConfirm: false,
            allowOutsideClick: false,
            customClass: {
                container: CONFIRM_CONTAINER_CLASS,
                popup: 'apics-confirm',
            },
            didOpen: focusSwalInput,
            inputValidator: (value) => {
                const trimmed = String(value || '').trim();
                if (trimmed.length < minLength) {
                    return `Please enter at least ${minLength} characters so compliance/the applicant understands the issue.`;
                }
                return null;
            },
        });

        if (!result.isConfirmed) {
            return null;
        }

        return String(result.value || '').trim();
    } finally {
        restoreFocusTraps();
    }
}
