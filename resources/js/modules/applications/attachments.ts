import { escapeHtml } from '../../utils/bootstrap-modal';
import { confirmAction, toastError, toastSuccess } from '../../utils/toast';
import { displayFileName, openDocumentViewer } from '../ops/document-viewer';

export type ApplicationDocument = {
    uuid: string;
    label: string;
    original_name: string;
    mime_type?: string | null;
    size?: number;
    is_pdf?: boolean;
    is_image?: boolean;
};

function humanizeLabel(label: string): string {
    return label.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function formatBytes(size?: number): string {
    if (!size || size <= 0) {
        return '';
    }
    if (size < 1024) {
        return `${size} B`;
    }
    if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`;
    }
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}

/**
 * Render required attachment upload slots for an editable application.
 */
export function mountAttachmentUploader(
    root: HTMLElement,
    options: {
        applicationUuid: string | null;
        requiredLabels: string[];
        documents: ApplicationDocument[];
        onChange: (documents: ApplicationDocument[]) => void;
    },
): void {
    const labels = options.requiredLabels.length
        ? options.requiredLabels
        : [];
    const docsByLabel = new Map(options.documents.map((doc) => [doc.label, doc]));

    if (!options.applicationUuid) {
        root.innerHTML = `
            <div class="border rounded p-3 bg-light-subtle">
                <h6 class="mb-1">Supporting documents</h6>
                <p class="text-muted fs-13 mb-0">Save the draft once to enable file uploads for this application.</p>
            </div>`;
        return;
    }

    if (!labels.length) {
        root.innerHTML = `
            <div class="border rounded p-3 bg-light-subtle">
                <h6 class="mb-1">Supporting documents</h6>
                <p class="text-muted fs-13 mb-0">This form has no required attachments configured.</p>
            </div>`;
        return;
    }

    root.innerHTML = `
        <div class="border rounded p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Supporting documents</h6>
                    <p class="text-muted fs-12 mb-0">PDF, JPG, PNG, or WEBP · max 10 MB each. Re-upload replaces the file for that slot.</p>
                </div>
            </div>
            <div class="d-flex flex-column gap-3">
                ${labels
                    .map((label) => {
                        const doc = docsByLabel.get(label);
                        const inputId = `att-file-${escapeHtml(label)}`;
                        return `<div class="border rounded p-3" data-attachment-slot="${escapeHtml(label)}">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <label class="form-label mb-0" for="${inputId}">
                                    ${escapeHtml(humanizeLabel(label))}
                                    <span class="text-danger">*</span>
                                </label>
                                ${
                                    doc
                                        ? `<span class="badge bg-success-subtle text-success">Uploaded</span>`
                                        : `<span class="badge bg-warning-subtle text-warning">Required</span>`
                                }
                            </div>
                                    ${
                                        doc
                                            ? `<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="small text-truncate" style="max-width:min(100%,22rem)">
                                            <i class="ri-file-3-line me-1"></i>${escapeHtml(displayFileName(doc.original_name))}
                                            ${doc.size ? `<span class="text-muted"> · ${escapeHtml(formatBytes(doc.size))}</span>` : ''}
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-soft-primary" data-preview-uploaded="${escapeHtml(doc.uuid)}" aria-label="Preview ${escapeHtml(humanizeLabel(label))}">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-soft-danger" data-remove-doc="${escapeHtml(doc.uuid)}" aria-label="Remove ${escapeHtml(humanizeLabel(label))}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>`
                                            : ''
                                    }
                            <input type="file" class="form-control" id="${inputId}" data-upload-label="${escapeHtml(label)}" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                        </div>`;
                    })
                    .join('')}
            </div>
        </div>`;

    root.querySelectorAll<HTMLInputElement>('[data-upload-label]').forEach((input) => {
        input.addEventListener('change', () => {
            void (async () => {
                const file = input.files?.[0];
                const label = input.dataset.uploadLabel || '';
                if (!file || !label || !options.applicationUuid) {
                    return;
                }

                const formData = new FormData();
                formData.append('label', label);
                formData.append('file', file);

                try {
                    input.disabled = true;
                    const { data } = await window.axios.post(
                        `/api/v1/applications/${options.applicationUuid}/documents`,
                        formData,
                        { headers: { 'Content-Type': 'multipart/form-data' } },
                    );
                    const uploaded = data.data as ApplicationDocument;
                    const next = [
                        ...options.documents.filter((doc) => doc.label !== uploaded.label),
                        uploaded,
                    ];
                    toastSuccess(data.message || 'Document uploaded');
                    options.onChange(next);
                } catch (error: any) {
                    const errors = error?.response?.data?.errors;
                    const first = errors ? Object.values(errors).flat()[0] : null;
                    toastError(String(first || error?.response?.data?.message || 'Upload failed'));
                    input.value = '';
                } finally {
                    input.disabled = false;
                }
            })();
        });
    });

    root.querySelectorAll<HTMLButtonElement>('[data-preview-uploaded]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const docUuid = btn.dataset.previewUploaded || '';
            const doc = options.documents.find((item) => item.uuid === docUuid);
            if (!doc || !options.applicationUuid) return;
            void openDocumentViewer(options.applicationUuid, doc);
        });
    });

    root.querySelectorAll<HTMLButtonElement>('[data-remove-doc]').forEach((btn) => {
        btn.addEventListener('click', () => {
            void (async () => {
                const docUuid = btn.dataset.removeDoc || '';
                if (!docUuid || !options.applicationUuid) {
                    return;
                }
                if (!(await confirmAction('Remove this document?', 'You can upload a replacement afterward.'))) {
                    return;
                }
                try {
                    btn.disabled = true;
                    await window.axios.delete(
                        `/api/v1/applications/${options.applicationUuid}/documents/${docUuid}`,
                    );
                    toastSuccess('Document removed');
                    options.onChange(options.documents.filter((doc) => doc.uuid !== docUuid));
                } catch (error: any) {
                    toastError(error?.response?.data?.message || 'Could not remove document');
                    btn.disabled = false;
                }
            })();
        });
    });
}

export function documentsListHtml(documents: ApplicationDocument[], applicationUuid?: string): string {
    if (!documents.length) {
        return `<p class="text-muted small mb-0">No documents uploaded yet.</p>`;
    }
    return `<ul class="list-group list-group-flush">
        ${documents
            .map(
                (doc) => `<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                    <span class="text-break"><span class="fw-medium">${escapeHtml(humanizeLabel(doc.label))}</span>
                    <span class="text-muted"> — ${escapeHtml(displayFileName(doc.original_name))}</span></span>
                    <span class="d-flex align-items-center gap-2 text-nowrap">
                        <span class="text-muted small">${escapeHtml(formatBytes(doc.size))}</span>
                        ${
                            applicationUuid
                                ? `<button type="button" class="btn btn-sm btn-soft-primary"
                                    data-preview-doc="${escapeHtml(doc.uuid)}"
                                    data-doc-label="${escapeHtml(doc.label)}"
                                    data-doc-name="${escapeHtml(doc.original_name)}"
                                    data-doc-mime="${escapeHtml(doc.mime_type || '')}"
                                    data-doc-pdf="${doc.is_pdf || (doc.mime_type || '').includes('pdf') || doc.original_name.toLowerCase().endsWith('.pdf') ? '1' : '0'}"
                                    data-doc-image="${doc.is_image || (doc.mime_type || '').startsWith('image/') ? '1' : '0'}">
                                    <i class="ri-eye-line"></i> View
                                  </button>`
                                : ''
                        }
                    </span>
                </li>`,
            )
            .join('')}
    </ul>`;
}
