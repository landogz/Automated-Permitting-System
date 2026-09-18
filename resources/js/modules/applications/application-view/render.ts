import { escapeHtml } from '../../../utils/bootstrap-modal';
import { statusBadge } from '../../../utils/status-badge';
import {
    buildTimelineEvents,
    emailDisplayHtml,
    formatAreaSqm,
    formatFieldValue,
    formatPhpCurrency,
    timelineStepperHtml,
} from '../../ops/application-detail/formatters';
import {
    googleDirectionsUrl,
    openInMapsUrl,
    parseCoords,
    siteMapThumbnailHtml,
} from '../../location-map/site-map-viewer';
import { normalizeSections, type FormSchema } from '../dynamic-fields';
import { documentsListHtml, type ApplicationDocument } from '../attachments';
import { displayFileName } from '../../ops/document-viewer';

export type ApplicantViewNotice = {
    uuid: string;
    notice_no: string;
    type: string;
    status: string;
    title: string;
    body?: string | null;
    issued_at?: string | null;
    due_at?: string | null;
    inspection?: {
        inspection_no?: string;
        result?: string | null;
        notes?: string | null;
    } | null;
    appeals?: Array<{
        uuid: string;
        status: string;
        grounds?: string | null;
        resolution_notes?: string | null;
        filed_at?: string | null;
        resolved_at?: string | null;
    }>;
};

export type ApplicantViewApp = {
    uuid: string;
    application_no: string;
    project_title?: string | null;
    project_location?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    status: string;
    classification?: string | null;
    classified_at?: string | null;
    submitted_at?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    form?: {
        uuid?: string;
        code?: string;
        title?: string;
        schema?: FormSchema;
        required_attachments?: string[];
    } | null;
    payload?: Record<string, unknown> | null;
    documents?: ApplicationDocument[];
    evaluations?: Array<{ decided_at?: string | null; status?: string | null; result?: string | null }>;
    inspections?: Array<{
        scheduled_at?: string | null;
        completed_at?: string | null;
        status?: string | null;
        result?: string | null;
    }>;
    orders_of_payment?: Array<{
        issued_at?: string | null;
        paid_at?: string | null;
        status?: string | null;
    }>;
    compliance_notices?: ApplicantViewNotice[];
};

function formatDate(value?: string | null): string {
    if (!value) return '—';
    try {
        return new Date(value).toLocaleString();
    } catch {
        return escapeHtml(value);
    }
}

