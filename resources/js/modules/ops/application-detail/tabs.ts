import { escapeHtml } from '../../../utils/bootstrap-modal';
import {
    classificationBadgeHtml,
    humanizeKey,
    slaCountdownHtml,
    statusBadgeHtml,
} from '../../../utils/ops-ui';
import { hasPermission } from '../../../utils/auth';
import {
    googleDirectionsUrl,
    openInMapsUrl,
    parseCoords,
    siteMapThumbnailHtml,
} from '../../location-map/site-map-viewer';
import {
    normalizeSections,
    type FormFieldDef,
} from '../../applications/dynamic-fields';
import { displayFileName } from '../document-viewer';
import {
    buildTimelineEvents,
    emailDisplayHtml,
    formatAreaSqm,
    formatBytes,
    formatFieldValue,
    formatPhpCurrency,
    timelineStepperHtml,
} from './formatters';
import type { StaffApplicationDetail } from './types';

const PROFESSIONAL_ROLES: Array<{
    role: string;
    nameKey: string;
    prcKey: string;
}> = [
    { role: 'Architect', nameKey: 'architect_name', prcKey: 'architect_prc' },
    { role: 'Civil / Structural Engineer', nameKey: 'engineer_name', prcKey: 'engineer_prc' },
    { role: 'Professional Electrical Engineer', nameKey: 'electrical_engineer_name', prcKey: 'electrical_prc' },
    { role: 'Sanitary Engineer / Master Plumber', nameKey: 'sanitary_engineer_name', prcKey: 'sanitary_prc' },
    { role: 'Mechanical Engineer', nameKey: 'mechanical_engineer_name', prcKey: 'mechanical_prc' },
];

export function headerMetaHtml(app: StaffApplicationDetail): string {
    const sla = slaCountdownHtml(app.submitted_at, app.classification);
    return `
        <div class="d-flex flex-wrap align-items-center gap-2">
            ${statusBadgeHtml(app.status || '', { pulse: true })}
            ${classificationBadgeHtml(app.classification)}
            <span class="text-muted small">${escapeHtml(app.form?.code || '—')}</span>
        </div>
        ${
            sla
                ? `<div class="mt-1 small fw-medium text-warning-emphasis d-flex align-items-center gap-1">
                    <span>RA 11032</span>${sla}
                   </div>`
                : ''
        }
    `;
}

export function headerActionsHtml(app: StaffApplicationDetail): string {
    const canEval = hasPermission('evaluations.manage');
    const status = String(app.status || '').toLowerCase();
    const evaluationOpen = status === 'submitted' || status === 'under_evaluation';
    const canRoute = canEval && evaluationOpen;
    const canStartEval = canEval && evaluationOpen;
    const hasSlip = (app.routing_slips || []).length > 0;
    const classified = Boolean(app.classification);

    return `
        <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-detail-action="print" title="Print QMS-36 summary">
            <i class="ri-printer-line align-bottom me-1"></i><span class="d-none d-md-inline">Print QMS-36</span>
        </button>
        ${
            canRoute
                ? `<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="routing"
                    ${!classified ? 'disabled title="Classify the application first"' : ''}
                    ${hasSlip ? 'title="A routing slip already exists — generate another if needed"' : ''}>
                    <i class="ri-git-branch-line align-bottom me-1"></i><span class="d-none d-md-inline">${hasSlip ? 'Regenerate slip' : 'Generate Routing Slip'}</span>
                   </button>`
                : ''
        }
        ${
            canStartEval
                ? `<button type="button" class="btn btn-sm btn-primary" data-ops-detail-action="evaluate">
                    <i class="ri-play-circle-line align-bottom me-1"></i><span class="d-none d-md-inline">Start Evaluation</span>
                   </button>`
                : ''
        }
    `;
}

