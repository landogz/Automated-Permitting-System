import{t as m,b as k,f as g,s as h,e as s,j as R}from"./app-Da3N1zM7.js";import{c as G}from"./create-apics-datatable-B4drYWec.js";import{o as w}from"./index-CG3ATP8u.js";import{s as j,a as q,b as O,t as U}from"./ops-completed-DHwq9PHJ.js";import{H as V,z}from"./site-map-viewer-BPmwqozC.js";import{i as Q}from"./application-select-BSDo_mRl.js";function _(e){if(!e)return"—";try{return new Date(e).toLocaleString()}catch{return s(e)}}function y(e){return e.replaceAll("_"," ").replace(/\b\w/g,a=>a.toUpperCase())}function P(e){const i=e==="g04_disapproval"?"bg-danger-subtle text-danger":"bg-warning-subtle text-warning",p=e==="g03_compliance"?"G-03 Compliance":e==="g04_disapproval"?"G-04 Disapproval":y(e);return`<span class="badge ${i}">${s(p)}</span>`}function E(e){return z(e,{pulse:e==="issued"||e==="appealed"})}function $(e){const a=(r,v)=>{document.querySelectorAll(`[data-notice-stat="${r}"]`).forEach(d=>{d.textContent=v})};if(!e){["issued","appealed","closed","g03","g04","g04_active","g04_archived","total"].forEach(r=>a(r,"—"));return}const i=e.g04_active??e.g04??0,p=e.g04_archived??0;a("issued",String(e.issued??0)),a("appealed",String(e.appealed??0)),a("closed",String(e.closed??0)),a("g03",String(e.g03??0)),a("g04",String(i)),a("g04_active",String(i)),a("g04_archived",String(p)),a("total",String(e.total??0))}function W(e){return e.length?e.map(a=>`<div class="border rounded p-3 mb-2 bg-light-subtle">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>${E(a.status)}</div>
                    <div class="text-muted small">Filed ${s(_(a.filed_at))} · ${s(a.filed_by?.name||"—")}</div>
                </div>
                <p class="mb-1 fs-13"><span class="text-muted">Grounds:</span> ${s(a.grounds||"—")}</p>
                ${a.resolution_notes?`<p class="mb-1 fs-13"><span class="text-muted">Resolution:</span> ${s(a.resolution_notes)}</p>`:""}
                ${a.resolved_at?`<p class="mb-0 text-muted small">Resolved ${s(_(a.resolved_at))} · ${s(a.resolved_by?.name||"—")}</p>`:""}
            </div>`).join(""):'<p class="text-muted mb-0 fs-13">No appeals filed for this notice.</p>'}function J(e){const a=e.application||{},i=e.inspection||{},p=e.appeals||[];return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Notice No.</p>
                <p class="fw-semibold mb-0">${s(e.notice_no)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Type</p>
                <div>${P(e.type)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Status</p>
                <div>${E(e.status)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Issued by</p>
                <p class="mb-0">${s(e.issued_by?.name||"—")}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Application</h6>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">Number</p>
                            <p class="fw-medium mb-0">${s(a.application_no||"—")}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">App status</p>
                            <p class="mb-0 text-capitalize">${s(y(String(a.status||"—")))}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Project</p>
                            <p class="fw-medium mb-0">${s(a.project_title||"—")}</p>
                            <p class="text-muted small mb-0">${s(a.project_location||"")}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Owner</p>
                            <p class="mb-0">${s(String(a.owner_name||"—"))}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Classification</p>
                            <p class="mb-0 text-capitalize">${s(a.classification?y(String(a.classification)):"—")}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Occupancy</p>
                            <p class="mb-0">${s(String(a.occupancy||"—"))}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Timeline</h6>
                    <p class="mb-2"><span class="text-muted">Issued:</span> ${s(_(e.issued_at))}</p>
                    <p class="mb-2"><span class="text-muted">Due:</span> ${s(_(e.due_at))}</p>
                    <p class="mb-2"><span class="text-muted">Linked inspection:</span> ${s(i.inspection_no||"—")}</p>
                    <p class="mb-2"><span class="text-muted">Result:</span> ${s(i.result?y(String(i.result)):"—")}</p>
                    ${i.notes?`<p class="mb-0"><span class="text-muted">Inspector findings:</span></p>
                               <p class="mb-0 fs-13 mt-1" style="white-space:pre-wrap">${s(i.notes)}</p>`:""}
                </div>
            </div>
        </div>

        <div class="border rounded p-3 mb-4">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Notice content</h6>
            <p class="fw-semibold mb-2">${s(e.title)}</p>
            <div class="fs-13 mb-0" style="white-space:pre-wrap">${s(e.body||"—")}</div>
        </div>

        <h6 class="fs-13 text-uppercase text-muted mb-2">Appeals (${p.length})</h6>
        ${W(p)}
    `}function T(){return[{data:"notice_no",title:"Notice No.",responsivePriority:1,render:e=>`<span class="fw-semibold">${s(String(e??""))}</span>`},{data:"type",title:"Type",responsivePriority:1,render:e=>P(String(e??""))},{data:"status",title:"Status",responsivePriority:1,render:e=>E(String(e??""))},{data:"title",title:"Title",responsivePriority:2,render:(e,a,i)=>`<div class="fw-medium text-truncate" style="max-width:16rem">${s(String(e??""))}</div>
                 <div class="text-muted small">${i.appeal_count?`${i.appeal_count} appeal(s)`:"No appeals"}</div>`},{data:"application",title:"Application",responsivePriority:1,render:(e,a,i)=>`<div class="fw-medium">${s(i.application?.application_no||"—")}</div>
                 <div class="text-muted small text-truncate" style="max-width:14rem">${s(i.application?.project_title||"")}</div>`},{data:"due_at",title:"Appeal window",responsivePriority:2,render:(e,a,i)=>V({type:i.type,status:i.status,issuedAt:i.issued_at,dueAt:i.due_at})},{data:"issued_by",title:"Issuer",responsivePriority:4,render:(e,a,i)=>s(i.issued_by?.name||"—")}]}function ae(){const e=document.getElementById("notices-table"),a=document.getElementById("notices-completed-table"),i=document.getElementById("form-issue-notice");if(!e||!i)return;const p=Q(i);let r=null,v=null,d=null,f=null;const F=async()=>{const t=p.get("notice-app-uuid")?.getValue()||"",l=document.getElementById("notice-body"),n=document.getElementById("notice-title");if(f=null,!(!t||!l))try{const{data:c}=await window.axios.get(`/api/v1/staff/applications/${t}`),o=(c.data?.inspections||[]).find(b=>b.result==="failed"&&b.notes);if(!o)return;if(f=o.uuid||null,n&&!n.value.trim()&&(n.value="Notice of Compliance — inspection deficiencies"),!l.value.trim()){const b=o.inspection_no||"site inspection";l.value=`Inspection findings (${b}):

${o.notes}

Required corrective actions:
1. 
2. 

Please comply within the appeal window stated on this notice.`}}catch{}};document.getElementById("notice-app-uuid")?.addEventListener("change",()=>{F()});const x=async()=>{await Promise.all([r?.reload(!1),v?.reload(!1)])},B=document.getElementById("notice-detail-body"),I=document.getElementById("modal-notice-detail-label"),A=document.getElementById("btn-notice-view-app"),S=document.getElementById("btn-notice-file-appeal"),C=document.getElementById("btn-notice-resolve-appeal"),H=document.getElementById("form-resolve-appeal"),N=t=>{d=t,I&&(I.textContent=`Notice · ${t.notice_no}`),B&&(B.innerHTML=J(t)),A?.classList.toggle("d-none",!t.application?.uuid),S?.classList.toggle("d-none",t.status!=="issued"),C?.classList.toggle("d-none",!(t.status==="appealed"&&t.pending_appeal_uuid)),h("modal-notice-detail")},L=async t=>{const l=await R({title:"File appeal?",text:`Explain the grounds for appealing ${t.notice_no}.`,inputLabel:"Appeal grounds",inputPlaceholder:"Cite why the findings should be re-evaluated…",confirmButtonText:"File appeal",minLength:10});if(l)try{await window.axios.post(`/api/v1/staff/compliance-notices/${t.uuid}/appeals`,{grounds:l}),g("modal-notice-detail"),k("Appeal filed"),await x()}catch(n){m(n?.response?.data?.message||"Appeal failed")}};A?.addEventListener("click",()=>{const t=d?.application?.uuid;t&&(g("modal-notice-detail"),w(t))}),S?.addEventListener("click",()=>{d&&L(d)}),C?.addEventListener("click",()=>{if(!d?.pending_appeal_uuid)return;const t=document.getElementById("resolve-appeal-uuid");t&&(t.value=d.pending_appeal_uuid);const l=document.getElementById("resolve-appeal-notes");l&&(l.value=""),g("modal-notice-detail"),h("modal-resolve-appeal")}),H?.addEventListener("submit",async t=>{t.preventDefault();const l=document.getElementById("resolve-appeal-uuid").value.trim();if(!l){m("No pending appeal selected");return}try{await window.axios.post(`/api/v1/staff/compliance-appeals/${l}/resolve`,{status:document.getElementById("resolve-appeal-status").value,resolution_notes:document.getElementById("resolve-appeal-notes").value.trim()||void 0}),k("Appeal resolved"),g("modal-resolve-appeal"),await x()}catch(n){m(n?.response?.data?.message||"Resolve failed")}});const M=[{id:"view-notice",label:"View Notice",primary:!0,onClick:t=>N(t)},{id:"view-app",label:"View application details",visible:t=>!!t.application?.uuid,onClick:t=>{t.application?.uuid&&w(t.application.uuid)}},{id:"appeal",label:"File appeal",dividerBefore:!0,visible:t=>t.status==="issued",onClick:t=>{L(t)}},{id:"resolve",label:"Resolve appeal",visible:t=>t.status==="appealed"&&!!t.pending_appeal_uuid,onClick:t=>{d=t;const l=document.getElementById("resolve-appeal-uuid");l&&t.pending_appeal_uuid&&(l.value=t.pending_appeal_uuid),h("modal-resolve-appeal")}}],D="All filings fully compliant — no pending G-03/G-04 notices. Use Issue notice to create one manually.";(async()=>{try{r=await G({table:e,exportFileName:"compliance-notices-active",rowId:"uuid",searchMode:"server",order:[[0,"desc"]],emptyMessage:D,noResultsMessage:D,filters:[{id:"type",label:"Type",options:[{value:"",label:"All"},{value:"g03_compliance",label:"G-03 Compliance"},{value:"g04_disapproval",label:"G-04 Disapproval"}]}],columns:T(),actions:M,fetchData:async({search:t,filters:l})=>{const{data:n}=await window.axios.get("/api/v1/staff/compliance-notices",{params:{search:t||void 0,type:l.type||void 0,bucket:"active",per_page:100}});$(n.data?.summary||null);const c=n.data?.items||[];j("notice-queue",n.data?.meta?.total??c.length);const u=e.closest(".apics-dt-shell");let o=u?.querySelector(".apics-empty-state");return!c.length&&u?(o||(o=document.createElement("div"),o.className="apics-empty-state",o.innerHTML=`
                                <div class="apics-empty-state__icon" aria-hidden="true"><i class="ri-checkbox-circle-line"></i></div>
                                <h4 class="apics-empty-state__title">All Filings Fully Compliant</h4>
                                <p class="apics-empty-state__copy">No pending G-03 (Compliance) or G-04 (Disapproval) notices required. All applications are clear of deficiencies.</p>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-issue-notice">+ Issue Notice Manually</button>`,u.appendChild(o)),o.classList.remove("d-none"),u.classList.add("is-empty")):(o?.classList.add("d-none"),u?.classList.remove("is-empty")),c}}),a&&(v=await G({table:a,exportFileName:"compliance-notices-completed",rowId:"uuid",searchMode:"server",order:[[0,"desc"]],filters:[{id:"type",label:"Type",options:[{value:"",label:"All"},{value:"g03_compliance",label:"G-03 Compliance"},{value:"g04_disapproval",label:"G-04 Disapproval"}]}],columns:T(),actions:[{id:"view-notice",label:"View Notice",primary:!0,onClick:t=>N(t)},{id:"view-app",label:"View application details",visible:t=>!!t.application?.uuid,onClick:t=>{t.application?.uuid&&w(t.application.uuid)}}],fetchData:async({search:t,filters:l})=>{const{data:n}=await window.axios.get("/api/v1/staff/compliance-notices",{params:{search:t||void 0,type:l.type||void 0,bucket:"completed",per_page:100}});$(n.data?.summary||null);const c=n.data?.items||[];return q("notice-queue",n.data?.meta?.total??c.length),c}})),O("notice-queue",()=>v?.raw,()=>r?.raw)}catch(t){m(t?.response?.data?.message||"Unable to load notices"),$(null)}})(),i.addEventListener("submit",async t=>{t.preventDefault();const l=p.get("notice-app-uuid")?.getValue()||document.getElementById("notice-app-uuid").value.trim();if(!l){m("Please select an application");return}const n=document.getElementById("btn-issue-notice");n&&(n.disabled=!0);try{const{data:c}=await window.axios.post(`/api/v1/staff/applications/${l}/compliance-notices`,{type:document.getElementById("notice-type").value,title:document.getElementById("notice-title").value.trim(),body:document.getElementById("notice-body").value.trim(),inspection_uuid:f||void 0});U("Notice issued",c.data?.next_step),i.reset(),f=null,p.get("notice-app-uuid")?.clear(),g("modal-issue-notice"),await x()}catch(c){const u=c?.response?.data?.errors,o=u?Object.values(u).flat()[0]:null;m(String(o||c?.response?.data?.message||"Issue failed"))}finally{n&&(n.disabled=!1)}})}export{ae as initComplianceNoticesPage};