function humanize(value: string): string {
    return value.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function noticeTypeLabel(type: string): string {
    if (type === 'g03_compliance') return 'G-03 Compliance';
    if (type === 'g04_disapproval') return 'G-04 Disapproval';
    return humanize(type);
}

const VIEW_TABS = [
    { id: 'overview', label: 'Overview & Location', icon: 'ri-map-pin-line' },
    { id: 'details', label: 'Form details', icon: 'ri-file-list-3-line' },
    { id: 'documents', label: 'Documents', icon: 'ri-folder-2-line' },
    { id: 'compliance', label: 'Compliance', icon: 'ri-alarm-warning-line' },
] as const;

export type ApplicantViewTabId = (typeof VIEW_TABS)[number]['id'];

export function applicantViewTabNavHtml(active: ApplicantViewTabId = 'overview', noticeCount = 0): string {
    return `<ul class="nav nav-tabs nav-tabs-custom nav-success mb-0 flex-wrap" role="tablist">
        ${VIEW_TABS.map((tab) => {
            const badge =
                tab.id === 'compliance' && noticeCount > 0
                    ? `<span class="badge bg-danger ms-1">${noticeCount}</span>`
                    : '';
            return `<li class="nav-item" role="presentation">
                <button type="button"
                    class="nav-link text-nowrap${active === tab.id ? ' active' : ''}"
                    data-app-view-tab="${tab.id}"
                    role="tab"
                    aria-selected="${active === tab.id ? 'true' : 'false'}">
                    <i class="${tab.icon} align-bottom me-1"></i>${escapeHtml(tab.label)}${badge}
                </button>
            </li>`;
        }).join('')}
    </ul>`;
}

export function switchApplicantViewTab(tabId: ApplicantViewTabId): void {
    document.querySelectorAll<HTMLButtonElement>('[data-app-view-tab]').forEach((btn) => {
        const on = btn.dataset.appViewTab === tabId;
        btn.classList.toggle('active', on);
        btn.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    document.querySelectorAll<HTMLElement>('[data-app-view-panel]').forEach((panel) => {
        const on = panel.dataset.appViewPanel === tabId;
        panel.classList.toggle('show', on);
        panel.classList.toggle('active', on);
        panel.classList.toggle('d-none', !on);
    });
}

function overviewHtml(app: ApplicantViewApp): string {
    const payload = (app.payload || {}) as Record<string, unknown>;
    const owner = String(payload.owner_name || '—');
    const ownerEmail = String(payload.owner_email || '');
    const ownerContact = String(payload.owner_contact || '—');
    const barangay = String(payload.barangay || '—');
    const lot = String(payload.lot_number || '—');
    const block = String(payload.block_number || '—');
    const street = String(payload.street_address || app.project_location || '—');
    const coords = parseCoords({ latitude: app.latitude, longitude: app.longitude });
    const coordText = coords
        ? `${coords.lat.toFixed(6)}, ${coords.lng.toFixed(6)}`
        : 'No coordinates pinned';

    const directions =
        coords != null
            ? `<div class="d-flex flex-wrap gap-1 mt-2">
                <a class="btn btn-sm btn-primary" href="${escapeHtml(googleDirectionsUrl(coords.lat, coords.lng))}" target="_blank" rel="noopener noreferrer">
                    <i class="ri-guide-line align-bottom me-1"></i>Get Directions
                </a>
                <a class="btn btn-sm btn-soft-secondary" href="${escapeHtml(openInMapsUrl(coords.lat, coords.lng, app.project_title))}" target="_blank" rel="noopener noreferrer">
                    Open in Maps
                </a>
               </div>`
            : '';

    return `
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Application No.</p>
                <p class="fw-semibold mb-0">${escapeHtml(app.application_no || '—')}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Status</p>
                <div>${statusBadge(app.status)}</div>
            </div>
            <div class="col-md-4 col-sm-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Classification</p>
                <p class="mb-0">${escapeHtml(app.classification ? humanize(app.classification) : '—')}</p>
            </div>
            <div class="col-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Project</p>
                <p class="fw-medium mb-0">${escapeHtml(app.project_title || '—')}</p>
            </div>
        </div>

        <div class="row g-3 align-items-stretch mb-3">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Site location</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Address</p>
                            <p class="fw-medium mb-0">${escapeHtml(street)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Barangay</p>
                            <p class="mb-0">${escapeHtml(barangay)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Lot</p>
                            <p class="mb-0">${escapeHtml(lot)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Block</p>
                            <p class="mb-0">${escapeHtml(block)}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Coordinates</p>
                            <p class="font-monospace small mb-0">${escapeHtml(coordText)}</p>
                        </div>
                    </div>
                    ${directions}
                </div>
            </div>
            <div class="col-lg-5">
                ${siteMapThumbnailHtml({
                    latitude: app.latitude,
                    longitude: app.longitude,
                    address: app.project_location,
                    label: app.project_title || app.application_no || 'Project site',
                })}
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Owner / applicant</p>
                    <p class="fw-semibold mb-1">${escapeHtml(owner)}</p>
                    <p class="small mb-1">${emailDisplayHtml(ownerEmail || null)}</p>
                    <p class="small text-muted mb-0">${escapeHtml(ownerContact)}</p>
                </div>
            </div>
            <div class="col-md-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Timeline</p>
                    ${timelineStepperHtml(buildTimelineEvents(app))}
                </div>
            </div>
        </div>
    `;
}

function detailsHtml(app: ApplicantViewApp, schema?: FormSchema | null): string {
    const payload = (app.payload || {}) as Record<string, unknown>;
    const cost = payload.estimated_cost;
    const lotArea = payload.lot_area;
    const floorArea = payload.floor_area;

    const snapshot = `
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Estimated project cost</p>
                    <p class="font-monospace fw-bold fs-5 mb-0">${cost != null && cost !== '' ? formatPhpCurrency(cost) : '—'}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Lot area</p>
                    <p class="font-monospace fw-semibold mb-0">${lotArea != null && lotArea !== '' ? formatAreaSqm(lotArea) : '—'}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Total floor area</p>
                    <p class="font-monospace fw-semibold mb-0">${floorArea != null && floorArea !== '' ? formatAreaSqm(floorArea) : '—'}</p>
                </div>
            </div>
        </div>
    `;

    const sections = normalizeSections(schema);
    const sectionHtml = sections
        .map((section) => {
            const fields = section.fields
                .map((field) => {
                    const raw = payload[field.name];
                    if (raw === null || raw === undefined || raw === '') return '';
                    return `<div class="col-sm-6 col-lg-4">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">${escapeHtml(field.label)}</p>
                        <p class="mb-0 text-break">${formatFieldValue(field, raw)}</p>
                    </div>`;
                })
                .filter(Boolean)
                .join('');
            if (!fields) return '';
            return `<div class="mb-3">
                <h6 class="fs-13 text-uppercase text-muted mb-2">${escapeHtml(section.title)}</h6>
                <div class="row g-3">${fields}</div>
            </div>`;
        })
        .filter(Boolean)
        .join('');

    return `${snapshot}
        <div class="border rounded p-3">
            <p class="text-muted small mb-3">${escapeHtml(app.form?.code || '—')} · ${escapeHtml(app.form?.title || '')}</p>
            ${sectionHtml || '<p class="text-muted mb-0">No form details provided.</p>'}
        </div>`;
}

function documentsHtml(app: ApplicantViewApp): string {
    const docs = app.documents || [];
    const required = app.form?.required_attachments || [];
    if (!docs.length && !required.length) {
        return `<p class="text-muted mb-0">No documents uploaded yet.</p>`;
    }

    const uploaded = new Map(docs.map((doc) => [doc.label, doc]));
    const labels = [...required, ...docs.map((d) => d.label).filter((l) => !required.includes(l))];

    const rows = labels
        .map((label) => {
            const doc = uploaded.get(label);
            const mandatory = required.includes(label);
            let status: string;
            if (doc) {
                status = '<span class="badge bg-success-subtle text-success border border-success-subtle">Uploaded</span>';
            } else if (mandatory) {
                status =
                    '<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">Mandatory deficient</span>';
            } else {
                status = '<span class="badge bg-secondary-subtle text-secondary">Optional</span>';
            }

            const preview = doc
                ? `<button type="button" class="btn btn-sm btn-soft-primary"
                        data-preview-doc="${escapeHtml(doc.uuid)}"
                        data-doc-label="${escapeHtml(doc.label)}"
                        data-doc-name="${escapeHtml(doc.original_name)}"
                        data-doc-mime="${escapeHtml(doc.mime_type || '')}"
                        data-doc-pdf="${doc.is_pdf || (doc.mime_type || '').includes('pdf') || doc.original_name.toLowerCase().endsWith('.pdf') ? '1' : '0'}"
                        data-doc-image="${doc.is_image || (doc.mime_type || '').startsWith('image/') ? '1' : '0'}">
                        <i class="ri-eye-line"></i> View
                   </button>`
                : '—';

            return `<tr>
                <td class="fw-medium">${escapeHtml(humanize(label))}</td>
                <td class="small text-break">${doc ? escapeHtml(displayFileName(doc.original_name)) : '—'}</td>
                <td>${status}</td>
                <td class="text-end">${preview}</td>
            </tr>`;
        })
        .join('');

    return `<div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Attachment</th><th>File</th><th>Status</th><th class="text-end">Preview</th></tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>
    <div class="mt-3 d-none" id="applicant-view-docs-fallback">${documentsListHtml(docs, app.uuid)}</div>`;
}

function complianceHtml(notices: ApplicantViewNotice[]): string {
    if (!notices.length) {
        return `<div class="text-center border border-2 border-dashed rounded-3 p-4 bg-light-subtle">
            <i class="ri-shield-check-line display-6 text-success d-block mb-2"></i>
            <p class="fw-medium mb-1">No compliance notices</p>
            <p class="text-muted small mb-0">If OCBO issues a G-03/G-04 notice, it will appear here with findings and an option to appeal.</p>
        </div>`;
    }

    return notices
        .map((notice) => {
            const canAppeal = notice.status === 'issued';
            const appeals = notice.appeals || [];
            const appealBlock =
                appeals.length > 0
                    ? `<div class="mt-2 pt-2 border-top">
                        <p class="text-muted fs-11 text-uppercase mb-1">Your appeals</p>
                        ${appeals
                            .map(
                                (appeal) => `<div class="small mb-2">
                                    <span class="badge bg-secondary-subtle text-secondary">${escapeHtml(humanize(appeal.status))}</span>
                                    <div class="mt-1" style="white-space:pre-wrap">${escapeHtml(appeal.grounds || '')}</div>
                                    ${
                                        appeal.resolution_notes
                                            ? `<div class="text-muted mt-1">Resolution: ${escapeHtml(appeal.resolution_notes)}</div>`
                                            : ''
                                    }
                                </div>`,
                            )
                            .join('')}
                       </div>`
                    : '';

            return `<div class="border rounded p-3 mb-2 bg-light-subtle">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <span class="fw-semibold">${escapeHtml(notice.notice_no)}</span>
                        <span class="badge bg-warning-subtle text-warning ms-1">${escapeHtml(noticeTypeLabel(notice.type))}</span>
                        <span class="badge bg-info-subtle text-info ms-1">${escapeHtml(humanize(notice.status))}</span>
                    </div>
                    <div class="text-muted small">Due ${escapeHtml(formatDate(notice.due_at))}</div>
                </div>
                <p class="fw-medium mb-1">${escapeHtml(notice.title)}</p>
                <div class="fs-13 mb-2" style="white-space:pre-wrap">${escapeHtml(notice.body || '—')}</div>
                ${
                    notice.inspection?.notes
                        ? `<p class="text-muted fs-11 text-uppercase mb-1">Inspection findings</p>
                           <div class="fs-13 mb-2" style="white-space:pre-wrap">${escapeHtml(notice.inspection.notes)}</div>`
                        : ''
                }
                ${appealBlock}
                ${
                    canAppeal
                        ? `<button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                data-appeal-notice="${escapeHtml(notice.uuid)}"
                                data-appeal-no="${escapeHtml(notice.notice_no)}">
                                File an appeal
                           </button>
                           <p class="text-muted small mb-0 mt-1">Disagree with the findings? Explain your grounds and OCBO will review.</p>`
                        : ''
                }
            </div>`;
        })
        .join('');
}

export function renderApplicantViewHtml(app: ApplicantViewApp, schema?: FormSchema | null): string {
    const notices = app.compliance_notices || [];
    return `
        <div class="tab-content">
            <div class="tab-pane fade show active" data-app-view-panel="overview" role="tabpanel">
                ${overviewHtml(app)}
            </div>
            <div class="tab-pane fade d-none" data-app-view-panel="details" role="tabpanel">
                ${detailsHtml(app, schema)}
            </div>
            <div class="tab-pane fade d-none" data-app-view-panel="documents" role="tabpanel">
                ${documentsHtml(app)}
            </div>
            <div class="tab-pane fade d-none" data-app-view-panel="compliance" role="tabpanel">
                ${complianceHtml(notices)}
            </div>
        </div>
    `;
}

export function applicantViewHeaderMetaHtml(app: ApplicantViewApp): string {
    return `<div class="d-flex flex-wrap align-items-center gap-2">
        ${statusBadge(app.status)}
        <span class="text-muted small">${escapeHtml(app.form?.code || '—')}</span>
        ${
            app.classification
                ? `<span class="badge bg-info-subtle text-info">${escapeHtml(humanize(app.classification))}</span>`
                : ''
        }
    </div>`;
}
