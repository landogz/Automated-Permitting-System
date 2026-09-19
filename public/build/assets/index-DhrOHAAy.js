import{e as i,t as b,d as L,b as $,j as Q,k as M,s as j}from"./app-Cmf22frh.js";import{n as B,h as A,I as m,f as T,c as x,y as O,z as _,A as F,e as N,t as U,b as z,d as K,J as W,p as J,g as G,a as V,s as X,m as C,l as Y,q as Z,o as P}from"./site-map-viewer-79iBAeGs.js";import{o as ee}from"./create-apics-datatable-Ka6mwlgT.js";async function te(e){const t=e.payload||{},s=B(e.form?.schema||null).map(o=>{const c=o.fields.map(r=>{const n=t[r.name];if(n==null||n==="")return"";const l=A(r,n).replace(/<[^>]+>/g,"");return`<tr><th>${i(r.label)}</th><td>${i(l)}</td></tr>`}).filter(Boolean).join("");return c?`<h3 style="font-size:13px;margin:14px 0 6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b">${i(o.title)}</h3>
                <table class="meta" style="width:100%">${c}</table>`:""}).filter(Boolean).join("");ee({formCode:e.form?.code||"QMS-36",formTitle:e.form?.title||"Unified Application Form",documentNo:e.application_no||e.uuid,subtitle:e.project_title||void 0,meta:[{label:"Status",value:m(e.status||"—")},{label:"Classification",value:m(e.classification||"Unclassified")},{label:"Applicant",value:String(t.owner_name||e.applicant?.name||"—")},{label:"Location",value:e.project_location||"—"},{label:"Estimated cost",value:t.estimated_cost!=null&&t.estimated_cost!==""?T(t.estimated_cost).replace(/<[^>]+>/g,""):"—"},{label:"Floor area",value:t.floor_area!=null&&t.floor_area!==""?x(t.floor_area):"—"}],bodyHtml:s||"<p>No form answers recorded.</p>",signatures:[{role:"Prepared / Printed by"},{role:"Reviewed by (Evaluator)"},{role:"Noted by (Building Official)"}],windowTitle:`${e.form?.code||"QMS-36"} · ${e.application_no||""}`})}async function ae(e){if(!e.classification)return b("Classify the application before generating a routing slip."),null;if(!await L("Generate routing slip?","Creates department review steps from the matching QMS-61/62 routing template."))return null;try{const{data:t}=await window.axios.post(`/api/v1/staff/applications/${e.uuid}/routing-slip`);return $(`Routing slip ${t.data?.slip_no||""} generated`),(await window.axios.get(`/api/v1/staff/applications/${e.uuid}`)).data.data}catch(t){return b(t?.response?.data?.message||"Routing failed"),null}}async function se(e){if(await L("Start evaluation?","Opens or continues the single draft evaluation sheet and starts the processing timer."))try{await window.axios.post(`/api/v1/staff/applications/${e.uuid}/timer/start`).catch(()=>null);const{data:t}=await window.axios.post(`/api/v1/staff/applications/${e.uuid}/evaluations`,{findings:{completeness:[{code:"APP_FORM",label:"Unified application form complete (QMS-36)",status:"na"}],technical:[]},remarks:"Evaluation started from application detail modal"});$(t.data?.status==="draft"?"Evaluation draft ready":"Evaluation started")}catch(t){b(t?.response?.data?.message||"Unable to start evaluation")}}async function ie(e){try{await window.axios.post(`/api/v1/staff/applications/${e.uuid}/timer/start`),$("Timer started")}catch(t){b(t?.response?.data?.message||"Timer start failed")}}async function ne(e,t){if(!e.applicant?.uuid){b("Applicant account is not linked to this filing.");return}const a=await Q({title:"Request document correction?",text:`Notify the applicant that “${m(t)}” must be uploaded or corrected.`,inputLabel:"Message to applicant",inputPlaceholder:"Please upload a clear PDF of the required document…",confirmButtonText:"Send request",minLength:10});if(a)try{await window.axios.post("/api/v1/staff/notifications/send",{user_uuid:e.applicant.uuid,template_code:"document.correction_requested",message:`Document request for ${e.application_no||"your application"} — ${m(t)}: ${a}`,vars:{name:e.applicant.name||"Applicant",application_no:e.application_no||"",document:m(t),message:`Please upload/correct “${m(t)}” for ${e.application_no||"your application"}. ${a}`},url:"/applications"}),$("Correction request sent (in-app + email)")}catch(s){b(s?.response?.data?.message||"Unable to send request")}}const q=[{role:"Architect",nameKey:"architect_name",prcKey:"architect_prc"},{role:"Civil / Structural Engineer",nameKey:"engineer_name",prcKey:"engineer_prc"},{role:"Professional Electrical Engineer",nameKey:"electrical_engineer_name",prcKey:"electrical_prc"},{role:"Sanitary Engineer / Master Plumber",nameKey:"sanitary_engineer_name",prcKey:"sanitary_prc"},{role:"Mechanical Engineer",nameKey:"mechanical_engineer_name",prcKey:"mechanical_prc"}];function le(e){const t=O(e.submitted_at,e.classification);return`
        <div class="d-flex flex-wrap align-items-center gap-2">
            ${_(e.status||"",{pulse:!0})}
            ${F(e.classification)}
            <span class="text-muted small">${i(e.form?.code||"—")}</span>
        </div>
        ${t?`<div class="mt-1 small fw-medium text-warning-emphasis d-flex align-items-center gap-1">
                    <span>RA 11032</span>${t}
                   </div>`:""}
    `}function oe(e){const t=M("evaluations.manage"),a=String(e.status||"").toLowerCase(),s=a==="submitted"||a==="under_evaluation",o=t&&s,c=t&&s,r=(e.routing_slips||[]).length>0,n=!!e.classification;return`
        <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-detail-action="print" title="Print QMS-36 summary">
            <i class="ri-printer-line align-bottom me-1"></i><span class="d-none d-md-inline">Print QMS-36</span>
        </button>
        ${o?`<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="routing"
                    ${n?"":'disabled title="Classify the application first"'}
                    ${r?'title="A routing slip already exists — generate another if needed"':""}>
                    <i class="ri-git-branch-line align-bottom me-1"></i><span class="d-none d-md-inline">${r?"Regenerate slip":"Generate Routing Slip"}</span>
                   </button>`:""}
        ${c?`<button type="button" class="btn btn-sm btn-primary" data-ops-detail-action="evaluate">
                    <i class="ri-play-circle-line align-bottom me-1"></i><span class="d-none d-md-inline">Start Evaluation</span>
                   </button>`:""}
    `}function re(e="overview"){return`<ul class="nav nav-tabs nav-tabs-custom apics-nav-tabs mb-0 flex-wrap" role="tablist">
        ${[{id:"overview",label:"Overview & Location",icon:"ri-map-pin-line"},{id:"technical",label:"Technical (QMS-36)",icon:"ri-building-2-line"},{id:"documents",label:"Document Vault",icon:"ri-folder-2-line"},{id:"routing",label:"Routing & Reviews",icon:"ri-organization-chart"},{id:"inspection",label:"Inspection Forms",icon:"ri-clipboard-line"}].map(a=>`<li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link text-nowrap${e===a.id?" active":""}"
                        data-ops-detail-tab="${a.id}"
                        role="tab"
                        aria-selected="${e===a.id?"true":"false"}">
                        <i class="${a.icon}" aria-hidden="true"></i>${i(a.label)}
                    </button>
                </li>`).join("")}
    </ul>`}function ce(e){const t=e.payload||{},a=String(t.barangay||"—"),s=String(t.lot_number||"—"),o=String(t.block_number||"—"),c=String(t.street_address||e.project_location||"—"),r=J({latitude:e.latitude,longitude:e.longitude}),n=r?`${r.lat.toFixed(6)}, ${r.lng.toFixed(6)}`:"No coordinates pinned",l=r!=null?`<div class="d-flex flex-wrap gap-1 mt-2">
                <a class="btn btn-sm btn-primary" href="${i(G(r.lat,r.lng))}" target="_blank" rel="noopener noreferrer">
                    <i class="ri-guide-line align-bottom me-1"></i>Get Directions
                </a>
                <a class="btn btn-sm btn-soft-secondary" href="${i(V(r.lat,r.lng,e.project_title))}" target="_blank" rel="noopener noreferrer">
                    Open in Maps
                </a>
               </div>`:"";return`
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Site location</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Site address</p>
                            <p class="fw-medium mb-0">${i(c)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Barangay</p>
                            <p class="mb-0">${i(a)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Lot</p>
                            <p class="mb-0">${i(s)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Block</p>
                            <p class="mb-0">${i(o)}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Coordinates</p>
                            <p class="font-monospace small mb-0">${i(n)}</p>
                        </div>
                    </div>
                    ${l}
                </div>
            </div>
            <div class="col-lg-5">
                ${X({latitude:e.latitude,longitude:e.longitude,address:e.project_location,label:e.project_title||e.application_no||"Project site"})}
            </div>
        </div>
    `}function de(e){const t=e.payload||{},a=String(t.owner_name||e.applicant?.name||"—"),s=String(t.owner_email||e.applicant?.email||""),o=String(t.owner_contact||e.applicant?.phone||"—");return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Application No.</p>
                <p class="fw-semibold mb-0">${i(e.application_no||"—")}</p>
            </div>
            <div class="col-md-5 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Project</p>
                <p class="fw-medium mb-0">${i(e.project_title||"—")}</p>
            </div>
            <div class="col-md-4 col-sm-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Permit form</p>
                <p class="mb-0">${i(e.form?.code||"—")}
                    <span class="text-muted small">${i(e.form?.title||"")}</span>
                </p>
            </div>
        </div>

        ${ce(e)}

        <div class="row g-3 mt-1">
            <div class="col-md-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Applicant / owner</p>
                    <p class="fw-semibold mb-1">${i(a)}</p>
                    <p class="small mb-1">${N(s||null)}</p>
                    <p class="small text-muted mb-0">${i(o)}</p>
                </div>
            </div>
            <div class="col-md-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Timeline</p>
                    ${U(z(e))}
                </div>
            </div>
        </div>
    `}function me(e){const t=q.map(a=>{const s=e[a.nameKey],o=e[a.prcKey];return(s==null||s==="")&&(o==null||o==="")?"":`<div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                    <span class="text-muted fs-11 text-uppercase">${i(a.role)}</span>
                    ${o?'<span class="badge border bg-success-subtle text-success border-success-subtle">PRC Active</span>':'<span class="badge border bg-secondary-subtle text-secondary">No PRC on file</span>'}
                </div>
                <p class="fw-semibold mb-1">${i(String(s||"—"))}</p>
                <p class="small text-muted mb-0 font-monospace">PRC ${i(String(o||"—"))}</p>
            </div>
        </div>`}).filter(Boolean);return t.length?`<div class="row g-3">${t.join("")}</div>`:'<p class="text-muted mb-0">No design professionals recorded on this filing.</p>'}function pe(e){const t=e.payload||{},a=B(e.form?.schema||null),s=new Set(q.flatMap(d=>[d.nameKey,d.prcKey])),o=a.filter(d=>!/design professional/i.test(d.title)).map(d=>{const f=d.fields.filter(u=>!s.has(u.name)).map(u=>{const p=t[u.name];return p==null||p===""?"":`<div class="col-sm-6 col-lg-4">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">${i(u.label)}</p>
                        <p class="mb-0 text-break">${A(u,p)}</p>
                    </div>`}).filter(Boolean).join("");return f?`<div class="mb-3">
                <h6 class="fs-13 text-uppercase text-muted mb-2">${i(d.title)}</h6>
                <div class="row g-3">${f}</div>
            </div>`:""}).filter(Boolean).join(""),c=t.estimated_cost,r=t.lot_area,n=t.floor_area;return`
        ${`
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Estimated project cost</p>
                    <p class="font-monospace fw-bold fs-5 mb-0">${c!=null&&c!==""?T(c):"—"}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Lot area</p>
                    <p class="font-monospace fw-semibold mb-0">${r!=null&&r!==""?x(r):"—"}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Total floor area</p>
                    <p class="font-monospace fw-semibold mb-0">${n!=null&&n!==""?x(n):"—"}</p>
                </div>
            </div>
        </div>
    `}
        <div class="border rounded p-3 mb-4">
            ${o||'<p class="text-muted mb-0">No form field answers recorded.</p>'}
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Signatories &amp; professionals</h6>
        <div class="border rounded p-3">${me(t)}</div>
    `}function ue(e){const t=e.documents||[],a=e.form?.required_attachments||[],s=new Map(t.map(n=>[n.label,n])),o=t.map(n=>n.label).filter(n=>!a.includes(n)),c=[...a,...o];return c.length?`
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
                        <tbody>${c.map(n=>{const l=s.get(n),d=a.includes(n),f=!!(l&&(l.is_pdf||l.is_image||(l.mime_type||"").includes("pdf")||(l.mime_type||"").startsWith("image/")||l.original_name.toLowerCase().endsWith(".pdf")));let u;l?u='<span class="badge bg-success-subtle text-success border border-success-subtle">Uploaded</span>':d?u='<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">Mandatory deficient</span>':u='<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Optional</span>';const p=l?`<button type="button" class="btn btn-sm btn-soft-primary"
                        data-preview-doc="${i(l.uuid)}"
                        data-doc-label="${i(l.label)}"
                        data-doc-name="${i(l.original_name)}"
                        data-doc-mime="${i(l.mime_type||"")}"
                        data-doc-pdf="${l.is_pdf||(l.mime_type||"").includes("pdf")||l.original_name.toLowerCase().endsWith(".pdf")?"1":"0"}"
                        data-doc-image="${l.is_image||(l.mime_type||"").startsWith("image/")?"1":"0"}"
                        data-ops-inline-preview="1"
                        title="${f?"Preview in vault":"Open / download"}">
                        <i class="ri-eye-line"></i> Preview
                   </button>`:`<button type="button" class="btn btn-sm btn-outline-danger"
                        data-ops-detail-action="request-doc"
                        data-doc-label="${i(n)}"
                        ${e.applicant?.uuid?"":'disabled title="Applicant account not linked"'}>
                        <i class="ri-mail-send-line"></i> Request correction
                   </button>`;return`<tr>
                <td class="fw-medium">${i(m(n))}
                    ${d?'<div class="text-muted small">Mandatory</div>':'<div class="text-muted small">Optional</div>'}
                </td>
                <td class="small text-break">${l?`${i(K(l.original_name))}${l.size?` <span class="text-muted">(${i(W(l.size))})</span>`:""}`:"—"}</td>
                <td>${u}</td>
                <td class="text-end text-nowrap">${p}</td>
            </tr>`}).join("")}</tbody>
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
    `:'<p class="text-muted mb-0">No documents required or uploaded.</p>'}function be(e){return e?[{key:"qms-61",label:"QMS-61"},{key:"qms-62",label:"QMS-62"}].filter(a=>!!e[a.key]).map(a=>`<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${i(String(e[a.key]||""))}">
                <i class="ri-printer-line align-bottom me-1"></i>${i(a.label)}
            </button>`).join(""):""}function fe(e){return e?[{key:"qms-63",label:"QMS-63"},{key:"qms-64",label:"QMS-64"}].filter(a=>!!e[a.key]).map(a=>`<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${i(String(e[a.key]||""))}">
                <i class="ri-printer-line align-bottom me-1"></i>${i(a.label)}
            </button>`).join(""):""}function H(e){const t=e.evaluations||[];if(!t.length)return`<div class="border rounded p-3 mt-3 bg-light-subtle">
            <p class="fw-medium mb-1">No evaluation sheets yet</p>
            <p class="text-muted small mb-0">QMS-63/64 sheets appear here after an evaluator saves or decides from the Evaluation Queue.</p>
        </div>`;const a=t.filter(n=>String(n.status||"")==="decided").slice().sort((n,l)=>String(l.decided_at||l.created_at||"").localeCompare(String(n.decided_at||n.created_at||""))),s=t.filter(n=>String(n.status||"")==="draft").slice().sort((n,l)=>String(l.updated_at||l.created_at||"").localeCompare(String(n.updated_at||n.created_at||""))),o=s[0],c=[...a,...o?[o]:[]],r=Math.max(0,s.length-(o?1:0));return`${r>0?`<p class="text-muted small mb-2">Showing the latest draft (${r} older draft${r===1?"":"s"} hidden — open Evaluate to continue the active sheet).</p>`:""}${c.map(n=>{const l=n.findings||{},d=l.completeness||[],f=l.technical||[];return`<div class="border rounded p-3 mt-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">Evaluation sheet
                            <span class="badge bg-primary-subtle text-primary ms-1">${i(m(String(n.status||"")))}</span>
                            ${n.result?`<span class="badge bg-info-subtle text-info ms-1">${i(m(String(n.result)))}</span>`:""}
                        </p>
                        <p class="text-muted small mb-0">${i(n.evaluator?.name||"Evaluator")}${n.decided_at?` · decided ${i(S(n.decided_at))}`:""}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-1">${fe(n.print_urls)}</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">QMS-63 completeness (${d.length})</p>
                        ${k(d)}
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">QMS-64 technical (${f.length})</p>
                        ${k(f)}
                    </div>
                </div>
                ${l.overall_remarks||l.discipline_remarks||n.remarks?`<div class="mt-2 small">
                            ${l.overall_remarks?`<div><span class="text-muted">Overall:</span> ${i(l.overall_remarks)}</div>`:""}
                            ${l.discipline_remarks?`<div><span class="text-muted">Discipline:</span> ${i(l.discipline_remarks)}</div>`:""}
                            ${n.remarks?`<div><span class="text-muted">Decision:</span> ${i(n.remarks)}</div>`:""}
                           </div>`:""}
            </div>`}).join("")}`}function ve(e){const t=e.routing_slips||[],a=String(e.status||"").toLowerCase(),s=a==="submitted"||a==="under_evaluation",o=M("evaluations.manage")&&s;return t.length?`${t.map(r=>{const n=(r.steps||[]).map(l=>`<div class="apics-routing-step d-flex flex-wrap align-items-start gap-2 py-2 border-bottom">
                        <span class="badge bg-secondary-subtle text-secondary">${i(String(l.step_order??""))}</span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-medium">${i(l.label||"Step")}</div>
                            <div class="text-muted small">${i(l.department?.name||l.department?.code||"Department")}</div>
                            ${l.started_at||l.completed_at?`<div class="text-muted small mt-1">
                                        ${l.started_at?`Started ${i(m(String(l.status||"")))}`:""}
                                       </div>`:""}
                        </div>
                        <span class="badge bg-info-subtle text-info">${i(m(String(l.status||"")))}</span>
                    </div>`).join("");return`<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">${i(r.slip_no||"Routing slip")}
                            <span class="badge bg-primary-subtle text-primary ms-1">${i(m(String(r.status||"")))}</span>
                        </p>
                        ${r.template?`<p class="text-muted small mb-0">Template ${i(r.template.code||"")}${r.template.name?` — ${i(r.template.name)}`:""}</p>`:""}
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        ${be(r.print_urls)}
                        ${o?`<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="timer">
                                    <i class="ri-timer-line align-bottom me-1"></i> Start timer
                                   </button>`:""}
                    </div>
                </div>
                <div>${n||'<p class="text-muted mb-0">No steps</p>'}</div>
            </div>`}).join("")}${H(e)}`:`
            <div class="apics-routing-empty text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No routing slips initialized for this filing.</p>
                <p class="text-muted small mb-3">Initialize the standard multi-discipline technical review path based on QMS-61/62 templates (Architectural, Civil/Structural, Electrical, Mechanical, Sanitary, Fire).</p>
                ${o?`<button type="button" class="btn btn-primary btn-sm" data-ops-detail-action="routing" ${e.classification?"":'disabled title="Classify first"'}>
                            <i class="ri-git-branch-line align-bottom me-1"></i> Generate Routing Slips
                           </button>`:s?'<p class="text-muted small mb-0">Ask an evaluator with routing permission to generate the slip.</p>':`<p class="text-muted small mb-0">Routing actions are closed for ${i(m(a||"this"))} filings.</p>`}
            </div>
            ${H(e)}
        `}function ge(e){const t=String(e||"").toLowerCase();return{joint:"Joint",joint_structural:"Joint · Structural",joint_architectural:"Joint · Architectural",joint_electrical:"Joint · Electrical",joint_sanitary:"Joint · Sanitary",joint_mechanical:"Joint · Mechanical",joint_fire_safety:"Joint · Fire Safety",electrical:"Electrical (DPWH 77-006-E)",final:"Final"}[t]||m(e||"Inspection")}function S(e){if(!e)return"—";try{return new Date(e).toLocaleString("en-US",{month:"short",day:"numeric",year:"numeric",hour:"numeric",minute:"2-digit"})}catch{return e}}function he(e,t){return e?[{key:"qms-38",label:"QMS-38",show:!!e["qms-38"]},{key:"qms-39",label:"QMS-39",show:!!e["qms-39"]},{key:"o-03",label:"O-03",show:!!e["o-03"]},{key:"qms-65",label:"QMS-65",show:!!e["qms-65"]},{key:"dpwh-77-006-e",label:"77-006-E",show:!!e["dpwh-77-006-e"]&&t}].filter(s=>s.show).map(s=>`<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${i(String(e[s.key]||""))}">
                <i class="ri-printer-line align-bottom me-1"></i>${i(s.label)}
            </button>`).join(""):""}function k(e){return e?.length?`<div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Code</th><th>Item</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody>${e.map(a=>{const s=String(a.status||"na").toLowerCase(),o=s==="ok"?"bg-success-subtle text-success":s==="fail"?"bg-danger-subtle text-danger":"bg-secondary-subtle text-secondary";return`<tr>
                <td class="small font-monospace">${i(a.code||"—")}</td>
                <td class="small">${i(a.label||"—")}</td>
                <td><span class="badge ${o}">${i(s.toUpperCase())}</span></td>
                <td class="small text-muted">${i(a.remarks||"")}</td>
            </tr>`}).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted small mb-0">No QMS-65 checklist items recorded.</p>'}function we(e){const t=e.inspections||[],a=M("inspections.manage");return t.length?t.map(s=>{const o=s.schedule_sheet||{},c=s.inspector_notes||{},r=s.team_inspectors||[],n=s.electrical_form||{},l=!!(s.requires_electrical_form||n.result&&n.result!=="na"||String(s.type||"").toLowerCase().includes("electrical")),d=Array.isArray(o.disciplines)?o.disciplines.join(", "):"—",f=r.length?`<ul class="list-unstyled mb-0 small">${r.map(p=>`<li><span class="fw-medium">${i(p.name||"—")}</span>
                                <span class="text-muted"> · ${i(p.role||"Member")}${p.discipline?` · ${i(p.discipline)}`:""}</span></li>`).join("")}</ul>`:`<p class="small text-muted mb-0">${i(s.inspector?.name||"No team recorded")}</p>`,u=l?`<div class="border rounded p-3 mb-0">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">DPWH 77-006-E</p>
                    <div class="row g-2 small">
                        ${[["Service entrance",n.service_entrance],["Grounding",n.grounding],["Panel boards",n.panel_boards],["Wiring methods",n.wiring_methods],["Fixtures / devices",n.fixtures_devices],["Load schedule",n.load_schedule],["Result",n.result||n.status]].map(([p,I])=>`<div class="col-sm-6 col-md-4">
                                    <span class="text-muted">${i(String(p))}</span>
                                    <div class="fw-medium">${i(String(I||"na").toUpperCase())}</div>
                                </div>`).join("")}
                    </div>
                    ${n.remarks?`<p class="small mt-2 mb-0"><span class="text-muted">Remarks:</span> ${i(String(n.remarks))}</p>`:""}
                   </div>`:"";return`<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <p class="fw-semibold mb-1 font-monospace">${i(s.inspection_no||"Inspection")}
                            ${_(String(s.status||""),{pulse:s.status==="scheduled"||s.status==="in_progress"})}
                            ${s.result?_(String(s.result)):""}
                        </p>
                        <p class="text-muted small mb-0">${i(ge(s.type))}
                            · Scheduled ${i(S(s.scheduled_at))}
                            ${s.completed_at?` · Completed ${i(S(s.completed_at))}`:""}
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-start">
                        ${he(s.print_urls,l)}
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-38 · Schedule</p>
                            <p class="small mb-1"><span class="text-muted">Purpose:</span> ${i(o.purpose||"—")}</p>
                            <p class="small mb-1"><span class="text-muted">Meeting point:</span> ${i(o.meeting_point||s.location||"—")}</p>
                            <p class="small mb-0"><span class="text-muted">Disciplines:</span> ${i(d)}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-39 · Team</p>
                            ${f}
                        </div>
                    </div>
                </div>

                <div class="border rounded p-3 mb-3">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">O-03 · Inspector notes</p>
                    <div class="row g-2 small mb-2">
                        <div class="col-md-6"><span class="text-muted">Weather / access:</span> ${i(c.weather||"—")}</div>
                        <div class="col-md-6"><span class="text-muted">Site conditions:</span> ${i(c.site_conditions||"—")}</div>
                    </div>
                    <p class="small mb-1"><span class="text-muted">Findings:</span> ${i(c.findings||s.notes||"—")}</p>
                    ${c.observed_defects?`<p class="small mb-1"><span class="text-muted">Defects:</span> ${i(c.observed_defects)}</p>`:""}
                    ${c.recommendations?`<p class="small mb-0"><span class="text-muted">Recommendations:</span> ${i(c.recommendations)}</p>`:""}
                </div>

                <div class="mb-3">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-65 · Compliance sheet</p>
                    ${k(s.compliance_sheet?.items)}
                    ${s.compliance_sheet?.overall_remarks?`<p class="small mt-2 mb-0"><span class="text-muted">Overall:</span> ${i(s.compliance_sheet.overall_remarks)}</p>`:""}
                </div>

                ${u}
            </div>`}).join(""):`
            <div class="text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No inspections scheduled for this filing.</p>
                <p class="text-muted small mb-3">QMS-38/39 schedule, O-03 notes, QMS-65 compliance sheet, and DPWH 77-006-E appear here after scheduling.</p>
                ${a?`<a class="btn btn-sm btn-primary" href="/admin/inspections">
                            <i class="ri-calendar-check-line align-bottom me-1"></i> Open Inspections
                           </a>`:""}
            </div>
        `}function ye(e,t="overview"){return`
        <div class="tab-content apics-app-detail__panels">
            <div class="tab-pane fade${t==="overview"?" show active":""}" data-ops-detail-panel="overview" role="tabpanel">
                ${de(e)}
            </div>
            <div class="tab-pane fade${t==="technical"?" show active":""}" data-ops-detail-panel="technical" role="tabpanel">
                ${pe(e)}
            </div>
            <div class="tab-pane fade${t==="documents"?" show active":""}" data-ops-detail-panel="documents" role="tabpanel">
                ${ue(e)}
            </div>
            <div class="tab-pane fade${t==="routing"?" show active":""}" data-ops-detail-panel="routing" role="tabpanel">
                ${ve(e)}
            </div>
            <div class="tab-pane fade${t==="inspection"?" show active":""}" data-ops-detail-panel="inspection" role="tabpanel">
                ${we(e)}
            </div>
        </div>
    `}function $e(e){if(!e){b("Print link unavailable. Reload details and try again.");return}window.open(e,"_blank","noopener,noreferrer")}let g=null,h=null,v=null,y="overview",w=null;function E(){w&&(URL.revokeObjectURL(w),w=null)}function xe(e){const t=document.getElementById("modal-ops-application-detail-label"),a=document.getElementById("ops-application-detail-header-meta"),s=document.getElementById("ops-application-detail-actions"),o=document.getElementById("ops-application-detail-tabnav"),c=document.getElementById("ops-application-detail-body");c&&(t&&(t.textContent=`Application details · ${e.application_no||""}`.trim()),a&&(a.innerHTML=le(e)),s&&(s.innerHTML=oe(e)),o&&(o.innerHTML=re(y)),c.innerHTML=ye(e,y))}function R(e){y=e,document.querySelectorAll("#ops-application-detail-tabnav [data-ops-detail-tab]").forEach(t=>{const a=t.dataset.opsDetailTab===e;t.classList.toggle("active",a),t.setAttribute("aria-selected",a?"true":"false")}),document.querySelectorAll("[data-ops-detail-panel]").forEach(t=>{const a=t.dataset.opsDetailPanel===e;t.classList.toggle("show",a),t.classList.toggle("active",a)}),e==="overview"&&(window.setTimeout(()=>g?.invalidateSize(),120),window.setTimeout(()=>g?.invalidateSize(),350))}async function _e(e,t){const a=document.getElementById("ops-doc-preview-pane"),s=t.dataset.previewDoc;if(!a||!s)return;const o={uuid:s,label:t.dataset.docLabel,original_name:t.dataset.docName||"document",mime_type:t.dataset.docMime||null,is_pdf:t.dataset.docPdf==="1",is_image:t.dataset.docImage==="1"};if(!o.is_pdf&&!o.is_image){await P(e,o);return}E(),a.innerHTML=`<div class="text-center text-muted p-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="small mb-0 mt-2">Loading preview…</p>
    </div>`;try{const r=(await window.axios.get(`/api/v1/applications/${e}/documents/${o.uuid}/file`,{responseType:"blob"})).data,n=o.mime_type||r.type||"application/octet-stream",l=r.type?r:new Blob([r],{type:n});w=URL.createObjectURL(l),o.is_pdf||n.includes("pdf")?a.innerHTML=`<iframe title="${i(o.original_name)}" src="${w}#toolbar=0&navpanes=0&view=FitH" class="apics-doc-vault-preview__frame"></iframe>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${i(o.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`:a.innerHTML=`<div class="apics-doc-vault-preview__image-wrap">
                    <img src="${w}" alt="${i(o.original_name)}" class="apics-doc-vault-preview__image">
                </div>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${i(o.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`,a.querySelector("[data-ops-open-lightbox]")?.addEventListener("click",()=>{P(e,o)})}catch(c){a.innerHTML=`<p class="text-danger small p-3 mb-0">${i(c?.response?.data?.message||"Preview failed")}</p>`,b(c?.response?.data?.message||"Preview failed")}}function Se(e){const t=document.getElementById("ops-map-expand-body"),a=document.getElementById("modal-ops-map-expand-label");t&&(h?.destroy(),h=null,a&&(a.textContent=`Project site · ${e.application_no||""}`.trim()),t.innerHTML=`<div class="p-3">${Z({latitude:e.latitude,longitude:e.longitude,address:e.project_location,label:e.project_title||e.application_no||"Project site"})}</div>`,j("modal-ops-map-expand"),window.setTimeout(()=>{h=C(t),h?.invalidateSize()},200),window.setTimeout(()=>h?.invalidateSize(),500))}function ke(e){const t=document.getElementById("modal-ops-application-detail");t&&(t.querySelectorAll("[data-ops-detail-tab]").forEach(a=>{a.addEventListener("click",()=>{const s=a.dataset.opsDetailTab;s&&R(s)})}),t.querySelectorAll("[data-ops-detail-action]").forEach(a=>{a.addEventListener("click",()=>{const s=a.dataset.opsDetailAction;!s||!v||(async()=>{if(s==="print"){await te(v);return}if(s==="routing"){const o=await ae(v);o&&(v=o,y="routing",D(o));return}if(s==="evaluate"){await se(v);return}if(s==="timer"){await ie(v);return}s==="request-doc"&&await ne(v,a.dataset.docLabel||"document")})()})}),t.querySelectorAll("[data-ops-expand-map]").forEach(a=>{a.addEventListener("click",s=>{s.preventDefault(),s.stopPropagation(),Se(e)})}),t.querySelectorAll('[data-ops-inline-preview="1"]').forEach(a=>{a.addEventListener("click",s=>{s.preventDefault(),s.stopPropagation(),_e(e.uuid,a)})}),Y(t,e.uuid),t.querySelectorAll("[data-ops-insp-print]").forEach(a=>{a.addEventListener("click",s=>{s.preventDefault(),s.stopPropagation(),$e(a.dataset.opsInspPrint||"")})}))}function D(e){g?.destroy(),g=null,E(),xe(e),ke(e);const t=document.getElementById("ops-application-detail-body");t&&(g=C(t)),window.setTimeout(()=>g?.invalidateSize(),200),window.setTimeout(()=>g?.invalidateSize(),500)}async function He(e,t){const a=document.getElementById("ops-application-detail-body"),s=document.getElementById("modal-ops-application-detail-label"),o=document.getElementById("ops-application-detail-header-meta"),c=document.getElementById("ops-application-detail-actions"),r=document.getElementById("ops-application-detail-tabnav");if(!e){b("Missing application reference. Reload the page and try again.");return}if(!a){b("Application detail viewer failed to load. Hard-refresh the page (Cmd/Ctrl+Shift+R).");return}g?.destroy(),g=null,h?.destroy(),h=null,E(),v=null;const n=t?.tab;y=n&&["overview","technical","documents","routing","inspection"].includes(n)?n:"overview",o&&(o.innerHTML=""),c&&(c.innerHTML=""),r&&(r.innerHTML=""),a.innerHTML=`<div class="text-center text-muted py-5">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></div>
        Loading application details…
    </div>`,s&&(s.textContent="Application details"),j("modal-ops-application-detail");try{const{data:l}=await window.axios.get(`/api/v1/staff/applications/${e}`),d=l.data;v=d,D(d),n==="inspection"&&R("inspection")}catch(l){a.innerHTML=`<p class="text-danger mb-0">${i(l?.response?.data?.message||"Unable to load application details")}</p>`,b(l?.response?.data?.message||"Unable to load application details")}}export{He as o};
