import '../../../css/apics-app-select.css';
import { escapeHtml } from '../../utils/bootstrap-modal';

export type ApplicationLookupItem = {
    uuid: string;
    application_no: string;
    project_title?: string | null;
    status?: string | null;
    applicant_name?: string | null;
};

export type ApplicationSelectApi = {
    getValue: () => string;
    clear: () => void;
    setValue: (uuid: string, label?: string) => void;
    refresh: (search?: string) => Promise<void>;
};

type MountOptions = {
    endpoint?: string;
    perPage?: number;
    forStep?: string;
};

const instances = new WeakMap<HTMLElement, ApplicationSelectApi>();

function formatLabel(item: ApplicationLookupItem): string {
    const title = (item.project_title || '').trim();
    const applicant = (item.applicant_name || '').trim();
    const parts = [item.application_no || item.uuid.slice(0, 8)];
    if (title) parts.push(title);
    if (applicant) parts.push(applicant);
    return parts.join(' — ');
}

function debounce(fn: (value: string) => void, ms: number): (value: string) => void {
    let timer: ReturnType<typeof setTimeout> | null = null;
    return (value: string) => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => fn(value), ms);
    };
}

export function mountApplicationSelect(
    root: HTMLElement,
    options: MountOptions = {},
): ApplicationSelectApi {
    const existing = instances.get(root);
    if (existing) return existing;

    const inputId = root.dataset.inputId || '';
    const hidden = (inputId ? document.getElementById(inputId) : null) as HTMLInputElement | null;
    const toggle = root.querySelector<HTMLButtonElement>('.apics-app-select-toggle');
    const clearBtn = root.querySelector<HTMLButtonElement>('.apics-app-select-clear');
    const panel = root.querySelector<HTMLElement>('.apics-app-select-panel');
    const query = root.querySelector<HTMLInputElement>('.apics-app-select-query');
    const results = root.querySelector<HTMLElement>('.apics-app-select-results');
    const placeholder = root.querySelector<HTMLElement>('.apics-app-select-placeholder');
    const labelEl = root.querySelector<HTMLElement>('.apics-app-select-label');

    if (!hidden || !toggle || !panel || !query || !results || !placeholder || !labelEl) {
        throw new Error('Application select markup is incomplete');
    }

    const endpoint = options.endpoint || '/api/v1/staff/applications/lookup';
    const perPage = options.perPage ?? 20;
    const forStep = options.forStep || root.dataset.forStep || '';
    let open = false;
    let requestSeq = 0;
    let lastItems: ApplicationLookupItem[] = [];

    const setOpen = (next: boolean): void => {
        open = next;
        root.classList.toggle('is-open', open);
        panel.classList.toggle('d-none', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            void refresh(query.value.trim());
            window.setTimeout(() => query.focus(), 0);
        }
    };

    const paintSelection = (text: string | null): void => {
        const hasValue = Boolean(text);
        placeholder.classList.toggle('d-none', hasValue);
        labelEl.classList.toggle('d-none', !hasValue);
        labelEl.textContent = text || '';
        clearBtn?.classList.toggle('d-none', !hasValue);
    };

    const selectItem = (item: ApplicationLookupItem): void => {
        hidden.value = item.uuid;
        paintSelection(formatLabel(item));
        setOpen(false);
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const renderResults = (items: ApplicationLookupItem[], emptyMessage: string): void => {
        lastItems = items;
        if (!items.length) {
            results.innerHTML = `<div class="p-3 text-muted small">${escapeHtml(emptyMessage)}</div>`;
            return;
        }

        results.innerHTML = items
            .map((item) => {
                const title = escapeHtml(item.project_title || 'Untitled project');
                const meta = escapeHtml(
                    [item.status || '', item.applicant_name || ''].filter(Boolean).join(' · '),
                );
                const no = escapeHtml(item.application_no || '');
                return `<button type="button" class="list-group-item list-group-item-action py-2 px-3 text-start"
                    role="option" data-uuid="${escapeHtml(item.uuid)}">
                    <div class="fw-semibold">${no}</div>
                    <div class="small text-body">${title}</div>
                    ${meta ? `<div class="small text-muted">${meta}</div>` : ''}
                </button>`;
            })
            .join('');
    };

    const emptyForStep =
        'No applications ready for this step. Finish the previous Operations step first.';

    const refresh = async (search = ''): Promise<void> => {
        const seq = ++requestSeq;
        results.innerHTML = `<div class="p-3 text-muted small">Searching…</div>`;
        try {
            const { data } = await window.axios.get(endpoint, {
                params: {
                    search: search || undefined,
                    per_page: perPage,
                    for_step: forStep || undefined,
                },
                skipLoading: true,
            });
            if (seq !== requestSeq) return;
            const items = (data.data?.items || []) as ApplicationLookupItem[];
            renderResults(
                items,
                search
                    ? 'No applications match your search.'
                    : forStep
                      ? emptyForStep
                      : 'No applications found.',
            );
        } catch (error: any) {
            if (seq !== requestSeq) return;
            results.innerHTML = `<div class="p-3 text-danger small">${escapeHtml(
                error?.response?.data?.message || 'Unable to load applications',
            )}</div>`;
        }
    };

    const debouncedRefresh = debounce((value: string) => {
        void refresh(value);
    }, 280);

    const api: ApplicationSelectApi = {
        getValue: () => hidden.value.trim(),
        clear: () => {
            hidden.value = '';
            paintSelection(null);
            query.value = '';
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        },
        setValue: (uuid: string, label?: string) => {
            hidden.value = uuid;
            paintSelection(label || uuid);
        },
        refresh,
    };

    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        setOpen(!open);
    });
    clearBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        api.clear();
    });

    query.addEventListener('input', () => debouncedRefresh(query.value.trim()));
    query.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
            toggle.focus();
        }
        if (event.key === 'Enter') {
            event.preventDefault();
            if (lastItems[0]) selectItem(lastItems[0]);
        }
    });

    results.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement).closest<HTMLElement>('[data-uuid]');
        if (!target?.dataset.uuid) return;
        const item = lastItems.find((row) => row.uuid === target.dataset.uuid);
        if (item) selectItem(item);
    });

    document.addEventListener('click', (event) => {
        if (!open) return;
        if (root.contains(event.target as Node)) return;
        setOpen(false);
    });

    const form = root.closest('form');
    form?.addEventListener('reset', () => {
        window.setTimeout(() => api.clear(), 0);
    });

    // Prefetch when parent modal opens so first click feels instant.
    const modal = root.closest('.modal');
    modal?.addEventListener('shown.bs.modal', () => {
        void refresh(query.value.trim());
    });

    instances.set(root, api);
    return api;
}

export function initApplicationSelects(
    scope: ParentNode = document,
    options: MountOptions = {},
): Map<string, ApplicationSelectApi> {
    const map = new Map<string, ApplicationSelectApi>();
    scope.querySelectorAll<HTMLElement>('[data-apics-app-select]').forEach((root) => {
        const api = mountApplicationSelect(root, options);
        const id = root.dataset.inputId || '';
        if (id) map.set(id, api);
    });
    return map;
}
