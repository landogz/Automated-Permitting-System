import { escapeHtml, showModal } from '../../../utils/bootstrap-modal';
import { toastError } from '../../../utils/toast';
import {
    mountSiteMapViewer,
    siteMapSectionHtml,
    type SiteMapViewerApi,
} from '../../location-map/site-map-viewer';
import { bindDocumentViewerClicks, openDocumentViewer } from '../document-viewer';
import {
    generateRoutingSlip,
    printQms36Summary,
    requestDocumentCorrection,
    startEvaluation,
    startTimer,
} from './actions';
import {
    detailPanelsHtml,
    headerActionsHtml,
    headerMetaHtml,
    tabNavHtml,
} from './tabs';
import type { StaffApplicationDetail } from './types';

function openSignedPrint(url: string): void {
    if (!url) {
        toastError('Print link unavailable. Reload details and try again.');
        return;
    }
    window.open(url, '_blank', 'noopener,noreferrer');
}

let activeSiteMap: SiteMapViewerApi | null = null;
let expandSiteMap: SiteMapViewerApi | null = null;
let currentApp: StaffApplicationDetail | null = null;
let activeTab = 'overview';
let inlinePreviewUrl: string | null = null;

function revokeInlinePreview(): void {
    if (inlinePreviewUrl) {
        URL.revokeObjectURL(inlinePreviewUrl);
        inlinePreviewUrl = null;
    }
}

function paintShell(app: StaffApplicationDetail): void {
    const title = document.getElementById('modal-ops-application-detail-label');
    const meta = document.getElementById('ops-application-detail-header-meta');
    const actions = document.getElementById('ops-application-detail-actions');
    const tabnav = document.getElementById('ops-application-detail-tabnav');
    const body = document.getElementById('ops-application-detail-body');
    if (!body) return;

    if (title) {
        title.textContent = `Application details · ${app.application_no || ''}`.trim();
    }
    if (meta) meta.innerHTML = headerMetaHtml(app);
    if (actions) actions.innerHTML = headerActionsHtml(app);
    if (tabnav) tabnav.innerHTML = tabNavHtml(activeTab);
    body.innerHTML = detailPanelsHtml(app, activeTab);
}

function switchTab(tabId: string): void {
    activeTab = tabId;
    document
        .querySelectorAll<HTMLButtonElement>('#ops-application-detail-tabnav [data-ops-detail-tab]')
        .forEach((btn) => {
            const on = btn.dataset.opsDetailTab === tabId;
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });

    document.querySelectorAll<HTMLElement>('[data-ops-detail-panel]').forEach((panel) => {
        const on = panel.dataset.opsDetailPanel === tabId;
        panel.classList.toggle('show', on);
        panel.classList.toggle('active', on);
    });

    if (tabId === 'overview') {
        window.setTimeout(() => activeSiteMap?.invalidateSize(), 120);
        window.setTimeout(() => activeSiteMap?.invalidateSize(), 350);
    }
}

async function loadInlinePreview(applicationUuid: string, btn: HTMLElement): Promise<void> {
    const pane = document.getElementById('ops-doc-preview-pane');
    const docUuid = btn.dataset.previewDoc;
    if (!pane || !docUuid) return;

    const doc = {
        uuid: docUuid,
        label: btn.dataset.docLabel,
        original_name: btn.dataset.docName || 'document',
        mime_type: btn.dataset.docMime || null,
        is_pdf: btn.dataset.docPdf === '1',
        is_image: btn.dataset.docImage === '1',
    };

    if (!doc.is_pdf && !doc.is_image) {
        await openDocumentViewer(applicationUuid, doc);
        return;
    }

    revokeInlinePreview();
    pane.innerHTML = `<div class="text-center text-muted p-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="small mb-0 mt-2">Loading preview…</p>
    </div>`;

    try {
        const response = await window.axios.get(
            `/api/v1/applications/${applicationUuid}/documents/${doc.uuid}/file`,
            { responseType: 'blob' },
        );
        const blob: Blob = response.data;
        const mime = doc.mime_type || blob.type || 'application/octet-stream';
        const typed = blob.type ? blob : new Blob([blob], { type: mime });
        inlinePreviewUrl = URL.createObjectURL(typed);

        if (doc.is_pdf || mime.includes('pdf')) {
            pane.innerHTML = `<iframe title="${escapeHtml(doc.original_name)}" src="${inlinePreviewUrl}#toolbar=0&navpanes=0&view=FitH" class="apics-doc-vault-preview__frame"></iframe>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${escapeHtml(doc.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`;
        } else {
            pane.innerHTML = `<div class="apics-doc-vault-preview__image-wrap">
                    <img src="${inlinePreviewUrl}" alt="${escapeHtml(doc.original_name)}" class="apics-doc-vault-preview__image">
                </div>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${escapeHtml(doc.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`;
        }

        pane.querySelector('[data-ops-open-lightbox]')?.addEventListener('click', () => {
            void openDocumentViewer(applicationUuid, doc);
        });
    } catch (error: any) {
        pane.innerHTML = `<p class="text-danger small p-3 mb-0">${escapeHtml(error?.response?.data?.message || 'Preview failed')}</p>`;
        toastError(error?.response?.data?.message || 'Preview failed');
    }
}

