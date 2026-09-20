import{e as i,t as g,b as $,f as v,s as E}from"./app-Cwtxlj4R.js";import{c as w}from"./create-apics-datatable-DGrRLurC.js";import{o as f,a as S}from"./index-C2GNI2hq.js";import{i as L}from"./application-select-BGgzVrVJ.js";import"./site-map-viewer-BGhnxqAR.js";const I={g01_releasing:"G-01 Releasing",o02_occupancy:"O-02 Occupancy",e_series:"E-series",g05:"G-05",g06:"G-06"};function d(e){if(!e)return"—";try{return new Date(e).toLocaleString()}catch{return i(e)}}function x(e){return e.replaceAll("_"," ").replace(/\b\w/g,a=>a.toUpperCase())}function k(e){return`<span class="badge ${{g01_releasing:"bg-success-subtle text-success",o02_occupancy:"bg-primary-subtle text-primary",e_series:"bg-info-subtle text-info",g05:"bg-warning-subtle text-warning",g06:"bg-secondary-subtle text-secondary"}[e]||"bg-info-subtle text-info"}">${i(I[e]||x(e))}</span>`}function y(e){const a=(l,c)=>{document.querySelectorAll(`[data-lb-stat="${l}"]`).forEach(n=>{n.textContent=c})};if(!e){["g01_releasing","o02_occupancy","e_series","g05","g06","total"].forEach(l=>a(l,"—"));return}a("g01_releasing",String(e.g01_releasing??0)),a("o02_occupancy",String(e.o02_occupancy??0)),a("e_series",String(e.e_series??0)),a("g05",String(e.g05??0)),a("g06",String(e.g06??0)),a("total",String(e.total??0))}function A(e){const a=e.application||{};return`
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Entry No.</p>
                <p class="fw-semibold mb-0">${i(e.entry_no)}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Book</p>
                <div>${k(e.book_type)}</div>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Recorded</p>
                <p class="mb-0">${i(d(e.recorded_at))}</p>
            </div>
        </div>

        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Subject</h6>
            <p class="fw-semibold mb-0">${i(e.subject)}</p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Recipient</h6>
                    <p class="mb-2"><span class="text-muted">Name:</span> ${i(e.recipient_name||"—")}</p>
                    <p class="mb-0"><span class="text-muted">Contact:</span> ${i(e.recipient_contact||"—")}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Recorder</h6>
                    <p class="mb-2"><span class="text-muted">By:</span> ${i(e.recorded_by?.name||"—")}</p>
                    <p class="mb-0"><span class="text-muted">At:</span> ${i(d(e.recorded_at))}</p>
                </div>
            </div>
        </div>

        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-3">Linked application</h6>
            ${a.application_no?`<p class="fw-medium mb-1">${i(a.application_no)}</p>
                       <p class="mb-1">${i(a.project_title||"—")}</p>
                       <p class="text-muted small mb-1">${i(a.project_location||"")}</p>
                       <p class="mb-0 text-capitalize"><span class="text-muted">Status:</span> ${i(x(String(a.status||"—")))}</p>`:'<p class="text-muted mb-0">No application linked to this entry.</p>'}
        </div>

        <div class="border rounded p-3">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Notes</h6>
            <div class="fs-13 mb-0" style="white-space:pre-wrap">${i(e.notes||"—")}</div>
        </div>
    `}function _(e){const a=e.print_url||`/admin/logbooks/${e.uuid}/print`;S(a)}function R(){const e=document.getElementById("logbooks-table"),a=document.getElementById("form-add-logbook");if(!e||!a)return;const l=L(a);let c=null,n=null;const r=document.getElementById("logbook-detail-body"),p=document.getElementById("modal-logbook-detail-label"),u=document.getElementById("btn-lb-view-app"),m=document.getElementById("btn-lb-print"),h=t=>{n=t,p&&(p.textContent=`Logbook · ${t.entry_no}`),r&&(r.innerHTML=A(t)),u?.classList.toggle("d-none",!t.application?.uuid),m?.classList.remove("d-none"),E("modal-logbook-detail")};u?.addEventListener("click",()=>{const t=n?.application?.uuid;t&&(v("modal-logbook-detail"),f(t))}),m?.addEventListener("click",()=>{n&&_(n)}),(async()=>{try{c=await w({table:e,exportFileName:"logbook-entries",rowId:"uuid",searchMode:"server",order:[[0,"desc"]],filters:[{id:"book_type",label:"Book",options:[{value:"",label:"All"},{value:"g01_releasing",label:"G-01 Releasing"},{value:"o02_occupancy",label:"O-02 Occupancy"},{value:"e_series",label:"E-series"},{value:"g05",label:"G-05"},{value:"g06",label:"G-06"}]}],columns:[{data:"entry_no",title:"Entry No.",responsivePriority:1,render:t=>`<span class="fw-semibold">${i(String(t??""))}</span>`},{data:"book_type",title:"Book",responsivePriority:1,render:t=>k(String(t??""))},{data:"subject",title:"Subject",responsivePriority:1,render:(t,o,s)=>`<div class="fw-medium text-truncate" style="max-width:16rem">${i(String(t??""))}</div>
                             <div class="text-muted small">${i(s.recipient_name||"No recipient")}</div>`},{data:"application",title:"Application",responsivePriority:2,render:(t,o,s)=>`<div class="fw-medium">${i(s.application?.application_no||"—")}</div>
                             <div class="text-muted small text-truncate" style="max-width:12rem">${i(s.application?.project_title||"")}</div>`},{data:"recorded_by",title:"Recorder",responsivePriority:3,render:(t,o,s)=>i(s.recorded_by?.name||"—")},{data:"recorded_at",title:"Recorded",responsivePriority:2,render:t=>`<span class="small">${i(d(t))}</span>`}],actions:[{id:"view",label:"View entry",onClick:t=>h(t)},{id:"view-app",label:"View application details",visible:t=>!!t.application?.uuid,onClick:t=>{t.application?.uuid&&f(t.application.uuid)}},{id:"print",label:"Print / PDF",dividerBefore:!0,onClick:t=>_(t)}],fetchData:async({search:t,filters:o})=>{const{data:s}=await window.axios.get("/api/v1/staff/logbook-entries",{params:{search:t||void 0,book_type:o.book_type||void 0,per_page:100}});return y(s.data?.summary||null),s.data?.items||[]}})}catch(t){g(t?.response?.data?.message||"Unable to load logbooks"),y(null)}})(),a.addEventListener("submit",async t=>{t.preventDefault();const o=document.getElementById("btn-save-logbook");o&&(o.disabled=!0);try{await window.axios.post("/api/v1/staff/logbook-entries",{book_type:document.getElementById("lb-book-type").value,subject:document.getElementById("lb-subject").value.trim(),application_uuid:l.get("lb-app-uuid")?.getValue()||void 0,recipient_name:document.getElementById("lb-recipient").value.trim()||void 0,recipient_contact:document.getElementById("lb-contact").value.trim()||void 0,notes:document.getElementById("lb-notes").value.trim()||void 0}),$("Logbook entry created"),a.reset(),l.get("lb-app-uuid")?.clear(),v("modal-add-logbook"),await c?.reload(!1)}catch(s){const b=s?.response?.data?.errors,B=b?Object.values(b).flat()[0]:null;g(String(B||s?.response?.data?.message||"Create failed"))}finally{o&&(o.disabled=!1)}})}export{R as initLogbooksPage};
