import{t as y,b as E,f as _,e as a,s as $,d as P}from"./app-Cmf22frh.js";import{c as M,b as T}from"./create-apics-datatable-Ka6mwlgT.js";function A(t){if(!t)return"—";try{return new Date(t).toLocaleString()}catch{return a(t)}}function U(t){return t.replaceAll("_"," ").replace(/\b\w/g,l=>l.toUpperCase())}function D(t){return`<span class="badge ${{simple:"bg-success-subtle text-success",complex:"bg-warning-subtle text-warning",highly_technical:"bg-danger-subtle text-danger"}[t]||"bg-info-subtle text-info"}">${a(U(t))}</span>`}function L(t){const l=(d,o)=>{document.querySelectorAll(`[data-rt-stat="${d}"]`).forEach(r=>{r.textContent=o})};if(!t){["active","inactive","simple","complex","highly_technical","total"].forEach(d=>l(d,"—"));return}l("active",String(t.active??0)),l("inactive",String(t.inactive??0)),l("simple",String(t.simple??0)),l("complex",String(t.complex??0)),l("highly_technical",String(t.highly_technical??0)),l("total",String(t.total??0))}function j(t){return t.length?`<div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr><th style="width:3rem">#</th><th>Step / Department</th><th class="text-end">SLA</th></tr>
            </thead>
            <tbody>${[...t].sort((o,r)=>o.step_order-r.step_order).map(o=>`<tr>
                <td class="text-muted">${a(String(o.step_order))}</td>
                <td>
                    <div class="fw-medium">${a(o.label)}</div>
                    <div class="text-muted small">${a(o.department?`${o.department.code||""} — ${o.department.name||""}`:"No department")}</div>
                </td>
                <td class="text-end">${a(String(o.sla_hours??24))} h</td>
            </tr>`).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted mb-0">No steps defined.</p>'}function R(t){const l=t.steps||[];return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Code</p>
                <p class="fw-semibold mb-0">${a(t.code)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Classification</p>
                <div>${D(t.classification)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Steps</p>
                <p class="fw-semibold mb-0">${a(String(t.step_count??l.length))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Active</p>
                <div>${T(!!t.is_active)}</div>
            </div>
        </div>
        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Name</h6>
            <p class="fw-semibold mb-0">${a(t.name)}</p>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Created</p>
                <p class="mb-0 small">${a(A(t.created_at))}</p>
            </div>
            <div class="col-md-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Updated</p>
                <p class="mb-0 small">${a(A(t.updated_at))}</p>
            </div>
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Routing path</h6>
        ${j(l)}
    `}function O(){const t=document.getElementById("templates-table"),l=document.getElementById("form-add-template"),d=document.getElementById("tpl-steps"),o=document.getElementById("btn-add-step");if(!t||!l||!d)return;let r=[],x=null,m=null;const w=document.getElementById("tpl-detail-body"),S=document.getElementById("modal-tpl-detail-label"),f=document.getElementById("modal-add-template-label"),v=document.getElementById("btn-save-template"),b=document.getElementById("tpl-uuid"),g=e=>{const i=r.map(n=>`<option value="${a(n.uuid)}"${e?.department_uuid===n.uuid?" selected":""}>${a(n.code)} — ${a(n.name)}</option>`).join(""),s=document.createElement("div");s.className="row g-2 align-items-end tpl-step-row border rounded p-2 bg-light-subtle",s.innerHTML=`
            <div class="col-md-5">
                <label class="form-label">Department</label>
                <select class="form-select step-dept" required>${i}</select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Label</label>
                <input class="form-control step-label" required placeholder="Step label" maxlength="255" value="${a(e?.label||"")}">
            </div>
            <div class="col-md-2">
                <label class="form-label">SLA (h)</label>
                <input type="number" class="form-control step-sla" value="${a(String(e?.sla_hours??24))}" min="1" max="8760">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-soft-danger w-100 btn-remove-step" aria-label="Remove step">
                    <i class="ri-close-line"></i>
                </button>
            </div>`,s.querySelector(".btn-remove-step")?.addEventListener("click",()=>s.remove()),d.appendChild(s)},N=async()=>{const{data:e}=await window.axios.get("/api/v1/admin/departments",{params:{per_page:100}});r=e.data?.items||[]},B=()=>{l.reset(),b&&(b.value=""),document.getElementById("tpl-active").value="1",d.innerHTML="",g(),f&&(f.textContent="Add routing template"),v&&(v.textContent="Save template")},k=()=>{B(),$("modal-add-template")},I=e=>{m=e,b&&(b.value=e.uuid),document.getElementById("tpl-code").value=e.code,document.getElementById("tpl-name").value=e.name,document.getElementById("tpl-classification").value=e.classification,document.getElementById("tpl-active").value=e.is_active?"1":"0",d.innerHTML="";const i=[...e.steps||[]].sort((s,n)=>s.step_order-n.step_order);i.length?i.forEach(s=>g({department_uuid:s.department?.uuid,label:s.label,sla_hours:s.sla_hours??24})):g(),f&&(f.textContent=`Edit template · ${e.code}`),v&&(v.textContent="Update template"),_("modal-tpl-detail"),$("modal-add-template")},q=e=>{m=e,S&&(S.textContent=`Template · ${e.code}`),w&&(w.innerHTML=R(e)),$("modal-tpl-detail")},C=async e=>{if(await P("Delete template?",`${e.code} will be soft-deleted.`))try{await window.axios.delete(`/api/v1/admin/routing-templates/${e.uuid}`),_("modal-tpl-detail"),E("Template deleted"),await x?.reload(!1)}catch(i){y(i?.response?.data?.message||"Delete failed")}};o?.addEventListener("click",()=>g()),document.getElementById("btn-open-add-template")?.addEventListener("click",()=>k()),document.getElementById("btn-tpl-edit")?.addEventListener("click",()=>{m&&I(m)}),document.getElementById("btn-tpl-delete")?.addEventListener("click",()=>{m&&C(m)}),l.addEventListener("submit",async e=>{e.preventDefault();const i=Array.from(d.querySelectorAll(".tpl-step-row"));if(!i.length){y("Add at least one routing step");return}const s=i.map((c,u)=>({department_uuid:c.querySelector(".step-dept").value,label:c.querySelector(".step-label").value.trim(),step_order:u+1,sla_hours:Number(c.querySelector(".step-sla").value||24)})),n=b?.value.trim()||"",h={code:document.getElementById("tpl-code").value.trim(),name:document.getElementById("tpl-name").value.trim(),classification:document.getElementById("tpl-classification").value,is_active:document.getElementById("tpl-active").value==="1",steps:s},p=v;p&&(p.disabled=!0);try{n?(await window.axios.put(`/api/v1/admin/routing-templates/${n}`,h),E("Template updated")):(await window.axios.post("/api/v1/admin/routing-templates",h),E("Template created")),B(),_("modal-add-template"),await x?.reload(!1)}catch(c){const u=c?.response?.data?.errors,H=u?Object.values(u).flat()[0]:null;y(String(H||c?.response?.data?.message||(n?"Update failed":"Create failed")))}finally{p&&(p.disabled=!1)}}),(async()=>{try{await N(),d.children.length||g(),x=await M({table:t,exportFileName:"routing-templates",rowId:"uuid",filters:[{id:"classification",label:"Classification",options:[{value:"",label:"All"},{value:"simple",label:"Simple"},{value:"complex",label:"Complex"},{value:"highly_technical",label:"Highly technical"}],match:(e,i)=>e.classification===i},{id:"active",label:"Active",options:[{value:"",label:"All"},{value:"1",label:"Active"},{value:"0",label:"Inactive"}],match:(e,i)=>String(Number(e.is_active))===i}],columns:[{data:"code",title:"Code",responsivePriority:1,render:e=>`<span class="fw-semibold">${a(String(e??""))}</span>`},{data:"name",title:"Name",responsivePriority:1,render:(e,i,s)=>`<div class="fw-medium text-truncate" style="max-width:14rem">${a(String(e??""))}</div>
                             <div class="text-muted small">${s.step_count??(s.steps||[]).length} step(s)</div>`},{data:"classification",title:"Classification",responsivePriority:1,render:e=>D(String(e??""))},{data:"steps",title:"Path preview",responsivePriority:3,orderable:!1,render:(e,i,s)=>{const n=[...s.steps||[]].sort((c,u)=>c.step_order-u.step_order);if(!n.length)return"—";const h=n.slice(0,3).map(c=>a(c.department?.code||c.label)).join(" → "),p=n.length>3?` +${n.length-3}`:"";return`<span class="small">${h}${p}</span>`}},{data:"is_active",title:"Active",responsivePriority:2,render:e=>T(!!e)}],actions:[{id:"view",label:"View template",onClick:e=>q(e)},{id:"edit",label:"Edit",onClick:e=>I(e)},{id:"delete",label:"Delete",danger:!0,dividerBefore:!0,onClick:e=>{C(e)}}],fetchData:async()=>{const{data:e}=await window.axios.get("/api/v1/admin/routing-templates",{params:{per_page:100}});return L(e.data?.summary||null),e.data?.items||[]}})}catch(e){y(e?.response?.data?.message||"Unable to initialize routing templates"),L(null)}})()}export{O as initRoutingTemplatesPage};
