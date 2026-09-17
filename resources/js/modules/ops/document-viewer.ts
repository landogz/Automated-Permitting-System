import { escapeHtml } from '../../utils/bootstrap-modal';
import { toastError } from '../../utils/toast';

type ViewerDoc = {
    uuid: string;
    label?: string;
    original_name: string;
    mime_type?: string | null;
    is_pdf?: boolean;
    is_image?: boolean;
};

let activeObjectUrl: string | null = null;
let keyHandler: ((event: KeyboardEvent) => void) | null = null;

function revokeActiveUrl(): void {
    if (activeObjectUrl) {
        URL.revokeObjectURL(activeObjectUrl);
        activeObjectUrl = null;
    }
}

function humanizeLabel(label?: string): string {
    if (!label) return 'Document';
    return label.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/** Prefer a readable file name; hide long UUID-looking prefixes when possible. */
export function displayFileName(name: string): string {
    const raw = name.trim();
    if (!raw) return 'document';
    const cleaned = raw.replace(/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i, '').trim();
    if (cleaned.length > 3) {
        return cleaned.replace(/^[_-\s]+/, '') || raw;
    }
    return raw;
}

function closeLightbox(): void {
    const root = document.getElementById('apics-doc-lightbox');
    if (!root) return;
    root.classList.remove('is-open');
    root.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('apics-doc-lightbox-open');
    revokeActiveUrl();
    const stage = document.getElementById('apics-doc-lightbox-stage');
    if (stage) {
        stage.innerHTML = '';
    }
    if (keyHandler) {
        document.removeEventListener('keydown', keyHandler);
        keyHandler = null;
    }
}

function ensureLightbox(): HTMLElement {
    let root = document.getElementById('apics-doc-lightbox');
    if (root) {
        return root;
    }

    root = document.createElement('div');
    root.id = 'apics-doc-lightbox';
    root.className = 'apics-doc-lightbox';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('aria-hidden', 'true');
    root.setAttribute('aria-labelledby', 'apics-doc-lightbox-title');
    root.innerHTML = `
        <div class="apics-doc-lightbox__chrome">
            <div class="apics-doc-lightbox__bar">
                <button type="button" class="apics-doc-lightbox__icon-btn" id="apics-doc-lightbox-back" aria-label="Back to application details">
                    <i class="ri-arrow-left-line" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline">Back</span>
                </button>
                <div class="apics-doc-lightbox__titles min-w-0">
                    <p class="apics-doc-lightbox__title text-truncate mb-0" id="apics-doc-lightbox-title">Document</p>
                    <p class="apics-doc-lightbox__subtitle text-truncate mb-0" id="apics-doc-lightbox-subtitle"></p>
                </div>
                <div class="apics-doc-lightbox__actions">
                    <a class="apics-doc-lightbox__icon-btn d-none" id="apics-doc-lightbox-download" href="#" download title="Download">
                        <i class="ri-download-2-line" aria-hidden="true"></i>
                        <span class="d-none d-md-inline">Download</span>
                    </a>
                    <button type="button" class="apics-doc-lightbox__icon-btn" id="apics-doc-lightbox-close" aria-label="Close preview">
                        <i class="ri-close-line" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="apics-doc-lightbox__stage" id="apics-doc-lightbox-stage"></div>
        </div>`;
    document.body.appendChild(root);

    root.querySelector('#apics-doc-lightbox-back')?.addEventListener('click', () => closeLightbox());
    root.querySelector('#apics-doc-lightbox-close')?.addEventListener('click', () => closeLightbox());

    return root;
}

/**
 * Full-screen document lightbox (not a stacked Bootstrap modal).
 */
export async function openDocumentViewer(
    applicationUuid: string,
    doc: ViewerDoc,
): Promise<void> {
    const root = ensureLightbox();
    const stage = document.getElementById('apics-doc-lightbox-stage');
    const titleEl = document.getElementById('apics-doc-lightbox-title');
    const subtitleEl = document.getElementById('apics-doc-lightbox-subtitle');
    const downloadBtn = document.getElementById('apics-doc-lightbox-download') as HTMLAnchorElement | null;
    if (!stage) {
        return;
    }

    revokeActiveUrl();
    if (titleEl) titleEl.textContent = humanizeLabel(doc.label);
    if (subtitleEl) subtitleEl.textContent = displayFileName(doc.original_name);
    if (downloadBtn) {
        downloadBtn.classList.add('d-none');
        downloadBtn.removeAttribute('href');
    }

    stage.innerHTML = `<div class="apics-doc-lightbox__loading">
        <div class="spinner-border text-light" role="status" aria-hidden="true"></div>
        <p class="mb-0 mt-3 text-white-50">Loading document…</p>
    </div>`;

    root.classList.add('is-open');
    root.setAttribute('aria-hidden', 'false');
    document.body.classList.add('apics-doc-lightbox-open');
    document.getElementById('apics-doc-lightbox-close')?.focus();

    keyHandler = (event: KeyboardEvent) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeLightbox();
        }
    };
    document.addEventListener('keydown', keyHandler);

    const fileUrl = `/api/v1/applications/${applicationUuid}/documents/${doc.uuid}/file`;

    try {
        const response = await window.axios.get(fileUrl, { responseType: 'blob' });
        const blob: Blob = response.data;
        const mime = doc.mime_type || blob.type || 'application/octet-stream';
        const typedBlob = blob.type ? blob : new Blob([blob], { type: mime });
        activeObjectUrl = URL.createObjectURL(typedBlob);

        if (downloadBtn) {
            downloadBtn.href = activeObjectUrl;
            downloadBtn.download = doc.original_name || 'document';
            downloadBtn.classList.remove('d-none');
        }

        const isPdf =
            Boolean(doc.is_pdf) || mime.includes('pdf') || doc.original_name.toLowerCase().endsWith('.pdf');
        const isImage = Boolean(doc.is_image) || mime.startsWith('image/');

        if (isPdf) {
            // Hide browser chrome extras where supported; keep page nav via #view=FitH
            stage.innerHTML = `
                <iframe
                    title="${escapeHtml(displayFileName(doc.original_name))}"
                    src="${activeObjectUrl}#toolbar=0&navpanes=0&scrollbar=1&view=FitH"
                    class="apics-doc-lightbox__frame"
                ></iframe>`;
            return;
        }

        if (isImage) {
            stage.innerHTML = `
                <div class="apics-doc-lightbox__image-wrap">
                    <img src="${activeObjectUrl}" alt="${escapeHtml(displayFileName(doc.original_name))}" class="apics-doc-lightbox__image">
                </div>`;
            return;
        }

        stage.innerHTML = `
            <div class="apics-doc-lightbox__fallback">
                <i class="ri-file-unknow-line" aria-hidden="true"></i>
                <p class="fw-medium mb-1">${escapeHtml(displayFileName(doc.original_name))}</p>
                <p class="text-white-50 fs-13 mb-3">Preview is available for PDF and images. Download to open this file.</p>
                <a class="btn btn-light btn-sm" href="${activeObjectUrl}" download="${escapeHtml(doc.original_name)}">Download file</a>
            </div>`;
    } catch (error: any) {
        stage.innerHTML = `<div class="apics-doc-lightbox__fallback">
            <p class="text-danger mb-3">${escapeHtml(error?.response?.data?.message || 'Unable to load document')}</p>
            <button type="button" class="btn btn-light btn-sm" id="apics-doc-lightbox-retry-close">Close</button>
        </div>`;
        document.getElementById('apics-doc-lightbox-retry-close')?.addEventListener('click', () => closeLightbox());
        toastError(error?.response?.data?.message || 'Unable to load document preview');
    }
}

export function bindDocumentViewerClicks(scope: ParentNode, applicationUuid: string): void {
    scope.querySelectorAll<HTMLElement>('[data-preview-doc]').forEach((btn) => {
        if (btn.dataset.opsInlinePreview === '1') {
            return;
        }
        btn.addEventListener('click', () => {
            const uuid = btn.dataset.previewDoc || '';
            if (!uuid || !applicationUuid) return;
            void openDocumentViewer(applicationUuid, {
                uuid,
                label: btn.dataset.docLabel,
                original_name: btn.dataset.docName || 'document',
                mime_type: btn.dataset.docMime || null,
                is_pdf: btn.dataset.docPdf === '1',
                is_image: btn.dataset.docImage === '1',
            });
        });
    });
}
