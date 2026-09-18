import{e as a,t as u}from"./app-B97eAumq.js";function b(s){return s==="completed"?{className:"bg-success-subtle text-success",label:"Completed"}:s==="in_progress"?{className:"bg-warning-subtle text-warning",label:"In progress"}:{className:"bg-secondary-subtle text-secondary",label:"Pending"}}function g(s){return s==="completed"?'<i class="ri-checkbox-circle-fill text-success fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">Completed:</span>':s==="in_progress"?'<i class="ri-loader-4-line text-warning fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">In progress:</span>':'<i class="ri-checkbox-blank-circle-line text-muted fs-5 mt-0" aria-hidden="true"></i><span class="visually-hidden">Pending:</span>'}function v(s){const t=s.summary.percent,o=(s.phases||[]).map(e=>{const i=b(e.status),r=`phase-${a(String(e.id))}`,l=e.status==="in_progress",n=e.status==="completed"?"bg-success":e.status==="in_progress"?"bg-warning":"bg-secondary",m=(e.items||[]).map(d=>{const p=d.status==="completed";return`
                        <li class="list-group-item d-flex align-items-start gap-2 px-0">
                            ${g(d.status)}
                            <span class="${p?"text-muted text-decoration-line-through":""}">${a(d.label)}</span>
                        </li>
                    `}).join("");return`
                <div class="accordion-item border mb-2 rounded overflow-hidden">
                    <h2 class="accordion-header" id="heading-${a(String(e.id))}">
                        <button
                            class="accordion-button ${l?"":"collapsed"} py-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${r}"
                            aria-expanded="${l?"true":"false"}"
                            aria-controls="${r}"
                        >
                            <span class="d-flex flex-wrap align-items-center gap-2 w-100 pe-3">
                                <span class="fw-semibold">${a(e.title)}</span>
                                ${e.weeks?`<span class="badge bg-info-subtle text-info">${a(e.weeks)}</span>`:""}
                                <span class="badge ${i.className}">${i.label}</span>
                                <span class="ms-auto text-muted small">${e.progress_percent}%</span>
                            </span>
                        </button>
                    </h2>
                    <div
                        id="${r}"
                        class="accordion-collapse collapse ${l?"show":""}"
                        aria-labelledby="heading-${a(String(e.id))}"
                        data-bs-parent="#projectPlanAccordion"
                    >
                        <div class="accordion-body pt-0">
                            <div class="progress mb-3" style="height: 6px;" role="progressbar" aria-valuenow="${e.progress_percent}" aria-valuemin="0" aria-valuemax="100" aria-label="${a(e.title)} progress">
                                <div class="progress-bar ${n}" style="width: ${e.progress_percent}%"></div>
                            </div>
                            <ul class="list-group list-group-flush">${m}</ul>
                        </div>
                    </div>
                </div>
            `}).join(""),c=s.roadmap&&s.roadmap.length>0?`
                <div class="mt-4">
                    <h6 class="fw-semibold mb-3">Beyond Phase I (roadmap)</h6>
                    <div class="row g-3">
                        ${s.roadmap.map(e=>`
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary">Later</span>
                                        <span class="fw-semibold">${a(e.title)}</span>
                                    </div>
                                    <p class="text-muted mb-0 small">${a(e.label)}</p>
                                </div>
                            </div>
                        `).join("")}
                    </div>
                </div>
            `:"";return`
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
            <div>
                <h5 class="card-title mb-1">APICS Project Plan — Delivery Phases 0–8</h5>
                <p class="text-muted mb-0 small">${a(s.project)}</p>
                ${s.source?`<p class="text-muted mb-0 small mt-1">Source: <code>${a(s.source)}</code></p>`:""}
            </div>
            <div class="text-md-end">
                <span class="badge bg-primary-subtle text-primary">Updated ${a(s.updated_at)}</span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Overall</p>
                    <h4 class="mb-0">${t}%</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Phases done</p>
                    <h4 class="mb-0 text-success">${s.summary.completed}/${s.summary.total}</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">In progress</p>
                    <h4 class="mb-0 text-warning">${s.summary.in_progress}</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="border rounded p-3 h-100">
                    <p class="text-muted text-uppercase fw-medium fs-12 mb-1">Pending</p>
                    <h4 class="mb-0 text-muted">${s.summary.pending}</h4>
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
            <strong>Current focus:</strong> ${a(s.current_focus)}
        </div>

        <div class="accordion" id="projectPlanAccordion">${o}</div>
        ${c}
    `}function f(){const s=document.getElementById("project-plan-root");s&&(async()=>{try{const{data:t}=await window.axios.get("/api/v1/admin/project-plan");if(!t?.status||!t?.data)throw new Error(t?.message||"Unable to load project plan");s.innerHTML=v(t.data)}catch(t){s.innerHTML=`
                <div class="alert alert-danger border-0 mb-0" role="alert">
                    ${a(t?.response?.data?.message||t?.message||"Unable to load project plan")}
                </div>
            `,u(t?.response?.data?.message||"Unable to load project plan")}})()}export{f as initProjectPlanPage};
