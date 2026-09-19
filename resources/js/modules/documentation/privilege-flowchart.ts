/**
 * Interactive privilege flowchart on the public documentation page.
 */
import { showModal } from '../../utils/bootstrap-modal';

type FlowPayload = {
    key: string;
    title: string;
    summary: string;
    code: string;
    screens: string[];
    functions: string[];
    anchor: string;
};

function readPayloadMap(): Record<string, FlowPayload> {
    const el = document.getElementById('privilege-flow-data');
    if (!el?.textContent) {
        return {};
    }
    try {
        return JSON.parse(el.textContent) as Record<string, FlowPayload>;
    } catch (error) {
        console.error('[APICS] Invalid privilege flow JSON', error);
        return {};
    }
}

function fillModal(payload: FlowPayload): void {
    const titleEl = document.getElementById('privilege-flow-modal-title');
    const codeEl = document.getElementById('privilege-flow-modal-code');
    const summaryEl = document.getElementById('privilege-flow-modal-summary');
    const screensWrap = document.getElementById('privilege-flow-modal-screens-wrap');
    const screensEl = document.getElementById('privilege-flow-modal-screens');
    const functionsEl = document.getElementById('privilege-flow-modal-functions');
    const moreEl = document.getElementById('privilege-flow-modal-more') as HTMLAnchorElement | null;

    if (titleEl) titleEl.textContent = payload.title;
    if (codeEl) {
        codeEl.textContent = payload.code;
        codeEl.classList.toggle('d-none', !payload.code);
    }
    if (summaryEl) summaryEl.textContent = payload.summary;

    if (screensWrap && screensEl) {
        const hasScreens = payload.screens.length > 0;
        screensWrap.classList.toggle('d-none', !hasScreens);
        screensEl.textContent = payload.screens.join(' · ');
    }

    if (functionsEl) {
        functionsEl.innerHTML = '';
        payload.functions.forEach((fn) => {
            const li = document.createElement('li');
            li.className = 'mb-1';
            li.textContent = fn;
            functionsEl.appendChild(li);
        });
    }

    if (moreEl) {
        moreEl.href = payload.anchor || '#privileges';
        moreEl.classList.toggle('d-none', !payload.anchor);
    }
}

function openPayload(payload: FlowPayload | undefined): void {
    if (!payload) return;
    fillModal(payload);
    const modalEl = document.getElementById('privilege-flow-modal');
    if (!modalEl) return;

    // Prefer shared helper; fall back if bootstrap binding is late.
    showModal('privilege-flow-modal');
    if (!modalEl.classList.contains('show') && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

export function initPrivilegeFlowchart(): void {
    const root = document.querySelector<HTMLElement>('[data-privilege-flowchart]');
    if (!root) return;

    const map = readPayloadMap();

    root.querySelectorAll<HTMLButtonElement>('[data-flow-node]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            const key = btn.getAttribute('data-flow-node') || '';
            openPayload(map[key]);
        });
    });

    document.getElementById('privilege-flow-modal-more')?.addEventListener('click', () => {
        const modalEl = document.getElementById('privilege-flow-modal');
        if (modalEl && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getInstance(modalEl)?.hide();
        }
    });
}
