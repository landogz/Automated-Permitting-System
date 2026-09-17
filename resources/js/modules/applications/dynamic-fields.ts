import { escapeHtml } from '../../utils/bootstrap-modal';
import {
    createLocationPicker,
    type LocationPickerApi,
} from '../location-map/location-picker';

export type FormFieldDef = {
    name: string;
    label: string;
    type?: string;
    required?: boolean;
    options?: string[];
    step?: string;
};

export type FormSectionDef = {
    title: string;
    fields: FormFieldDef[];
};

export type FormSchema = {
    sections?: FormSectionDef[];
    fields?: FormFieldDef[];
};

function humanizeOption(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function locationPickerHtml(
    field: FormFieldDef,
    address: string,
    lat: unknown,
    lng: unknown,
    opts?: { compact?: boolean; collapsed?: boolean },
): string {
    const name = escapeHtml(field.name);
    const id = `dyn-field-${name}`;
    const required = field.required ? 'required' : '';
    const latVal = lat != null && lat !== '' ? escapeHtml(String(lat)) : '';
    const lngVal = lng != null && lng !== '' ? escapeHtml(String(lng)) : '';
    const compact = opts?.compact !== false;
    const collapsed = Boolean(opts?.collapsed);
    const compactClass = compact ? ' apics-location-picker--compact' : '';
    const collapsedClass = collapsed ? ' is-map-collapsed' : '';

    return `<div
        class="apics-location-picker${compactClass}${collapsedClass}"
        data-location-picker
        data-dyn-location="${name}"
        data-address-input="${id}"
        data-lat-input="${id}-lat"
        data-lng-input="${id}-lng"
        ${field.required ? 'data-required="1"' : ''}
    >
        <div class="position-relative mb-2">
            <div class="input-group">
                <span class="input-group-text"><i class="ri-search-line" aria-hidden="true"></i></span>
                <input
                    type="text"
                    class="form-control"
                    id="${id}"
                    name="${name}"
                    data-dyn-field="${name}"
                    data-location-address
                    data-location-search
                    value="${escapeHtml(address)}"
                    maxlength="500"
                    autocomplete="off"
                    placeholder="Search barangay, street, or landmark…"
                    ${required}
                >
                <button type="button" class="btn btn-soft-secondary" data-location-toggle-map aria-expanded="${collapsed ? 'false' : 'true'}" title="Toggle map">
                    <i class="ri-map-2-line" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline ms-1">${collapsed ? 'Show map' : 'Hide map'}</span>
                </button>
            </div>
            <div
                class="list-group position-absolute w-100 shadow-sm border rounded mt-1 d-none apics-location-picker__results"
                data-location-results
                style="z-index: 1080; max-height: 14rem; overflow: auto;"
                role="listbox"
            ></div>
        </div>
        <input type="hidden" id="${id}-lat" data-dyn-field="${name}_lat" data-location-lat value="${latVal}">
        <input type="hidden" id="${id}-lng" data-dyn-field="${name}_lng" data-location-lng value="${lngVal}">
        <div class="apics-location-picker__map-wrap">
            <div class="apics-location-picker__map border rounded overflow-hidden mb-2" data-location-map role="application"></div>
            <div class="d-flex flex-wrap justify-content-between gap-2">
                <p class="text-muted fs-12 mb-0">Search or click to pin. Drag the marker to adjust.</p>
                <p class="text-muted fs-12 mb-0 font-monospace" data-location-coords>No pin yet — search or click the map</p>
            </div>
        </div>
    </div>`;
}

function fieldControlHtml(field: FormFieldDef, values: Record<string, unknown>): string {
    const id = `dyn-field-${escapeHtml(field.name)}`;
    const name = escapeHtml(field.name);
    const required = field.required ? 'required' : '';
    const type = field.type || 'text';
    const raw = values[field.name] == null ? '' : String(values[field.name]);

    if (type === 'location') {
        // Owner address starts collapsed to reduce scroll fatigue; street/site stays open.
        const collapsed = field.name === 'owner_address';
        return locationPickerHtml(field, raw, values[`${field.name}_lat`], values[`${field.name}_lng`], {
            compact: true,
            collapsed,
        });
    }

    if (type === 'textarea') {
        return `<textarea class="form-control" id="${id}" name="${name}" data-dyn-field="${name}" rows="3" maxlength="5000" ${required}>${escapeHtml(raw)}</textarea>`;
    }

    if (type === 'select') {
        const options = (field.options || [])
            .map((opt) => {
                const selected = raw === opt ? 'selected' : '';
                return `<option value="${escapeHtml(opt)}" ${selected}>${escapeHtml(humanizeOption(opt))}</option>`;
            })
            .join('');
        return `<select class="form-select" id="${id}" name="${name}" data-dyn-field="${name}" ${required}>
            <option value="">Select…</option>
            ${options}
        </select>`;
    }

    const inputType = ['number', 'email', 'tel', 'date'].includes(type) ? type : 'text';
    const step = type === 'number' ? `step="${escapeHtml(field.step || '0.01')}" min="0"` : '';
    const maxLength = type === 'number' || type === 'date' ? '' : 'maxlength="500"';

    if (field.name.includes('cost') || field.name.includes('amount')) {
        return `<div class="input-group">
            <span class="input-group-text">₱</span>
            <input class="form-control font-monospace" type="number" id="${id}" name="${name}" data-dyn-field="${name}" value="${escapeHtml(raw)}" ${step} ${required} placeholder="0.00">
        </div>`;
    }

    if (field.name.includes('area')) {
        return `<div class="input-group">
            <input class="form-control font-monospace" type="number" id="${id}" name="${name}" data-dyn-field="${name}" value="${escapeHtml(raw)}" ${step} ${required} placeholder="0.00">
            <span class="input-group-text">sq.m</span>
        </div>`;
    }

    if (field.name.includes('height')) {
        return `<div class="input-group">
            <input class="form-control font-monospace" type="number" id="${id}" name="${name}" data-dyn-field="${name}" value="${escapeHtml(raw)}" ${step} ${required} placeholder="0.00">
            <span class="input-group-text">m</span>
        </div>`;
    }

    return `<input class="form-control" type="${inputType}" id="${id}" name="${name}" data-dyn-field="${name}" value="${escapeHtml(raw)}" ${step} ${maxLength} ${required}>`;
}

export function normalizeSections(schema?: FormSchema | null): FormSectionDef[] {
    if (!schema) {
        return [];
    }
    if (Array.isArray(schema.sections) && schema.sections.length) {
        return schema.sections.filter((s) => Array.isArray(s.fields) && s.fields.length);
    }
    if (Array.isArray(schema.fields) && schema.fields.length) {
        return [{ title: 'Application details', fields: schema.fields }];
    }
    return [];
}

const dynPickers = new WeakMap<HTMLElement, LocationPickerApi[]>();

function bindMapToggles(container: HTMLElement, apis: LocationPickerApi[]): void {
    container.querySelectorAll<HTMLElement>('[data-location-picker]').forEach((root) => {
        const toggle = root.querySelector<HTMLButtonElement>('[data-location-toggle-map]');
        if (!toggle) return;
        toggle.addEventListener('click', () => {
            const collapsed = root.classList.toggle('is-map-collapsed');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            const label = toggle.querySelector('span');
            if (label) label.textContent = collapsed ? 'Show map' : 'Hide map';
            if (!collapsed) {
                window.setTimeout(() => apis.forEach((api) => api.invalidateSize()), 80);
                window.setTimeout(() => apis.forEach((api) => api.invalidateSize()), 280);
            }
        });
    });
}

function mountPickersIn(container: HTMLElement): void {
    dynPickers.get(container)?.forEach((api) => api.destroy());
    dynPickers.delete(container);

    requestAnimationFrame(() => {
        const apis: LocationPickerApi[] = [];
        container.querySelectorAll<HTMLElement>('[data-location-picker]').forEach((root) => {
            const addressId = root.dataset.addressInput || '';
            const addressInput = document.getElementById(addressId) as HTMLInputElement | null;
            if (!addressInput) return;
            const latInput = document.getElementById(`${addressId}-lat`) as HTMLInputElement | null;
            const lngInput = document.getElementById(`${addressId}-lng`) as HTMLInputElement | null;
            try {
                apis.push(
                    createLocationPicker({
                        root,
                        addressInput,
                        latInput,
                        lngInput,
                        required: root.dataset.required === '1',
                    }),
                );
            } catch {
                // Map init can fail offline; address text still works.
            }
        });
        dynPickers.set(container, apis);
        bindMapToggles(container, apis);
        window.setTimeout(() => apis.forEach((api) => api.invalidateSize()), 320);
    });
}

function sectionsHtml(sections: FormSectionDef[], values: Record<string, unknown>): string {
    if (!sections.length) {
        return `<p class="text-muted small mb-0">No fields in this step.</p>`;
    }

    return sections
        .map((section) => {
            const fieldsHtml = section.fields
                .map((field) => {
                    const col =
                        field.type === 'textarea' ||
                        field.type === 'location' ||
                        field.name.includes('address')
                            ? 'col-12'
                            : 'col-sm-6';
                    const star = field.required ? '<span class="text-danger">*</span>' : '';
                    return `<div class="${col}">
                        <label class="form-label" for="dyn-field-${escapeHtml(field.name)}">${escapeHtml(field.label)} ${star}</label>
                        ${fieldControlHtml(field, values)}
                    </div>`;
                })
                .join('');

            return `<div class="apics-app-form__section mb-3">
                <h6 class="fs-13 text-uppercase text-muted mb-3">${escapeHtml(section.title)}</h6>
                <div class="row g-3">${fieldsHtml}</div>
            </div>`;
        })
        .join('');
}

export function renderDynamicFields(
    container: HTMLElement,
    schema: FormSchema | null | undefined,
    values: Record<string, unknown> = {},
): void {
    const sections = normalizeSections(schema);
    if (!sections.length) {
        container.innerHTML = `<p class="text-muted small mb-0">No additional fields for this form.</p>`;
        mountPickersIn(container);
        return;
    }

    container.innerHTML = sectionsHtml(sections, values);
    mountPickersIn(container);
}

/**
 * Render schema sections into named tab panel hosts (data-app-form-dyn="<tabId>").
 */
export function renderDynamicFieldsByTab(
    root: HTMLElement,
    schema: FormSchema | null | undefined,
    values: Record<string, unknown>,
    grouper: (schema?: FormSchema | null) => Record<string, FormSectionDef[]>,
): void {
    const grouped = grouper(schema);
    Object.entries(grouped).forEach(([tabId, sections]) => {
        const host = root.querySelector<HTMLElement>(`[data-app-form-dyn="${tabId}"]`);
        if (!host) return;
        if (tabId === 'documents') {
            host.innerHTML = '';
            return;
        }
        host.innerHTML = sectionsHtml(sections, values);
    });
    mountPickersIn(root);
}

export function collectDynamicFieldValues(container: HTMLElement): Record<string, unknown> {
    const payload: Record<string, unknown> = {};
    container.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>('[data-dyn-field]').forEach((el) => {
        const key = el.dataset.dynField || el.name;
        if (!key) return;
        const raw = el.value.trim();
        if (raw === '') return;

        if (el instanceof HTMLInputElement && (el.type === 'number' || key.endsWith('_lat') || key.endsWith('_lng'))) {
            const num = Number(raw);
            if (Number.isFinite(num)) {
                payload[key] = num;
            }
            return;
        }

        payload[key] = raw;
    });
    return payload;
}

export function validateRequiredDynamicFields(container: HTMLElement): string | null {
    const missing: string[] = [];
    container.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>('[data-dyn-field][required]').forEach((el) => {
        if (!el.value.trim()) {
            const label = container.querySelector(`label[for="${el.id}"]`)?.textContent?.replace('*', '').trim();
            missing.push(label || el.dataset.dynField || 'Required field');
        }
    });
    if (!missing.length) {
        return null;
    }
    return `Please complete: ${missing.slice(0, 4).join(', ')}${missing.length > 4 ? '…' : ''}`;
}

export function renderPayloadSummary(payload: Record<string, unknown> | null | undefined, schema?: FormSchema | null): string {
    const sections = normalizeSections(schema);
    const labels = new Map<string, string>();
    sections.forEach((section) => {
        section.fields.forEach((field) => labels.set(field.name, field.label));
    });

    const entries = Object.entries(payload || {}).filter(([key, value]) => {
        if (key.endsWith('_lat') || key.endsWith('_lng')) return false;
        return value !== null && value !== undefined && value !== '';
    });
    if (!entries.length) {
        return `<p class="text-muted mb-0">No form details provided.</p>`;
    }

    return `<div class="row g-3">${entries
        .map(([key, value]) => {
            const label = labels.get(key) || humanizeOption(key);
            const lat = payload?.[`${key}_lat`];
            const lng = payload?.[`${key}_lng`];
            const coords =
                lat != null && lng != null
                    ? `<span class="text-muted small d-block font-monospace">${escapeHtml(String(lat))}, ${escapeHtml(String(lng))}</span>`
                    : '';
            return `<div class="col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">${escapeHtml(label)}</p>
                <p class="mb-0">${escapeHtml(String(value))}${coords}</p>
            </div>`;
        })
        .join('')}</div>`;
}

export function resizeDynamicLocationPickers(container: HTMLElement): void {
    dynPickers.get(container)?.forEach((api) => api.invalidateSize());
}
