import { escapeHtml } from '../../utils/bootstrap-modal';

export type SchemaField = {
    name: string;
    label: string;
    type: string;
    required: boolean;
    options?: string[];
    step?: string;
};

export type SchemaSection = {
    title: string;
    fields: SchemaField[];
};

export type FormSchema = {
    sections: SchemaSection[];
};

const FIELD_TYPES = [
    { value: 'text', label: 'Text' },
    { value: 'number', label: 'Number' },
    { value: 'email', label: 'Email' },
    { value: 'tel', label: 'Phone' },
    { value: 'date', label: 'Date' },
    { value: 'textarea', label: 'Long text' },
    { value: 'select', label: 'Dropdown' },
    { value: 'location', label: 'Map location' },
] as const;

function slugify(value: string): string {
    return value
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '')
        .slice(0, 60);
}

export function emptySchema(): FormSchema {
    return {
        sections: [
            {
                title: 'Application details',
                fields: [
                    {
                        name: 'owner_name',
                        label: 'Owner full name',
                        type: 'text',
                        required: true,
                    },
                ],
            },
        ],
    };
}

export function normalizeSchema(raw: unknown): FormSchema {
    if (!raw || typeof raw !== 'object') {
        return emptySchema();
    }

    const schema = raw as Record<string, unknown>;

    if (Array.isArray(schema.sections) && schema.sections.length) {
        return {
            sections: schema.sections.map((section, index) => {
                const row = (section && typeof section === 'object' ? section : {}) as Record<string, unknown>;
                const fields = Array.isArray(row.fields) ? row.fields : [];
                return {
                    title: String(row.title || `Section ${index + 1}`),
                    fields: fields.map((field) => normalizeField(field)).filter((f): f is SchemaField => Boolean(f)),
                };
            }),
        };
    }

    if (Array.isArray(schema.fields) && schema.fields.length) {
        return {
            sections: [
                {
                    title: 'Application details',
                    fields: schema.fields.map((field) => normalizeField(field)).filter((f): f is SchemaField => Boolean(f)),
                },
            ],
        };
    }

    return emptySchema();
}

function normalizeField(raw: unknown): SchemaField | null {
    if (!raw || typeof raw !== 'object') {
        return null;
    }
    const field = raw as Record<string, unknown>;
    const name = String(field.name || '').trim();
    const label = String(field.label || '').trim();
    if (!name || !label) {
        return null;
    }

    const type = String(field.type || 'text');
    const options = Array.isArray(field.options)
        ? field.options.map((opt) => String(opt)).filter(Boolean)
        : String(field.options || '')
              .split(',')
              .map((opt) => opt.trim())
              .filter(Boolean);

    return {
        name,
        label,
        type: FIELD_TYPES.some((item) => item.value === type) ? type : 'text',
        required: Boolean(field.required),
        options: type === 'select' ? options : undefined,
        step: field.step != null ? String(field.step) : type === 'number' ? '0.01' : undefined,
    };
}

export function countFields(schema: FormSchema): number {
    return schema.sections.reduce((sum, section) => sum + section.fields.length, 0);
}

export type SchemaTemplate = {
    code: string;
    title: string;
    schema: FormSchema;
    required_attachments?: string[];
};

