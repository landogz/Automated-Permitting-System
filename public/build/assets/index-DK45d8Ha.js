import{e as i,t as b,d as k,b as $,j as I,k as S,s as L}from"./app-Dl4Yi89w.js";import{n as j,h as H,I as u,f as B,c as x,y as D,z as _,A as F,e as N,t as O,b as Q,d as z,J as K,p as U,g as W,a as J,s as G,m as A,l as V,q as X,o as M}from"./site-map-viewer-Bf2Bb5hl.js";import{o as Y}from"./create-apics-datatable-D2pZGRop.js";async function Z(e){const t=e.payload||{},a=j(e.form?.schema||null).map(n=>{const o=n.fields.map(c=>{const l=t[c.name];if(l==null||l==="")return"";const r=H(c,l).replace(/<[^>]+>/g,"");return`<tr><th>${i(c.label)}</th><td>${i(r)}</td></tr>`}).filter(Boolean).join("");return o?`<h3 style="font-size:13px;margin:14px 0 6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b">${i(n.title)}</h3>
                <table class="meta" style="width:100%">${o}</table>`:""}).filter(Boolean).join("");Y({formCode:e.form?.code||"QMS-36",formTitle:e.form?.title||"Unified Application Form",documentNo:e.application_no||e.uuid,subtitle:e.project_title||void 0,meta:[{label:"Status",value:u(e.status||"—")},{label:"Classification",value:u(e.classification||"Unclassified")},{label:"Applicant",value:String(t.owner_name||e.applicant?.name||"—")},{label:"Location",value:e.project_location||"—"},{label:"Estimated cost",value:t.estimated_cost!=null&&t.estimated_cost!==""?B(t.estimated_cost).replace(/<[^>]+>/g,""):"—"},{label:"Floor area",value:t.floor_area!=null&&t.floor_area!==""?x(t.floor_area):"—"}],bodyHtml:a||"<p>No form answers recorded.</p>",signatures:[{role:"Prepared / Printed by"},{role:"Reviewed by (Evaluator)"},{role:"Noted by (Building Official)"}],windowTitle:`${e.form?.code||"QMS-36"} · ${e.application_no||""}`})}async function ee(e){if(!e.classification)return b("Classify the application before generating a routing slip."),null;if(!await k("Generate routing slip?","Creates department review steps from the matching QMS-61/62 routing template."))return null;try{const{data:t}=await window.axios.post(`/api/v1/staff/applications/${e.uuid}/routing-slip`);return $(`Routing slip ${t.data?.slip_no||""} generated`),(await window.axios.get(`/api/v1/staff/applications/${e.uuid}`)).data.data}catch(t){return b(t?.response?.data?.message||"Routing failed"),null}}async function te(e){if(await k("Start evaluation?","Creates an evaluation sheet and starts the statutory processing timer for this filing."))try{await window.axios.post(`/api/v1/staff/applications/${e.uuid}/timer/start`).catch(()=>null);const{data:t}=await window.axios.post(`/api/v1/staff/applications/${e.uuid}/evaluations`,{findings:[{item:"Completeness",status:"ok"}],remarks:"Evaluation started from application detail modal"});$(`Evaluation ${t.data?.uuid?"started":"created"}`)}catch(t){b(t?.response?.data?.message||"Unable to start evaluation")}}async function ae(e){try{await window.axios.post(`/api/v1/staff/applications/${e.uuid}/timer/start`),$("Timer started")}catch(t){b(t?.response?.data?.message||"Timer start failed")}}async function se(e,t){if(!e.applicant?.uuid){b("Applicant account is not linked to this filing.");return}const s=await I({title:"Request document correction?",text:`Notify the applicant that “${u(t)}” must be uploaded or corrected.`,inputLabel:"Message to applicant",inputPlaceholder:"Please upload a clear PDF of the required document…",confirmButtonText:"Send request",minLength:10});if(s)try{await window.axios.post("/api/v1/staff/notifications/send",{user_uuid:e.applicant.uuid,template_code:"document.correction_requested",message:`Document request for ${e.application_no||"your application"} — ${u(t)}: ${s}`,vars:{name:e.applicant.name||"Applicant",application_no:e.application_no||"",document:u(t),message:`Please upload/correct “${u(t)}” for ${e.application_no||"your application"}. ${s}`},url:"/applications"}),$("Correction request sent (in-app + email)")}catch(a){b(a?.response?.data?.message||"Unable to send request")}}const T=[{role:"Architect",nameKey:"architect_name",prcKey:"architect_prc"},{role:"Civil / Structural Engineer",nameKey:"engineer_name",prcKey:"engineer_prc"},{role:"Professional Electrical Engineer",nameKey:"electrical_engineer_name",prcKey:"electrical_prc"},{role:"Sanitary Engineer / Master Plumber",nameKey:"sanitary_engineer_name",prcKey:"sanitary_prc"},{role:"Mechanical Engineer",nameKey:"mechanical_engineer_name",prcKey:"mechanical_prc"}];function ie(e){const t=D(e.submitted_at,e.classification);return`
        <div class="d-flex flex-wrap align-items-center gap-2">
            ${_(e.status||"",{pulse:!0})}
            ${F(e.classification)}
            <span class="text-muted small">${i(e.form?.code||"—")}</span>
        </div>
        ${t?`<div class="mt-1 small fw-medium text-warning-emphasis d-flex align-items-center gap-1">
                    <span>RA 11032</span>${t}
                   </div>`:""}
    `}function ne(e){const t=S("evaluations.manage"),s=t,a=(e.routing_slips||[]).length>0,n=!!e.classification;return`
        <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-detail-action="print" title="Print QMS-36 summary">
            <i class="ri-printer-line align-bottom me-1"></i><span class="d-none d-md-inline">Print QMS-36</span>
        </button>
        ${s?`<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="routing"
                    ${n?"":'disabled title="Classify the application first"'}
                    ${a?'title="A routing slip already exists — generate another if needed"':""}>
                    <i class="ri-git-branch-line align-bottom me-1"></i><span class="d-none d-md-inline">${a?"Regenerate slip":"Generate Routing Slip"}</span>
                   </button>`:""}
        ${t?`<button type="button" class="btn btn-sm btn-primary" data-ops-detail-action="evaluate">
                    <i class="ri-play-circle-line align-bottom me-1"></i><span class="d-none d-md-inline">Start Evaluation</span>
                   </button>`:""}
    `}function le(e="overview"){return`<ul class="nav nav-tabs nav-tabs-custom nav-success mb-0 flex-wrap" role="tablist">
        ${[{id:"overview",label:"Overview & Location",icon:"ri-map-pin-line"},{id:"technical",label:"Technical (QMS-36)",icon:"ri-building-2-line"},{id:"documents",label:"Document Vault",icon:"ri-folder-2-line"},{id:"routing",label:"Routing & Reviews",icon:"ri-organization-chart"},{id:"inspection",label:"Inspection forms",icon:"ri-clipboard-line"}].map(s=>`<li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link text-nowrap${e===s.id?" active":""}"
                        data-ops-detail-tab="${s.id}"
                        role="tab"
                        aria-selected="${e===s.id?"true":"false"}">
                        <i class="${s.icon} align-bottom me-1"></i>${i(s.label)}
                    </button>
                </li>`).join("")}
    </ul>`}function oe(e){const t=e.payload||{},s=String(t.barangay||"—"),a=String(t.lot_number||"—"),n=String(t.block_number||"—"),o=String(t.street_address||e.project_location||"—"),c=U({latitude:e.latitude,longitude:e.longitude}),l=c?`${c.lat.toFixed(6)}, ${c.lng.toFixed(6)}`:"No coordinates pinned",r=c!=null?`<div class="d-flex flex-wrap gap-1 mt-2">
                <a class="btn btn-sm btn-primary" href="${i(W(c.lat,c.lng))}" target="_blank" rel="noopener noreferrer">
                    <i class="ri-guide-line align-bottom me-1"></i>Get Directions
                </a>
                <a class="btn btn-sm btn-soft-secondary" href="${i(J(c.lat,c.lng,e.project_title))}" target="_blank" rel="noopener noreferrer">
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
                            <p class="fw-medium mb-0">${i(o)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Barangay</p>
                            <p class="mb-0">${i(s)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Lot</p>
                            <p class="mb-0">${i(a)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Block</p>
                            <p class="mb-0">${i(n)}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Coordinates</p>
                            <p class="font-monospace small mb-0">${i(l)}</p>
                        </div>
                    </div>
                    ${r}
                </div>
            </div>
            <div class="col-lg-5">
                ${G({latitude:e.latitude,longitude:e.longitude,address:e.project_location,label:e.project_title||e.application_no||"Project site"})}
            </div>
        </div>
    `}function re(e){const t=e.payload||{},s=String(t.owner_name||e.applicant?.name||"—"),a=String(t.owner_email||e.applicant?.email||""),n=String(t.owner_contact||e.applicant?.phone||"—");return`
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

        ${oe(e)}

        <div class="row g-3 mt-1">
            <div class="col-md-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Applicant / owner</p>
                    <p class="fw-semibold mb-1">${i(s)}</p>
                    <p class="small mb-1">${N(a||null)}</p>
                    <p class="small text-muted mb-0">${i(n)}</p>
                </div>
            </div>
            <div class="col-md-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Timeline</p>
                    ${O(Q(e))}
                </div>
            </div>
        </div>
    `}function ce(e){const t=T.map(s=>{const a=e[s.nameKey],n=e[s.prcKey];return(a==null||a==="")&&(n==null||n==="")?"":`<div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                    <span class="text-muted fs-11 text-uppercase">${i(s.role)}</span>
                    ${n?'<span class="badge border bg-success-subtle text-success border-success-subtle">PRC Active</span>':'<span class="badge border bg-secondary-subtle text-secondary">No PRC on file</span>'}
                </div>
                <p class="fw-semibold mb-1">${i(String(a||"—"))}</p>
                <p class="small text-muted mb-0 font-monospace">PRC ${i(String(n||"—"))}</p>
            </div>
        </div>`}).filter(Boolean);return t.length?`<div class="row g-3">${t.join("")}</div>`:'<p class="text-muted mb-0">No design professionals recorded on this filing.</p>'}function de(e){const t=e.payload||{},s=j(e.form?.schema||null),a=new Set(T.flatMap(d=>[d.nameKey,d.prcKey])),n=s.filter(d=>!/design professional/i.test(d.title)).map(d=>{const h=d.fields.filter(p=>!a.has(p.name)).map(p=>{const m=t[p.name];return m==null||m===""?"":`<div class="col-sm-6 col-lg-4">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">${i(p.label)}</p>
                        <p class="mb-0 text-break">${H(p,m)}</p>
                    </div>`}).filter(Boolean).join("");return h?`<div class="mb-3">
                <h6 class="fs-13 text-uppercase text-muted mb-2">${i(d.title)}</h6>
                <div class="row g-3">${h}</div>
            </div>`:""}).filter(Boolean).join(""),o=t.estimated_cost,c=t.lot_area,l=t.floor_area;return`
        ${`
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Estimated project cost</p>
                    <p class="font-monospace fw-bold fs-5 mb-0">${o!=null&&o!==""?B(o):"—"}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Lot area</p>
                    <p class="font-monospace fw-semibold mb-0">${c!=null&&c!==""?x(c):"—"}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Total floor area</p>
                    <p class="font-monospace fw-semibold mb-0">${l!=null&&l!==""?x(l):"—"}</p>
                </div>
            </div>
        </div>
    `}
        <div class="border rounded p-3 mb-4">
            ${n||'<p class="text-muted mb-0">No form field answers recorded.</p>'}
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Signatories &amp; professionals</h6>
        <div class="border rounded p-3">${ce(t)}</div>
    `}function me(e){const t=e.documents||[],s=e.form?.required_attachments||[],a=new Map(t.map(l=>[l.label,l])),n=t.map(l=>l.label).filter(l=>!s.includes(l)),o=[...s,...n];return o.length?`
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
                        <tbody>${o.map(l=>{const r=a.get(l),d=s.includes(l),h=!!(r&&(r.is_pdf||r.is_image||(r.mime_type||"").includes("pdf")||(r.mime_type||"").startsWith("image/")||r.original_name.toLowerCase().endsWith(".pdf")));let p;r?p='<span class="badge bg-success-subtle text-success border border-success-subtle">Uploaded</span>':d?p='<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">Mandatory deficient</span>':p='<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Optional</span>';const m=r?`<button type="button" class="btn btn-sm btn-soft-primary"
                        data-preview-doc="${i(r.uuid)}"
                        data-doc-label="${i(r.label)}"
                        data-doc-name="${i(r.original_name)}"
                        data-doc-mime="${i(r.mime_type||"")}"
                        data-doc-pdf="${r.is_pdf||(r.mime_type||"").includes("pdf")||r.original_name.toLowerCase().endsWith(".pdf")?"1":"0"}"
                        data-doc-image="${r.is_image||(r.mime_type||"").startsWith("image/")?"1":"0"}"
                        data-ops-inline-preview="1"
                        title="${h?"Preview in vault":"Open / download"}">
                        <i class="ri-eye-line"></i> Preview
                   </button>`:`<button type="button" class="btn btn-sm btn-outline-danger"
                        data-ops-detail-action="request-doc"
                        data-doc-label="${i(l)}"
                        ${e.applicant?.uuid?"":'disabled title="Applicant account not linked"'}>
                        <i class="ri-mail-send-line"></i> Request correction
                   </button>`;return`<tr>
                <td class="fw-medium">${i(u(l))}
                    ${d?'<div class="text-muted small">Mandatory</div>':'<div class="text-muted small">Optional</div>'}
                </td>
                <td class="small text-break">${r?`${i(z(r.original_name))}${r.size?` <span class="text-muted">(${i(K(r.size))})</span>`:""}`:"—"}</td>
                <td>${p}</td>
                <td class="text-end text-nowrap">${m}</td>
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
    `:'<p class="text-muted mb-0">No documents required or uploaded.</p>'}function pe(e){const t=e.routing_slips||[],s=S("evaluations.manage");return t.length?t.map(a=>{const n=(a.steps||[]).map(o=>`<div class="apics-routing-step d-flex flex-wrap align-items-start gap-2 py-2 border-bottom">
                        <span class="badge bg-secondary-subtle text-secondary">${i(String(o.step_order??""))}</span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-medium">${i(o.label||"Step")}</div>
                            <div class="text-muted small">${i(o.department?.name||o.department?.code||"Department")}</div>
                            ${o.started_at||o.completed_at?`<div class="text-muted small mt-1">
                                        ${o.started_at?`Started ${i(u(String(o.status||"")))}`:""}
                                       </div>`:""}
                        </div>
                        <span class="badge bg-info-subtle text-info">${i(u(String(o.status||"")))}</span>
                    </div>`).join("");return`<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">${i(a.slip_no||"Routing slip")}
                            <span class="badge bg-primary-subtle text-primary ms-1">${i(u(String(a.status||"")))}</span>
                        </p>
                        ${a.template?`<p class="text-muted small mb-0">Template ${i(a.template.code||"")}${a.template.name?` — ${i(a.template.name)}`:""}</p>`:""}
                    </div>
                    ${s?`<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="timer">
                                <i class="ri-timer-line align-bottom me-1"></i> Start timer
                               </button>`:""}
                </div>
                <div>${n||'<p class="text-muted mb-0">No steps</p>'}</div>
            </div>`}).join(""):`
            <div class="apics-routing-empty text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No routing slips initialized for this filing.</p>
                <p class="text-muted small mb-3">Initialize the standard multi-discipline technical review path based on QMS-61/62 templates (Architectural, Civil/Structural, Electrical, Mechanical, Sanitary, Fire).</p>
                ${s?`<button type="button" class="btn btn-primary btn-sm" data-ops-detail-action="routing" ${e.classification?"":'disabled title="Classify first"'}>
                            <i class="ri-git-branch-line align-bottom me-1"></i> Generate Routing Slips
                           </button>`:'<p class="text-muted small mb-0">Ask an evaluator with routing permission to generate the slip.</p>'}
            </div>
        `}function ue(e){const t=String(e||"").toLowerCase();return{joint:"Joint",joint_structural:"Joint · Structural",joint_architectural:"Joint · Architectural",joint_electrical:"Joint · Electrical",joint_sanitary:"Joint · Sanitary",joint_mechanical:"Joint · Mechanical",joint_fire_safety:"Joint · Fire Safety",electrical:"Electrical (DPWH 77-006-E)",final:"Final"}[t]||u(e||"Inspection")}function P(e){if(!e)return"—";try{return new Date(e).toLocaleString("en-US",{month:"short",day:"numeric",year:"numeric",hour:"numeric",minute:"2-digit"})}catch{return e}}function be(e,t){return e?[{key:"qms-38",label:"QMS-38",show:!!e["qms-38"]},{key:"qms-39",label:"QMS-39",show:!!e["qms-39"]},{key:"o-03",label:"O-03",show:!!e["o-03"]},{key:"qms-65",label:"QMS-65",show:!!e["qms-65"]},{key:"dpwh-77-006-e",label:"77-006-E",show:!!e["dpwh-77-006-e"]&&t}].filter(a=>a.show).map(a=>`<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${i(String(e[a.key]||""))}">
                <i class="ri-printer-line align-bottom me-1"></i>${i(a.label)}
            </button>`).join(""):""}function fe(e){return e?.length?`<div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Code</th><th>Item</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody>${e.map(s=>{const a=String(s.status||"na").toLowerCase(),n=a==="ok"?"bg-success-subtle text-success":a==="fail"?"bg-danger-subtle text-danger":"bg-secondary-subtle text-secondary";return`<tr>
                <td class="small font-monospace">${i(s.code||"—")}</td>
                <td class="small">${i(s.label||"—")}</td>
                <td><span class="badge ${n}">${i(a.toUpperCase())}</span></td>
                <td class="small text-muted">${i(s.remarks||"")}</td>
            </tr>`}).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted small mb-0">No QMS-65 checklist items recorded.</p>'}function ve(e){const t=e.inspections||[],s=S("inspections.manage");return t.length?t.map(a=>{const n=a.schedule_sheet||{},o=a.inspector_notes||{},c=a.team_inspectors||[],l=a.electrical_form||{},r=!!(a.requires_electrical_form||l.result&&l.result!=="na"||String(a.type||"").toLowerCase().includes("electrical")),d=Array.isArray(n.disciplines)?n.disciplines.join(", "):"—",h=c.length?`<ul class="list-unstyled mb-0 small">${c.map(m=>`<li><span class="fw-medium">${i(m.name||"—")}</span>
                                <span class="text-muted"> · ${i(m.role||"Member")}${m.discipline?` · ${i(m.discipline)}`:""}</span></li>`).join("")}</ul>`:`<p class="small text-muted mb-0">${i(a.inspector?.name||"No team recorded")}</p>`,p=r?`<div class="border rounded p-3 mb-0">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">DPWH 77-006-E</p>
                    <div class="row g-2 small">
                        ${[["Service entrance",l.service_entrance],["Grounding",l.grounding],["Panel boards",l.panel_boards],["Wiring methods",l.wiring_methods],["Fixtures / devices",l.fixtures_devices],["Load schedule",l.load_schedule],["Result",l.result||l.status]].map(([m,R])=>`<div class="col-sm-6 col-md-4">
                                    <span class="text-muted">${i(String(m))}</span>
                                    <div class="fw-medium">${i(String(R||"na").toUpperCase())}</div>
                                </div>`).join("")}
                    </div>
                    ${l.remarks?`<p class="small mt-2 mb-0"><span class="text-muted">Remarks:</span> ${i(String(l.remarks))}</p>`:""}
                   </div>`:"";return`<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <p class="fw-semibold mb-1 font-monospace">${i(a.inspection_no||"Inspection")}
                            ${_(String(a.status||""),{pulse:a.status==="scheduled"||a.status==="in_progress"})}
                            ${a.result?_(String(a.result)):""}
                        </p>
                        <p class="text-muted small mb-0">${i(ue(a.type))}
                            · Scheduled ${i(P(a.scheduled_at))}
                            ${a.completed_at?` · Completed ${i(P(a.completed_at))}`:""}
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-start">
                        ${be(a.print_urls,r)}
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-38 · Schedule</p>
                            <p class="small mb-1"><span class="text-muted">Purpose:</span> ${i(n.purpose||"—")}</p>
                            <p class="small mb-1"><span class="text-muted">Meeting point:</span> ${i(n.meeting_point||a.location||"—")}</p>
                            <p class="small mb-0"><span class="text-muted">Disciplines:</span> ${i(d)}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-39 · Team</p>
                            ${h}
                        </div>
                    </div>
                </div>

                <div class="border rounded p-3 mb-3">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">O-03 · Inspector notes</p>
                    <div class="row g-2 small mb-2">
                        <div class="col-md-6"><span class="text-muted">Weather / access:</span> ${i(o.weather||"—")}</div>
                        <div class="col-md-6"><span class="text-muted">Site conditions:</span> ${i(o.site_conditions||"—")}</div>
                    </div>
                    <p class="small mb-1"><span class="text-muted">Findings:</span> ${i(o.findings||a.notes||"—")}</p>
                    ${o.observed_defects?`<p class="small mb-1"><span class="text-muted">Defects:</span> ${i(o.observed_defects)}</p>`:""}
                    ${o.recommendations?`<p class="small mb-0"><span class="text-muted">Recommendations:</span> ${i(o.recommendations)}</p>`:""}
                </div>

                <div class="mb-3">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">QMS-65 · Compliance sheet</p>
                    ${fe(a.compliance_sheet?.items)}
                    ${a.compliance_sheet?.overall_remarks?`<p class="small mt-2 mb-0"><span class="text-muted">Overall:</span> ${i(a.compliance_sheet.overall_remarks)}</p>`:""}
                </div>

                ${p}
            </div>`}).join(""):`
            <div class="text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No inspections scheduled for this filing.</p>
                <p class="text-muted small mb-3">QMS-38/39 schedule, O-03 notes, QMS-65 compliance sheet, and DPWH 77-006-E appear here after scheduling.</p>
                ${s?`<a class="btn btn-sm btn-primary" href="/admin/inspections">
                            <i class="ri-calendar-check-line align-bottom me-1"></i> Open Inspections
                           </a>`:""}
            </div>
        `}function ge(e,t="overview"){return`
        <div class="tab-content apics-app-detail__panels">
            <div class="tab-pane fade${t==="overview"?" show active":""}" data-ops-detail-panel="overview" role="tabpanel">
                ${re(e)}
            </div>
            <div class="tab-pane fade${t==="technical"?" show active":""}" data-ops-detail-panel="technical" role="tabpanel">
                ${de(e)}
            </div>
            <div class="tab-pane fade${t==="documents"?" show active":""}" data-ops-detail-panel="documents" role="tabpanel">
                ${me(e)}
            </div>
            <div class="tab-pane fade${t==="routing"?" show active":""}" data-ops-detail-panel="routing" role="tabpanel">
                ${pe(e)}
            </div>
            <div class="tab-pane fade${t==="inspection"?" show active":""}" data-ops-detail-panel="inspection" role="tabpanel">
                ${ve(e)}
            </div>
        </div>
    `}function he(e){if(!e){b("Print link unavailable. Reload details and try again.");return}window.open(e,"_blank","noopener,noreferrer")}let v=null,g=null,f=null,y="overview",w=null;function E(){w&&(URL.revokeObjectURL(w),w=null)}function we(e){const t=document.getElementById("modal-ops-application-detail-label"),s=document.getElementById("ops-application-detail-header-meta"),a=document.getElementById("ops-application-detail-actions"),n=document.getElementById("ops-application-detail-tabnav"),o=document.getElementById("ops-application-detail-body");o&&(t&&(t.textContent=`Application details · ${e.application_no||""}`.trim()),s&&(s.innerHTML=ie(e)),a&&(a.innerHTML=ne(e)),n&&(n.innerHTML=le(y)),o.innerHTML=ge(e,y))}function C(e){y=e,document.querySelectorAll("#ops-application-detail-tabnav [data-ops-detail-tab]").forEach(t=>{const s=t.dataset.opsDetailTab===e;t.classList.toggle("active",s),t.setAttribute("aria-selected",s?"true":"false")}),document.querySelectorAll("[data-ops-detail-panel]").forEach(t=>{const s=t.dataset.opsDetailPanel===e;t.classList.toggle("show",s),t.classList.toggle("active",s)}),e==="overview"&&(window.setTimeout(()=>v?.invalidateSize(),120),window.setTimeout(()=>v?.invalidateSize(),350))}async function ye(e,t){const s=document.getElementById("ops-doc-preview-pane"),a=t.dataset.previewDoc;if(!s||!a)return;const n={uuid:a,label:t.dataset.docLabel,original_name:t.dataset.docName||"document",mime_type:t.dataset.docMime||null,is_pdf:t.dataset.docPdf==="1",is_image:t.dataset.docImage==="1"};if(!n.is_pdf&&!n.is_image){await M(e,n);return}E(),s.innerHTML=`<div class="text-center text-muted p-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="small mb-0 mt-2">Loading preview…</p>
    </div>`;try{const c=(await window.axios.get(`/api/v1/applications/${e}/documents/${n.uuid}/file`,{responseType:"blob"})).data,l=n.mime_type||c.type||"application/octet-stream",r=c.type?c:new Blob([c],{type:l});w=URL.createObjectURL(r),n.is_pdf||l.includes("pdf")?s.innerHTML=`<iframe title="${i(n.original_name)}" src="${w}#toolbar=0&navpanes=0&view=FitH" class="apics-doc-vault-preview__frame"></iframe>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${i(n.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`:s.innerHTML=`<div class="apics-doc-vault-preview__image-wrap">
                    <img src="${w}" alt="${i(n.original_name)}" class="apics-doc-vault-preview__image">
                </div>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${i(n.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`,s.querySelector("[data-ops-open-lightbox]")?.addEventListener("click",()=>{M(e,n)})}catch(o){s.innerHTML=`<p class="text-danger small p-3 mb-0">${i(o?.response?.data?.message||"Preview failed")}</p>`,b(o?.response?.data?.message||"Preview failed")}}function $e(e){const t=document.getElementById("ops-map-expand-body"),s=document.getElementById("modal-ops-map-expand-label");t&&(g?.destroy(),g=null,s&&(s.textContent=`Project site · ${e.application_no||""}`.trim()),t.innerHTML=`<div class="p-3">${X({latitude:e.latitude,longitude:e.longitude,address:e.project_location,label:e.project_title||e.application_no||"Project site"})}</div>`,L("modal-ops-map-expand"),window.setTimeout(()=>{g=A(t),g?.invalidateSize()},200),window.setTimeout(()=>g?.invalidateSize(),500))}function xe(e){const t=document.getElementById("modal-ops-application-detail");t&&(t.querySelectorAll("[data-ops-detail-tab]").forEach(s=>{s.addEventListener("click",()=>{const a=s.dataset.opsDetailTab;a&&C(a)})}),t.querySelectorAll("[data-ops-detail-action]").forEach(s=>{s.addEventListener("click",()=>{const a=s.dataset.opsDetailAction;!a||!f||(async()=>{if(a==="print"){await Z(f);return}if(a==="routing"){const n=await ee(f);n&&(f=n,y="routing",q(n));return}if(a==="evaluate"){await te(f);return}if(a==="timer"){await ae(f);return}a==="request-doc"&&await se(f,s.dataset.docLabel||"document")})()})}),t.querySelectorAll("[data-ops-expand-map]").forEach(s=>{s.addEventListener("click",a=>{a.preventDefault(),a.stopPropagation(),$e(e)})}),t.querySelectorAll('[data-ops-inline-preview="1"]').forEach(s=>{s.addEventListener("click",a=>{a.preventDefault(),a.stopPropagation(),ye(e.uuid,s)})}),V(t,e.uuid),t.querySelectorAll("[data-ops-insp-print]").forEach(s=>{s.addEventListener("click",a=>{a.preventDefault(),a.stopPropagation(),he(s.dataset.opsInspPrint||"")})}))}function q(e){v?.destroy(),v=null,E(),we(e),xe(e);const t=document.getElementById("ops-application-detail-body");t&&(v=A(t)),window.setTimeout(()=>v?.invalidateSize(),200),window.setTimeout(()=>v?.invalidateSize(),500)}async function Me(e,t){const s=document.getElementById("ops-application-detail-body"),a=document.getElementById("modal-ops-application-detail-label"),n=document.getElementById("ops-application-detail-header-meta"),o=document.getElementById("ops-application-detail-actions"),c=document.getElementById("ops-application-detail-tabnav");if(!s||!e){b("Application detail viewer is not available on this page.");return}v?.destroy(),v=null,g?.destroy(),g=null,E(),f=null;const l=t?.tab;y=l&&["overview","technical","documents","routing","inspection"].includes(l)?l:"overview",n&&(n.innerHTML=""),o&&(o.innerHTML=""),c&&(c.innerHTML=""),s.innerHTML=`<div class="text-center text-muted py-5">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></div>
        Loading application details…
    </div>`,a&&(a.textContent="Application details"),L("modal-ops-application-detail");try{const{data:r}=await window.axios.get(`/api/v1/staff/applications/${e}`),d=r.data;f=d,q(d),l==="inspection"&&C("inspection")}catch(r){s.innerHTML=`<p class="text-danger mb-0">${i(r?.response?.data?.message||"Unable to load application details")}</p>`,b(r?.response?.data?.message||"Unable to load application details")}}export{Me as o};
