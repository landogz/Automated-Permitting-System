/**
 * Shared show/hide password toggles for auth + account modals.
 * Prefer `data-password-toggle="<inputId>"` on the eye button.
 */

const BOUND = 'data-apics-password-toggle-bound';

function syncToggleUi(button: HTMLElement, input: HTMLInputElement): void {
    const showing = input.type === 'text';
    const icon = button.querySelector('i');
    if (icon) {
        icon.className = showing ? 'ri-eye-off-line align-middle' : 'ri-eye-line align-middle';
    }
    button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
    button.setAttribute('aria-pressed', showing ? 'true' : 'false');
}

function bindButton(button: HTMLElement, input: HTMLInputElement): void {
    if (button.getAttribute(BOUND) === '1') {
        return;
    }
    button.setAttribute(BOUND, '1');
    button.setAttribute('type', 'button');
    button.setAttribute('aria-pressed', 'false');

    button.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        syncToggleUi(button, input);
        input.focus({ preventScroll: true });
    });
}

/**
 * Bind every `[data-password-toggle]` control under `root`.
 */
export function bindPasswordToggles(root: ParentNode = document): void {
    root.querySelectorAll<HTMLElement>('[data-password-toggle]').forEach((button) => {
        const inputId = button.getAttribute('data-password-toggle') || '';
        if (!inputId) {
            return;
        }
        const input = document.getElementById(inputId);
        if (!(input instanceof HTMLInputElement)) {
            return;
        }
        bindButton(button, input);
    });
}

/**
 * Bind a single eye button by element id to a password input id.
 */
export function bindPasswordToggleById(buttonId: string, inputId: string): void {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    if (!(button instanceof HTMLElement) || !(input instanceof HTMLInputElement)) {
        return;
    }
    if (!button.hasAttribute('data-password-toggle')) {
        button.setAttribute('data-password-toggle', inputId);
    }
    bindButton(button, input);
}
