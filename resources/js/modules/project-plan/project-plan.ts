import { escapeHtml } from '../../utils/bootstrap-modal';
import { toastError } from '../../utils/toast';

type PlanItem = {
    id?: string;
    label: string;
    status: string;
};

type PlanPhase = {
    id: string | number;
    title: string;
    weeks?: string | null;
    status: string;
    progress_percent: number;
    items: PlanItem[];
};

type PlanRoadmap = {
    id: string;
    title: string;
    label: string;
};

type ProjectPlanPayload = {
    project: string;
    current_focus: string;
    updated_at: string;
    source?: string | null;
    summary: {
        total: number;
        completed: number;
        pending: number;
        in_progress: number;
        percent: number;
    };
    phases: PlanPhase[];
    roadmap?: PlanRoadmap[];
};

function statusBadge(status: string): { className: string; label: string } {
    if (status === 'completed') {
        return { className: 'bg-success-subtle text-success', label: 'Completed' };
    }
    if (status === 'in_progress') {
        return { className: 'bg-warning-subtle text-warning', label: 'In progress' };
    }
    return { className: 'bg-secondary-subtle text-secondary', label: 'Pending' };
}

function itemIcon(status: string): string {
    if (status === 'completed') {
        return '<i class="ri-checkbox-circle-fill text-success fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">Completed:</span>';
    }
    if (status === 'in_progress') {
        return '<i class="ri-loader-4-line text-warning fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">In progress:</span>';
    }
    return '<i class="ri-checkbox-blank-circle-line text-muted fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">Pending:</span>';
}

function renderPlan(plan: ProjectPlanPayload): string {
    const percent = plan.summary.percent;
    const phasesHtml = (plan.phases || [])
        .map((phase) => {
            const badge = statusBadge(phase.status);
            const collapseId = `phase-${escapeHtml(String(phase.id))}`;
            const isOpen = phase.status === 'in_progress';
            const barClass =
                phase.status === 'completed'
                    ? 'bg-success'
                    : phase.status === 'in_progress'
                      ? 'bg-warning'
                      : 'bg-secondary';
            const items = (phase.items || [])
                .map((item) => {
                    const done = item.status === 'completed';
                    return `
                        <li class="list-group-item d-flex align-items-start gap-2 px-0">
                            ${itemIcon(item.status)}
                            <span class="${done ? 'text-muted text-decoration-line-through' : ''}">${escapeHtml(item.label)}</span>
                        </li>
                    `;
                })
                .join('');

            return `
                <div class="accordion-item border mb-2 rounded overflow-hidden">
                    <h2 class="accordion-header" id="heading-${escapeHtml(String(phase.id))}">
                        <button
                            class="accordion-button ${isOpen ? '' : 'collapsed'} py-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${collapseId}"
                            aria-expanded="${isOpen ? 'true' : 'false'}"
                            aria-controls="${collapseId}"
                        >
                            <span class="d-flex flex-wrap align-items-center gap-2 w-100 pe-3">
                                <span class="fw-semibold">${escapeHtml(phase.title)}</span>
                                ${phase.weeks ? `<span class="badge bg-info-subtle text-info">${escapeHtml(phase.weeks)}</span>` : ''}
                                <span class="badge ${badge.className}">${badge.label}</span>
                                <span class="ms-auto text-muted small">${phase.progress_percent}%</span>
                            </span>
                        </button>
                    </h2>
                    <div
                        id="${collapseId}"
                        class="accordion-collapse collapse ${isOpen ? 'show' : ''}"
                        aria-labelledby="heading-${escapeHtml(String(phase.id))}"
                        data-bs-parent="#projectPlanAccordion"
                    >
                        <div class="accordion-body pt-0">
                            <div class="progress mb-3" style="height: 6px;" role="progressbar" aria-valuenow="${phase.progress_percent}" aria-valuemin="0" aria-valuemax="100" aria-label="${escapeHtml(phase.title)} progress">
                                <div class="progress-bar ${barClass}" style="width: ${phase.progress_percent}%"></div>
                            </div>
                            <ul class="list-group list-group-flush">${items}</ul>
                        </div>
                    </div>
                </div>
            `;
        })
        .join('');

    const roadmapHtml =
        plan.roadmap && plan.roadmap.length > 0
            ? `
                <div class="mt-4">
                    <h6 class="fw-semibold mb-3">Beyond Phase I (roadmap)</h6>
                    <div class="row g-3">
                        ${plan.roadmap
                            .map(
                                (road) => `
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary">Later</span>
                                        <span class="fw-semibold">${escapeHtml(road.title)}</span>
                                    </div>
                                    <p class="text-muted mb-0 small">${escapeHtml(road.label)}</p>
                                </div>
                            </div>
                        `,
                            )
                            .join('')}
                    </div>
                </div>
            `
            : '';

    return `
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
            <div>
                <h5 class="card-title mb-1">APICS Project Plan — Delivery Phases 0–8</h5>
                <p class="text-muted mb-0 small">${escapeHtml(plan.project)}</p>
                ${plan.source ? `<p class="text-muted mb-0 small mt-1">Source: <code>${escapeHtml(plan.source)}</code></p>` : ''}
            </div>
            <div class="text-md-end">
                <span class="badge bg-primary-subtle text-primary">Updated ${escapeHtml(plan.updated_at)}</span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Overall</p>
                    <h4 class="mb-0">${percent}%</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Phases done</p>
                    <h4 class="mb-0 text-success">${plan.summary.completed}/${plan.summary.total}</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">In progress</p>
                    <h4 class="mb-0 text-warning">${plan.summary.in_progress}</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Pending</p>
                    <h4 class="mb-0 text-muted">${plan.summary.pending}</h4>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-medium">Delivery phase progress (0–8)</span>
                <span class="text-muted small">${percent}% done</span>
            </div>
            <div class="progress progress-lg" role="progressbar" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100" aria-label="Project plan completion">
                <div class="progress-bar bg-success" style="width: ${percent}%"></div>
            </div>
        </div>

        <div class="alert alert-primary border-0 mb-4" role="status">
            <strong>Current focus:</strong> ${escapeHtml(plan.current_focus)}
        </div>

        <div class="accordion" id="projectPlanAccordion">${phasesHtml}</div>
        ${roadmapHtml}
    `;
}

/**
 * Load and render APICS Phase I project plan on the dedicated admin page.
 */
export function initProjectPlanPage(): void {
    const root = document.getElementById('project-plan-root');
    if (!root) {
        return;
    }

    void (async () => {
        try {
            const { data } = await window.axios.get('/api/v1/admin/project-plan');
            if (!data?.status || !data?.data) {
                throw new Error(data?.message || 'Unable to load project plan');
            }
            root.innerHTML = renderPlan(data.data as ProjectPlanPayload);
        } catch (error: any) {
            root.innerHTML = `
                <div class="alert alert-danger border-0 mb-0" role="alert">
                    ${escapeHtml(error?.response?.data?.message || error?.message || 'Unable to load project plan')}
                </div>
            `;
            toastError(error?.response?.data?.message || 'Unable to load project plan');
        }
    })();
}
