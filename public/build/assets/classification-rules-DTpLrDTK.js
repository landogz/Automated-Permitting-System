import{b as r,t as u,e as s,f,s as g,d as N}from"./app-DRefgvz5.js";import{c as D,b as S}from"./create-apics-datatable-CPLKmlT1.js";function I(t){if(!t)return"—";try{return new Date(t).toLocaleString()}catch{return s(t)}}function P(t){return t.replaceAll("_"," ").replace(/\b\w/g,l=>l.toUpperCase())}function $(t){return`<span class="badge ${{simple:"bg-success-subtle text-success",complex:"bg-warning-subtle text-warning",highly_technical:"bg-danger-subtle text-danger"}[t]||"bg-info-subtle text-info"}">${s(P(t))}</span>`}function _(t){const l=(i,d)=>{document.querySelectorAll(`[data-cr-stat="${i}"]`).forEach(n=>{n.textContent=d})};if(!t){["active","inactive","simple","complex","highly_technical","total"].forEach(i=>l(i,"—"));return}l("active",String(t.active??0)),l("inactive",String(t.inactive??0)),l("simple",String(t.simple??0)),l("complex",String(t.complex??0)),l("highly_technical",String(t.highly_technical??0)),l("total",String(t.total??0))}function R(t){const l=t||[];return l.length?`<div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 align-middle">
            <thead class="table-light"><tr><th>#</th><th>Field</th><th>Op</th><th>Value</th></tr></thead>
            <tbody>${l.map((d,n)=>`<tr>
                <td class="text-muted">${n+1}</td>
                <td><code>${s(String(d.field||""))}</code></td>
                <td>${s(String(d.operator||""))}</td>
                <td>${s(String(d.value??""))}</td>
            </tr>`).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted mb-0 fs-13">No conditions — unconditional match (default / catch-all).</p>'}function L(t){return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Code</p>
                <p class="fw-semibold mb-0">${s(t.code)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Classification</p>
                <div>${$(t.classification)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Priority</p>
                <p class="fw-semibold mb-0">${s(String(t.priority))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Active</p>
                <div>${S(!!t.is_active)}</div>
            </div>
        </div>
        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Name</h6>
            <p class="fw-semibold mb-0">${s(t.name)}</p>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <p class="text-muted fs-11 text-uppercase mb-1">SLA hours</p>
                <p class="fw-medium mb-0">${s(String(t.sla_hours))} h</p>
            </div>
            <div class="col-md-4">
                <p class="text-muted fs-11 text-uppercase mb-1">Created</p>
                <p class="mb-0 small">${s(I(t.created_at))}</p>
            </div>
            <div class="col-md-4">
                <p class="text-muted fs-11 text-uppercase mb-1">Updated</p>
                <p class="mb-0 small">${s(I(t.updated_at))}</p>
            </div>
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Conditions (${t.condition_count??(t.conditions||[]).length})</h6>
        ${R(t.conditions)}
    `}function k(){const t=document.getElementById("rule-field").value.trim(),l=document.getElementById("rule-operator").value;let i=document.getElementById("rule-value").value.trim();return i!==""&&!Number.isNaN(Number(i))&&l!=="contains"&&(i=Number(i)),{code:document.getElementById("rule-code").value.trim(),name:document.getElementById("rule-name").value.trim(),classification:document.getElementById("rule-classification").value,priority:Number(document.getElementById("rule-priority").value||100),sla_hours:Number(document.getElementById("rule-sla").value||72),is_active:document.getElementById("rule-active").value==="1",conditions:t&&i!==""?[{field:t,operator:l,value:i}]:[]}}function H(){const t=document.getElementById("rules-table"),l=document.getElementById("form-add-rule");if(!t||!l)return;let i=null,d=null;const n=document.getElementById("rule-detail-body"),b=document.getElementById("modal-rule-detail-label"),m=document.getElementById("modal-add-rule-label"),c=document.getElementById("btn-save-rule"),o=document.getElementById("rule-uuid"),y=()=>{l.reset(),o&&(o.value=""),document.getElementById("rule-priority").value="100",document.getElementById("rule-sla").value="72",document.getElementById("rule-field").value="lot_area",document.getElementById("rule-active").value="1",m&&(m.textContent="Add classification rule"),c&&(c.textContent="Save rule")},w=()=>{y(),g("modal-add-rule")},h=e=>{d=e,o&&(o.value=e.uuid),document.getElementById("rule-code").value=e.code,document.getElementById("rule-name").value=e.name,document.getElementById("rule-classification").value=e.classification,document.getElementById("rule-priority").value=String(e.priority??100),document.getElementById("rule-sla").value=String(e.sla_hours??72),document.getElementById("rule-active").value=e.is_active?"1":"0";const a=(e.conditions||[])[0];document.getElementById("rule-field").value=a?.field||"",document.getElementById("rule-operator").value=a?.operator||">=",document.getElementById("rule-value").value=a?.value!=null&&a.value!==""?String(a.value):"",m&&(m.textContent=`Edit rule · ${e.code}`),c&&(c.textContent="Update rule"),f("modal-rule-detail"),g("modal-add-rule")},C=e=>{d=e,b&&(b.textContent=`Rule · ${e.code}`),n&&(n.innerHTML=L(e));const a=document.getElementById("btn-rule-toggle");a&&(a.textContent=e.is_active?"Deactivate":"Activate"),g("modal-rule-detail")},x=async e=>{if(await N("Delete rule?",`${e.code} will be soft-deleted from the classifier.`))try{await window.axios.delete(`/api/v1/admin/classification-rules/${e.uuid}`),f("modal-rule-detail"),r("Rule deleted"),await i?.reload(!1)}catch(a){u(a?.response?.data?.message||"Delete failed")}};document.getElementById("btn-open-add-rule")?.addEventListener("click",()=>w()),document.getElementById("btn-rule-edit")?.addEventListener("click",()=>{d&&h(d)}),document.getElementById("btn-rule-toggle")?.addEventListener("click",async()=>{if(d)try{await window.axios.put(`/api/v1/admin/classification-rules/${d.uuid}`,{is_active:!d.is_active}),r(d.is_active?"Rule deactivated":"Rule activated"),f("modal-rule-detail"),await i?.reload(!1)}catch(e){u(e?.response?.data?.message||"Update failed")}}),document.getElementById("btn-rule-delete")?.addEventListener("click",()=>{d&&x(d)}),(async()=>{try{i=await D({table:t,exportFileName:"classification-rules",rowId:"uuid",order:[[0,"asc"]],filters:[{id:"classification",label:"Classification",options:[{value:"",label:"All"},{value:"simple",label:"Simple"},{value:"complex",label:"Complex"},{value:"highly_technical",label:"Highly technical"}],match:(e,a)=>e.classification===a},{id:"active",label:"Active",options:[{value:"",label:"All"},{value:"1",label:"Active"},{value:"0",label:"Inactive"}],match:(e,a)=>String(Number(e.is_active))===a}],columns:[{data:"priority",title:"Priority",responsivePriority:2},{data:"code",title:"Code",responsivePriority:1,render:e=>`<span class="fw-semibold">${s(String(e??""))}</span>`},{data:"name",title:"Name",responsivePriority:1,render:(e,a,p)=>`<div class="fw-medium text-truncate" style="max-width:14rem">${s(String(e??""))}</div>
                             <div class="text-muted small">${p.condition_count??0} condition(s)</div>`},{data:"classification",title:"Classification",responsivePriority:1,render:e=>$(String(e??""))},{data:"sla_hours",title:"SLA (h)",responsivePriority:3,render:e=>s(String(e??""))},{data:"is_active",title:"Active",responsivePriority:2,render:e=>S(!!e)}],actions:[{id:"view",label:"View rule",onClick:e=>C(e)},{id:"edit",label:"Edit",onClick:e=>h(e)},{id:"toggle",label:"Toggle active",onClick:async e=>{try{await window.axios.put(`/api/v1/admin/classification-rules/${e.uuid}`,{is_active:!e.is_active}),r(e.is_active?"Rule deactivated":"Rule activated"),await i?.reload(!1)}catch(a){u(a?.response?.data?.message||"Update failed")}}},{id:"delete",label:"Delete",danger:!0,dividerBefore:!0,onClick:e=>{x(e)}}],fetchData:async()=>{const{data:e}=await window.axios.get("/api/v1/admin/classification-rules",{params:{per_page:100}});return _(e.data?.summary||null),e.data?.items||[]}})}catch(e){u(e?.response?.data?.message||"Unable to load rules"),_(null)}})(),l.addEventListener("submit",async e=>{e.preventDefault();const a=o?.value.trim()||"",p=k(),v=c;v&&(v.disabled=!0);try{a?(await window.axios.put(`/api/v1/admin/classification-rules/${a}`,p),r("Rule updated")):(await window.axios.post("/api/v1/admin/classification-rules",p),r("Rule created")),y(),f("modal-add-rule"),await i?.reload(!1)}catch(E){const B=E?.response?.data?.errors,A=B?Object.values(B).flat()[0]:null;u(String(A||E?.response?.data?.message||(a?"Update failed":"Create failed")))}finally{v&&(v.disabled=!1)}})}export{H as initClassificationRulesPage};
