import{s as l,e as a,t as c}from"./app-DpvXLM-T.js";function i(t){return t.replaceAll("_"," ").replace(/\b\w/g,e=>e.toUpperCase())}function p(t){return`<span class="badge ${{simple:"bg-success-subtle text-success",complex:"bg-warning-subtle text-warning",highly_technical:"bg-danger-subtle text-danger"}[t]||"bg-info-subtle text-info"}">${a(i(t))}</span>`}function d(t){return t.length?t.map(e=>{const s=[...e.steps||[]].sort((n,o)=>(n.step_order||0)-(o.step_order||0)),r=s.length?s.map(n=>`<li class="mb-1">
                                <span class="badge bg-secondary-subtle text-secondary me-1">${a(String(n.step_order??""))}</span>
                                <span class="fw-medium">${a(n.label||"Step")}</span>
                                <span class="text-muted"> — ${a(n.department?.name||n.department?.code||"Dept")}</span>
                                <span class="text-muted small">(${a(String(n.sla_hours??24))}h)</span>
                            </li>`).join(""):'<li class="text-muted">No steps</li>';return`<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="fw-semibold">${a(e.code)}</span>
                    ${p(e.classification)}
                    ${e.is_active?'<span class="badge bg-success-subtle text-success">Active</span>':'<span class="badge bg-secondary-subtle text-secondary">Inactive</span>'}
                </div>
                <p class="mb-2">${a(e.name)}</p>
                <ol class="list-unstyled mb-0 ps-1">${r}</ol>
            </div>`}).join(""):'<p class="text-muted mb-0">No routing templates configured yet. An admin with Workflow Config access can add them.</p>'}function g(){const t=document.getElementById("btn-browse-routing-templates"),e=document.getElementById("ops-routing-templates-body");!t||!e||t.addEventListener("click",()=>{(async()=>{e.innerHTML=`<div class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading templates…
            </div>`,l("modal-ops-routing-templates");try{const{data:s}=await window.axios.get("/api/v1/staff/routing-templates",{params:{per_page:50}});e.innerHTML=d(s.data?.items||[]);const r=document.getElementById("btn-ops-manage-routing-templates");r&&s.data?.can_manage&&r.classList.remove("d-none")}catch(s){e.innerHTML=`<p class="text-danger mb-0">${a(s?.response?.data?.message||"Unable to load routing templates.")}</p>`,c(s?.response?.data?.message||"Unable to load routing templates")}})()})}export{g as initOpsRoutingTemplatesBrowse};
