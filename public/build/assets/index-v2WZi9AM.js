import{t as m,e as i,d as L,b as $,j as Q,k as M,s as j}from"./app-Bea-huIk.js";import{n as B,h as A,I as p,f as T,c as x,y as O,z as _,A as F,e as N,t as U,b as z,d as K,J as W,p as J,g as G,a as V,s as Y,m as C,l as X,q as Z,o as P}from"./site-map-viewer-f2cablRX.js";import{o as tt}from"./create-apics-datatable-CBJrbmST.js";async function et(t){if(!t){m("Print link unavailable. Reload and try again.");return}try{const{data:e}=await window.axios.get(t,{responseType:"text",headers:{Accept:"text/html, application/xhtml+xml"},skipLoading:!0}),a=typeof e=="string"?e:String(e??"");if(!a.trim()){m("Print document was empty.");return}const s=window.open("","_blank","width=960,height=780");if(!s){m("Pop-up blocked. Allow pop-ups for APICS to print.");return}s.opener=null,s.document.open(),s.document.write(a),s.document.close()}catch(e){const a=e?.response?.status;if(a===401){m("Sign in again to open official prints.");return}if(a===403){m("You are not allowed to open this print.");return}m(e?.response?.data?.message||"Unable to open print document.")}}async function at(t){const e=t.payload||{},s=B(t.form?.schema||null).map(o=>{const c=o.fields.map(r=>{const n=e[r.name];if(n==null||n==="")return"";const l=A(r,n).replace(/<[^>]+>/g,"");return`<tr><th>${i(r.label)}</th><td>${i(l)}</td></tr>`}).filter(Boolean).join("");return c?`<h3 style="font-size:13px;margin:14px 0 6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b">${i(o.title)}</h3>
                <table class="meta" style="width:100%">${c}</table>`:""}).filter(Boolean).join("");tt({formCode:t.form?.code||"QMS-36",formTitle:t.form?.title||"Unified Application Form",documentNo:t.application_no||t.uuid,subtitle:t.project_title||void 0,meta:[{label:"Status",value:p(t.status||"—")},{label:"Classification",value:p(t.classification||"Unclassified")},{label:"Applicant",value:String(e.owner_name||t.applicant?.name||"—")},{label:"Location",value:t.project_location||"—"},{label:"Estimated cost",value:e.estimated_cost!=null&&e.estimated_cost!==""?T(e.estimated_cost).replace(/<[^>]+>/g,""):"—"},{label:"Floor area",value:e.floor_area!=null&&e.floor_area!==""?x(e.floor_area):"—"}],bodyHtml:s||"<p>No form answers recorded.</p>",signatures:[{role:"Prepared / Printed by"},{role:"Reviewed by (Evaluator)"},{role:"Noted by (Building Official)"}],windowTitle:`${t.form?.code||"QMS-36"} · ${t.application_no||""}`})}async function st(t){if(!t.classification)return m("Classify the application before generating a routing slip."),null;if(!await L("Generate routing slip?","Creates department review steps from the matching QMS-61/62 routing template."))return null;try{const{data:e}=await window.axios.post(`/api/v1/staff/applications/${t.uuid}/routing-slip`);return $(`Routing slip ${e.data?.slip_no||""} generated`),(await window.axios.get(`/api/v1/staff/applications/${t.uuid}`)).data.data}catch(e){return m(e?.response?.data?.message||"Routing failed"),null}}async function it(t){if(await L("Start evaluation?","Opens or continues the single draft evaluation sheet and starts the processing timer."))try{await window.axios.post(`/api/v1/staff/applications/${t.uuid}/timer/start`).catch(()=>null);const{data:e}=await window.axios.post(`/api/v1/staff/applications/${t.uuid}/evaluations`,{findings:{completeness:[{code:"APP_FORM",label:"Unified application form complete (QMS-36)",status:"na"}],technical:[]},remarks:"Evaluation started from application detail modal"});$(e.data?.status==="draft"?"Evaluation draft ready":"Evaluation started")}catch(e){m(e?.response?.data?.message||"Unable to start evaluation")}}async function nt(t){try{await window.axios.post(`/api/v1/staff/applications/${t.uuid}/timer/start`),$("Timer started")}catch(e){m(e?.response?.data?.message||"Timer start failed")}}async function lt(t,e){if(!t.applicant?.uuid){m("Applicant account is not linked to this filing.");return}const a=await Q({title:"Request document correction?",text:`Notify the applicant that “${p(e)}” must be uploaded or corrected.`,inputLabel:"Message to applicant",inputPlaceholder:"Please upload a clear PDF of the required document…",confirmButtonText:"Send request",minLength:10});if(a)try{await window.axios.post("/api/v1/staff/notifications/send",{user_uuid:t.applicant.uuid,template_code:"document.correction_requested",message:`Document request for ${t.application_no||"your application"} — ${p(e)}: ${a}`,vars:{name:t.applicant.name||"Applicant",application_no:t.application_no||"",document:p(e),message:`Please upload/correct “${p(e)}” for ${t.application_no||"your application"}. ${a}`},url:"/applications"}),$("Correction request sent (in-app + email)")}catch(s){m(s?.response?.data?.message||"Unable to send request")}}const q=[{role:"Architect",nameKey:"architect_name",prcKey:"architect_prc"},{role:"Civil / Structural Engineer",nameKey:"engineer_name",prcKey:"engineer_prc"},{role:"Professional Electrical Engineer",nameKey:"electrical_engineer_name",prcKey:"electrical_prc"},{role:"Sanitary Engineer / Master Plumber",nameKey:"sanitary_engineer_name",prcKey:"sanitary_prc"},{role:"Mechanical Engineer",nameKey:"mechanical_engineer_name",prcKey:"mechanical_prc"}];function ot(t){const e=O(t.submitted_at,t.classification);return`
        <div class="d-flex flex-wrap align-items-center gap-2">
            ${_(t.status||"",{pulse:!0})}
            ${F(t.classification)}
            <span class="text-muted small">${i(t.form?.code||"—")}</span>
        </div>
        ${e?`<div class="mt-1 small fw-medium text-warning-emphasis d-flex align-items-center gap-1">
                    <span>RA 11032</span>${e}
                   </div>`:""}
    `}function rt(t){const e=M("evaluations.manage"),a=String(t.status||"").toLowerCase(),s=a==="submitted"||a==="under_evaluation",o=e&&s,c=e&&s,r=(t.routing_slips||[]).length>0,n=!!t.classification;return`
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
    `}function ct(t="overview"){return`<ul class="nav nav-tabs nav-tabs-custom apics-nav-tabs mb-0 flex-wrap" role="tablist">
        ${[{id:"overview",label:"Overview & Location",icon:"ri-map-pin-line"},{id:"technical",label:"Technical (QMS-36)",icon:"ri-building-2-line"},{id:"documents",label:"Document Vault",icon:"ri-folder-2-line"},{id:"routing",label:"Routing & Reviews",icon:"ri-organization-chart"},{id:"inspection",label:"Inspection Forms",icon:"ri-clipboard-line"}].map(a=>`<li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link text-nowrap${t===a.id?" active":""}"
                        data-ops-detail-tab="${a.id}"
                        role="tab"
                        aria-selected="${t===a.id?"true":"false"}">
                        <i class="${a.icon}" aria-hidden="true"></i>${i(a.label)}
                    </button>
                </li>`).join("")}
    </ul>`}function dt(t){const e=t.payload||{},a=String(e.barangay||"—"),s=String(e.lot_number||"—"),o=String(e.block_number||"—"),c=String(e.street_address||t.project_location||"—"),r=J({latitude:t.latitude,longitude:t.longitude}),n=r?`${r.lat.toFixed(6)}, ${r.lng.toFixed(6)}`:"No coordinates pinned",l=r!=null?`<div class="d-flex flex-wrap gap-1 mt-2">
                <a class="btn btn-sm btn-primary" href="${i(G(r.lat,r.lng))}" target="_blank" rel="noopener noreferrer">
                    <i class="ri-guide-line align-bottom me-1"></i>Get Directions
                </a>
                <a class="btn btn-sm btn-soft-secondary" href="${i(V(r.lat,r.lng,t.project_title))}" target="_blank" rel="noopener noreferrer">
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
                ${Y({latitude:t.latitude,longitude:t.longitude,address:t.project_location,label:t.project_title||t.application_no||"Project site"})}
            </div>
        </div>
    `}function mt(t){const e=t.payload||{},a=String(e.owner_name||t.applicant?.name||"—"),s=String(e.owner_email||t.applicant?.email||""),o=String(e.owner_contact||t.applicant?.phone||"—");return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Application No.</p>
                <p class="fw-semibold mb-0">${i(t.application_no||"—")}</p>
            </div>
            <div class="col-md-5 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Project</p>
                <p class="fw-medium mb-0">${i(t.project_title||"—")}</p>
            </div>
            <div class="col-md-4 col-sm-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Permit form</p>
                <p class="mb-0">${i(t.form?.code||"—")}
                    <span class="text-muted small">${i(t.form?.title||"")}</span>
                </p>
            </div>
        </div>

        ${dt(t)}

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
                    ${U(z(t))}
                </div>
            </div>
        </div>
    `}function pt(t){const e=q.map(a=>{const s=t[a.nameKey],o=t[a.prcKey];return(s==null||s==="")&&(o==null||o==="")?"":`<div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                    <span class="text-muted fs-11 text-uppercase">${i(a.role)}</span>
                    ${o?'<span class="badge border bg-success-subtle text-success border-success-subtle">PRC Active</span>':'<span class="badge border bg-secondary-subtle text-secondary">No PRC on file</span>'}
                </div>
                <p class="fw-semibold mb-1">${i(String(s||"—"))}</p>
                <p class="small text-muted mb-0 font-monospace">PRC ${i(String(o||"—"))}</p>
            </div>
        </div>`}).filter(Boolean);return e.length?`<div class="row g-3">${e.join("")}</div>`:'<p class="text-muted mb-0">No design professionals recorded on this filing.</p>'}function ut(t){const e=t.payload||{},a=B(t.form?.schema||null),s=new Set(q.flatMap(d=>[d.nameKey,d.prcKey])),o=a.filter(d=>!/design professional/i.test(d.title)).map(d=>{const f=d.fields.filter(b=>!s.has(b.name)).map(b=>{const u=e[b.name];return u==null||u===""?"":`<div class="col-sm-6 col-lg-4">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">${i(b.label)}</p>
                        <p class="mb-0 text-break">${A(b,u)}</p>
                    </div>`}).filter(Boolean).join("");return f?`<div class="mb-3">
                <h6 class="fs-13 text-uppercase text-muted mb-2">${i(d.title)}</h6>
                <div class="row g-3">${f}</div>
            </div>`:""}).filter(Boolean).join(""),c=e.estimated_cost,r=e.lot_area,n=e.floor_area;return`
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
        <div class="border rounded p-3">${pt(e)}</div>
    `}function bt(t){const e=t.documents||[],a=t.form?.required_attachments||[],s=new Map(e.map(n=>[n.label,n])),o=e.map(n=>n.label).filter(n=>!a.includes(n)),c=[...a,...o];return c.length?`
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
                        <tbody>${c.map(n=>{const l=s.get(n),d=a.includes(n),f=!!(l&&(l.is_pdf||l.is_image||(l.mime_type||"").includes("pdf")||(l.mime_type||"").startsWith("image/")||l.original_name.toLowerCase().endsWith(".pdf")));let b;l?b='<span class="badge bg-success-subtle text-success border border-success-subtle">Uploaded</span>':d?b='<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">Mandatory deficient</span>':b='<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Optional</span>';const u=l?`<button type="button" class="btn btn-sm btn-soft-primary"
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
                        ${t.applicant?.uuid?"":'disabled title="Applicant account not linked"'}>
                        <i class="ri-mail-send-line"></i> Request correction
                   </button>`;return`<tr>
                <td class="fw-medium">${i(p(n))}
                    ${d?'<div class="text-muted small">Mandatory</div>':'<div class="text-muted small">Optional</div>'}
                </td>
                <td class="small text-break">${l?`${i(K(l.original_name))}${l.size?` <span class="text-muted">(${i(W(l.size))})</span>`:""}`:"—"}</td>
                <td>${b}</td>
                <td class="text-end text-nowrap">${u}</td>
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
    `:'<p class="text-muted mb-0">No documents required or uploaded.</p>'}function ft(t){return t?[{key:"qms-61",label:"QMS-61"},{key:"qms-62",label:"QMS-62"}].filter(a=>!!t[a.key]).map(a=>`<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${i(String(t[a.key]||""))}">
                <i class="ri-printer-line align-bottom me-1"></i>${i(a.label)}
            </button>`).join(""):""}function vt(t){return t?[{key:"qms-63",label:"QMS-63"},{key:"qms-64",label:"QMS-64"}].filter(a=>!!t[a.key]).map(a=>`<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${i(String(t[a.key]||""))}">
                <i class="ri-printer-line align-bottom me-1"></i>${i(a.label)}
            </button>`).join(""):""}function H(t){const e=t.evaluations||[];if(!e.length)return`<div class="border rounded p-3 mt-3 bg-light-subtle">
            <p class="fw-medium mb-1">No evaluation sheets yet</p>
            <p class="text-muted small mb-0">QMS-63/64 sheets appear here after an evaluator saves or decides from the Evaluation Queue.</p>
        </div>`;const a=e.filter(n=>String(n.status||"")==="decided").slice().sort((n,l)=>String(l.decided_at||l.created_at||"").localeCompare(String(n.decided_at||n.created_at||""))),s=e.filter(n=>String(n.status||"")==="draft").slice().sort((n,l)=>String(l.updated_at||l.created_at||"").localeCompare(String(n.updated_at||n.created_at||""))),o=s[0],c=[...a,...o?[o]:[]],r=Math.max(0,s.length-(o?1:0));return`${r>0?`<p class="text-muted small mb-2">Showing the latest draft (${r} older draft${r===1?"":"s"} hidden — open Evaluate to continue the active sheet).</p>`:""}${c.map(n=>{const l=n.findings||{},d=l.completeness||[],f=l.technical||[];return`<div class="border rounded p-3 mt-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">Evaluation sheet
                            <span class="badge bg-primary-subtle text-primary ms-1">${i(p(String(n.status||"")))}</span>
                            ${n.result?`<span class="badge bg-info-subtle text-info ms-1">${i(p(String(n.result)))}</span>`:""}
                        </p>
                        <p class="text-muted small mb-0">${i(n.evaluator?.name||"Evaluator")}${n.decided_at?` · decided ${i(S(n.decided_at))}`:""}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-1">${vt(n.print_urls)}</div>
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
            </div>`}).join("")}`}function gt(t){const e=t.routing_slips||[],a=String(t.status||"").toLowerCase(),s=a==="submitted"||a==="under_evaluation",o=M("evaluations.manage")&&s;return e.length?`${e.map(r=>{const n=(r.steps||[]).map(l=>`<div class="apics-routing-step d-flex flex-wrap align-items-start gap-2 py-2 border-bottom">
                        <span class="badge bg-secondary-subtle text-secondary">${i(String(l.step_order??""))}</span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-medium">${i(l.label||"Step")}</div>
                            <div class="text-muted small">${i(l.department?.name||l.department?.code||"Department")}</div>
                            ${l.started_at||l.completed_at?`<div class="text-muted small mt-1">
                                        ${l.started_at?`Started ${i(p(String(l.status||"")))}`:""}
                                       </div>`:""}
                        </div>
                        <span class="badge bg-info-subtle text-info">${i(p(String(l.status||"")))}</span>
                    </div>`).join("");return`<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">${i(r.slip_no||"Routing slip")}
                            <span class="badge bg-primary-subtle text-primary ms-1">${i(p(String(r.status||"")))}</span>
                        </p>
                        ${r.template?`<p class="text-muted small mb-0">Template ${i(r.template.code||"")}${r.template.name?` — ${i(r.template.name)}`:""}</p>`:""}
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        ${ft(r.print_urls)}
                        ${o?`<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="timer">
                                    <i class="ri-timer-line align-bottom me-1"></i> Start timer
                                   </button>`:""}
                    </div>
                </div>
                <div>${n||'<p class="text-muted mb-0">No steps</p>'}</div>
            </div>`}).join("")}${H(t)}`:`
            <div class="apics-routing-empty text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No routing slips initialized for this filing.</p>
                <p class="text-muted small mb-3">Initialize the standard multi-discipline technical review path based on QMS-61/62 templates (Architectural, Civil/Structural, Electrical, Mechanical, Sanitary, Fire).</p>
                ${o?`<button type="button" class="btn btn-primary btn-sm" data-ops-detail-action="routing" ${t.classification?"":'disabled title="Classify first"'}>
                            <i class="ri-git-branch-line align-bottom me-1"></i> Generate Routing Slips
                           </button>`:s?'<p class="text-muted small mb-0">Ask an evaluator with routing permission to generate the slip.</p>':`<p class="text-muted small mb-0">Routing actions are closed for ${i(p(a||"this"))} filings.</p>`}
            </div>
            ${H(t)}
        `}function ht(t){const e=String(t||"").toLowerCase();return{joint:"Joint",joint_structural:"Joint · Structural",joint_architectural:"Joint · Architectural",joint_electrical:"Joint · Electrical",joint_sanitary:"Joint · Sanitary",joint_mechanical:"Joint · Mechanical",joint_fire_safety:"Joint · Fire Safety",electrical:"Electrical (DPWH 77-006-E)",final:"Final"}[e]||p(t||"Inspection")}function S(t){if(!t)return"—";try{return new Date(t).toLocaleString("en-US",{month:"short",day:"numeric",year:"numeric",hour:"numeric",minute:"2-digit"})}catch{return t}}function wt(t,e){return t?[{key:"qms-38",label:"QMS-38",show:!!t["qms-38"]},{key:"qms-39",label:"QMS-39",show:!!t["qms-39"]},{key:"o-03",label:"O-03",show:!!t["o-03"]},{key:"qms-65",label:"QMS-65",show:!!t["qms-65"]},{key:"dpwh-77-006-e",label:"77-006-E",show:!!t["dpwh-77-006-e"]&&e}].filter(s=>s.show).map(s=>`<button type="button" class="btn btn-sm btn-soft-secondary"
                data-ops-insp-print="${i(String(t[s.key]||""))}">
                <i class="ri-printer-line align-bottom me-1"></i>${i(s.label)}
            </button>`).join(""):""}function k(t){return t?.length?`<div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Code</th><th>Item</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody>${t.map(a=>{const s=String(a.status||"na").toLowerCase(),o=s==="ok"?"bg-success-subtle text-success":s==="fail"?"bg-danger-subtle text-danger":"bg-secondary-subtle text-secondary";return`<tr>
                <td class="small font-monospace">${i(a.code||"—")}</td>
                <td class="small">${i(a.label||"—")}</td>
                <td><span class="badge ${o}">${i(s.toUpperCase())}</span></td>
                <td class="small text-muted">${i(a.remarks||"")}</td>
            </tr>`}).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted small mb-0">No QMS-65 checklist items recorded.</p>'}function yt(t){const e=t.inspections||[],a=M("inspections.manage");return e.length?e.map(s=>{const o=s.schedule_sheet||{},c=s.inspector_notes||{},r=s.team_inspectors||[],n=s.electrical_form||{},l=!!(s.requires_electrical_form||n.result&&n.result!=="na"||String(s.type||"").toLowerCase().includes("electrical")),d=Array.isArray(o.disciplines)?o.disciplines.join(", "):"—",f=r.length?`<ul class="list-unstyled mb-0 small">${r.map(u=>`<li><span class="fw-medium">${i(u.name||"—")}</span>
                                <span class="text-muted"> · ${i(u.role||"Member")}${u.discipline?` · ${i(u.discipline)}`:""}</span></li>`).join("")}</ul>`:`<p class="small text-muted mb-0">${i(s.inspector?.name||"No team recorded")}</p>`,b=l?`<div class="border rounded p-3 mb-0">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">DPWH 77-006-E</p>
                    <div class="row g-2 small">
                        ${[["Service entrance",n.service_entrance],["Grounding",n.grounding],["Panel boards",n.panel_boards],["Wiring methods",n.wiring_methods],["Fixtures / devices",n.fixtures_devices],["Load schedule",n.load_schedule],["Result",n.result||n.status]].map(([u,I])=>`<div class="col-sm-6 col-md-4">
                                    <span class="text-muted">${i(String(u))}</span>
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
                        <p class="text-muted small mb-0">${i(ht(s.type))}
                            · Scheduled ${i(S(s.scheduled_at))}
                            ${s.completed_at?` · Completed ${i(S(s.completed_at))}`:""}
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-start">
                        ${wt(s.print_urls,l)}
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

                ${b}
            </div>`}).join(""):`
            <div class="text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No inspections scheduled for this filing.</p>
                <p class="text-muted small mb-3">QMS-38/39 schedule, O-03 notes, QMS-65 compliance sheet, and DPWH 77-006-E appear here after scheduling.</p>
                ${a?`<a class="btn btn-sm btn-primary" href="/admin/inspections">
                            <i class="ri-calendar-check-line align-bottom me-1"></i> Open Inspections
                           </a>`:""}
            </div>
        `}function $t(t,e="overview"){return`
        <div class="tab-content apics-app-detail__panels">
            <div class="tab-pane fade${e==="overview"?" show active":""}" data-ops-detail-panel="overview" role="tabpanel">
                ${mt(t)}
            </div>
            <div class="tab-pane fade${e==="technical"?" show active":""}" data-ops-detail-panel="technical" role="tabpanel">
                ${ut(t)}
            </div>
            <div class="tab-pane fade${e==="documents"?" show active":""}" data-ops-detail-panel="documents" role="tabpanel">
                ${bt(t)}
            </div>
            <div class="tab-pane fade${e==="routing"?" show active":""}" data-ops-detail-panel="routing" role="tabpanel">
                ${gt(t)}
            </div>
            <div class="tab-pane fade${e==="inspection"?" show active":""}" data-ops-detail-panel="inspection" role="tabpanel">
                ${yt(t)}
            </div>
        </div>
    `}function xt(t){et(t)}let g=null,h=null,v=null,y="overview",w=null;function E(){w&&(URL.revokeObjectURL(w),w=null)}function _t(t){const e=document.getElementById("modal-ops-application-detail-label"),a=document.getElementById("ops-application-detail-header-meta"),s=document.getElementById("ops-application-detail-actions"),o=document.getElementById("ops-application-detail-tabnav"),c=document.getElementById("ops-application-detail-body");c&&(e&&(e.textContent=`Application details · ${t.application_no||""}`.trim()),a&&(a.innerHTML=ot(t)),s&&(s.innerHTML=rt(t)),o&&(o.innerHTML=ct(y)),c.innerHTML=$t(t,y))}function R(t){y=t,document.querySelectorAll("#ops-application-detail-tabnav [data-ops-detail-tab]").forEach(e=>{const a=e.dataset.opsDetailTab===t;e.classList.toggle("active",a),e.setAttribute("aria-selected",a?"true":"false")}),document.querySelectorAll("[data-ops-detail-panel]").forEach(e=>{const a=e.dataset.opsDetailPanel===t;e.classList.toggle("show",a),e.classList.toggle("active",a)}),t==="overview"&&(window.setTimeout(()=>g?.invalidateSize(),120),window.setTimeout(()=>g?.invalidateSize(),350))}async function St(t,e){const a=document.getElementById("ops-doc-preview-pane"),s=e.dataset.previewDoc;if(!a||!s)return;const o={uuid:s,label:e.dataset.docLabel,original_name:e.dataset.docName||"document",mime_type:e.dataset.docMime||null,is_pdf:e.dataset.docPdf==="1",is_image:e.dataset.docImage==="1"};if(!o.is_pdf&&!o.is_image){await P(t,o);return}E(),a.innerHTML=`<div class="text-center text-muted p-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="small mb-0 mt-2">Loading preview…</p>
    </div>`;try{const r=(await window.axios.get(`/api/v1/applications/${t}/documents/${o.uuid}/file`,{responseType:"blob"})).data,n=o.mime_type||r.type||"application/octet-stream",l=r.type?r:new Blob([r],{type:n});w=URL.createObjectURL(l),o.is_pdf||n.includes("pdf")?a.innerHTML=`<iframe title="${i(o.original_name)}" src="${w}#toolbar=0&navpanes=0&view=FitH" class="apics-doc-vault-preview__frame"></iframe>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${i(o.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`:a.innerHTML=`<div class="apics-doc-vault-preview__image-wrap">
                    <img src="${w}" alt="${i(o.original_name)}" class="apics-doc-vault-preview__image">
                </div>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${i(o.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`,a.querySelector("[data-ops-open-lightbox]")?.addEventListener("click",()=>{P(t,o)})}catch(c){a.innerHTML=`<p class="text-danger small p-3 mb-0">${i(c?.response?.data?.message||"Preview failed")}</p>`,m(c?.response?.data?.message||"Preview failed")}}function kt(t){const e=document.getElementById("ops-map-expand-body"),a=document.getElementById("modal-ops-map-expand-label");e&&(h?.destroy(),h=null,a&&(a.textContent=`Project site · ${t.application_no||""}`.trim()),e.innerHTML=`<div class="p-3">${Z({latitude:t.latitude,longitude:t.longitude,address:t.project_location,label:t.project_title||t.application_no||"Project site"})}</div>`,j("modal-ops-map-expand"),window.setTimeout(()=>{h=C(e),h?.invalidateSize()},200),window.setTimeout(()=>h?.invalidateSize(),500))}function Mt(t){const e=document.getElementById("modal-ops-application-detail");e&&(e.querySelectorAll("[data-ops-detail-tab]").forEach(a=>{a.addEventListener("click",()=>{const s=a.dataset.opsDetailTab;s&&R(s)})}),e.querySelectorAll("[data-ops-detail-action]").forEach(a=>{a.addEventListener("click",()=>{const s=a.dataset.opsDetailAction;!s||!v||(async()=>{if(s==="print"){await at(v);return}if(s==="routing"){const o=await st(v);o&&(v=o,y="routing",D(o));return}if(s==="evaluate"){await it(v);return}if(s==="timer"){await nt(v);return}s==="request-doc"&&await lt(v,a.dataset.docLabel||"document")})()})}),e.querySelectorAll("[data-ops-expand-map]").forEach(a=>{a.addEventListener("click",s=>{s.preventDefault(),s.stopPropagation(),kt(t)})}),e.querySelectorAll('[data-ops-inline-preview="1"]').forEach(a=>{a.addEventListener("click",s=>{s.preventDefault(),s.stopPropagation(),St(t.uuid,a)})}),X(e,t.uuid),e.querySelectorAll("[data-ops-insp-print]").forEach(a=>{a.addEventListener("click",s=>{s.preventDefault(),s.stopPropagation(),xt(a.dataset.opsInspPrint||"")})}))}function D(t){g?.destroy(),g=null,E(),_t(t),Mt(t);const e=document.getElementById("ops-application-detail-body");e&&(g=C(e)),window.setTimeout(()=>g?.invalidateSize(),200),window.setTimeout(()=>g?.invalidateSize(),500)}async function Lt(t,e){const a=document.getElementById("ops-application-detail-body"),s=document.getElementById("modal-ops-application-detail-label"),o=document.getElementById("ops-application-detail-header-meta"),c=document.getElementById("ops-application-detail-actions"),r=document.getElementById("ops-application-detail-tabnav");if(!t){m("Missing application reference. Reload the page and try again.");return}if(!a){m("Application detail viewer failed to load. Hard-refresh the page (Cmd/Ctrl+Shift+R).");return}g?.destroy(),g=null,h?.destroy(),h=null,E(),v=null;const n=e?.tab;y=n&&["overview","technical","documents","routing","inspection"].includes(n)?n:"overview",o&&(o.innerHTML=""),c&&(c.innerHTML=""),r&&(r.innerHTML=""),a.innerHTML=`<div class="text-center text-muted py-5">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></div>
        Loading application details…
    </div>`,s&&(s.textContent="Application details"),j("modal-ops-application-detail");try{const{data:l}=await window.axios.get(`/api/v1/staff/applications/${t}`),d=l.data;v=d,D(d),n==="inspection"&&R("inspection")}catch(l){a.innerHTML=`<p class="text-danger mb-0">${i(l?.response?.data?.message||"Unable to load application details")}</p>`,m(l?.response?.data?.message||"Unable to load application details")}}export{et as a,Lt as o};
