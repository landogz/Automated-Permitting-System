import { getApiUser, isAuthenticated } from '../../utils/auth';
import { escapeHtml, hideModal, showModal } from '../../utils/bootstrap-modal';
import { createApicsDataTable, type ApicsDataTableApi } from '../../utils/datatable';
import { statusBadge } from '../../utils/status-badge';
import { confirmAction, confirmWithReason, toastError, toastSuccess } from '../../utils/toast';
import {
    mountAttachmentUploader,
    type ApplicationDocument,
} from './attachments';
import { bindDocumentViewerClicks } from '../ops/document-viewer';
import {
    collectDynamicFieldValues,
    renderDynamicFieldsByTab,
    resizeDynamicLocationPickers,
    validateRequiredDynamicFields,
    type FormSchema,
} from './dynamic-fields';
import {
    applicationFormTabNavHtml,
    groupSectionsByTab,
    nextApplicationFormTab,
    prevApplicationFormTab,
    switchApplicationFormTab,
    type ApplicationFormTabId,
} from './application-form/tabs';
import {
    applicantViewHeaderMetaHtml,
    applicantViewTabNavHtml,
    renderApplicantViewHtml,
    switchApplicantViewTab,
    type ApplicantViewTabId,
} from './application-view/render';
import {
    createLocationPicker,
    type LocationPickerApi,
} from '../location-map/location-picker';
import {
    mountSiteMapViewer,
    siteMapSectionHtml,
    type SiteMapViewerApi,
} from '../location-map/site-map-viewer';

type ComplianceAppealRow = {
    uuid: string;
    status: string;
    grounds?: string | null;
    resolution_notes?: string | null;
    filed_at?: string | null;
    resolved_at?: string | null;
};

type ComplianceNoticeRow = {
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
    appeals?: ComplianceAppealRow[];
};

type ApplicationRow = {
    uuid: string;
    application_no: string;
    project_title?: string | null;
    project_location?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    status: string;
    classification?: string | null;
    submitted_at?: string | null;
    created_at?: string | null;
    form?: {
        uuid?: string;
        code?: string;
        title?: string;
        schema?: FormSchema;
        required_attachments?: string[];
    } | null;
    payload?: Record<string, unknown> | null;
    documents?: ApplicationDocument[];
    compliance_notices?: ComplianceNoticeRow[];
};

type FormOption = {
    uuid: string;
    code: string;
    title: string;
    schema?: FormSchema;
    required_attachments?: string[];
};

const IN_PROGRESS_STATUSES = new Set([
    'submitted',
    'under_evaluation',
    'for_inspection',
    'for_compliance',
    'for_payment',
    'for_releasing',
    'disapproved',
]);

const FILING_FORM_CODES = new Set(['QMS-36', 'QMS-37']);

/** Matches PermitApplicationService::APPLICANT_EDITABLE_STATUSES */
const APPLICANT_EDITABLE_STATUSES = new Set([
    'draft',
    'submitted',
    'under_evaluation',
    'for_compliance',
]);

function canApplicantEdit(status: string | undefined | null): boolean {
    return APPLICANT_EDITABLE_STATUSES.has(status || '');
}

function updateStats(rows: ApplicationRow[]): void {
    const total = rows.length;
    const draft = rows.filter((r) => r.status === 'draft').length;
    const released = rows.filter((r) => r.status === 'released').length;
    const inProgress = rows.filter((r) => IN_PROGRESS_STATUSES.has(r.status)).length;

    const set = (key: string, value: number): void => {
        document.querySelectorAll(`[data-stat="${key}"]`).forEach((el) => {
            el.textContent = String(value);
        });
    };

    set('total', total);
    set('draft', draft);
    set('in_progress', inProgress);
    set('released', released);
}