function openExpandedMap(app: StaffApplicationDetail): void {
    const body = document.getElementById('ops-map-expand-body');
    const title = document.getElementById('modal-ops-map-expand-label');
    if (!body) return;

    expandSiteMap?.destroy();
    expandSiteMap = null;
    if (title) {
        title.textContent = `Project site · ${app.application_no || ''}`.trim();
    }
    body.innerHTML = `<div class="p-3">${siteMapSectionHtml({
        latitude: app.latitude,
        longitude: app.longitude,
        address: app.project_location,
        label: app.project_title || app.application_no || 'Project site',
    })}</div>`;
    showModal('modal-ops-map-expand');
    window.setTimeout(() => {
        expandSiteMap = mountSiteMapViewer(body);
        expandSiteMap?.invalidateSize();
    }, 200);
    window.setTimeout(() => expandSiteMap?.invalidateSize(), 500);
}

function bindDetailInteractions(app: StaffApplicationDetail): void {
    const modal = document.getElementById('modal-ops-application-detail');
    if (!modal) return;

    modal.querySelectorAll<HTMLButtonElement>('[data-ops-detail-tab]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.opsDetailTab;
            if (tab) switchTab(tab);
        });
    });

    modal.querySelectorAll<HTMLElement>('[data-ops-detail-action]').forEach((el) => {
        el.addEventListener('click', () => {
            const action = el.dataset.opsDetailAction;
            if (!action || !currentApp) return;
            void (async () => {
                if (action === 'print') {
                    await printQms36Summary(currentApp);
                    return;
                }
                if (action === 'routing') {
                    const refreshed = await generateRoutingSlip(currentApp);
                    if (refreshed) {
                        currentApp = refreshed;
                        activeTab = 'routing';
                        paintAndBind(refreshed);
                    }
                    return;
                }
                if (action === 'evaluate') {
                    await startEvaluation(currentApp);
                    return;
                }
                if (action === 'timer') {
                    await startTimer(currentApp);
                    return;
                }
                if (action === 'request-doc') {
                    await requestDocumentCorrection(currentApp, el.dataset.docLabel || 'document');
                }
            })();
        });
    });

    modal.querySelectorAll<HTMLElement>('[data-ops-expand-map]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openExpandedMap(app);
        });
    });

    modal.querySelectorAll<HTMLElement>('[data-ops-inline-preview="1"]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            void loadInlinePreview(app.uuid, btn);
        });
    });

    bindDocumentViewerClicks(modal, app.uuid);

    modal.querySelectorAll<HTMLElement>('[data-ops-insp-print]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openSignedPrint(btn.dataset.opsInspPrint || '');
        });
    });
}

function paintAndBind(app: StaffApplicationDetail): void {
    activeSiteMap?.destroy();
    activeSiteMap = null;
    revokeInlinePreview();
    paintShell(app);
    bindDetailInteractions(app);
    const body = document.getElementById('ops-application-detail-body');
    if (body) {
        activeSiteMap = mountSiteMapViewer(body);
    }
    window.setTimeout(() => activeSiteMap?.invalidateSize(), 200);
    window.setTimeout(() => activeSiteMap?.invalidateSize(), 500);
}

/** Compatibility helper for callers that only need HTML. */
export function renderStaffApplicationDetailHtml(app: StaffApplicationDetail): string {
    return detailPanelsHtml(app, 'overview');
}

/**
 * Open the shared Operations application detail modal (tabbed QMS review layout).
 */
export async function openOpsApplicationDetail(
    applicationUuid: string,
    options?: { tab?: string },
): Promise<void> {
    const body = document.getElementById('ops-application-detail-body');
    const title = document.getElementById('modal-ops-application-detail-label');
    const meta = document.getElementById('ops-application-detail-header-meta');
    const actions = document.getElementById('ops-application-detail-actions');
    const tabnav = document.getElementById('ops-application-detail-tabnav');
    if (!body || !applicationUuid) {
        toastError('Application detail viewer is not available on this page.');
        return;
    }

    activeSiteMap?.destroy();
    activeSiteMap = null;
    expandSiteMap?.destroy();
    expandSiteMap = null;
    revokeInlinePreview();
    currentApp = null;
    const preferred = options?.tab;
    activeTab =
        preferred && ['overview', 'technical', 'documents', 'routing', 'inspection'].includes(preferred)
            ? preferred
            : 'overview';

    if (meta) meta.innerHTML = '';
    if (actions) actions.innerHTML = '';
    if (tabnav) tabnav.innerHTML = '';
    body.innerHTML = `<div class="text-center text-muted py-5">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></div>
        Loading application details…
    </div>`;
    if (title) title.textContent = 'Application details';
    showModal('modal-ops-application-detail');

    try {
        const { data } = await window.axios.get(`/api/v1/staff/applications/${applicationUuid}`);
        const app = data.data as StaffApplicationDetail;
        currentApp = app;
        paintAndBind(app);
        if (preferred === 'inspection') {
            switchTab('inspection');
        }
    } catch (error: any) {
        body.innerHTML = `<p class="text-danger mb-0">${escapeHtml(error?.response?.data?.message || 'Unable to load application details')}</p>`;
        toastError(error?.response?.data?.message || 'Unable to load application details');
    }
}

export type { StaffApplicationDetail } from './types';
