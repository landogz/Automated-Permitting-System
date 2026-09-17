import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { badgeYesNo, createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import {
    countFields,
    emptySchema,
    formatAttachments,
    mountSchemaEditor,
    normalizeSchema,
    parseAttachments,
    type SchemaTemplate,
} from '../form-builder/schema-editor';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';

type FormRow = {
    uuid: string;
    code: string;
    title: string;
    revision: string;
    effective_date?: string | null;
    is_active: boolean;
    schema?: unknown;
    required_attachments?: string[] | null;
};

export function initFormsPage(): void {
    const tableEl = document.getElementById('forms-table');
    const form = document.getElementById('form-builder') as HTMLFormElement | null;
    const editorRoot = document.getElementById('form-schema-editor');
    if (!tableEl || !form || !editorRoot) {
        return;
    }

    let table: ApicsDataTableApi<FormRow> | null = null;
    let editingUuid: string | null = null;
    let templates: SchemaTemplate[] = [];
    let editor: ReturnType<typeof mountSchemaEditor> | null = null;

    const modalLabel = document.getElementById('modal-form-builder-label');
    const uuidInput = document.getElementById('form-uuid') as HTMLInputElement;
    const codeInput = document.getElementById('form-code') as HTMLInputElement;
    const titleInput = document.getElementById('form-title') as HTMLInputElement;
    const revisionInput = document.getElementById('form-revision') as HTMLInputElement;
    const effectiveInput = document.getElementById('form-effective-date') as HTMLInputElement;
    const activeInput = document.getElementById('form-active') as HTMLInputElement;
    const attachmentsInput = document.getElementById('form-attachments') as HTMLTextAreaElement;

    const ensureEditor = (): ReturnType<typeof mountSchemaEditor> => {
        if (!editor) {
            editor = mountSchemaEditor(editorRoot, emptySchema(), {
                templates,
                confirmReplace: () =>
                    confirmAction(
                        'Replace current fields?',
                        'Loading a template will overwrite the sections and fields in this editor. Unsaved changes will be lost.',
                    ),
                onApplyTemplate: (template) => {
                    if (!editingUuid && !codeInput.value.trim()) {
                        codeInput.value = template.code;
                    }
                    if (!titleInput.value.trim()) {
                        titleInput.value = template.title;
                    }
                    if (template.required_attachments?.length) {
                        attachmentsInput.value = formatAttachments(template.required_attachments);
                    }
                    toastSuccess(`Loaded ${template.code} field template`);
                },
            });
        }
        return editor;
    };

    const resetBuilder = (row?: FormRow): void => {
        editingUuid = row?.uuid || null;
        uuidInput.value = editingUuid || '';
        codeInput.value = row?.code || '';
        titleInput.value = row?.title || '';
        revisionInput.value = row?.revision || '01';
        effectiveInput.value = row?.effective_date || '';
        activeInput.checked = row ? Boolean(row.is_active) : true;
        attachmentsInput.value = formatAttachments(row?.required_attachments || []);
        ensureEditor().setSchema(row?.schema || emptySchema());
        codeInput.disabled = Boolean(row);
        if (modalLabel) {
            modalLabel.textContent = row ? `Edit form · ${row.code}` : 'Add form definition';
        }
    };

    const openCreate = (): void => {
        resetBuilder();
        showModal('modal-form-builder');
    };

    const openEdit = async (row: FormRow): Promise<void> => {
        try {
            const { data } = await window.axios.get(`/api/v1/admin/form-definitions/${row.uuid}`);
            const full = (data.data || row) as FormRow;
            resetBuilder(full);
            showModal('modal-form-builder');
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load form');
        }
    };

    document.getElementById('btn-add-form')?.addEventListener('click', () => openCreate());

    void (async () => {
        try {
            const templatesRes = await window.axios.get('/api/v1/admin/form-definitions/templates');
            templates = (templatesRes.data?.data?.items || []) as SchemaTemplate[];
        } catch {
            templates = [];
        }

        ensureEditor();

        try {
            table = await createApicsDataTable<FormRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'form-definitions',
                rowId: 'uuid',
                filters: [
                    {
                        id: 'active',
                        label: 'Active',
                        options: [
                            { value: '', label: 'All' },
                            { value: '1', label: 'Active' },
                            { value: '0', label: 'Inactive' },
                        ],
                        match: (row, value) => String(Number(row.is_active)) === value,
                    },
                ],
                columns: [
                    {
                        data: 'code',
                        title: 'Code',
                        responsivePriority: 1,
                        render: (data) => `<span class="fw-medium">${escapeHtml(String(data ?? ''))}</span>`,
                    },
                    { data: 'title', title: 'Title', responsivePriority: 1 },
                    { data: 'revision', title: 'Rev', responsivePriority: 3 },
                    {
                        data: 'schema',
                        title: 'Fields',
                        orderable: false,
                        responsivePriority: 2,
                        render: (_data, _type, row) => {
                            const schema = normalizeSchema(row.schema);
                            return `<span class="badge bg-primary-subtle text-primary">${countFields(schema)} fields</span>
                                <span class="text-muted small ms-1">${schema.sections.length} sec</span>`;
                        },
                    },
                    {
                        data: 'is_active',
                        title: 'Active',
                        responsivePriority: 2,
                        render: (data) => badgeYesNo(Boolean(data)),
                    },
                ],
                actions: [
                    {
                        id: 'edit',
                        label: 'Edit fields',
                        onClick: (row) => void openEdit(row),
                    },
                    {
                        id: 'toggle',
                        label: 'Toggle active',
                        onClick: async (row) => {
                            try {
                                await window.axios.put(`/api/v1/admin/form-definitions/${row.uuid}`, {
                                    is_active: !row.is_active,
                                });
                                toastSuccess(row.is_active ? 'Form deactivated' : 'Form activated');
                                await table?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Update failed');
                            }
                        },
                    },
                    {
                        id: 'delete',
                        label: 'Delete',
                        danger: true,
                        dividerBefore: true,
                        onClick: async (row) => {
                            if (
                                !(await confirmAction(
                                    'Delete form definition?',
                                    `${row.code} will be soft-deleted. Existing applications keep their saved payload.`,
                                ))
                            ) {
                                return;
                            }
                            try {
                                await window.axios.delete(`/api/v1/admin/form-definitions/${row.uuid}`);
                                toastSuccess('Form deleted');
                                await table?.reload(false);
                            } catch (error: any) {
                                toastError(error?.response?.data?.message || 'Delete failed');
                            }
                        },
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/admin/form-definitions', {
                        params: { per_page: 100 },
                    });
                    return data.data?.items || [];
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Sign in as admin to manage forms.');
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const code = codeInput.value.trim();
        const title = titleInput.value.trim();
        const revision = revisionInput.value.trim() || '01';
        const saveBtn = document.getElementById('btn-save-form') as HTMLButtonElement | null;
        const activeEditor = ensureEditor();

        if (!code || !title) {
            toastError('Code and title are required.');
            return;
        }

        const schemaError = activeEditor.validate();
        if (schemaError) {
            toastError(schemaError);
            return;
        }

        const schema = activeEditor.getSchema();
        if (countFields(schema) === 0) {
            toastError('Add at least one field before saving.');
            return;
        }

        if (saveBtn) saveBtn.disabled = true;

        const payload = {
            code,
            title,
            revision,
            effective_date: effectiveInput.value || null,
            schema,
            required_attachments: parseAttachments(attachmentsInput.value),
            is_active: activeInput.checked,
        };

        try {
            if (editingUuid) {
                const { data } = await window.axios.put(`/api/v1/admin/form-definitions/${editingUuid}`, payload);
                toastSuccess(data.message || 'Form updated');
            } else {
                const { data } = await window.axios.post('/api/v1/admin/form-definitions', payload);
                toastSuccess(data.message || 'Form created');
            }
            hideModal('modal-form-builder');
            resetBuilder();
            await table?.reload(false);
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || 'Save failed'));
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });
}