export function tabNavHtml(active = 'overview'): string {
    const tabs = [
        { id: 'overview', label: 'Overview & Location', icon: 'ri-map-pin-line' },
        { id: 'technical', label: 'Technical (QMS-36)', icon: 'ri-building-2-line' },
        { id: 'documents', label: 'Document Vault', icon: 'ri-folder-2-line' },
        { id: 'routing', label: 'Routing & Reviews', icon: 'ri-organization-chart' },
        { id: 'inspection', label: 'Inspection Forms', icon: 'ri-clipboard-line' },
    ];

    return `<ul class="nav nav-tabs nav-tabs-custom apics-nav-tabs mb-0 flex-wrap" role="tablist">
        ${tabs
            .map(
                (tab) => `<li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link text-nowrap${active === tab.id ? ' active' : ''}"
                        data-ops-detail-tab="${tab.id}"
                        role="tab"
                        aria-selected="${active === tab.id ? 'true' : 'false'}">
                        <i class="${tab.icon}" aria-hidden="true"></i>${escapeHtml(tab.label)}
                    </button>
                </li>`,
            )
            .join('')}
    </ul>`;
}

function locationBlockHtml(app: StaffApplicationDetail): string {
    const payload = (app.payload || {}) as Record<string, unknown>;
    const barangay = String(payload.barangay || '—');
    const lot = String(payload.lot_number || '—');
    const block = String(payload.block_number || '—');
    const street = String(payload.street_address || app.project_location || '—');
    const coords = parseCoords({
        latitude: app.latitude,
        longitude: app.longitude,
    });
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
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Site location</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Site address</p>
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
    `;
}

function overviewTabHtml(app: StaffApplicationDetail): string {
    const payload = (app.payload || {}) as Record<string, unknown>;
    const owner = String(payload.owner_name || app.applicant?.name || '—');
    const ownerEmail = String(payload.owner_email || app.applicant?.email || '');
    const ownerContact = String(payload.owner_contact || app.applicant?.phone || '—');

    return `
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Application No.</p>
                <p class="fw-semibold mb-0">${escapeHtml(app.application_no || '—')}</p>
            </div>
            <div class="col-md-5 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Project</p>
                <p class="fw-medium mb-0">${escapeHtml(app.project_title || '—')}</p>
            </div>
            <div class="col-md-4 col-sm-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Permit form</p>
                <p class="mb-0">${escapeHtml(app.form?.code || '—')}
                    <span class="text-muted small">${escapeHtml(app.form?.title || '')}</span>
                </p>
            </div>
        </div>

        ${locationBlockHtml(app)}

        <div class="row g-3 mt-1">
            <div class="col-md-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Applicant / owner</p>
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

function professionalsHtml(payload: Record<string, unknown>): string {
    const rows = PROFESSIONAL_ROLES.map((role) => {
        const name = payload[role.nameKey];
        const prc = payload[role.prcKey];
        if ((name == null || name === '') && (prc == null || prc === '')) return '';
        return `<div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                    <span class="text-muted fs-11 text-uppercase">${escapeHtml(role.role)}</span>
                    ${
                        prc
                            ? `<span class="badge border bg-success-subtle text-success border-success-subtle">PRC Active</span>`
                            : `<span class="badge border bg-secondary-subtle text-secondary">No PRC on file</span>`
                    }
                </div>
                <p class="fw-semibold mb-1">${escapeHtml(String(name || '—'))}</p>
                <p class="small text-muted mb-0 font-monospace">PRC ${escapeHtml(String(prc || '—'))}</p>
            </div>
        </div>`;
    }).filter(Boolean);

    if (!rows.length) {
        return `<p class="text-muted mb-0">No design professionals recorded on this filing.</p>`;
    }

    return `<div class="row g-3">${rows.join('')}</div>`;
}

function technicalFieldsHtml(app: StaffApplicationDetail): string {
    const payload = (app.payload || {}) as Record<string, unknown>;
    const sections = normalizeSections(app.form?.schema || null);
    const skip = new Set(PROFESSIONAL_ROLES.flatMap((r) => [r.nameKey, r.prcKey]));

    const sectionHtml = sections
        .filter((section) => !/design professional/i.test(section.title))
        .map((section) => {
            const fields = section.fields
                .filter((field) => !skip.has(field.name))
                .map((field: FormFieldDef) => {
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

    return `
        ${snapshot}
        <div class="border rounded p-3 mb-4">
            ${sectionHtml || '<p class="text-muted mb-0">No form field answers recorded.</p>'}
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Signatories &amp; professionals</h6>
        <div class="border rounded p-3">${professionalsHtml(payload)}</div>
    `;
}

function documentsTabHtml(app: StaffApplicationDetail): string {
    const docs = app.documents || [];
    const required = app.form?.required_attachments || [];
    const uploaded = new Map(docs.map((doc) => [doc.label, doc]));
    const extraLabels = docs.map((d) => d.label).filter((label) => !required.includes(label));
    const labels = [...required, ...extraLabels];

    if (!labels.length) {
        return `<p class="text-muted mb-0">No documents required or uploaded.</p>`;
    }

    const rows = labels
        .map((label) => {
            const doc = uploaded.get(label);
            const mandatory = required.includes(label);
            const canPreview = Boolean(
                doc &&
                    (doc.is_pdf ||
                        doc.is_image ||
                        (doc.mime_type || '').includes('pdf') ||
                        (doc.mime_type || '').startsWith('image/') ||
                        doc.original_name.toLowerCase().endsWith('.pdf')),
            );

            let statusBadge: string;
            if (doc) {
                statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle">Uploaded</span>';
            } else if (mandatory) {
                statusBadge =
                    '<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">Mandatory deficient</span>';
            } else {
                statusBadge =
                    '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Optional</span>';
            }

            const actions = doc
                ? `<button type="button" class="btn btn-sm btn-soft-primary"
                        data-preview-doc="${escapeHtml(doc.uuid)}"
                        data-doc-label="${escapeHtml(doc.label)}"
                        data-doc-name="${escapeHtml(doc.original_name)}"
                        data-doc-mime="${escapeHtml(doc.mime_type || '')}"
                        data-doc-pdf="${doc.is_pdf || (doc.mime_type || '').includes('pdf') || doc.original_name.toLowerCase().endsWith('.pdf') ? '1' : '0'}"
                        data-doc-image="${doc.is_image || (doc.mime_type || '').startsWith('image/') ? '1' : '0'}"
                        data-ops-inline-preview="1"
                        title="${canPreview ? 'Preview in vault' : 'Open / download'}">
                        <i class="ri-eye-line"></i> Preview
                   </button>`
                : `<button type="button" class="btn btn-sm btn-outline-danger"
                        data-ops-detail-action="request-doc"
                        data-doc-label="${escapeHtml(label)}"
                        ${!app.applicant?.uuid ? 'disabled title="Applicant account not linked"' : ''}>
                        <i class="ri-mail-send-line"></i> Request correction
                   </button>`;

            return `<tr>
                <td class="fw-medium">${escapeHtml(humanizeKey(label))}
                    ${mandatory ? '<div class="text-muted small">Mandatory</div>' : '<div class="text-muted small">Optional</div>'}
                </td>
                <td class="small text-break">${
                    doc
                        ? `${escapeHtml(displayFileName(doc.original_name))}${doc.size ? ` <span class="text-muted">(${escapeHtml(formatBytes(doc.size))})</span>` : ''}`
                        : '—'
                }</td>
                <td>${statusBadge}</td>
                <td class="text-end text-nowrap">${actions}</td>
            </tr>`;
        })
        .join('');

    return `
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="table-responsive border rounded">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Attachment</th>
                                <th>File</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="apics-doc-vault-preview border rounded bg-light-subtle" id="ops-doc-preview-pane">
                    <div class="text-center text-muted p-4">
                        <i class="ri-file-pdf-2-line display-6 d-block mb-2"></i>
                        <p class="mb-0 small">Select <strong>Preview</strong> on a PDF or image to view it here without leaving this modal.</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function routingPrintButtonsHtml(
    urls: NonNullable<StaffApplicationDetail['routing_slips']>[number]['print_urls'],
): string {
    if (!urls) return '';
    const docs: Array<{ key: keyof NonNullable<typeof urls>; label: string }> = [
        { key: 'qms-61', label: 'QMS-61' },
        { key: 'qms-62', label: 'QMS-62' },
    ];
    return docs
        .filter((d) => Boolean(urls[d.key]))
        .map(
            (d) => `<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${escapeHtml(String(urls[d.key] || ''))}">
                <i class="ri-printer-line align-bottom me-1"></i>${escapeHtml(d.label)}
            </button>`,
        )
        .join('');
}

function evaluationPrintButtonsHtml(
    urls: NonNullable<StaffApplicationDetail['evaluations']>[number]['print_urls'],
): string {
    if (!urls) return '';
    const docs: Array<{ key: keyof NonNullable<typeof urls>; label: string }> = [
        { key: 'qms-63', label: 'QMS-63' },
        { key: 'qms-64', label: 'QMS-64' },
    ];
    return docs
        .filter((d) => Boolean(urls[d.key]))
        .map(
            (d) => `<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${escapeHtml(String(urls[d.key] || ''))}">
                <i class="ri-printer-line align-bottom me-1"></i>${escapeHtml(d.label)}
            </button>`,
        )
        .join('');
}

function evaluationsBlockHtml(app: StaffApplicationDetail): string {
    const evaluations = app.evaluations || [];
    if (!evaluations.length) {
        return `<div class="border rounded p-3 mt-3 bg-light-subtle">
            <p class="fw-medium mb-1">No evaluation sheets yet</p>
            <p class="text-muted small mb-0">QMS-63/64 sheets appear here after an evaluator saves or decides from the Evaluation Queue.</p>
        </div>`;
    }

    const decided = evaluations
        .filter((ev) => String(ev.status || '') === 'decided')
        .slice()
        .sort((a, b) => String(b.decided_at || b.created_at || '').localeCompare(String(a.decided_at || a.created_at || '')));
    const drafts = evaluations
        .filter((ev) => String(ev.status || '') === 'draft')
        .slice()
        .sort((a, b) => String(b.updated_at || b.created_at || '').localeCompare(String(a.updated_at || a.created_at || '')));
    const latestDraft = drafts[0];
    const visible = [...decided, ...(latestDraft ? [latestDraft] : [])];
    const hiddenDrafts = Math.max(0, drafts.length - (latestDraft ? 1 : 0));

    return `${
        hiddenDrafts > 0
            ? `<p class="text-muted small mb-2">Showing the latest draft (${hiddenDrafts} older draft${hiddenDrafts === 1 ? '' : 's'} hidden — open Evaluate to continue the active sheet).</p>`
            : ''
    }${visible
        .map((ev) => {
            const findings = ev.findings || {};
            const completeness = findings.completeness || [];
            const technical = findings.technical || [];
            return `<div class="border rounded p-3 mt-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">Evaluation sheet
                            <span class="badge bg-primary-subtle text-primary ms-1">${escapeHtml(humanizeKey(String(ev.status || '')))}</span>
                            ${ev.result ? `<span class="badge bg-info-subtle text-info ms-1">${escapeHtml(humanizeKey(String(ev.result)))}</span>` : ''}
                        </p>
                        <p class="text-muted small mb-0">${escapeHtml(ev.evaluator?.name || 'Evaluator')}${ev.decided_at ? ` · decided ${escapeHtml(formatWhen(ev.decided_at))}` : ''}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-1">${evaluationPrintButtonsHtml(ev.print_urls)}</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">QMS-63 completeness (${completeness.length})</p>
                        ${complianceItemsSummaryHtml(completeness)}
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">QMS-64 technical (${technical.length})</p>
                        ${complianceItemsSummaryHtml(technical)}
                    </div>
                </div>
                ${
                    findings.overall_remarks || findings.discipline_remarks || ev.remarks
                        ? `<div class="mt-2 small">
                            ${findings.overall_remarks ? `<div><span class="text-muted">Overall:</span> ${escapeHtml(findings.overall_remarks)}</div>` : ''}
                            ${findings.discipline_remarks ? `<div><span class="text-muted">Discipline:</span> ${escapeHtml(findings.discipline_remarks)}</div>` : ''}
                            ${ev.remarks ? `<div><span class="text-muted">Decision:</span> ${escapeHtml(ev.remarks)}</div>` : ''}
                           </div>`
                        : ''
                }
            </div>`;
        })
        .join('')}`;
}

function routingTabHtml(app: StaffApplicationDetail): string {
    const slips = app.routing_slips || [];
    const status = String(app.status || '').toLowerCase();
    const evaluationOpen = status === 'submitted' || status === 'under_evaluation';
    const canRoute = hasPermission('evaluations.manage') && evaluationOpen;

    if (!slips.length) {
        return `
            <div class="apics-routing-empty text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No routing slips initialized for this filing.</p>
                <p class="text-muted small mb-3">Initialize the standard multi-discipline technical review path based on QMS-61/62 templates (Architectural, Civil/Structural, Electrical, Mechanical, Sanitary, Fire).</p>
                ${
                    canRoute
                        ? `<button type="button" class="btn btn-primary btn-sm" data-ops-detail-action="routing" ${!app.classification ? 'disabled title="Classify first"' : ''}>
                            <i class="ri-git-branch-line align-bottom me-1"></i> Generate Routing Slips
                           </button>`
                        : evaluationOpen
                          ? `<p class="text-muted small mb-0">Ask an evaluator with routing permission to generate the slip.</p>`
                          : `<p class="text-muted small mb-0">Routing actions are closed for ${escapeHtml(humanizeKey(status || 'this'))} filings.</p>`
                }
            </div>
            ${evaluationsBlockHtml(app)}
        `;
    }

    const slipsHtml = slips
        .map((slip) => {
            const steps = (slip.steps || [])
                .map(
                    (step) => `<div class="apics-routing-step d-flex flex-wrap align-items-start gap-2 py-2 border-bottom">
                        <span class="badge bg-secondary-subtle text-secondary">${escapeHtml(String(step.step_order ?? ''))}</span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-medium">${escapeHtml(step.label || 'Step')}</div>
                            <div class="text-muted small">${escapeHtml(step.department?.name || step.department?.code || 'Department')}</div>
                            ${
                                step.started_at || step.completed_at
                                    ? `<div class="text-muted small mt-1">
                                        ${step.started_at ? `Started ${escapeHtml(humanizeKey(String(step.status || '')))}` : ''}
                                       </div>`
                                    : ''
                            }
                        </div>
                        <span class="badge bg-info-subtle text-info">${escapeHtml(humanizeKey(String(step.status || '')))}</span>
                    </div>`,
                )
                .join('');

            return `<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">${escapeHtml(slip.slip_no || 'Routing slip')}
                            <span class="badge bg-primary-subtle text-primary ms-1">${escapeHtml(humanizeKey(String(slip.status || '')))}</span>
                        </p>
                        ${
                            slip.template
                                ? `<p class="text-muted small mb-0">Template ${escapeHtml(slip.template.code || '')}${slip.template.name ? ` — ${escapeHtml(slip.template.name)}` : ''}</p>`
                                : ''
                        }
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        ${routingPrintButtonsHtml(slip.print_urls)}
                        ${
                            canRoute
                                ? `<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="timer">
                                    <i class="ri-timer-line align-bottom me-1"></i> Start timer
                                   </button>`
                                : ''
                        }
                    </div>
                </div>
                <div>${steps || '<p class="text-muted mb-0">No steps</p>'}</div>
            </div>`;
        })
        .join('');

    return `${slipsHtml}${evaluationsBlockHtml(app)}`;
}

function inspectionTypeLabel(type: string | null | undefined): string {
    const key = String(type || '').toLowerCase();
    const map: Record<string, string> = {
        joint: 'Joint',
        joint_structural: 'Joint · Structural',
        joint_architectural: 'Joint · Architectural',
        joint_electrical: 'Joint · Electrical',
        joint_sanitary: 'Joint · Sanitary',
        joint_mechanical: 'Joint · Mechanical',
        joint_fire_safety: 'Joint · Fire Safety',
        electrical: 'Electrical (DPWH 77-006-E)',
        final: 'Final',
    };
    return map[key] || humanizeKey(type || 'Inspection');
}

function formatWhen(iso: string | null | undefined): string {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        });
    } catch {
        return iso;
    }
}

function inspectionPrintButtonsHtml(
    urls: NonNullable<StaffApplicationDetail['inspections']>[number]['print_urls'],
    requiresElectrical: boolean,
): string {
    if (!urls) return '';
    const docs: Array<{ key: keyof NonNullable<typeof urls>; label: string; show: boolean }> = [
        { key: 'qms-38', label: 'QMS-38', show: Boolean(urls['qms-38']) },
        { key: 'qms-39', label: 'QMS-39', show: Boolean(urls['qms-39']) },
        { key: 'o-03', label: 'O-03', show: Boolean(urls['o-03']) },
        { key: 'qms-65', label: 'QMS-65', show: Boolean(urls['qms-65']) },
        {
            key: 'dpwh-77-006-e',
            label: '77-006-E',
            show: Boolean(urls['dpwh-77-006-e']) && requiresElectrical,
        },
    ];
    return docs
        .filter((d) => d.show)
        .map(
            (d) => `<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${escapeHtml(String(urls[d.key] || ''))}">
                <i class="ri-printer-line align-bottom me-1"></i>${escapeHtml(d.label)}
            </button>`,
        )
        .join('');
}

function complianceItemsSummaryHtml(
    items: Array<{ code?: string; label?: string; status?: string; remarks?: string }> | undefined,
): string {
    if (!items?.length) {
        return `<p class="text-muted small mb-0">No QMS-65 checklist items recorded.</p>`;
    }
    const rows = items
        .map((item) => {
            const status = String(item.status || 'na').toLowerCase();
            const badge =
                status === 'ok'
                    ? 'bg-success-subtle text-success'
                    : status === 'fail'
                      ? 'bg-danger-subtle text-danger'
                      : 'bg-secondary-subtle text-secondary';
            return `<tr>
                <td class="small font-monospace">${escapeHtml(item.code || '—')}</td>
                <td class="small">${escapeHtml(item.label || '—')}</td>
                <td><span class="badge ${badge}">${escapeHtml(status.toUpperCase())}</span></td>
                <td class="small text-muted">${escapeHtml(item.remarks || '')}</td>
            </tr>`;
        })
        .join('');
    return `<div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Code</th><th>Item</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

function inspectionTabHtml(app: StaffApplicationDetail): string {
    const inspections = app.inspections || [];
    const canInspect = hasPermission('inspections.manage');

    if (!inspections.length) {
        return `
            <div class="text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No inspections scheduled for this filing.</p>
                <p class="text-muted small mb-3">QMS-38/39 schedule, O-03 notes, QMS-65 compliance sheet, and DPWH 77-006-E appear here after scheduling.</p>
                ${
                    canInspect
                        ? `<a class="btn btn-sm btn-primary" href="/admin/inspections">
                            <i class="ri-calendar-check-line align-bottom me-1"></i> Open Inspections
                           </a>`
                        : ''
                }
            </div>
        `;
    }

    return inspections
        .map((insp) => {
            const sheet = insp.schedule_sheet || {};
            const notes = insp.inspector_notes || {};
            const team = insp.team_inspectors || [];
            const elec = insp.electrical_form || {};
            const requiresElec = Boolean(
                insp.requires_electrical_form ||
                    (elec.result && elec.result !== 'na') ||
                    String(insp.type || '').toLowerCase().includes('electrical'),
            );
            const disciplines = Array.isArray(sheet.disciplines) ? sheet.disciplines.join(', ') : '—';
            const teamHtml = team.length
                ? `<ul class="list-unstyled mb-0 small">${team
                      .map(
                          (m) =>
                              `<li><span class="fw-medium">${escapeHtml(m.name || '—')}</span>
                                <span class="text-muted"> · ${escapeHtml(m.role || 'Member')}${
                                    m.discipline ? ` · ${escapeHtml(m.discipline)}` : ''
                                }</span></li>`,
                      )
                      .join('')}</ul>`
                : `<p class="small text-muted mb-0">${escapeHtml(insp.inspector?.name || 'No team recorded')}</p>`;

            const elecBlock = requiresElec
                ? `<div class="border rounded p-3 mb-0">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">DPWH 77-006-E</p>
                    <div class="row g-2 small">
                        ${[
                            ['Service entrance', elec.service_entrance],
                            ['Grounding', elec.grounding],
                            ['Panel boards', elec.panel_boards],
                            ['Wiring methods', elec.wiring_methods],
                            ['Fixtures / devices', elec.fixtures_devices],
                            ['Load schedule', elec.load_schedule],
                            ['Result', elec.result || elec.status],
                        ]
                            .map(
                                ([label, value]) => `<div class="col-sm-6 col-md-4">
                                    <span class="text-muted">${escapeHtml(String(label))}</span>
                                    <div class="fw-medium">${escapeHtml(String(value || 'na').toUpperCase())}</div>
                                </div>`,
                            )
                            .join('')}
                    </div>
                    ${
                        elec.remarks
                            ? `<p class="small mt-2 mb-0"><span class="text-muted">Remarks:</span> ${escapeHtml(String(elec.remarks))}</p>`
                            : ''
                    }
                   </div>`
                : '';

            return `<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <p class="fw-semibold mb-1 font-monospace">${escapeHtml(insp.inspection_no || 'Inspection')}
                            ${statusBadgeHtml(String(insp.status || ''), { pulse: insp.status === 'scheduled' || insp.status === 'in_progress' })}
                            ${insp.result ? statusBadgeHtml(String(insp.result)) : ''}
                        </p>
                        <p class="text-muted small mb-0">${escapeHtml(inspectionTypeLabel(insp.type))}
                            · Scheduled ${escapeHtml(formatWhen(insp.scheduled_at))}
                            ${insp.completed_at ? ` · Completed ${escapeHtml(formatWhen(insp.completed_at))}` : ''}
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-start">
                        ${inspectionPrintButtonsHtml(insp.print_urls, requiresElec)}
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-38 · Schedule</p>
                            <p class="small mb-1"><span class="text-muted">Purpose:</span> ${escapeHtml(sheet.purpose || '—')}</p>
                            <p class="small mb-1"><span class="text-muted">Meeting point:</span> ${escapeHtml(sheet.meeting_point || insp.location || '—')}</p>
                            <p class="small mb-0"><span class="text-muted">Disciplines:</span> ${escapeHtml(disciplines)}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-39 · Team</p>
                            ${teamHtml}
                        </div>
                    </div>
                </div>

                <div class="border rounded p-3 mb-3">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">O-03 · Inspector notes</p>
                    <div class="row g-2 small mb-2">
                        <div class="col-md-6"><span class="text-muted">Weather / access:</span> ${escapeHtml(notes.weather || '—')}</div>
                        <div class="col-md-6"><span class="text-muted">Site conditions:</span> ${escapeHtml(notes.site_conditions || '—')}</div>
                    </div>
                    <p class="small mb-1"><span class="text-muted">Findings:</span> ${escapeHtml(notes.findings || insp.notes || '—')}</p>
                    ${
                        notes.observed_defects
                            ? `<p class="small mb-1"><span class="text-muted">Defects:</span> ${escapeHtml(notes.observed_defects)}</p>`
                            : ''
                    }
                    ${
                        notes.recommendations
                            ? `<p class="small mb-0"><span class="text-muted">Recommendations:</span> ${escapeHtml(notes.recommendations)}</p>`
                            : ''
                    }
                </div>

                <div class="mb-3">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-65 · Compliance sheet</p>
                    ${complianceItemsSummaryHtml(insp.compliance_sheet?.items)}
                    ${
                        insp.compliance_sheet?.overall_remarks
                            ? `<p class="small mt-2 mb-0"><span class="text-muted">Overall:</span> ${escapeHtml(insp.compliance_sheet.overall_remarks)}</p>`
                            : ''
                    }
                </div>

                ${elecBlock}
            </div>`;
        })
        .join('');
}

export function detailPanelsHtml(app: StaffApplicationDetail, activeTab = 'overview'): string {
    return `
        <div class="tab-content apics-app-detail__panels">
            <div class="tab-pane fade${activeTab === 'overview' ? ' show active' : ''}" data-ops-detail-panel="overview" role="tabpanel">
                ${overviewTabHtml(app)}
            </div>
            <div class="tab-pane fade${activeTab === 'technical' ? ' show active' : ''}" data-ops-detail-panel="technical" role="tabpanel">
                ${technicalFieldsHtml(app)}
            </div>
            <div class="tab-pane fade${activeTab === 'documents' ? ' show active' : ''}" data-ops-detail-panel="documents" role="tabpanel">
                ${documentsTabHtml(app)}
            </div>
            <div class="tab-pane fade${activeTab === 'routing' ? ' show active' : ''}" data-ops-detail-panel="routing" role="tabpanel">
                ${routingTabHtml(app)}
            </div>
            <div class="tab-pane fade${activeTab === 'inspection' ? ' show active' : ''}" data-ops-detail-panel="inspection" role="tabpanel">
                ${inspectionTabHtml(app)}
            </div>
        </div>
    `;
}
