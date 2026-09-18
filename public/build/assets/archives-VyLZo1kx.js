import{e as a,t as u,b as $,f as v,s as w}from"./app-DtJZ15sX.js";import{c as S}from"./create-apics-datatable-LCtvdTnZ.js";import{o as b}from"./index-ByjBgPoN.js";import{i as A}from"./application-select-luCQkdB6.js";import"./site-map-viewer-DXF0R4cC.js";function f(t){if(!t)return"—";try{return new Date(t).toLocaleString()}catch{return a(t)}}function g(t){return t.replaceAll("_"," ").replace(/\b\w/g,i=>i.toUpperCase())}function y(t){return`<span class="badge ${{digital:"bg-primary-subtle text-primary",physical:"bg-secondary-subtle text-secondary",hybrid:"bg-info-subtle text-info"}[t]||"bg-secondary-subtle text-secondary"}">${a(g(t))}</span>`}function h(t){const i=(c,d)=>{document.querySelectorAll(`[data-arc-stat="${c}"]`).forEach(r=>{r.textContent=d})};if(!t){["digital","physical","hybrid","total"].forEach(c=>i(c,"—"));return}i("digital",String(t.digital??0)),i("physical",String(t.physical??0)),i("hybrid",String(t.hybrid??0)),i("total",String(t.total??0))}function E(t){const i=t.application||{},c=t.meta&&Object.keys(t.meta).length?a(JSON.stringify(t.meta,null,2)):null;return`
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Archive No.</p>
                <p class="fw-semibold mb-0">${a(t.archive_no)}</p>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Media</p>
                <div>${y(t.media_type)}</div>
            </div>
            <div class="col-md-4 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Archived</p>
                <p class="mb-0">${a(f(t.archived_at))}</p>
            </div>
        </div>

        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Title</h6>
            <p class="fw-semibold mb-0">${a(t.title)}</p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Storage</h6>
                    <p class="mb-2"><span class="text-muted">Location:</span> ${a(t.storage_location||"—")}</p>
                    <p class="mb-0"><span class="text-muted">Archived by:</span> ${a(t.archived_by?.name||"—")}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Linked application</h6>
                    ${i.application_no?`<p class="fw-medium mb-1">${a(i.application_no)}</p>
                               <p class="mb-1">${a(i.project_title||"—")}</p>
                               <p class="text-muted small mb-1">${a(i.project_location||"")}</p>
                               <p class="mb-0 text-capitalize"><span class="text-muted">Status:</span> ${a(g(String(i.status||"—")))}</p>`:'<p class="text-muted mb-0">No application linked.</p>'}
                </div>
            </div>
        </div>

        <div class="border rounded p-3 mb-3">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Integrity checksum (SHA-256)</h6>
            <code class="fs-12 text-break d-block">${a(t.checksum||"—")}</code>
        </div>

        ${c?`<div class="border rounded p-3 mb-3 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-2">CICTO / meta stub</h6>
                    <pre class="mb-0 small font-monospace" style="white-space:pre-wrap">${c}</pre>
                   </div>`:""}

        <div class="border rounded p-3">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Notes</h6>
            <div class="fs-13 mb-0" style="white-space:pre-wrap">${a(t.notes||"—")}</div>
        </div>
    `}function P(){const t=document.getElementById("archives-table"),i=document.getElementById("form-add-archive");if(!t||!i)return;const c=A(i);let d=null,r=null;const n=document.getElementById("archive-detail-body"),o=document.getElementById("modal-archive-detail-label"),p=document.getElementById("btn-arc-view-app"),x=e=>{r=e,o&&(o.textContent=`Archive · ${e.archive_no}`),n&&(n.innerHTML=E(e)),p?.classList.toggle("d-none",!e.application?.uuid),w("modal-archive-detail")};p?.addEventListener("click",()=>{const e=r?.application?.uuid;e&&(v("modal-archive-detail"),b(e))}),(async()=>{try{d=await S({table:t,exportFileName:"archive-records",rowId:"uuid",searchMode:"server",order:[[0,"desc"]],filters:[{id:"media_type",label:"Media",options:[{value:"",label:"All"},{value:"digital",label:"Digital"},{value:"physical",label:"Physical"},{value:"hybrid",label:"Hybrid"}]}],columns:[{data:"archive_no",title:"Archive No.",responsivePriority:1,render:e=>`<span class="fw-semibold">${a(String(e??""))}</span>`},{data:"title",title:"Title",responsivePriority:1,render:(e,s,l)=>`<div class="fw-medium text-truncate" style="max-width:16rem">${a(String(e??""))}</div>
                             <div class="text-muted small text-truncate" style="max-width:16rem">${a(l.storage_location||"")}</div>`},{data:"application",title:"Application",responsivePriority:2,render:(e,s,l)=>`<div class="fw-medium">${a(l.application?.application_no||"—")}</div>
                             <div class="text-muted small text-truncate" style="max-width:12rem">${a(l.application?.project_title||"")}</div>`},{data:"media_type",title:"Media",responsivePriority:1,render:e=>y(String(e??""))},{data:"checksum",title:"Checksum",responsivePriority:3,render:e=>{if(!e)return"—";const s=String(e);return`<span class="small font-monospace" title="${a(s)}">${a(s.slice(0,12))}…</span>`}},{data:"archived_by",title:"Archivist",responsivePriority:4,render:(e,s,l)=>a(l.archived_by?.name||"—")},{data:"archived_at",title:"Archived",responsivePriority:2,render:e=>`<span class="small">${a(f(e))}</span>`}],actions:[{id:"view",label:"View archive",onClick:e=>x(e)},{id:"view-app",label:"View application details",visible:e=>!!e.application?.uuid,onClick:e=>{e.application?.uuid&&b(e.application.uuid)}}],fetchData:async({search:e,filters:s})=>{const{data:l}=await window.axios.get("/api/v1/staff/archive-records",{params:{search:e||void 0,media_type:s.media_type||void 0,per_page:100}});return h(l.data?.summary||null),l.data?.items||[]}})}catch(e){u(e?.response?.data?.message||"Unable to load archives"),h(null)}})(),i.addEventListener("submit",async e=>{e.preventDefault();const s=document.getElementById("btn-save-archive");s&&(s.disabled=!0);try{await window.axios.post("/api/v1/staff/archive-records",{title:document.getElementById("arc-title").value.trim(),application_uuid:c.get("arc-app-uuid")?.getValue()||void 0,storage_location:document.getElementById("arc-location").value.trim()||void 0,media_type:document.getElementById("arc-media").value,notes:document.getElementById("arc-notes").value.trim()||void 0}),$("Archive record created"),i.reset(),c.get("arc-app-uuid")?.clear(),v("modal-add-archive"),await d?.reload(!1)}catch(l){const m=l?.response?.data?.errors,_=m?Object.values(m).flat()[0]:null;u(String(_||l?.response?.data?.message||"Create failed"))}finally{s&&(s.disabled=!1)}})}export{P as initArchivesPage};