export function mountSchemaEditor(
    root: HTMLElement,
    initial?: unknown,
    options?: {
        templates?: SchemaTemplate[];
        onApplyTemplate?: (template: SchemaTemplate) => void;
        confirmReplace?: () => Promise<boolean>;
    },
): {
    getSchema: () => FormSchema;
    setSchema: (raw: unknown) => void;
    validate: () => string | null;
} {
    let schema = normalizeSchema(initial);
    const templates = options?.templates || [];

    const paint = (): void => {
        const summary = `${schema.sections.length} section(s) · ${countFields(schema)} field(s)`;
        const templateButtons = templates.length
            ? `<div class="d-flex flex-wrap gap-2 mb-3" data-templates>
                <span class="text-muted fs-12 align-self-center me-1">Load template:</span>
                ${templates
                    .map(
                        (t, i) =>
                            `<button type="button" class="btn btn-sm btn-soft-info" data-apply-template="${i}">
                                <i class="ri-file-copy-2-line align-bottom me-1"></i>${escapeHtml(t.code)}
                            </button>`,
                    )
                    .join('')}
            </div>`
            : '';

        root.innerHTML = `
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <h6 class="mb-0">Form fields builder</h6>
                    <p class="text-muted fs-12 mb-0" data-schema-summary>${escapeHtml(summary)}</p>
                </div>
                <button type="button" class="btn btn-sm btn-soft-primary" data-add-section>
                    <i class="ri-add-line align-bottom me-1"></i> Add section
                </button>
            </div>
            ${templateButtons}
            <div data-sections class="d-flex flex-column gap-3">
                ${schema.sections.map((section, sectionIndex) => renderSection(section, sectionIndex, schema.sections.length)).join('')}
            </div>
        `;

        bind(root);
    };

    const bind = (scope: HTMLElement): void => {
        scope.querySelectorAll<HTMLButtonElement>('[data-apply-template]').forEach((btn) => {
            btn.addEventListener('click', () => {
                void (async () => {
                    const index = Number(btn.dataset.applyTemplate);
                    const template = templates[index];
                    if (!template) {
                        return;
                    }
                    if (countFields(schema) > 0 && options?.confirmReplace) {
                        const ok = await options.confirmReplace();
                        if (!ok) {
                            return;
                        }
                    }
                    schema = normalizeSchema(template.schema);
                    options?.onApplyTemplate?.(template);
                    paint();
                })();
            });
        });

        scope.querySelector('[data-add-section]')?.addEventListener('click', () => {
            schema.sections.push({
                title: `Section ${schema.sections.length + 1}`,
                fields: [],
            });
            paint();
        });

        scope.querySelectorAll<HTMLElement>('[data-section]').forEach((sectionEl) => {
            const sectionIndex = Number(sectionEl.dataset.sectionIndex);

            sectionEl.querySelector('[data-section-title]')?.addEventListener('input', (event) => {
                schema.sections[sectionIndex].title = (event.target as HTMLInputElement).value;
                updateSummary(scope);
            });

            sectionEl.querySelector('[data-section-up]')?.addEventListener('click', () => {
                if (sectionIndex <= 0) return;
                const sections = schema.sections;
                [sections[sectionIndex - 1], sections[sectionIndex]] = [sections[sectionIndex], sections[sectionIndex - 1]];
                paint();
            });

            sectionEl.querySelector('[data-section-down]')?.addEventListener('click', () => {
                if (sectionIndex >= schema.sections.length - 1) return;
                const sections = schema.sections;
                [sections[sectionIndex + 1], sections[sectionIndex]] = [sections[sectionIndex], sections[sectionIndex + 1]];
                paint();
            });

            sectionEl.querySelector('[data-remove-section]')?.addEventListener('click', () => {
                if (schema.sections.length <= 1) {
                    return;
                }
                schema.sections.splice(sectionIndex, 1);
                paint();
            });

            sectionEl.querySelector('[data-add-field]')?.addEventListener('click', () => {
                const labelInput = sectionEl.querySelector<HTMLInputElement>('[data-new-label]');
                const typeSelect = sectionEl.querySelector<HTMLSelectElement>('[data-new-type]');
                const requiredInput = sectionEl.querySelector<HTMLInputElement>('[data-new-required]');
                const optionsInput = sectionEl.querySelector<HTMLInputElement>('[data-new-options]');
                const label = labelInput?.value.trim() || '';
                if (!label) {
                    labelInput?.focus();
                    return;
                }
                const type = typeSelect?.value || 'text';
                const nameBase = slugify(label) || `field_${schema.sections[sectionIndex].fields.length + 1}`;
                let name = nameBase;
                let i = 2;
                const used = new Set(schema.sections.flatMap((s) => s.fields.map((f) => f.name)));
                while (used.has(name)) {
                    name = `${nameBase}_${i++}`;
                }

                const options = (optionsInput?.value || '')
                    .split(',')
                    .map((opt) => opt.trim())
                    .filter(Boolean);

                schema.sections[sectionIndex].fields.push({
                    name,
                    label,
                    type,
                    required: Boolean(requiredInput?.checked),
                    options: type === 'select' ? options : undefined,
                    step: type === 'number' ? '0.01' : undefined,
                });
                paint();
            });

            sectionEl.querySelectorAll<HTMLElement>('[data-field-row]').forEach((rowEl) => {
                const fieldIndex = Number(rowEl.dataset.fieldIndex);

                rowEl.querySelector('[data-field-label]')?.addEventListener('input', (event) => {
                    const value = (event.target as HTMLInputElement).value;
                    schema.sections[sectionIndex].fields[fieldIndex].label = value;
                    const nameInput = rowEl.querySelector<HTMLInputElement>('[data-field-name]');
                    if (nameInput && !nameInput.dataset.locked) {
                        const next = slugify(value);
                        if (next) {
                            nameInput.value = next;
                            schema.sections[sectionIndex].fields[fieldIndex].name = next;
                        }
                    }
                });

                rowEl.querySelector('[data-field-name]')?.addEventListener('input', (event) => {
                    const input = event.target as HTMLInputElement;
                    input.dataset.locked = '1';
                    schema.sections[sectionIndex].fields[fieldIndex].name = slugify(input.value) || input.value;
                });

                rowEl.querySelector('[data-field-type]')?.addEventListener('change', (event) => {
                    const type = (event.target as HTMLSelectElement).value;
                    schema.sections[sectionIndex].fields[fieldIndex].type = type;
                    if (type !== 'select') {
                        delete schema.sections[sectionIndex].fields[fieldIndex].options;
                    }
                    if (type === 'number') {
                        schema.sections[sectionIndex].fields[fieldIndex].step = '0.01';
                    }
                    paint();
                });

                rowEl.querySelector('[data-field-required]')?.addEventListener('change', (event) => {
                    schema.sections[sectionIndex].fields[fieldIndex].required = (event.target as HTMLInputElement).checked;
                });

                rowEl.querySelector('[data-field-options]')?.addEventListener('input', (event) => {
                    const options = (event.target as HTMLInputElement).value
                        .split(',')
                        .map((opt) => opt.trim())
                        .filter(Boolean);
                    schema.sections[sectionIndex].fields[fieldIndex].options = options;
                });

                rowEl.querySelector('[data-remove-field]')?.addEventListener('click', () => {
                    schema.sections[sectionIndex].fields.splice(fieldIndex, 1);
                    paint();
                });

                rowEl.querySelector('[data-move-up]')?.addEventListener('click', () => {
                    if (fieldIndex <= 0) return;
                    const fields = schema.sections[sectionIndex].fields;
                    [fields[fieldIndex - 1], fields[fieldIndex]] = [fields[fieldIndex], fields[fieldIndex - 1]];
                    paint();
                });

                rowEl.querySelector('[data-move-down]')?.addEventListener('click', () => {
                    const fields = schema.sections[sectionIndex].fields;
                    if (fieldIndex >= fields.length - 1) return;
                    [fields[fieldIndex + 1], fields[fieldIndex]] = [fields[fieldIndex], fields[fieldIndex + 1]];
                    paint();
                });
            });
        });
    };

    const updateSummary = (scope: HTMLElement): void => {
        const el = scope.querySelector('[data-schema-summary]');
        if (el) {
            el.textContent = `${schema.sections.length} section(s) · ${countFields(schema)} field(s)`;
        }
    };

    paint();

    return {
        getSchema: () => normalizeSchema(schema),
        setSchema: (raw: unknown) => {
            schema = normalizeSchema(raw);
            paint();
        },
        validate: () => {
            if (!schema.sections.length) {
                return 'Add at least one section.';
            }
            for (const section of schema.sections) {
                if (!section.title.trim()) {
                    return 'Every section needs a title.';
                }
                for (const field of section.fields) {
                    if (!field.name.trim() || !field.label.trim()) {
                        return 'Every field needs a name and label.';
                    }
                    if (!/^[a-z][a-z0-9_]*$/.test(field.name)) {
                        return `Invalid field key “${field.name}”. Use lowercase letters, numbers, underscores.`;
                    }
                    if (field.type === 'select' && (!field.options || field.options.length === 0)) {
                        return `Dropdown “${field.label}” needs at least one option.`;
                    }
                }
            }
            const names = schema.sections.flatMap((s) => s.fields.map((f) => f.name));
            if (new Set(names).size !== names.length) {
                return 'Field keys must be unique across the whole form.';
            }
            return null;
        },
    };
}

