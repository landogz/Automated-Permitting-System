import type { AxiosInstance, InternalAxiosRequestConfig } from 'axios';
import '../../../css/apics-loading.css';

declare module 'axios' {
    export interface AxiosRequestConfig {
        /** Skip the global APICS loading indicator for this request. */
        skipLoading?: boolean;
        /** Prefer a blocking overlay (default: true for mutating methods). */
        blockingLoading?: boolean;
        /** Optional status message on the blocking overlay. */
        loadingMessage?: string;
    }
}

const SHOW_DELAY_MS = 120;
const HIDE_DELAY_MS = 80;
const MUTATING = new Set(['post', 'put', 'patch', 'delete']);

type LoadingOptions = {
    message?: string;
    blocking?: boolean;
};

let pendingCount = 0;
let showTimer: ReturnType<typeof setTimeout> | null = null;
let hideTimer: ReturnType<typeof setTimeout> | null = null;
let rootEl: HTMLElement | null = null;
let messageEl: HTMLElement | null = null;
let blockingPreferred = false;
let currentMessage = 'Please wait…';

function ensureDom(): void {
    if (rootEl || typeof document === 'undefined') {
        return;
    }

    rootEl = document.createElement('div');
    rootEl.id = 'apics-loading';
    rootEl.className = 'apics-loading';
    rootEl.setAttribute('aria-hidden', 'true');
    rootEl.innerHTML = `
        <div class="apics-loading__bar" aria-hidden="true"></div>
        <div class="apics-loading__overlay" role="status" aria-live="polite" aria-busy="true">
            <div class="apics-loading__card">
                <div class="apics-loading__spinner" aria-hidden="true"></div>
                <p class="apics-loading__message">Please wait…</p>
            </div>
        </div>
    `;
    document.body.appendChild(rootEl);
    messageEl = rootEl.querySelector('.apics-loading__message');
}

function paint(): void {
    ensureDom();
    if (!rootEl) {
        return;
    }

    const visible = pendingCount > 0;
    rootEl.classList.toggle('is-visible', visible);
    rootEl.classList.toggle('is-blocking', visible && blockingPreferred);
    rootEl.setAttribute('aria-hidden', visible ? 'false' : 'true');
    document.body.classList.toggle('apics-loading-active', visible && blockingPreferred);

    if (messageEl) {
        messageEl.textContent = currentMessage || 'Please wait…';
    }
}

function bump(delta: number, options?: LoadingOptions): void {
    if (hideTimer) {
        clearTimeout(hideTimer);
        hideTimer = null;
    }

    if (delta > 0) {
        pendingCount += delta;
        if (options?.message) {
            currentMessage = options.message;
        }
        if (options?.blocking) {
            blockingPreferred = true;
        }

        if (pendingCount === delta) {
            if (showTimer) {
                clearTimeout(showTimer);
            }
            showTimer = setTimeout(() => {
                showTimer = null;
                paint();
            }, SHOW_DELAY_MS);
        } else {
            paint();
        }
        return;
    }

    pendingCount = Math.max(0, pendingCount + delta);
    if (pendingCount > 0) {
        paint();
        return;
    }

    if (showTimer) {
        clearTimeout(showTimer);
        showTimer = null;
    }

    hideTimer = setTimeout(() => {
        hideTimer = null;
        blockingPreferred = false;
        currentMessage = 'Please wait…';
        paint();
    }, HIDE_DELAY_MS);
}

/**
 * Show the global loading indicator (reference-counted).
 */
export function showLoading(messageOrOptions?: string | LoadingOptions): void {
    const options: LoadingOptions = typeof messageOrOptions === 'string'
        ? { message: messageOrOptions, blocking: true }
        : { blocking: true, ...messageOrOptions };
    bump(1, options);
}

/**
 * Hide one layer of the global loading indicator.
 */
export function hideLoading(): void {
    bump(-1);
}

/**
 * Run an async task with the global loading indicator.
 */
export async function withLoading<T>(
    task: () => Promise<T>,
    messageOrOptions?: string | LoadingOptions,
): Promise<T> {
    showLoading(messageOrOptions);
    try {
        return await task();
    } finally {
        hideLoading();
    }
}

/**
 * Disable a button and show a spinner label while an async task runs.
 */
export async function withButtonLoading<T>(
    button: HTMLButtonElement | HTMLElement | null,
    task: () => Promise<T>,
    busyLabel = 'Working…',
): Promise<T> {
    if (!(button instanceof HTMLElement)) {
        return task();
    }

    const wasDisabled = button instanceof HTMLButtonElement
        ? button.disabled
        : button.getAttribute('aria-disabled') === 'true';
    const originalHtml = button.innerHTML;

    if (button instanceof HTMLButtonElement) {
        button.disabled = true;
    } else {
        button.setAttribute('aria-disabled', 'true');
        button.classList.add('disabled');
    }
    button.classList.add('apics-btn-loading');
    button.setAttribute('aria-busy', 'true');
    button.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span><span>${busyLabel}</span>`;

    try {
        return await task();
    } finally {
        button.classList.remove('apics-btn-loading');
        button.removeAttribute('aria-busy');
        button.innerHTML = originalHtml;
        if (button instanceof HTMLButtonElement) {
            button.disabled = wasDisabled;
        } else if (!wasDisabled) {
            button.removeAttribute('aria-disabled');
            button.classList.remove('disabled');
        }
    }
}

function shouldSkip(config?: InternalAxiosRequestConfig): boolean {
    return Boolean(config?.skipLoading);
}

function isMutating(config?: InternalAxiosRequestConfig): boolean {
    const method = (config?.method || 'get').toLowerCase();
    if (config?.blockingLoading === true) {
        return true;
    }
    if (config?.blockingLoading === false) {
        return false;
    }
    return MUTATING.has(method);
}

/**
 * Attach global loading indicators to every Axios request (unless skipLoading).
 */
export function installAxiosLoading(instance: AxiosInstance): void {
    ensureDom();

    instance.interceptors.request.use((config) => {
        if (!shouldSkip(config)) {
            const message = config.loadingMessage
                || (isMutating(config) ? 'Saving…' : 'Loading…');
            bump(1, {
                message,
                blocking: isMutating(config),
            });
            (config as InternalAxiosRequestConfig & { __apicsLoading?: boolean }).__apicsLoading = true;
        }
        return config;
    });

    const release = (config?: InternalAxiosRequestConfig): void => {
        const flagged = config as (InternalAxiosRequestConfig & { __apicsLoading?: boolean }) | undefined;
        if (flagged?.__apicsLoading) {
            flagged.__apicsLoading = false;
            bump(-1);
        }
    };

    instance.interceptors.response.use(
        (response) => {
            release(response.config);
            return response;
        },
        (error) => {
            release(error?.config);
            return Promise.reject(error);
        },
    );
}
