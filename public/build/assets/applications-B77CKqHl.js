import{e as i,b as k,t as $,d as be,i as _e,g as Se,s as q,f as oe,j as Le}from"./app-B97eAumq.js";import{c as je}from"./create-apics-datatable-rUSVJS3p.js";import{s as Y}from"./status-badge-CaU8VUam.js";import{d as Q,o as Be,n as ve,p as Ae,g as Te,a as Ie,s as ke,e as Fe,t as Me,b as Pe,f as Ce,c as de,h as De,i as ce,r as W,v as qe,j as He,k as Ue,l as Ne,m as re,q as Ve}from"./site-map-viewer-ChF2lJr_.js";function O(e){return e.replaceAll("_"," ").replace(/\b\w/g,t=>t.toUpperCase())}function ge(e){return!e||e<=0?"":e<1024?`${e} B`:e<1024*1024?`${(e/1024).toFixed(1)} KB`:`${(e/(1024*1024)).toFixed(1)} MB`}function Oe(e,t){const n=t.requiredLabels.length?t.requiredLabels:[],u=new Map(t.documents.map(s=>[s.label,s]));if(!t.applicationUuid){e.innerHTML=`
            <div class="border rounded p-3 bg-light-subtle">
                <h6 class="mb-1">Supporting documents</h6>
                <p class="text-muted fs-13 mb-0">Save the draft once to enable file uploads for this application.</p>
            </div>`;return}if(!n.length){e.innerHTML=`
            <div class="border rounded p-3 bg-light-subtle">
                <h6 class="mb-1">Supporting documents</h6>
                <p class="text-muted fs-13 mb-0">This form has no required attachments configured.</p>
            </div>`;return}e.innerHTML=`
        <div class="border rounded p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Supporting documents</h6>
                    <p class="text-muted fs-12 mb-0">PDF, JPG, PNG, or WEBP · max 10 MB each. Re-upload replaces the file for that slot.</p>
                </div>
            </div>
            <div class="d-flex flex-column gap-3">
                ${n.map(s=>{const d=u.get(s),c=`att-file-${i(s)}`;return`<div class="border rounded p-3" data-attachment-slot="${i(s)}">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <label class="form-label mb-0" for="${c}">
                                    ${i(O(s))}
                                    <span class="text-danger">*</span>
                                </label>
                                ${d?'<span class="badge bg-success-subtle text-success">Uploaded</span>':'<span class="badge bg-warning-subtle text-warning">Required</span>'}
                            </div>
                                    ${d?`<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="small text-truncate" style="max-width:min(100%,22rem)">
                                            <i class="ri-file-3-line me-1"></i>${i(Q(d.original_name))}
                                            ${d.size?`<span class="text-muted"> · ${i(ge(d.size))}</span>`:""}
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-soft-primary" data-preview-uploaded="${i(d.uuid)}" aria-label="Preview ${i(O(s))}">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-soft-danger" data-remove-doc="${i(d.uuid)}" aria-label="Remove ${i(O(s))}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>`:""}
                            <input type="file" class="form-control" id="${c}" data-upload-label="${i(s)}" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                        </div>`}).join("")}
            </div>
        </div>`,e.querySelectorAll("[data-upload-label]").forEach(s=>{s.addEventListener("change",()=>{(async()=>{const d=s.files?.[0],c=s.dataset.uploadLabel||"";if(!d||!c||!t.applicationUuid)return;const r=new FormData;r.append("label",c),r.append("file",d);try{s.disabled=!0;const{data:m}=await window.axios.post(`/api/v1/applications/${t.applicationUuid}/documents`,r,{headers:{"Content-Type":"multipart/form-data"}}),p=m.data,g=[...t.documents.filter(h=>h.label!==p.label),p];k(m.message||"Document uploaded"),t.onChange(g)}catch(m){const p=m?.response?.data?.errors,g=p?Object.values(p).flat()[0]:null;$(String(g||m?.response?.data?.message||"Upload failed")),s.value=""}finally{s.disabled=!1}})()})}),e.querySelectorAll("[data-preview-uploaded]").forEach(s=>{s.addEventListener("click",()=>{const d=s.dataset.previewUploaded||"",c=t.documents.find(r=>r.uuid===d);!c||!t.applicationUuid||Be(t.applicationUuid,c)})}),e.querySelectorAll("[data-remove-doc]").forEach(s=>{s.addEventListener("click",()=>{(async()=>{const d=s.dataset.removeDoc||"";if(!(!d||!t.applicationUuid)&&await be("Remove this document?","You can upload a replacement afterward."))try{s.disabled=!0,await window.axios.delete(`/api/v1/applications/${t.applicationUuid}/documents/${d}`),k("Document removed"),t.onChange(t.documents.filter(c=>c.uuid!==d))}catch(c){$(c?.response?.data?.message||"Could not remove document"),s.disabled=!1}})()})})}function Re(e,t){return e.length?`<ul class="list-group list-group-flush">
        ${e.map(n=>`<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                    <span class="text-break"><span class="fw-medium">${i(O(n.label))}</span>
                    <span class="text-muted"> — ${i(Q(n.original_name))}</span></span>
                    <span class="d-flex align-items-center gap-2 text-nowrap">
                        <span class="text-muted small">${i(ge(n.size))}</span>
                        ${t?`<button type="button" class="btn btn-sm btn-soft-primary"
                                    data-preview-doc="${i(n.uuid)}"
                                    data-doc-label="${i(n.label)}"
                                    data-doc-name="${i(n.original_name)}"
                                    data-doc-mime="${i(n.mime_type||"")}"
                                    data-doc-pdf="${n.is_pdf||(n.mime_type||"").includes("pdf")||n.original_name.toLowerCase().endsWith(".pdf")?"1":"0"}"
                                    data-doc-image="${n.is_image||(n.mime_type||"").startsWith("image/")?"1":"0"}">
                                    <i class="ri-eye-line"></i> View
                                  </button>`:""}
                    </span>
                </li>`).join("")}
    </ul>`:'<p class="text-muted small mb-0">No documents uploaded yet.</p>'}const L=[{id:"project",label:"Project & Site",icon:"ri-map-pin-line",short:"Project"},{id:"owner",label:"Owner / Applicant",icon:"ri-user-line",short:"Owner"},{id:"building",label:"Building data",icon:"ri-building-2-line",short:"Building"},{id:"professionals",label:"Professionals",icon:"ri-account-box-line",short:"Pros"},{id:"documents",label:"Documents",icon:"ri-folder-2-line",short:"Docs"}];function ze(e){const t=e.toLowerCase();return t.includes("owner")||t.includes("applicant")?"owner":t.includes("property")||t.includes("location")?"project":t.includes("building")||t.includes("project /")||t.includes("project/")?"building":t.includes("professional")||t.includes("remark")||t.includes("affidavit")?"professionals":"building"}function Ge(e){const t={project:[],owner:[],building:[],professionals:[],documents:[]};return ve(e).forEach(n=>{t[ze(n.title)].push(n)}),t}function We(e="project"){return`<ul class="nav nav-tabs nav-tabs-custom nav-success mb-0 flex-wrap apics-app-form__tabs" role="tablist">
        ${L.map(t=>`<li class="nav-item" role="presentation">
                <button type="button"
                    class="nav-link text-nowrap${e===t.id?" active":""}"
                    data-app-form-tab="${t.id}"
                    role="tab"
                    aria-selected="${e===t.id?"true":"false"}">
                    <i class="${t.icon} align-bottom me-1"></i>
                    <span class="d-none d-md-inline">${i(t.label)}</span>
                    <span class="d-md-none">${i(t.short)}</span>
                </button>
            </li>`).join("")}
    </ul>`}function pe(e){document.querySelectorAll("[data-app-form-tab]").forEach(s=>{const d=s.dataset.appFormTab===e;s.classList.toggle("active",d),s.setAttribute("aria-selected",d?"true":"false")}),document.querySelectorAll("[data-app-form-panel]").forEach(s=>{const d=s.dataset.appFormPanel===e;s.classList.toggle("show",d),s.classList.toggle("active",d),s.classList.toggle("d-none",!d)});const t=document.getElementById("btn-app-form-back"),n=document.getElementById("btn-app-form-next"),u=L.findIndex(s=>s.id===e);t&&(t.disabled=u<=0),n&&n.classList.toggle("d-none",u>=L.length-1)}function Ye(e){const t=L.findIndex(n=>n.id===e);return L[Math.min(t+1,L.length-1)]?.id||e}function Qe(e){const t=L.findIndex(n=>n.id===e);return L[Math.max(t-1,0)]?.id||e}function Je(e){if(!e)return"—";try{return new Date(e).toLocaleString()}catch{return i(e)}}function F(e){return e.replaceAll("_"," ").replace(/\b\w/g,t=>t.toUpperCase())}function Ke(e){return e==="g03_compliance"?"G-03 Compliance":e==="g04_disapproval"?"G-04 Disapproval":F(e)}const Xe=[{id:"overview",label:"Overview & Location",icon:"ri-map-pin-line"},{id:"details",label:"Form details",icon:"ri-file-list-3-line"},{id:"documents",label:"Documents",icon:"ri-folder-2-line"},{id:"compliance",label:"Compliance",icon:"ri-alarm-warning-line"}];function Ze(e="overview",t=0){return`<ul class="nav nav-tabs nav-tabs-custom nav-success mb-0 flex-wrap" role="tablist">
        ${Xe.map(n=>{const u=n.id==="compliance"&&t>0?`<span class="badge bg-danger ms-1">${t}</span>`:"";return`<li class="nav-item" role="presentation">
                <button type="button"
                    class="nav-link text-nowrap${e===n.id?" active":""}"
                    data-app-view-tab="${n.id}"
                    role="tab"
                    aria-selected="${e===n.id?"true":"false"}">
                    <i class="${n.icon} align-bottom me-1"></i>${i(n.label)}${u}
                </button>
            </li>`}).join("")}
    </ul>`}function ue(e){document.querySelectorAll("[data-app-view-tab]").forEach(t=>{const n=t.dataset.appViewTab===e;t.classList.toggle("active",n),t.setAttribute("aria-selected",n?"true":"false")}),document.querySelectorAll("[data-app-view-panel]").forEach(t=>{const n=t.dataset.appViewPanel===e;t.classList.toggle("show",n),t.classList.toggle("active",n),t.classList.toggle("d-none",!n)})}function et(e){const t=e.payload||{},n=String(t.owner_name||"—"),u=String(t.owner_email||""),s=String(t.owner_contact||"—"),d=String(t.barangay||"—"),c=String(t.lot_number||"—"),r=String(t.block_number||"—"),m=String(t.street_address||e.project_location||"—"),p=Ae({latitude:e.latitude,longitude:e.longitude}),g=p?`${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}`:"No coordinates pinned",h=p!=null?`<div class="d-flex flex-wrap gap-1 mt-2">
                <a class="btn btn-sm btn-primary" href="${i(Te(p.lat,p.lng))}" target="_blank" rel="noopener noreferrer">
                    <i class="ri-guide-line align-bottom me-1"></i>Get Directions
                </a>
                <a class="btn btn-sm btn-soft-secondary" href="${i(Ie(p.lat,p.lng,e.project_title))}" target="_blank" rel="noopener noreferrer">
                    Open in Maps
                </a>
               </div>`:"";return`
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Application No.</p>
                <p class="fw-semibold mb-0">${i(e.application_no||"—")}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Status</p>
                <div>${Y(e.status)}</div>
            </div>
            <div class="col-md-4 col-sm-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Classification</p>
                <p class="mb-0">${i(e.classification?F(e.classification):"—")}</p>
            </div>
            <div class="col-12">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Project</p>
                <p class="fw-medium mb-0">${i(e.project_title||"—")}</p>
            </div>
        </div>

        <div class="row g-3 align-items-stretch mb-3">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Site location</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Address</p>
                            <p class="fw-medium mb-0">${i(m)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Barangay</p>
                            <p class="mb-0">${i(d)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Lot</p>
                            <p class="mb-0">${i(c)}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Block</p>
                            <p class="mb-0">${i(r)}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Coordinates</p>
                            <p class="font-monospace small mb-0">${i(g)}</p>
                        </div>
                    </div>
                    ${h}
                </div>
            </div>
            <div class="col-lg-5">
                ${ke({latitude:e.latitude,longitude:e.longitude,address:e.project_location,label:e.project_title||e.application_no||"Project site"})}
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Owner / applicant</p>
                    <p class="fw-semibold mb-1">${i(n)}</p>
                    <p class="small mb-1">${Fe(u||null)}</p>
                    <p class="small text-muted mb-0">${i(s)}</p>
                </div>
            </div>
            <div class="col-md-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <p class="text-muted text-uppercase fw-medium fs-11 mb-2">Timeline</p>
                    ${Me(Pe(e))}
                </div>
            </div>
        </div>
    `}function tt(e,t){const n=e.payload||{},u=n.estimated_cost,s=n.lot_area,d=n.floor_area,c=`
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Estimated project cost</p>
                    <p class="font-monospace fw-bold fs-5 mb-0">${u!=null&&u!==""?Ce(u):"—"}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Lot area</p>
                    <p class="font-monospace fw-semibold mb-0">${s!=null&&s!==""?de(s):"—"}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light-subtle h-100">
                    <p class="text-muted fs-11 text-uppercase mb-1">Total floor area</p>
                    <p class="font-monospace fw-semibold mb-0">${d!=null&&d!==""?de(d):"—"}</p>
                </div>
            </div>
        </div>
    `,m=ve(t).map(p=>{const g=p.fields.map(h=>{const x=n[h.name];return x==null||x===""?"":`<div class="col-sm-6 col-lg-4">
                        <p class="text-muted text-uppercase fw-medium fs-11 mb-1">${i(h.label)}</p>
                        <p class="mb-0 text-break">${De(h,x)}</p>
                    </div>`}).filter(Boolean).join("");return g?`<div class="mb-3">
                <h6 class="fs-13 text-uppercase text-muted mb-2">${i(p.title)}</h6>
                <div class="row g-3">${g}</div>
            </div>`:""}).filter(Boolean).join("");return`${c}
        <div class="border rounded p-3">
            <p class="text-muted small mb-3">${i(e.form?.code||"—")} · ${i(e.form?.title||"")}</p>
            ${m||'<p class="text-muted mb-0">No form details provided.</p>'}
        </div>`}function at(e){const t=e.documents||[],n=e.form?.required_attachments||[];if(!t.length&&!n.length)return'<p class="text-muted mb-0">No documents uploaded yet.</p>';const u=new Map(t.map(c=>[c.label,c]));return`<div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Attachment</th><th>File</th><th>Status</th><th class="text-end">Preview</th></tr>
            </thead>
            <tbody>${[...n,...t.map(c=>c.label).filter(c=>!n.includes(c))].map(c=>{const r=u.get(c),m=n.includes(c);let p;r?p='<span class="badge bg-success-subtle text-success border border-success-subtle">Uploaded</span>':m?p='<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">Mandatory deficient</span>':p='<span class="badge bg-secondary-subtle text-secondary">Optional</span>';const g=r?`<button type="button" class="btn btn-sm btn-soft-primary"
                        data-preview-doc="${i(r.uuid)}"
                        data-doc-label="${i(r.label)}"
                        data-doc-name="${i(r.original_name)}"
                        data-doc-mime="${i(r.mime_type||"")}"
                        data-doc-pdf="${r.is_pdf||(r.mime_type||"").includes("pdf")||r.original_name.toLowerCase().endsWith(".pdf")?"1":"0"}"
                        data-doc-image="${r.is_image||(r.mime_type||"").startsWith("image/")?"1":"0"}">
                        <i class="ri-eye-line"></i> View
                   </button>`:"—";return`<tr>
                <td class="fw-medium">${i(F(c))}</td>
                <td class="small text-break">${r?i(Q(r.original_name)):"—"}</td>
                <td>${p}</td>
                <td class="text-end">${g}</td>
            </tr>`}).join("")}</tbody>
        </table>
    </div>
    <div class="mt-3 d-none" id="applicant-view-docs-fallback">${Re(t,e.uuid)}</div>`}function it(e){return e.length?e.map(t=>{const n=t.status==="issued",u=t.appeals||[],s=u.length>0?`<div class="mt-2 pt-2 border-top">
                        <p class="text-muted fs-11 text-uppercase mb-1">Your appeals</p>
                        ${u.map(d=>`<div class="small mb-2">
                                    <span class="badge bg-secondary-subtle text-secondary">${i(F(d.status))}</span>
                                    <div class="mt-1" style="white-space:pre-wrap">${i(d.grounds||"")}</div>
                                    ${d.resolution_notes?`<div class="text-muted mt-1">Resolution: ${i(d.resolution_notes)}</div>`:""}
                                </div>`).join("")}
                       </div>`:"";return`<div class="border rounded p-3 mb-2 bg-light-subtle">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <span class="fw-semibold">${i(t.notice_no)}</span>
                        <span class="badge bg-warning-subtle text-warning ms-1">${i(Ke(t.type))}</span>
                        <span class="badge bg-info-subtle text-info ms-1">${i(F(t.status))}</span>
                    </div>
                    <div class="text-muted small">Due ${i(Je(t.due_at))}</div>
                </div>
                <p class="fw-medium mb-1">${i(t.title)}</p>
                <div class="fs-13 mb-2" style="white-space:pre-wrap">${i(t.body||"—")}</div>
                ${t.inspection?.notes?`<p class="text-muted fs-11 text-uppercase mb-1">Inspection findings</p>
                           <div class="fs-13 mb-2" style="white-space:pre-wrap">${i(t.inspection.notes)}</div>`:""}
                ${s}
                ${n?`<button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                data-appeal-notice="${i(t.uuid)}"
                                data-appeal-no="${i(t.notice_no)}">
                                File an appeal
                           </button>
                           <p class="text-muted small mb-0 mt-1">Disagree with the findings? Explain your grounds and OCBO will review.</p>`:""}
            </div>`}).join(""):`<div class="text-center border border-2 border-dashed rounded-3 p-4 bg-light-subtle">
            <i class="ri-shield-check-line display-6 text-success d-block mb-2"></i>
            <p class="fw-medium mb-1">No compliance notices</p>
            <p class="text-muted small mb-0">If OCBO issues a G-03/G-04 notice, it will appear here with findings and an option to appeal.</p>
        </div>`}function nt(e,t){const n=e.compliance_notices||[];return`
        <div class="tab-content">
            <div class="tab-pane fade show active" data-app-view-panel="overview" role="tabpanel">
                ${et(e)}
            </div>
            <div class="tab-pane fade d-none" data-app-view-panel="details" role="tabpanel">
                ${tt(e,t)}
            </div>
            <div class="tab-pane fade d-none" data-app-view-panel="documents" role="tabpanel">
                ${at(e)}
            </div>
            <div class="tab-pane fade d-none" data-app-view-panel="compliance" role="tabpanel">
                ${it(n)}
            </div>
        </div>
    `}function st(e){return`<div class="d-flex flex-wrap align-items-center gap-2">
        ${Y(e.status)}
        <span class="text-muted small">${i(e.form?.code||"—")}</span>
        ${e.classification?`<span class="badge bg-info-subtle text-info">${i(F(e.classification))}</span>`:""}
    </div>`}const lt=new Set(["submitted","under_evaluation","for_inspection","for_compliance","for_payment","for_releasing","disapproved"]),ot=new Set(["QMS-36","QMS-37"]),dt=new Set(["draft","submitted","under_evaluation","for_compliance"]);function me(e){return dt.has(e||"")}function fe(e){const t=e.length,n=e.filter(c=>c.status==="draft").length,u=e.filter(c=>c.status==="released").length,s=e.filter(c=>lt.has(c.status)).length,d=(c,r)=>{document.querySelectorAll(`[data-stat="${c}"]`).forEach(m=>{m.textContent=String(r)})};d("total",t),d("draft",n),d("in_progress",s),d("released",u)}function mt(){const e=document.getElementById("applications-table"),t=document.getElementById("applications-skeleton"),n=document.getElementById("applications-auth-gate"),u=document.getElementById("applications-workspace"),s=document.getElementById("form-application");if(!e||!s)return;if(!_e()){n?.classList.remove("d-none"),u?.classList.add("d-none");return}n?.classList.add("d-none"),u?.classList.remove("d-none");const d=Se(),c=document.getElementById("applications-welcome");c&&(c.textContent=d?.name?`Welcome, ${d.name}`:"Welcome");let r=null,m=[],p=null,g=null,h={},x=[];const E=document.getElementById("application-form-type"),H=document.getElementById("modal-application-form-label"),R=document.getElementById("application-form-tabnav"),J=document.getElementById("application-attachments"),U=document.getElementById("btn-view-edit"),K=document.getElementById("btn-view-submit"),A=document.querySelector('[data-location-picker][data-address-input="application-project-location"]');let S=null,j=null,M=null,T="project";const we=a=>{if(!a)return"—";try{return new Date(a).toLocaleString()}catch{return i(a)}},X=()=>{const a=A?.querySelector("[data-location-toggle-map]");!a||!A||a.dataset.bound==="1"||(a.dataset.bound="1",a.addEventListener("click",()=>{const o=A.classList.toggle("is-map-collapsed");a.setAttribute("aria-expanded",o?"false":"true");const l=a.querySelector("span");l&&(l.textContent=o?"Show map":"Hide map"),o||(window.setTimeout(()=>S?.invalidateSize(),80),window.setTimeout(()=>S?.invalidateSize(),280))}))},z=()=>{if(S)return S;const a=document.getElementById("application-project-location");if(!A||!a)return null;try{S=Ue({root:A,addressInput:a,latInput:document.getElementById("application-project-location-lat"),lngInput:document.getElementById("application-project-location-lng"),required:!0}),A.dataset.locationMounted="1",X()}catch{S=null}return S},Z=()=>m.find(a=>a.uuid===E.value),B=a=>{T=a,pe(a),a==="project"?window.setTimeout(()=>{z()?.invalidateSize(),W(s)},120):window.setTimeout(()=>W(s),120)},ee=(a={})=>{He(s,Z()?.schema||null,a,Ge)},ye=()=>{R&&(R.innerHTML=We(T),R.querySelectorAll("[data-app-form-tab]").forEach(a=>{a.addEventListener("click",()=>{const o=a.dataset.appFormTab;o&&B(o)})})),pe(T)},he=a=>{const o=document.getElementById("application-map-expand-body"),l=document.getElementById("modal-application-map-expand-label");o&&(M?.destroy(),M=null,l&&(l.textContent=`Project site · ${a.application_no||""}`.trim()),o.innerHTML=`<div class="p-3">${Ve({latitude:a.latitude,longitude:a.longitude,address:a.project_location,label:a.project_title||a.application_no||"Project site"})}</div>`,q("modal-application-map-expand"),window.setTimeout(()=>{M=re(o),M?.invalidateSize()},200),window.setTimeout(()=>M?.invalidateSize(),500))},te=a=>{ue(a),a==="overview"&&(window.setTimeout(()=>j?.invalidateSize(),120),window.setTimeout(()=>j?.invalidateSize(),350))},ae=async a=>{p=a;const o=document.getElementById("application-view-body"),l=document.getElementById("modal-application-view-label"),f=document.getElementById("application-view-header-meta"),b=document.getElementById("application-view-tabnav");if(o){j?.destroy(),j=null,f&&(f.innerHTML=""),b&&(b.innerHTML=""),o.innerHTML='<div class="text-center text-muted py-4">Loading…</div>',q("modal-application-view");try{const{data:_}=await window.axios.get(`/api/v1/applications/${a.uuid}`),w=_.data||a;p=w;const C=m.find(v=>v.uuid===w.form?.uuid)||w.form,I=w.compliance_notices||[];l&&(l.textContent=`Application details · ${w.application_no||""}`.trim()),f&&(f.innerHTML=st(w)),b&&(b.innerHTML=Ze("overview",I.length),b.querySelectorAll("[data-app-view-tab]").forEach(v=>{v.addEventListener("click",()=>{const y=v.dataset.appViewTab;y&&te(y)})})),o.innerHTML=nt(w,C?.schema||null),ue("overview"),Ne(o,w.uuid),j=re(o),window.setTimeout(()=>j?.invalidateSize(),200),window.setTimeout(()=>j?.invalidateSize(),500),o.querySelectorAll("[data-ops-expand-map]").forEach(v=>{v.addEventListener("click",y=>{y.preventDefault(),y.stopPropagation(),he(w)})}),o.querySelectorAll("[data-appeal-notice]").forEach(v=>{v.addEventListener("click",()=>{const y=v.dataset.appealNotice,V=v.dataset.appealNo||"this notice";y&&$e(y,V,w)})}),I.length&&w.status==="for_compliance"&&te("compliance");const D=w.status==="draft",N=me(w.status);U?.classList.toggle("d-none",!N),K?.classList.toggle("d-none",!D),U&&(U.textContent=D?"Edit draft":"Edit / upload docs")}catch(_){o.innerHTML=`<p class="text-danger mb-0">${i(_?.response?.data?.message||"Unable to load details")}</p>`}}},P=()=>{J&&Oe(J,{applicationUuid:g,requiredLabels:Z()?.required_attachments||[],documents:x,onChange:a=>{x=a,P(),r?.reload(!1)}})},xe=()=>{if(E.innerHTML="",!m.length){const a=document.createElement("option");a.value="",a.textContent="No active filing forms available",E.appendChild(a);return}m.forEach((a,o)=>{const l=document.createElement("option");l.value=a.uuid,l.textContent=`${a.code} — ${a.title}`,o===0&&(l.selected=!0),E.appendChild(l)})},G=(a,o)=>{if(g=a==="edit"&&o?o.uuid:null,h={...o?.payload||{}},x=[...o?.documents||[]],T="project",document.getElementById("application-uuid").value=g||"",document.getElementById("application-project-title").value=o?.project_title||"",z()?.setValue({address:o?.project_location||"City of San Fernando, Pampanga",latitude:o?.latitude??null,longitude:o?.longitude??null}),S||(document.getElementById("application-project-location").value=o?.project_location||"City of San Fernando, Pampanga"),xe(),o?.form?.uuid&&(E.value=o.form.uuid),E.disabled=a==="edit",ye(),ee(h),P(),B("project"),H){const l=o?.status||"draft";H.textContent=a==="create"?"New permit application":l==="draft"?"Edit draft application":`Edit application (${l.replaceAll("_"," ")})`}},ie=()=>{G("create"),q("modal-application-form")},ne=async a=>{try{const{data:o}=await window.axios.get(`/api/v1/applications/${a.uuid}`),l=o.data||a;G("edit",l),q("modal-application-form")}catch(o){$(o?.response?.data?.message||"Unable to load application"),G("edit",a),q("modal-application-form")}},$e=async(a,o,l)=>{const f=await Le({title:`Appeal ${o}?`,text:"Explain why the inspection findings or notice should be reconsidered.",inputLabel:"Appeal grounds",inputPlaceholder:"Describe your side of the issue and any supporting context…",confirmButtonText:"Submit appeal",minLength:10});if(f)try{const b=await window.axios.post(`/api/v1/compliance-notices/${a}/appeals`,{grounds:f});k(b.data.message||"Appeal filed"),await ae(l),await r?.reload(!1)}catch(b){const _=b?.response?.data?.errors,w=_?Object.values(_).flat()[0]:null;$(String(w||b?.response?.data?.message||"Appeal failed"))}},se=async a=>{if(await be("Submit application for intake?","Required attachments must be uploaded first. You can still update details or documents while status is Submitted / Under evaluation / For compliance."))try{const l=await window.axios.post(`/api/v1/applications/${a.uuid}/submit`);k(l.data.message||"Application submitted"),oe("modal-application-view"),await r?.reload(!1)}catch(l){const f=l?.response?.data?.errors,b=f?Object.values(f).flat()[0]:null;$(String(b||l?.response?.data?.message||"Submit failed"))}};document.getElementById("btn-new-application")?.addEventListener("click",()=>ie()),document.getElementById("btn-new-application-secondary")?.addEventListener("click",()=>ie()),E.addEventListener("change",()=>{h=ce(s),ee(h),P()}),document.getElementById("btn-app-form-back")?.addEventListener("click",()=>{B(Qe(T))}),document.getElementById("btn-app-form-next")?.addEventListener("click",()=>{B(Ye(T))}),document.getElementById("modal-application-form")?.addEventListener("shown.bs.modal",()=>{z()?.invalidateSize(),W(s),X()}),U?.addEventListener("click",()=>{p&&(oe("modal-application-view"),ne(p))}),K?.addEventListener("click",()=>{p&&se(p)}),(async()=>{try{const{data:a}=await window.axios.get("/api/v1/applications/forms"),o=a.data||[];m=o.filter(l=>ot.has(l.code)),m.length||(m=o),r=await je({table:e,exportFileName:"my-applications",rowId:"uuid",searchMode:"client",filters:[{id:"status",label:"Status",options:[{value:"",label:"All"},{value:"draft",label:"Draft"},{value:"submitted",label:"Submitted"},{value:"under_evaluation",label:"Under evaluation"},{value:"for_inspection",label:"For inspection"},{value:"for_payment",label:"For payment"},{value:"for_releasing",label:"For releasing"},{value:"for_compliance",label:"For compliance"},{value:"released",label:"Released"},{value:"disapproved",label:"Disapproved"}],match:(l,f)=>(l.status||"")===f}],columns:[{data:"application_no",title:"Application No.",responsivePriority:1,render:l=>`<span class="fw-medium">${i(String(l??""))}</span>`},{data:"project_title",title:"Project",responsivePriority:1,render:(l,f,b)=>`
                            <div>${i(b.project_title||"—")}</div>
                            <div class="text-muted small">${i(b.project_location||"")}</div>`},{data:"status",title:"Status",responsivePriority:1,render:l=>Y(String(l??""))},{data:"form",title:"Form",responsivePriority:3,render:(l,f,b)=>i(b.form?.code||"—")},{data:"submitted_at",title:"Submitted",responsivePriority:4,render:l=>i(we(l))}],actions:[{id:"view",label:"View",onClick:l=>{ae(l)}},{id:"edit",label:"Edit / upload docs",visible:l=>me(l.status),onClick:l=>{ne(l)}},{id:"submit",label:"Submit",visible:l=>l.status==="draft",onClick:l=>{se(l)}}],fetchData:async()=>{const{data:l}=await window.axios.get("/api/v1/applications",{params:{per_page:100}}),f=l.data?.items||[];return fe(f),f}})}catch(a){$(a?.response?.data?.message||"Unable to load your applications."),fe([])}finally{t?.classList.add("d-none")}})(),s.addEventListener("submit",async a=>{a.preventDefault();const o=document.getElementById("btn-save-application"),l=E.value,f=document.getElementById("application-project-title").value.trim(),b=document.getElementById("application-project-location").value.trim(),_=document.getElementById("application-project-location-lat")?.value,w=document.getElementById("application-project-location-lng")?.value,C=_?Number(_):null,I=w?Number(w):null;if(!l){$("Select a permit form."),B("project");return}if(!f||!b){$("Project title and location are required."),B("project");return}const D=qe(s);if(D){$(D);const v=s.querySelector("[data-dyn-field][required]"),le=(Array.from(s.querySelectorAll("[data-dyn-field][required]")).find(Ee=>!Ee.value.trim())||v)?.closest("[data-app-form-panel]")?.dataset.appFormPanel;le&&B(le);return}o&&(o.disabled=!0);const N={form_definition_uuid:l,project_title:f,project_location:b,latitude:C!=null&&Number.isFinite(C)?C:null,longitude:I!=null&&Number.isFinite(I)?I:null,payload:ce(s)};try{if(g){const{data:v}=await window.axios.put(`/api/v1/applications/${g}`,N);x=v.data?.documents||x,k(v.message||"Application updated"),P(),await r?.reload(!1)}else{const{data:v}=await window.axios.post("/api/v1/applications",N),y=v.data;g=y.uuid,x=y.documents||[],document.getElementById("application-uuid").value=g,E.disabled=!0,H&&(H.textContent="Edit draft application"),k("Draft saved — upload required documents below."),P(),await r?.reload(!1)}}catch(v){const y=v?.response?.data?.errors,V=y?Object.values(y).flat()[0]:null;$(String(V||v?.response?.data?.message||"Could not save application"))}finally{o&&(o.disabled=!1)}})}export{mt as initApplicationsPage};
