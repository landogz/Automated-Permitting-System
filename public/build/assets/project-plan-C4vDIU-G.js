import{e as s,t as h}from"./app-Cwtxlj4R.js";function c(e){return e==="completed"?{className:"bg-success-subtle text-success",label:"Completed"}:e==="in_progress"?{className:"bg-warning-subtle text-warning",label:"In progress"}:{className:"bg-secondary-subtle text-secondary",label:"Pending"}}function m(e){return e==="completed"?'<i class="ri-checkbox-circle-fill text-success fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">Completed:</span>':e==="in_progress"?'<i class="ri-loader-4-line text-warning fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">In progress:</span>':'<i class="ri-checkbox-blank-circle-line text-muted fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">Pending:</span>'}function f(e){return!e||e.length===0?"":`
        <div class="mt-4">
            <h6 class="fw-semibold mb-2">Plan todos (from markdown frontmatter)</h6>
            <div class="table-responsive border rounded">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Work item</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>${e.map(l=>{const r=c(l.status);return`
                <tr>
                    <td class="text-nowrap"><code class="fs-12">${s(l.id)}</code></td>
                    <td>${s(l.label)}</td>
                    <td class="text-nowrap"><span class="badge ${r.className}">${r.label}</span></td>
                </tr>
            `}).join("")}</tbody>
                </table>
            </div>
        </div>
    `}function x(e){if(!e||e.length===0)return"";const t=e.slice(0,12),l=t.map(r=>`
            <tr>
                <td class="text-nowrap fw-medium">${s(r.date)}</td>
                <td>${s(r.completed)}</td>
                <td class="text-muted small">${s(r.notes)}</td>
            </tr>
        `).join("");return`
        <div class="mt-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h6 class="fw-semibold mb-0">Progress changelog</h6>
                <span class="text-muted small">Showing ${t.length} of ${e.length} (newest first)</span>
            </div>
            <div class="table-responsive border rounded" style="max-height: 22rem; overflow: auto;">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Completed</th>
                            <th scope="col">Notes / next</th>
                        </tr>
                    </thead>
                    <tbody>${l}</tbody>
                </table>
            </div>
        </div>
    `}function $(e){return!e||e.length===0?"":`
        <div class="mt-4">
            <h6 class="fw-semibold mb-2">Immediate next execution order</h6>
            <ul class="list-group list-group-flush border rounded px-3 py-1">${e.map(l=>{const r=/\(done\)/i.test(l);return`
                <li class="list-group-item d-flex align-items-start gap-2 px-0">
                    ${m(r?"completed":"pending")}
                    <span class="${r?"text-muted text-decoration-line-through":""}">${s(l)}</span>
                </li>
            `}).join("")}</ul>
        </div>
    `}function w(e){const t=e.summary.percent,l=e.summary.todos_total??e.todos?.length??0,r=e.summary.todos_completed??0,p=(e.phases||[]).map(a=>{const n=c(a.status),d=`phase-${s(String(a.id))}`,o=a.status==="in_progress",b=a.status==="completed"?"bg-success":a.status==="in_progress"?"bg-warning":"bg-secondary",g=(a.items||[]).map(i=>{const v=i.status==="completed";return`
                        <li class="list-group-item d-flex align-items-start gap-2 px-0">
                            ${m(i.status)}
                            <span class="${v?"text-muted text-decoration-line-through":""}">${s(i.label)}</span>
                        </li>
                    `}).join("");return`
                <div class="accordion-item border mb-2 rounded overflow-hidden">
                    <h2 class="accordion-header" id="heading-${s(String(a.id))}">
                        <button
                            class="accordion-button ${o?"":"collapsed"} py-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${d}"
                            aria-expanded="${o?"true":"false"}"
                            aria-controls="${d}"
                        >
                            <span class="d-flex flex-wrap align-items-center gap-2 w-100 pe-3">
                                <span class="fw-semibold">${s(a.title)}</span>
                                ${a.weeks?`<span class="badge bg-info-subtle text-info">${s(a.weeks)}</span>`:""}
                                <span class="badge ${n.className}">${n.label}</span>
                                <span class="ms-auto text-muted small">${a.progress_percent}%</span>
                            </span>
                        </button>
                    </h2>
                    <div
                        id="${d}"
                        class="accordion-collapse collapse ${o?"show":""}"
                        aria-labelledby="heading-${s(String(a.id))}"
                        data-bs-parent="#projectPlanAccordion"
                    >
                        <div class="accordion-body pt-0">
                            <div class="progress mb-3" style="height: 6px;" role="progressbar" aria-valuenow="${a.progress_percent}" aria-valuemin="0" aria-valuemax="100" aria-label="${s(a.title)} progress">
                                <div class="progress-bar ${b}" style="width: ${a.progress_percent}%"></div>
                            </div>
                            <ul class="list-group list-group-flush">${g}</ul>
                        </div>
                    </div>
                </div>
            `}).join(""),u=e.roadmap&&e.roadmap.length>0?`
                <div class="mt-4">
                    <h6 class="fw-semibold mb-3">Beyond Phase I (roadmap)</h6>
                    <div class="row g-3">
                        ${e.roadmap.map(a=>`
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary">Later</span>
                                        <span class="fw-semibold">${s(a.title)}</span>
                                    </div>
                                    <p class="text-muted mb-0 small">${s(a.label)}</p>
                                </div>
                            </div>
                        `).join("")}
                    </div>
                </div>
            `:"";return`
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
            <div>
                <h5 class="card-title mb-1">APICS Project Plan — Delivery Phases 0–8</h5>
                <p class="text-muted mb-0 small">${s(e.project)}</p>
                ${e.source?`<p class="text-muted mb-0 small mt-1">Source: <code>${s(e.source)}</code></p>`:""}
            </div>
            <div class="text-md-end">
                <span class="badge bg-primary-subtle text-primary">Updated ${s(e.updated_at)}</span>
            </div>
        </div>

        ${e.overview?`<div class="alert alert-secondary border-0 mb-4" role="note">
                    <div class="text-uppercase fw-semibold fs-11 text-muted mb-1">Plan overview</div>
                    <p class="mb-0">${s(e.overview)}</p>
                   </div>`:""}

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Phases done</p>
                    <h4 class="mb-0 text-success">${e.summary.completed}/${e.summary.total}</h4>
                    <p class="text-muted mb-0 fs-12 mt-1">${t}% of delivery phases</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Todos done</p>
                    <h4 class="mb-0 text-primary">${r}/${l}</h4>
                    <p class="text-muted mb-0 fs-12 mt-1">Frontmatter checklist</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">In progress</p>
                    <h4 class="mb-0 text-warning">${e.summary.in_progress}</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Pending phases</p>
                    <h4 class="mb-0 text-muted">${e.summary.pending}</h4>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-medium">Delivery phase progress (0–8)</span>
                <span class="text-muted small">${t}% done</span>
            </div>
            <div class="progress progress-lg" role="progressbar" aria-valuenow="${t}" aria-valuemin="0" aria-valuemax="100" aria-label="Project plan completion">
                <div class="progress-bar bg-success" style="width: ${t}%"></div>
            </div>
        </div>

        <div class="alert alert-primary border-0 mb-4" role="status">
            <strong>Current focus:</strong> ${s(e.current_focus)}
        </div>

        <h6 class="fw-semibold mb-2">Delivery phases</h6>
        <div class="accordion" id="projectPlanAccordion">${p}</div>
        ${f(e.todos)}
        ${$(e.next_steps)}
        ${x(e.changelog)}
        ${u}
    `}function j(){const e=document.getElementById("project-plan-root");e&&(async()=>{try{const{data:t}=await window.axios.get("/api/v1/admin/project-plan");if(!t?.status||!t?.data)throw new Error(t?.message||"Unable to load project plan");e.innerHTML=w(t.data)}catch(t){e.innerHTML=`
                <div class="alert alert-danger border-0 mb-0" role="alert">
                    ${s(t?.response?.data?.message||t?.message||"Unable to load project plan")}
                </div>
            `,h(t?.response?.data?.message||"Unable to load project plan")}})()}export{j as initProjectPlanPage};
