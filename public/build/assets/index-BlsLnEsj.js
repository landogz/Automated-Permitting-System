import{e as s,t as f,d as E,b as $,j as C,k as M,s as L}from"./app-DpvXLM-T.js";import{n as P,h as T,I as p,f as A,c as x,y as k,z as R,A as q,e as D,t as I,b as N,d as z,J as F,p as K,g as O,a as U,s as Q,m as H,l as G,q as V,o as S}from"./site-map-viewer-D-JseiQO.js";import{o as W}from"./create-apics-datatable-DPPhyJg9.js";async function J(t){const e=t.payload||{},i=P(t.form?.schema||null).map(n=>{const o=n.fields.map(d=>{const l=e[d.name];if(l==null||l==="")return"";const r=T(d,l).replace(/<[^>]+>/g,"");return`<tr><th>${s(d.label)}</th><td>${s(r)}</td></tr>`}).filter(Boolean).join("");return o?`<h3 style="font-size:13px;margin:14px 0 6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b">${s(n.title)}</h3>
                <table class="meta" style="width:100%">${o}</table>`:""}).filter(Boolean).join("");W({formCode:t.form?.code||"QMS-36",formTitle:t.form?.title||"Unified Application Form",documentNo:t.application_no||t.uuid,subtitle:t.project_title||void 0,meta:[{label:"Status",value:p(t.status||"—")},{label:"Classification",value:p(t.classification||"Unclassified")},{label:"Applicant",value:String(e.owner_name||t.applicant?.name||"—")},{label:"Location",value:t.project_location||"—"},{label:"Estimated cost",value:e.estimated_cost!=null&&e.estimated_cost!==""?A(e.estimated_cost).replace(/<[^>]+>/g,""):"—"},{label:"Floor area",value:e.floor_area!=null&&e.floor_area!==""?x(e.floor_area):"—"}],bodyHtml:i||"<p>No form answers recorded.</p>",signatures:[{role:"Prepared / Printed by"},{role:"Reviewed by (Evaluator)"},{role:"Noted by (Building Official)"}],windowTitle:`${t.form?.code||"QMS-36"} · ${t.application_no||""}`})}async function X(t){if(!t.classification)return f("Classify the application before generating a routing slip."),null;if(!await E("Generate routing slip?","Creates department review steps from the matching QMS-61/62 routing template."))return null;try{const{data:e}=await window.axios.post(`/api/v1/staff/applications/${t.uuid}/routing-slip`);return $(`Routing slip ${e.data?.slip_no||""} generated`),(await window.axios.get(`/api/v1/staff/applications/${t.uuid}`)).data.data}catch(e){return f(e?.response?.data?.message||"Routing failed"),null}}async function Y(t){if(await E("Start evaluation?","Creates an evaluation sheet and starts the statutory processing timer for this filing."))try{await window.axios.post(`/api/v1/staff/applications/${t.uuid}/timer/start`).catch(()=>null);const{data:e}=await window.axios.post(`/api/v1/staff/applications/${t.uuid}/evaluations`,{findings:[{item:"Completeness",status:"ok"}],remarks:"Evaluation started from application detail modal"});$(`Evaluation ${e.data?.uuid?"started":"created"}`)}catch(e){f(e?.response?.data?.message||"Unable to start evaluation")}}async function Z(t){try{await window.axios.post(`/api/v1/staff/applications/${t.uuid}/timer/start`),$("Timer started")}catch(e){f(e?.response?.data?.message||"Timer start failed")}}async function tt(t,e){if(!t.applicant?.uuid){f("Applicant account is not linked to this filing.");return}const a=await C({title:"Request document correction?",text:`Notify the applicant that “${p(e)}” must be uploaded or corrected.`,inputLabel:"Message to applicant",inputPlaceholder:"Please upload a clear PDF of the required document…",confirmButtonText:"Send request",minLength:10});if(a)try{await window.axios.post("/api/v1/staff/notifications/send",{user_uuid:t.applicant.uuid,template_code:"document.correction_requested",message:`Document request for ${t.application_no||"your application"} — ${p(e)}: ${a}`,vars:{name:t.applicant.name||"Applicant",application_no:t.application_no||"",document:p(e),message:`Please upload/correct “${p(e)}” for ${t.application_no||"your application"}. ${a}`},url:"/applications"}),$("Correction request sent (in-app + email)")}catch(i){f(i?.response?.data?.message||"Unable to send request")}}const B=[{role:"Architect",nameKey:"architect_name",prcKey:"architect_prc"},{role:"Civil / Structural Engineer",nameKey:"engineer_name",prcKey:"engineer_prc"},{role:"Professional Electrical Engineer",nameKey:"electrical_engineer_name",prcKey:"electrical_prc"},{role:"Sanitary Engineer / Master Plumber",nameKey:"sanitary_engineer_name",prcKey:"sanitary_prc"},{role:"Mechanical Engineer",nameKey:"mechanical_engineer_name",prcKey:"mechanical_prc"}];function et(t){const e=k(t.submitted_at,t.classification);return`
        <div class="d-flex flex-wrap align-items-center gap-2">
            ${R(t.status||"",{pulse:!0})}
            ${q(t.classification)}
            <span class="text-muted small">${s(t.form?.code||"—")}</span>
        </div>
        ${e?`<div class="mt-1 small fw-medium text-warning-emphasis d-flex align-items-center gap-1">
                    <span>RA 11032</span>${e}
                   </div>`:""}
    `}function at(t){const e=M("evaluations.manage"),a=e,i=(t.routing_slips||[]).length>0,n=!!t.classification;return`
        <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-detail-action="print" title="Print QMS-36 summary">
            <i class="ri-printer-line align-bottom me-1"></i><span class="d-none d-md-inline">Print QMS-36</span>
        </button>
        ${a?`<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="routing"
                    ${n?"":'disabled title="Classify the application first"'}
                    ${i?'title="A routing slip already exists — generate another if needed"':""}>
                    <i class="ri-git-branch-line align-bottom me-1"></i><span class="d-none d-md-inline">${i?"Regenerate slip":"Generate Routing Slip"}</span>
                   </button>`:""}
        ${e?`<button type="button" class="btn btn-sm btn-primary" data-ops-detail-action="evaluate">
                    <i class="ri-play-circle-line align-bottom me-1"></i><span class="d-none d-md-inline">Start Evaluation</span>
                   </button>`:""}
    `}function it(t="overview"){return`<ul class="nav nav-tabs nav-tabs-custom nav-success mb-0 flex-wrap" role="tablist">
        ${[{id:"overview",label:"Overview & Location",icon:"ri-map-pin-line"},{id:"technical",label:"Technical (QMS-36)",icon:"ri-building-2-line"},{id:"documents",label:"Document Vault",icon:"ri-folder-2-line"},{id:"routing",label:"Routing & Reviews",icon:"ri-organization-chart"}].map(a=>`<li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link text-nowrap${t===a.id?" active":""}"
                        data-ops-detail-tab="${a.id}"
                        role="tab"
                        aria-selected="${t===a.id?"true":"false"}">
                        <i class="${a.icon} align-bottom me-1"></i>${s(a.label)}
                    </button>
                </li>`).join("")}
    </ul>`}function st(t){const e=t.payload||{},a=String(e.barangay||"—"),i=String(e.lot_number||"—"),n=String(e.block_number||"—"),o=String(e.street_address||t.project_location||"—"),d=K({latitude:t.latitude,longitude:t.longitude}),l=d?`${d.lat.toFixed(6)}, ${d.lng.toFixed(6)}`:"No coordinates pinned",r=d!=null?`<div class="d-flex flex-wrap gap-1 mt-2">
                <a class="btn btn-sm btn-primary" href="${s(O(d.lat,d.lng))}" target="_blank" rel="noopener noreferrer">
                    <i class="ri-guide-line align-bottom me-1"></i>Get Directions
                </a>
                <a class="btn btn-sm btn-soft-secondary" href="${s(U(d.lat,d.lng,t.project_title))}" target="_blank" rel="noopener noreferrer">
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
                            <p class="fw-medium mb-0">${s(o)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Barangay</p>
                            <p class="mb-0">${s(a)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Lot</p>
                            <p class="mb-0">${s(i)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Block</p>
                            <p class="mb-0">${s(n)}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Coordinates</p>
                            <p class="font-monospace small mb-0">${s(l)}</p>
                        </div>
                    </div>
                    ${r}
                </div>
            </div>
            <div class="col-lg-5">
                ${Q({latitude:t.latitude,longitude:t.longitude,address:t.project_location,label:t.project_title||t.application_no||"Project site"})}
            </div>
        </div>
    `}function nt(t){const e=t.payload||{},a=String(e.owner_name||t.applicant?.name||"—"),i=String(e.owner_email||t.applicant?.email||""),n=String(e.owner_contact||t.applicant?.phone||"—");return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Application No.</p>
                <p class="fw-semibold mb-0">${s(t.application_no||"—")}</p>
            </div>
            <div class="col-md-5 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Project</p>
                <p class="fw-medium mb-0">${s(t.project_title||"—")}</p>
            </div>
            <div class="col-md-4 col-sm-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Permit form</p>
                <p class="mb-0">${s(t.form?.code||"—")}
                    <span class="text-muted small">${s(t.form?.title||"")}</span>
                </p>
            </div>
        </div>

        ${st(t)}

        <div class="row g-3 mt-1">
            <div class="col-md-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Applicant / owner</p>
                    <p class="fw-semibold mb-1">${s(a)}</p>
                    <p class="small mb-1">${D(i||null)}</p>
                    <p class="small text-muted mb-0">${s(n)}</p>
                </div>
            </div>
            <div class="col-md-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Timeline</p>
                    ${I(N(t))}
                </div>
            </div>
        </div>
    `}function ot(t){const e=B.map(a=>{const i=t[a.nameKey],n=t[a.prcKey];return(i==null||i==="")&&(n==null||n==="")?"":`<div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                    <span class="text-muted fs-11 text-uppercase">${s(a.role)}</span>
                    ${n?'<span class="badge border bg-success-subtle text-success border-success-subtle">PRC Active</span>':'<span class="badge border bg-secondary-subtle text-secondary">No PRC on file</span>'}
                </div>
                <p class="fw-semibold mb-1">${s(String(i||"—"))}</p>
                <p class="small text-muted mb-0 font-monospace">PRC ${s(String(n||"—"))}</p>
            </div>
        </div>`}).filter(Boolean);return e.length?`<div class="row g-3">${e.join("")}</div>`:'<p class="text-muted mb-0">No design professionals recorded on this filing.</p>'}function lt(t){const e=t.payload||{},a=P(t.form?.schema||null),i=new Set(B.flatMap(c=>[c.nameKey,c.prcKey])),n=a.filter(c=>!/design professional/i.test(c.title)).map(c=>{const h=c.fields.filter(m=>!i.has(m.name)).map(m=>{const g=e[m.name];return g==null||g===""?"":`<div class="col-sm-6 col-lg-4">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">${s(m.label)}</p>
                        <p class="mb-0 text-break">${T(m,g)}</p>
                    </div>`}).filter(Boolean).join("");return h?`<div class="mb-3">
                <h6 class="fs-13 text-uppercase text-muted mb-2">${s(c.title)}</h6>
                <div class="row g-3">${h}</div>
            </div>`:""}).filter(Boolean).join(""),o=e.estimated_cost,d=e.lot_area,l=e.floor_area;return`
        ${`
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Estimated project cost</p>
                    <p class="font-monospace fw-bold fs-5 mb-0">${o!=null&&o!==""?A(o):"—"}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Lot area</p>
                    <p class="font-monospace fw-semibold mb-0">${d!=null&&d!==""?x(d):"—"}</p>
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
        <div class="border rounded p-3">${ot(e)}</div>
    `}function rt(t){const e=t.documents||[],a=t.form?.required_attachments||[],i=new Map(e.map(l=>[l.label,l])),n=e.map(l=>l.label).filter(l=>!a.includes(l)),o=[...a,...n];return o.length?`
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
                        <tbody>${o.map(l=>{const r=i.get(l),c=a.includes(l),h=!!(r&&(r.is_pdf||r.is_image||(r.mime_type||"").includes("pdf")||(r.mime_type||"").startsWith("image/")||r.original_name.toLowerCase().endsWith(".pdf")));let m;r?m='<span class="badge bg-success-subtle text-success border border-success-subtle">Uploaded</span>':c?m='<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">Mandatory deficient</span>':m='<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Optional</span>';const g=r?`<button type="button" class="btn btn-sm btn-soft-primary"
                        data-preview-doc="${s(r.uuid)}"
                        data-doc-label="${s(r.label)}"
                        data-doc-name="${s(r.original_name)}"
                        data-doc-mime="${s(r.mime_type||"")}"
                        data-doc-pdf="${r.is_pdf||(r.mime_type||"").includes("pdf")||r.original_name.toLowerCase().endsWith(".pdf")?"1":"0"}"
                        data-doc-image="${r.is_image||(r.mime_type||"").startsWith("image/")?"1":"0"}"
                        data-ops-inline-preview="1"
                        title="${h?"Preview in vault":"Open / download"}">
                        <i class="ri-eye-line"></i> Preview
                   </button>`:`<button type="button" class="btn btn-sm btn-outline-danger"
                        data-ops-detail-action="request-doc"
                        data-doc-label="${s(l)}"
                        ${t.applicant?.uuid?"":'disabled title="Applicant account not linked"'}>
                        <i class="ri-mail-send-line"></i> Request correction
                   </button>`;return`<tr>
                <td class="fw-medium">${s(p(l))}
                    ${c?'<div class="text-muted small">Mandatory</div>':'<div class="text-muted small">Optional</div>'}
                </td>
                <td class="small text-break">${r?`${s(z(r.original_name))}${r.size?` <span class="text-muted">(${s(F(r.size))})</span>`:""}`:"—"}</td>
                <td>${m}</td>
                <td class="text-end text-nowrap">${g}</td>
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
    `:'<p class="text-muted mb-0">No documents required or uploaded.</p>'}function dt(t){const e=t.routing_slips||[],a=M("evaluations.manage");return e.length?e.map(i=>{const n=(i.steps||[]).map(o=>`<div class="apics-routing-step d-flex flex-wrap align-items-start gap-2 py-2 border-bottom">
                        <span class="badge bg-secondary-subtle text-secondary">${s(String(o.step_order??""))}</span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-medium">${s(o.label||"Step")}</div>
                            <div class="text-muted small">${s(o.department?.name||o.department?.code||"Department")}</div>
                            ${o.started_at||o.completed_at?`<div class="text-muted small mt-1">
                                        ${o.started_at?`Started ${s(p(String(o.status||"")))}`:""}
                                       </div>`:""}
                        </div>
                        <span class="badge bg-info-subtle text-info">${s(p(String(o.status||"")))}</span>
                    </div>`).join("");return`<div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <p class="fw-semibold mb-0">${s(i.slip_no||"Routing slip")}
                            <span class="badge bg-primary-subtle text-primary ms-1">${s(p(String(i.status||"")))}</span>
                        </p>
                        ${i.template?`<p class="text-muted small mb-0">Template ${s(i.template.code||"")}${i.template.name?` — ${s(i.template.name)}`:""}</p>`:""}
                    </div>
                    ${a?`<button type="button" class="btn btn-sm btn-soft-primary" data-ops-detail-action="timer">
                                <i class="ri-timer-line align-bottom me-1"></i> Start timer
                               </button>`:""}
                </div>
                <div>${n||'<p class="text-muted mb-0">No steps</p>'}</div>
            </div>`}).join(""):`
            <div class="apics-routing-empty text-center border border-2 border-dashed rounded-3 p-4 p-md-5 bg-light-subtle">
                <p class="fw-medium mb-1">No routing slips initialized for this filing.</p>
                <p class="text-muted small mb-3">Initialize the standard multi-discipline technical review path based on QMS-61/62 templates (Architectural, Civil/Structural, Electrical, Mechanical, Sanitary, Fire).</p>
                ${a?`<button type="button" class="btn btn-primary btn-sm" data-ops-detail-action="routing" ${t.classification?"":'disabled title="Classify first"'}>
                            <i class="ri-git-branch-line align-bottom me-1"></i> Generate Routing Slips
                           </button>`:'<p class="text-muted small mb-0">Ask an evaluator with routing permission to generate the slip.</p>'}
            </div>
        `}function ct(t,e="overview"){return`
        <div class="tab-content apics-app-detail__panels">
            <div class="tab-pane fade${e==="overview"?" show active":""}" data-ops-detail-panel="overview" role="tabpanel">
                ${nt(t)}
            </div>
            <div class="tab-pane fade${e==="technical"?" show active":""}" data-ops-detail-panel="technical" role="tabpanel">
                ${lt(t)}
            </div>
            <div class="tab-pane fade${e==="documents"?" show active":""}" data-ops-detail-panel="documents" role="tabpanel">
                ${rt(t)}
            </div>
            <div class="tab-pane fade${e==="routing"?" show active":""}" data-ops-detail-panel="routing" role="tabpanel">
                ${dt(t)}
            </div>
        </div>
    `}let b=null,v=null,u=null,y="overview",w=null;function _(){w&&(URL.revokeObjectURL(w),w=null)}function mt(t){const e=document.getElementById("modal-ops-application-detail-label"),a=document.getElementById("ops-application-detail-header-meta"),i=document.getElementById("ops-application-detail-actions"),n=document.getElementById("ops-application-detail-tabnav"),o=document.getElementById("ops-application-detail-body");o&&(e&&(e.textContent=`Application details · ${t.application_no||""}`.trim()),a&&(a.innerHTML=et(t)),i&&(i.innerHTML=at(t)),n&&(n.innerHTML=it(y)),o.innerHTML=ct(t,y))}function pt(t){y=t,document.querySelectorAll("#ops-application-detail-tabnav [data-ops-detail-tab]").forEach(e=>{const a=e.dataset.opsDetailTab===t;e.classList.toggle("active",a),e.setAttribute("aria-selected",a?"true":"false")}),document.querySelectorAll("[data-ops-detail-panel]").forEach(e=>{const a=e.dataset.opsDetailPanel===t;e.classList.toggle("show",a),e.classList.toggle("active",a)}),t==="overview"&&(window.setTimeout(()=>b?.invalidateSize(),120),window.setTimeout(()=>b?.invalidateSize(),350))}async function ut(t,e){const a=document.getElementById("ops-doc-preview-pane"),i=e.dataset.previewDoc;if(!a||!i)return;const n={uuid:i,label:e.dataset.docLabel,original_name:e.dataset.docName||"document",mime_type:e.dataset.docMime||null,is_pdf:e.dataset.docPdf==="1",is_image:e.dataset.docImage==="1"};if(!n.is_pdf&&!n.is_image){await S(t,n);return}_(),a.innerHTML=`<div class="text-center text-muted p-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <p class="small mb-0 mt-2">Loading preview…</p>
    </div>`;try{const d=(await window.axios.get(`/api/v1/applications/${t}/documents/${n.uuid}/file`,{responseType:"blob"})).data,l=n.mime_type||d.type||"application/octet-stream",r=d.type?d:new Blob([d],{type:l});w=URL.createObjectURL(r),n.is_pdf||l.includes("pdf")?a.innerHTML=`<iframe title="${s(n.original_name)}" src="${w}#toolbar=0&navpanes=0&view=FitH" class="apics-doc-vault-preview__frame"></iframe>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${s(n.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`:a.innerHTML=`<div class="apics-doc-vault-preview__image-wrap">
                    <img src="${w}" alt="${s(n.original_name)}" class="apics-doc-vault-preview__image">
                </div>
                <div class="apics-doc-vault-preview__bar">
                    <span class="text-truncate small">${s(n.original_name)}</span>
                    <button type="button" class="btn btn-sm btn-soft-secondary" data-ops-open-lightbox>Full screen</button>
                </div>`,a.querySelector("[data-ops-open-lightbox]")?.addEventListener("click",()=>{S(t,n)})}catch(o){a.innerHTML=`<p class="text-danger small p-3 mb-0">${s(o?.response?.data?.message||"Preview failed")}</p>`,f(o?.response?.data?.message||"Preview failed")}}function bt(t){const e=document.getElementById("ops-map-expand-body"),a=document.getElementById("modal-ops-map-expand-label");e&&(v?.destroy(),v=null,a&&(a.textContent=`Project site · ${t.application_no||""}`.trim()),e.innerHTML=`<div class="p-3">${V({latitude:t.latitude,longitude:t.longitude,address:t.project_location,label:t.project_title||t.application_no||"Project site"})}</div>`,L("modal-ops-map-expand"),window.setTimeout(()=>{v=H(e),v?.invalidateSize()},200),window.setTimeout(()=>v?.invalidateSize(),500))}function ft(t){const e=document.getElementById("modal-ops-application-detail");e&&(e.querySelectorAll("[data-ops-detail-tab]").forEach(a=>{a.addEventListener("click",()=>{const i=a.dataset.opsDetailTab;i&&pt(i)})}),e.querySelectorAll("[data-ops-detail-action]").forEach(a=>{a.addEventListener("click",()=>{const i=a.dataset.opsDetailAction;!i||!u||(async()=>{if(i==="print"){await J(u);return}if(i==="routing"){const n=await X(u);n&&(u=n,y="routing",j(n));return}if(i==="evaluate"){await Y(u);return}if(i==="timer"){await Z(u);return}i==="request-doc"&&await tt(u,a.dataset.docLabel||"document")})()})}),e.querySelectorAll("[data-ops-expand-map]").forEach(a=>{a.addEventListener("click",i=>{i.preventDefault(),i.stopPropagation(),bt(t)})}),e.querySelectorAll('[data-ops-inline-preview="1"]').forEach(a=>{a.addEventListener("click",i=>{i.preventDefault(),i.stopPropagation(),ut(t.uuid,a)})}),G(e,t.uuid))}function j(t){b?.destroy(),b=null,_(),mt(t),ft(t);const e=document.getElementById("ops-application-detail-body");e&&(b=H(e)),window.setTimeout(()=>b?.invalidateSize(),200),window.setTimeout(()=>b?.invalidateSize(),500)}async function yt(t){const e=document.getElementById("ops-application-detail-body"),a=document.getElementById("modal-ops-application-detail-label"),i=document.getElementById("ops-application-detail-header-meta"),n=document.getElementById("ops-application-detail-actions"),o=document.getElementById("ops-application-detail-tabnav");if(!e||!t){f("Application detail viewer is not available on this page.");return}b?.destroy(),b=null,v?.destroy(),v=null,_(),u=null,y="overview",i&&(i.innerHTML=""),n&&(n.innerHTML=""),o&&(o.innerHTML=""),e.innerHTML=`<div class="text-center text-muted py-5">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></div>
        Loading application details…
    </div>`,a&&(a.textContent="Application details"),L("modal-ops-application-detail");try{const{data:d}=await window.axios.get(`/api/v1/staff/applications/${t}`),l=d.data;u=l,j(l)}catch(d){e.innerHTML=`<p class="text-danger mb-0">${s(d?.response?.data?.message||"Unable to load application details")}</p>`,f(d?.response?.data?.message||"Unable to load application details")}}export{yt as o};
