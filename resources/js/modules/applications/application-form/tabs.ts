import { escapeHtml } from '../../../utils/bootstrap-modal';
import type { FormSchema, FormSectionDef } from '../dynamic-fields';
import { normalizeSections } from '../dynamic-fields';

export type ApplicationFormTabId =
    | 'project'
    | 'owner'
    | 'building'
    | 'professionals'
    | 'documents';

export const APPLICATION_FORM_TABS: Array<{
    id: ApplicationFormTabId;
    label: string;
    icon: string;
    short: string;
}> = [
    { id: 'project', label: 'Project & Site', icon: 'ri-map-pin-line', short: 'Project' },
    { id: 'owner', label: 'Owner / Applicant', icon: 'ri-user-line', short: 'Owner' },
    { id: 'building', label: 'Building data', icon: 'ri-building-2-line', short: 'Building' },
    { id: 'professionals', label: 'Professionals', icon: 'ri-account-box-line', short: 'Pros' },
    { id: 'documents', label: 'Documents', icon: 'ri-folder-2-line', short: 'Docs' },
];

function sectionTab(title: string): ApplicationFormTabId {
    const key = title.toLowerCase();
    if (key.includes('owner') || key.includes('applicant')) return 'owner';
    if (key.includes('property') || key.includes('location')) return 'project';
    if (key.includes('building') || key.includes('project /') || key.includes('project/')) return 'building';
    if (key.includes('professional') || key.includes('remark') || key.includes('affidavit')) {
        return 'professionals';
    }
    return 'building';
}

export function groupSectionsByTab(schema?: FormSchema | null): Record<ApplicationFormTabId, FormSectionDef[]> {
    const grouped: Record<ApplicationFormTabId, FormSectionDef[]> = {
        project: [],
        owner: [],
        building: [],
        professionals: [],
        documents: [],
    };

    normalizeSections(schema).forEach((section) => {
        grouped[sectionTab(section.title)].push(section);
    });

    return grouped;
}

export function applicationFormTabNavHtml(active: ApplicationFormTabId = 'project'): string {
    return `<ul class="nav nav-tabs nav-tabs-custom nav-success mb-0 flex-wrap apics-app-form__tabs" role="tablist">
        ${APPLICATION_FORM_TABS.map(
            (tab) => `<li class="nav-item" role="presentation">
                <button type="button"
                    class="nav-link text-nowrap${active === tab.id ? ' active' : ''}"
                    data-app-form-tab="${tab.id}"
                    role="tab"
                    aria-selected="${active === tab.id ? 'true' : 'false'}">
                    <i class="${tab.icon} align-bottom me-1"></i>
                    <span class="d-none d-md-inline">${escapeHtml(tab.label)}</span>
                    <span class="d-md-none">${escapeHtml(tab.short)}</span>
                </button>
            </li>`,
        ).join('')}
    </ul>`;
}

export function switchApplicationFormTab(tabId: ApplicationFormTabId): void {
    document.querySelectorAll<HTMLButtonElement>('[data-app-form-tab]').forEach((btn) => {
        const on = btn.dataset.appFormTab === tabId;
        btn.classList.toggle('active', on);
        btn.setAttribute('aria-selected', on ? 'true' : 'false');
    });

    document.querySelectorAll<HTMLElement>('[data-app-form-panel]').forEach((panel) => {
        const on = panel.dataset.appFormPanel === tabId;
        panel.classList.toggle('show', on);
        panel.classList.toggle('active', on);
        panel.classList.toggle('d-none', !on);
    });

    const backBtn = document.getElementById('btn-app-form-back') as HTMLButtonElement | null;
    const nextBtn = document.getElementById('btn-app-form-next') as HTMLButtonElement | null;
    const index = APPLICATION_FORM_TABS.findIndex((tab) => tab.id === tabId);
    if (backBtn) backBtn.disabled = index <= 0;
    if (nextBtn) {
        nextBtn.classList.toggle('d-none', index >= APPLICATION_FORM_TABS.length - 1);
    }
}

export function nextApplicationFormTab(current: ApplicationFormTabId): ApplicationFormTabId {
    const index = APPLICATION_FORM_TABS.findIndex((tab) => tab.id === current);
    return APPLICATION_FORM_TABS[Math.min(index + 1, APPLICATION_FORM_TABS.length - 1)]?.id || current;
}

export function prevApplicationFormTab(current: ApplicationFormTabId): ApplicationFormTabId {
    const index = APPLICATION_FORM_TABS.findIndex((tab) => tab.id === current);
    return APPLICATION_FORM_TABS[Math.max(index - 1, 0)]?.id || current;
}