export function initApplicationsPage(): void {
    const tableEl = document.getElementById('applications-table');
    const skeleton = document.getElementById('applications-skeleton');
    const authGate = document.getElementById('applications-auth-gate');
    const workspace = document.getElementById('applications-workspace');
    const form = document.getElementById('form-application') as HTMLFormElement | null;

    if (!tableEl || !form) {
        return;
    }

    if (!isAuthenticated()) {
        authGate?.classList.remove('d-none');
        workspace?.classList.add('d-none');
        return;
    }

    authGate?.classList.add('d-none');
    workspace?.classList.remove('d-none');

    const user = getApiUser();
    const welcome = document.getElementById('applications-welcome');
    if (welcome) {
        welcome.textContent = user?.name ? `Welcome, ${user.name}` : 'Welcome';
    }

    let table: ApicsDataTableApi<ApplicationRow> | null = null;
    let forms: FormOption[] = [];
    let viewingRow: ApplicationRow | null = null;
    let editingUuid: string | null = null;
    let pendingPayload: Record<string, unknown> = {};
    let currentDocuments: ApplicationDocument[] = [];

    const formTypeSelect = document.getElementById('application-form-type') as HTMLSelectElement;
    const modalLabel = document.getElementById('modal-application-form-label');
    const formTabNav = document.getElementById('application-form-tabnav');
    const attachmentsRoot = document.getElementById('application-attachments');
    const viewEditBtn = document.getElementById('btn-view-edit') as HTMLButtonElement | null;
    const viewSubmitBtn = document.getElementById('btn-view-submit') as HTMLButtonElement | null;
    const projectLocationRoot = document.querySelector<HTMLElement>(
        '[data-location-picker][data-address-input="application-project-location"]',
    );
    let projectLocationPicker: LocationPickerApi | null = null;
    let viewSiteMap: SiteMapViewerApi | null = null;
    let expandViewSiteMap: SiteMapViewerApi | null = null;
    let activeViewTab: ApplicantViewTabId = 'overview';
    let activeFormTab: ApplicationFormTabId = 'project';

    const formatDate = (value?: string | null): string => {
        if (!value) return '—';
        try {
            return new Date(value).toLocaleString();
        } catch {
            return escapeHtml(value);
        }
    };

    const bindProjectMapToggle = (): void => {
        const toggle = projectLocationRoot?.querySelector<HTMLButtonElement>('[data-location-toggle-map]');
        if (!toggle || !projectLocationRoot || toggle.dataset.bound === '1') return;
        toggle.dataset.bound = '1';
        toggle.addEventListener('click', () => {
            const collapsed = projectLocationRoot.classList.toggle('is-map-collapsed');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            const label = toggle.querySelector('span');
            if (label) label.textContent = collapsed ? 'Show map' : 'Hide map';
            if (!collapsed) {
                window.setTimeout(() => projectLocationPicker?.invalidateSize(), 80);
                window.setTimeout(() => projectLocationPicker?.invalidateSize(), 280);
            }
        });
    };

    const ensureProjectLocationPicker = (): LocationPickerApi | null => {
        if (projectLocationPicker) return projectLocationPicker;
        const addressInput = document.getElementById('application-project-location') as HTMLInputElement | null;
        if (!projectLocationRoot || !addressInput) return null;
        try {
            projectLocationPicker = createLocationPicker({
                root: projectLocationRoot,
                addressInput,
                latInput: document.getElementById('application-project-location-lat') as HTMLInputElement | null,
                lngInput: document.getElementById('application-project-location-lng') as HTMLInputElement | null,
                required: true,
            });
            projectLocationRoot.dataset.locationMounted = '1';
            bindProjectMapToggle();
        } catch {
            projectLocationPicker = null;
        }
        return projectLocationPicker;
    };

    const selectedForm = (): FormOption | undefined => forms.find((item) => item.uuid === formTypeSelect.value);

    const goFormTab = (tab: ApplicationFormTabId): void => {
        activeFormTab = tab;
        switchApplicationFormTab(tab);
        if (tab === 'project') {
            window.setTimeout(() => {
                ensureProjectLocationPicker()?.invalidateSize();
                resizeDynamicLocationPickers(form);
            }, 120);
        } else {
            window.setTimeout(() => resizeDynamicLocationPickers(form), 120);
        }
    };

    const paintDynamicFields = (payload: Record<string, unknown> = {}): void => {
        renderDynamicFieldsByTab(form, selectedForm()?.schema || null, payload, groupSectionsByTab);
    };

    const paintFormChrome = (): void => {
        if (formTabNav) {
            formTabNav.innerHTML = applicationFormTabNavHtml(activeFormTab);
            formTabNav.querySelectorAll<HTMLButtonElement>('[data-app-form-tab]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const tab = btn.dataset.appFormTab as ApplicationFormTabId | undefined;
                    if (tab) goFormTab(tab);
                });
            });
        }
        switchApplicationFormTab(activeFormTab);
    };

    const openApplicantExpandedMap = (app: ApplicationRow): void => {
        const expandBody = document.getElementById('application-map-expand-body');
        const expandTitle = document.getElementById('modal-application-map-expand-label');
        if (!expandBody) return;
        expandViewSiteMap?.destroy();
        expandViewSiteMap = null;
        if (expandTitle) {
            expandTitle.textContent = `Project site · ${app.application_no || ''}`.trim();
        }
        expandBody.innerHTML = `<div class="p-3">${siteMapSectionHtml({
            latitude: app.latitude,
            longitude: app.longitude,
            address: app.project_location,
            label: app.project_title || app.application_no || 'Project site',
        })}</div>`;
        showModal('modal-application-map-expand');
        window.setTimeout(() => {
            expandViewSiteMap = mountSiteMapViewer(expandBody);
            expandViewSiteMap?.invalidateSize();
        }, 200);
        window.setTimeout(() => expandViewSiteMap?.invalidateSize(), 500);
    };

    const goViewTab = (tab: ApplicantViewTabId): void => {
        activeViewTab = tab;
        switchApplicantViewTab(tab);
        if (tab === 'overview') {
            window.setTimeout(() => viewSiteMap?.invalidateSize(), 120);
            window.setTimeout(() => viewSiteMap?.invalidateSize(), 350);
        }
    };

    const renderViewModal = async (row: ApplicationRow): Promise<void> => {
        viewingRow = row;
        const body = document.getElementById('application-view-body');
        const title = document.getElementById('modal-application-view-label');
        const meta = document.getElementById('application-view-header-meta');
        const tabnav = document.getElementById('application-view-tabnav');
        if (!body) return;

        activeViewTab = 'overview';
        viewSiteMap?.destroy();
        viewSiteMap = null;
        if (meta) meta.innerHTML = '';
        if (tabnav) tabnav.innerHTML = '';
        body.innerHTML = `<div class="text-center text-muted py-4">Loading…</div>`;
        showModal('modal-application-view');

        try {
            const { data } = await window.axios.get(`/api/v1/applications/${row.uuid}`);
            const full = (data.data || row) as ApplicationRow;
            viewingRow = full;
            const formMeta = forms.find((item) => item.uuid === full.form?.uuid) || full.form;
            const notices = full.compliance_notices || [];

            if (title) {
                title.textContent = `Application details · ${full.application_no || ''}`.trim();
            }
            if (meta) meta.innerHTML = applicantViewHeaderMetaHtml(full);
            if (tabnav) {
                tabnav.innerHTML = applicantViewTabNavHtml('overview', notices.length);
                tabnav.querySelectorAll<HTMLButtonElement>('[data-app-view-tab]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const tab = btn.dataset.appViewTab as ApplicantViewTabId | undefined;
                        if (tab) goViewTab(tab);
                    });
                });
            }

            body.innerHTML = renderApplicantViewHtml(full, (formMeta as FormOption | undefined)?.schema || null);
            switchApplicantViewTab('overview');

            bindDocumentViewerClicks(body, full.uuid);
            viewSiteMap = mountSiteMapViewer(body);
            window.setTimeout(() => viewSiteMap?.invalidateSize(), 200);
            window.setTimeout(() => viewSiteMap?.invalidateSize(), 500);

            body.querySelectorAll<HTMLElement>('[data-ops-expand-map]').forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    openApplicantExpandedMap(full);
                });
            });

            body.querySelectorAll<HTMLButtonElement>('[data-appeal-notice]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const noticeUuid = btn.dataset.appealNotice;
                    const noticeNo = btn.dataset.appealNo || 'this notice';
                    if (!noticeUuid) return;
                    void fileApplicantAppeal(noticeUuid, noticeNo, full);
                });
            });

            // Auto-open Compliance tab when notices exist and status is for_compliance
            if (notices.length && full.status === 'for_compliance') {
                goViewTab('compliance');
            }

            const isDraft = full.status === 'draft';
            const canEdit = canApplicantEdit(full.status);
            viewEditBtn?.classList.toggle('d-none', !canEdit);
            viewSubmitBtn?.classList.toggle('d-none', !isDraft);
            if (viewEditBtn) {
                viewEditBtn.textContent = isDraft ? 'Edit draft' : 'Edit / upload docs';
            }
        } catch (error: any) {
            body.innerHTML = `<p class="text-danger mb-0">${escapeHtml(error?.response?.data?.message || 'Unable to load details')}</p>`;
        }
    };

    const paintAttachments = (): void => {
        if (!attachmentsRoot) return;
        mountAttachmentUploader(attachmentsRoot, {
            applicationUuid: editingUuid,
            requiredLabels: selectedForm()?.required_attachments || [],
            documents: currentDocuments,
            onChange: (docs) => {
                currentDocuments = docs;
                paintAttachments();
                void table?.reload(false);
            },
        });
    };

    const fillFormSelect = (): void => {
        formTypeSelect.innerHTML = '';
        if (!forms.length) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'No active filing forms available';
            formTypeSelect.appendChild(opt);
            return;
        }
        forms.forEach((item, index) => {
            const opt = document.createElement('option');
            opt.value = item.uuid;
            opt.textContent = `${item.code} — ${item.title}`;
            if (index === 0) opt.selected = true;
            formTypeSelect.appendChild(opt);
        });
    };

    const resetFormModal = (mode: 'create' | 'edit', row?: ApplicationRow): void => {
        editingUuid = mode === 'edit' && row ? row.uuid : null;
        pendingPayload = { ...(row?.payload || {}) };
        currentDocuments = [...(row?.documents || [])];
        activeFormTab = 'project';
        (document.getElementById('application-uuid') as HTMLInputElement).value = editingUuid || '';
        (document.getElementById('application-project-title') as HTMLInputElement).value =
            row?.project_title || '';
        ensureProjectLocationPicker()?.setValue({
            address: row?.project_location || '',
            latitude: row?.latitude ?? null,
            longitude: row?.longitude ?? null,
        });
        if (!projectLocationPicker) {
            (document.getElementById('application-project-location') as HTMLInputElement).value =
                row?.project_location || '';
        }
        fillFormSelect();
        if (row?.form?.uuid) {
            formTypeSelect.value = row.form.uuid;
        }
        formTypeSelect.disabled = mode === 'edit';
        paintFormChrome();
        paintDynamicFields(pendingPayload);
        paintAttachments();
        goFormTab('project');
        if (modalLabel) {
            const status = row?.status || 'draft';
            modalLabel.textContent =
                mode === 'create'
                    ? 'New permit application'
                    : status === 'draft'
                        ? 'Edit draft application'
                        : `Edit application (${status.replaceAll('_', ' ')})`;
        }
    };

    const openCreateModal = (): void => {
        resetFormModal('create');
        showModal('modal-application-form');
    };

    const openEditModal = async (row: ApplicationRow): Promise<void> => {
        try {
            const { data } = await window.axios.get(`/api/v1/applications/${row.uuid}`);
            const full = (data.data || row) as ApplicationRow;
            resetFormModal('edit', full);
            showModal('modal-application-form');
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load application');
            resetFormModal('edit', row);
            showModal('modal-application-form');
        }
    };

    const fileApplicantAppeal = async (
        noticeUuid: string,
        noticeNo: string,
        application: ApplicationRow,
    ): Promise<void> => {
        const grounds = await confirmWithReason({
            title: `Appeal ${noticeNo}?`,
            text: 'Explain why the inspection findings or notice should be reconsidered.',
            inputLabel: 'Appeal grounds',
            inputPlaceholder: 'Describe your side of the issue and any supporting context…',
            confirmButtonText: 'Submit appeal',
            minLength: 10,
        });
        if (!grounds) return;

        try {
            const res = await window.axios.post(`/api/v1/compliance-notices/${noticeUuid}/appeals`, {
                grounds,
            });
            toastSuccess(res.data.message || 'Appeal filed');
            await renderViewModal(application);
            await table?.reload(false);
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || 'Appeal failed'));
        }
    };

    const submitApplication = async (row: ApplicationRow): Promise<void> => {
        const ok = await confirmAction(
            'Submit application for intake?',
            'Required attachments must be uploaded first. You can still update details or documents while status is Submitted / Under evaluation / For compliance.',
        );
        if (!ok) return;

        try {
            const res = await window.axios.post(`/api/v1/applications/${row.uuid}/submit`);
            toastSuccess(res.data.message || 'Application submitted');
            hideModal('modal-application-view');
            await table?.reload(false);
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || 'Submit failed'));
        }
    };

    document.getElementById('btn-new-application')?.addEventListener('click', () => openCreateModal());
    document.getElementById('btn-new-application-secondary')?.addEventListener('click', () => openCreateModal());

    formTypeSelect.addEventListener('change', () => {
        pendingPayload = collectDynamicFieldValues(form);
        paintDynamicFields(pendingPayload);
        paintAttachments();
    });

    document.getElementById('btn-app-form-back')?.addEventListener('click', () => {
        goFormTab(prevApplicationFormTab(activeFormTab));
    });

    document.getElementById('btn-app-form-next')?.addEventListener('click', () => {
        goFormTab(nextApplicationFormTab(activeFormTab));
    });

    document.getElementById('modal-application-form')?.addEventListener('shown.bs.modal', () => {
        ensureProjectLocationPicker()?.invalidateSize();
        resizeDynamicLocationPickers(form);
        bindProjectMapToggle();
    });

    viewEditBtn?.addEventListener('click', () => {
        if (!viewingRow) return;
        hideModal('modal-application-view');
        void openEditModal(viewingRow);
    });

    viewSubmitBtn?.addEventListener('click', () => {
        if (!viewingRow) return;
        void submitApplication(viewingRow);
    });

    void (async () => {
        try {
            const { data: formsRes } = await window.axios.get('/api/v1/applications/forms');
            const allForms = (formsRes.data || []) as FormOption[];
            forms = allForms.filter((item) => FILING_FORM_CODES.has(item.code));
            if (!forms.length) {
                forms = allForms;
            }

            table = await createApicsDataTable<ApplicationRow>({
                table: tableEl as HTMLTableElement,
                exportFileName: 'my-applications',
                rowId: 'uuid',
                searchMode: 'client',
                filters: [
                    {
                        id: 'status',
                        label: 'Status',
                        options: [
                            { value: '', label: 'All' },
                            { value: 'draft', label: 'Draft' },
                            { value: 'submitted', label: 'Submitted' },
                            { value: 'under_evaluation', label: 'Under evaluation' },
                            { value: 'for_inspection', label: 'For inspection' },
                            { value: 'for_payment', label: 'For payment' },
                            { value: 'for_releasing', label: 'For releasing' },
                            { value: 'for_compliance', label: 'For compliance' },
                            { value: 'released', label: 'Released' },
                            { value: 'disapproved', label: 'Disapproved' },
                        ],
                        match: (row, value) => (row.status || '') === value,
                    },
                ],
                columns: [
                    {
                        data: 'application_no',
                        title: 'Application No.',
                        responsivePriority: 1,
                        render: (data) => `<span class="fw-medium">${escapeHtml(String(data ?? ''))}</span>`,
                    },
                    {
                        data: 'project_title',
                        title: 'Project',
                        responsivePriority: 1,
                        render: (_data, _type, row) => `
                            <div>${escapeHtml(row.project_title || '—')}</div>
                            <div class="text-muted small">${escapeHtml(row.project_location || '')}</div>`,
                    },
                    {
                        data: 'status',
                        title: 'Status',
                        responsivePriority: 1,
                        render: (data) => statusBadge(String(data ?? '')),
                    },
                    {
                        data: 'form',
                        title: 'Form',
                        responsivePriority: 3,
                        render: (_data, _type, row) => escapeHtml(row.form?.code || '—'),
                    },
                    {
                        data: 'submitted_at',
                        title: 'Submitted',
                        responsivePriority: 4,
                        render: (data) => escapeHtml(formatDate(data as string | null)),
                    },
                ],
                actions: [
                    {
                        id: 'view',
                        label: 'View',
                        onClick: (row) => void renderViewModal(row),
                    },
                    {
                        id: 'edit',
                        label: 'Edit / upload docs',
                        visible: (row) => canApplicantEdit(row.status),
                        onClick: (row) => void openEditModal(row),
                    },
                    {
                        id: 'submit',
                        label: 'Submit',
                        visible: (row) => row.status === 'draft',
                        onClick: (row) => void submitApplication(row),
                    },
                ],
                fetchData: async () => {
                    const { data } = await window.axios.get('/api/v1/applications', {
                        params: { per_page: 100 },
                    });
                    const rows: ApplicationRow[] = data.data?.items || [];
                    updateStats(rows);
                    return rows;
                },
            });
        } catch (error: any) {
            toastError(error?.response?.data?.message || 'Unable to load your applications.');
            updateStats([]);
        } finally {
            skeleton?.classList.add('d-none');
        }
    })();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const saveBtn = document.getElementById('btn-save-application') as HTMLButtonElement | null;
        const formUuid = formTypeSelect.value;
        const projectTitle = (document.getElementById('application-project-title') as HTMLInputElement).value.trim();
        const projectLocation = (
            document.getElementById('application-project-location') as HTMLInputElement
        ).value.trim();
        const latRaw = (document.getElementById('application-project-location-lat') as HTMLInputElement | null)
            ?.value;
        const lngRaw = (document.getElementById('application-project-location-lng') as HTMLInputElement | null)
            ?.value;
        const latitude = latRaw ? Number(latRaw) : null;
        const longitude = lngRaw ? Number(lngRaw) : null;

        if (!formUuid) {
            toastError('Select a permit form.');
            goFormTab('project');
            return;
        }
        if (!projectTitle || !projectLocation) {
            toastError('Project title and location are required.');
            goFormTab('project');
            return;
        }

        const requiredError = validateRequiredDynamicFields(form);
        if (requiredError) {
            toastError(requiredError);
            // Jump to the tab that holds the first missing required field.
            const missing = form.querySelector<HTMLElement>('[data-dyn-field][required]');
            const empty = Array.from(form.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>('[data-dyn-field][required]')).find(
                (el) => !el.value.trim(),
            );
            const host = (empty || missing)?.closest<HTMLElement>('[data-app-form-panel]');
            const tab = host?.dataset.appFormPanel as ApplicationFormTabId | undefined;
            if (tab) goFormTab(tab);
            return;
        }

        if (saveBtn) saveBtn.disabled = true;

        const payload = {
            form_definition_uuid: formUuid,
            project_title: projectTitle,
            project_location: projectLocation,
            latitude: latitude != null && Number.isFinite(latitude) ? latitude : null,
            longitude: longitude != null && Number.isFinite(longitude) ? longitude : null,
            payload: collectDynamicFieldValues(form),
        };

        try {
            if (editingUuid) {
                const { data } = await window.axios.put(`/api/v1/applications/${editingUuid}`, payload);
                currentDocuments = (data.data?.documents as ApplicationDocument[]) || currentDocuments;
                toastSuccess(data.message || 'Application updated');
                paintAttachments();
                await table?.reload(false);
            } else {
                const { data } = await window.axios.post('/api/v1/applications', payload);
                const created = data.data as ApplicationRow;
                editingUuid = created.uuid;
                currentDocuments = created.documents || [];
                (document.getElementById('application-uuid') as HTMLInputElement).value = editingUuid;
                formTypeSelect.disabled = true;
                if (modalLabel) {
                    modalLabel.textContent = 'Edit draft application';
                }
                toastSuccess('Draft saved — upload required documents below.');
                paintAttachments();
                await table?.reload(false);
            }
        } catch (error: any) {
            const errors = error?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;
            toastError(String(first || error?.response?.data?.message || 'Could not save application'));
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });
}