function renderSection(section: SchemaSection, sectionIndex: number, sectionTotal: number): string {
    const fields = section.fields
        .map((field, fieldIndex) => renderFieldRow(field, sectionIndex, fieldIndex, section.fields.length))
        .join('');
    const canRemove = sectionTotal > 1;

    return `<div class="border rounded p-3 bg-light-subtle" data-section data-section-index="${sectionIndex}">
        <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
            <div class="flex-grow-1">
                <label class="form-label mb-1">Section title</label>
                <input type="text" class="form-control" data-section-title value="${escapeHtml(section.title)}" maxlength="120">
            </div>
            <div class="btn-group" role="group" aria-label="Section order">
                <button type="button" class="btn btn-soft-secondary" data-section-up ${sectionIndex === 0 ? 'disabled' : ''} aria-label="Move section up" title="Move section up">
                    <i class="ri-arrow-up-s-line"></i>
                </button>
                <button type="button" class="btn btn-soft-secondary" data-section-down ${sectionIndex >= sectionTotal - 1 ? 'disabled' : ''} aria-label="Move section down" title="Move section down">
                    <i class="ri-arrow-down-s-line"></i>
                </button>
                <button type="button" class="btn btn-soft-danger" data-remove-section ${canRemove ? '' : 'disabled'} aria-label="Remove section" title="${canRemove ? 'Remove section' : 'At least one section required'}">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-3">
                <thead>
                    <tr>
                        <th style="min-width:8rem">Key</th>
                        <th style="min-width:10rem">Label</th>
                        <th style="min-width:7rem">Type</th>
                        <th>Required</th>
                        <th style="min-width:10rem">Options</th>
                        <th class="text-end" style="width:7rem">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${fields || `<tr><td colspan="6" class="text-muted text-center py-3">No fields yet — add one below.</td></tr>`}
                </tbody>
            </table>
        </div>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">New field label</label>
                <input type="text" class="form-control form-control-sm" data-new-label placeholder="e.g. Floor area (sqm)">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Type</label>
                <select class="form-select form-select-sm" data-new-type>
                    ${FIELD_TYPES.map((t) => `<option value="${t.value}">${t.label}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Options (dropdown)</label>
                <input type="text" class="form-control form-control-sm" data-new-options placeholder="a, b, c">
            </div>
            <div class="col-md-1">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" data-new-required id="new-req-${sectionIndex}">
                    <label class="form-check-label" for="new-req-${sectionIndex}">Req.</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-primary w-100" data-add-field>
                    <i class="ri-add-line align-bottom"></i> Add field
                </button>
            </div>
        </div>
    </div>`;
}

function renderFieldRow(field: SchemaField, sectionIndex: number, fieldIndex: number, total: number): string {
    const typeOptions = FIELD_TYPES.map(
        (t) => `<option value="${t.value}" ${field.type === t.value ? 'selected' : ''}>${t.label}</option>`,
    ).join('');
    const optionsValue = (field.options || []).join(', ');
    const optionsDisabled = field.type === 'select' ? '' : 'disabled';

    return `<tr data-field-row data-field-index="${fieldIndex}">
        <td><input type="text" class="form-control form-control-sm font-monospace" data-field-name value="${escapeHtml(field.name)}" maxlength="60"></td>
        <td><input type="text" class="form-control form-control-sm" data-field-label value="${escapeHtml(field.label)}" maxlength="255"></td>
        <td><select class="form-select form-select-sm" data-field-type>${typeOptions}</select></td>
        <td class="text-center"><input class="form-check-input" type="checkbox" data-field-required ${field.required ? 'checked' : ''}></td>
        <td><input type="text" class="form-control form-control-sm" data-field-options value="${escapeHtml(optionsValue)}" placeholder="opt1, opt2" ${optionsDisabled}></td>
        <td class="text-end text-nowrap">
            <button type="button" class="btn btn-sm btn-soft-secondary" data-move-up ${fieldIndex === 0 ? 'disabled' : ''} aria-label="Move up"><i class="ri-arrow-up-s-line"></i></button>
            <button type="button" class="btn btn-sm btn-soft-secondary" data-move-down ${fieldIndex >= total - 1 ? 'disabled' : ''} aria-label="Move down"><i class="ri-arrow-down-s-line"></i></button>
            <button type="button" class="btn btn-sm btn-soft-danger" data-remove-field aria-label="Remove field"><i class="ri-close-line"></i></button>
        </td>
    </tr>`;
}

export function parseAttachments(raw: string): string[] {
    return raw
        .split(/[\n,]+/)
        .map((item) => slugify(item))
        .filter(Boolean);
}

export function formatAttachments(list: unknown): string {
    if (!Array.isArray(list)) {
        return '';
    }
    return list.map((item) => String(item)).filter(Boolean).join('\n');
}
