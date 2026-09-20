import{e as n,t as f,b as v,f as b,s as g,d as k}from"./app-Cwtxlj4R.js";import{c as q,b as w}from"./create-apics-datatable-DGrRLurC.js";const T=[{value:">=",label:">="},{value:">",label:">"},{value:"<=",label:"<="},{value:"<",label:"<"},{value:"=",label:"="},{value:"contains",label:"contains"}],I=[{value:"lot_area",label:"Lot area (sqm)"},{value:"floor_area",label:"Floor area (sqm)"},{value:"occupancy",label:"Occupancy"},{value:"classification",label:"Classification"},{value:"owner_name",label:"Owner name"},{value:"project_title",label:"Project title"},{value:"project_location",label:"Project location"},{value:"permit_type",label:"Permit type"}];function C(e){const l=Number(e??0);return Number.isNaN(l)?"₱0.00":new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP",minimumFractionDigits:2}).format(l)}function j(e){if(!e)return"—";try{return new Date(e).toLocaleString()}catch{return n(e)}}function N(e){return e.replaceAll("_"," ").replace(/\b\w/g,l=>l.toUpperCase())}function P(e){return`<span class="badge ${{lgu:"bg-primary-subtle text-primary",bfp:"bg-danger-subtle text-danger",dpwh:"bg-warning-subtle text-warning",cto:"bg-success-subtle text-success"}[e]||"bg-secondary-subtle text-secondary"}">${n(e.toUpperCase())}</span>`}function S(e){const l=(a,i)=>{document.querySelectorAll(`[data-fr-stat="${a}"]`).forEach(o=>{o.textContent=i})};if(!e){["active","inactive","lgu","bfp","dpwh","cto","total"].forEach(a=>l(a,"—"));return}l("active",String(e.active??0)),l("inactive",String(e.inactive??0)),l("lgu",String(e.lgu??0)),l("bfp",String(e.bfp??0)),l("dpwh",String(e.dpwh??0)),l("cto",String(e.cto??0)),l("total",String(e.total??0))}function H(e){const l=e||[];return l.length?`<div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 align-middle">
            <thead class="table-light"><tr><th>#</th><th>Field</th><th>Op</th><th>Value</th></tr></thead>
            <tbody>${l.map((i,o)=>`<tr>
                <td class="text-muted">${o+1}</td>
                <td><code>${n(String(i.field||""))}</code></td>
                <td>${n(String(i.operator||""))}</td>
                <td>${n(String(i.value??""))}</td>
            </tr>`).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted mb-0 fs-13">No conditions — applies to all matching applications by priority.</p>'}function U(e){return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Code</p>
                <p class="fw-semibold mb-0">${n(e.code)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Agency</p>
                <div>${P(e.agency)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Basis</p>
                <p class="mb-0 text-capitalize">${n(N(e.basis))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Active</p>
                <div>${w(!!e.is_active)}</div>
            </div>
        </div>
        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Name</h6>
            <p class="fw-semibold mb-0">${n(e.name)}</p>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Amount</p>
                <p class="fw-semibold text-primary mb-0">${n(C(e.amount))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Rate</p>
                <p class="fw-medium mb-0">${e.rate!=null&&e.rate!==""?n(String(e.rate)):"—"}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Priority</p>
                <p class="fw-medium mb-0">${n(String(e.priority))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Updated</p>
                <p class="mb-0 small">${n(j(e.updated_at))}</p>
            </div>
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Conditions (${e.condition_count??(e.conditions||[]).length})</h6>
        ${H(e.conditions)}
    `}function M(e){return T.map(l=>`<option value="${n(l.value)}"${l.value===e?" selected":""}>${n(l.label)}</option>`).join("")}function V(e){const l=String(e||"").trim(),a=new Set(I.map(o=>o.value)),i=I.map(o=>`<option value="${n(o.value)}"${o.value===l?" selected":""}>${n(o.label)}</option>`);return l&&!a.has(l)&&i.unshift(`<option value="${n(l)}" selected>${n(l)} (custom)</option>`),`<option value="">Select field…</option>${i.join("")}`}function p(){const e=document.getElementById("fee-conditions-list"),l=document.getElementById("fee-conditions-empty");!e||!l||l.classList.toggle("d-none",e.children.length>0)}function A(e){const l=document.getElementById("fee-conditions-list");if(!l)return;const a=document.createElement("div");a.className="fee-condition-row border rounded p-2 bg-light-subtle",a.innerHTML=`
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-4">
                <label class="form-label fs-12 mb-1">Field</label>
                <select class="form-select form-select-sm fee-cond-field" aria-label="Condition field">
                    ${V(e?.field)}
                </select>
            </div>
            <div class="col-6 col-sm-3">
                <label class="form-label fs-12 mb-1">Operator</label>
                <select class="form-select form-select-sm fee-cond-operator" aria-label="Condition operator">
                    ${M(e?.operator||">=")}
                </select>
            </div>
            <div class="col-6 col-sm-3">
                <label class="form-label fs-12 mb-1">Value</label>
                <input type="text" class="form-control form-control-sm fee-cond-value"
                    placeholder="e.g. 50" maxlength="255"
                    value="${n(String(e?.value??""))}">
            </div>
            <div class="col-12 col-sm-2">
                <button type="button" class="btn btn-sm btn-soft-danger w-100 fee-cond-remove" aria-label="Remove condition">
                    <i class="ri-delete-bin-line" aria-hidden="true"></i>
                    <span class="d-sm-none ms-1">Remove</span>
                </button>
            </div>
        </div>
    `,a.querySelector(".fee-cond-remove")?.addEventListener("click",()=>{a.remove(),p()}),l.appendChild(a),p()}function _(){const e=document.getElementById("fee-conditions-list");e&&(e.innerHTML=""),p()}function z(e){_();const l=e||[];l.length&&l.forEach(a=>A(a))}function F(){const e=document.querySelectorAll(".fee-condition-row"),l=[];return e.forEach(a=>{const i=a.querySelector(".fee-cond-field")?.value.trim()||"",o=a.querySelector(".fee-cond-operator")?.value||">=",d=a.querySelector(".fee-cond-value")?.value.trim()||"";if(!i&&d==="")return;let c=d;o!=="contains"&&d!==""&&!Number.isNaN(Number(d))&&(c=Number(d)),l.push({field:i,operator:o,value:c})}),l}function $(e){const l=document.getElementById("btn-save-fee-rule"),a=l?.querySelector(".btn-label");a?a.textContent=e:l&&(l.textContent=e)}function G(){return{code:document.getElementById("fee-code").value.trim(),name:document.getElementById("fee-name").value.trim(),agency:document.getElementById("fee-agency").value,basis:document.getElementById("fee-basis").value,amount:Number(document.getElementById("fee-amount").value||0),rate:Number(document.getElementById("fee-rate").value||0)||null,priority:Number(document.getElementById("fee-priority").value||100),is_active:document.getElementById("fee-active").value==="1",conditions:F()}}function Q(){const e=document.getElementById("fee-rules-table"),l=document.getElementById("form-add-fee-rule");if(!e||!l)return;let a=null,i=null;const o=document.getElementById("fee-detail-body"),d=document.getElementById("modal-fee-detail-label"),c=document.getElementById("modal-add-fee-rule-label"),L=document.getElementById("btn-save-fee-rule"),u=document.getElementById("fee-uuid"),y=()=>{l.reset(),u&&(u.value=""),document.getElementById("fee-priority").value="100",document.getElementById("fee-amount").value="0",document.getElementById("fee-rate").value="0",document.getElementById("fee-active").value="1",_(),c&&(c.textContent="Add fee rule"),$("Save rule")},D=()=>{y(),g("modal-add-fee-rule")},E=t=>{i=t,u&&(u.value=t.uuid),document.getElementById("fee-code").value=t.code,document.getElementById("fee-name").value=t.name,document.getElementById("fee-agency").value=t.agency,document.getElementById("fee-basis").value=t.basis,document.getElementById("fee-priority").value=String(t.priority??100),document.getElementById("fee-amount").value=String(t.amount??0),document.getElementById("fee-rate").value=t.rate!=null&&t.rate!==""?String(t.rate):"0",document.getElementById("fee-active").value=t.is_active?"1":"0",z(t.conditions),c&&(c.textContent=`Edit fee rule · ${t.code}`),$("Update rule"),b("modal-fee-detail"),g("modal-add-fee-rule")},O=t=>{i=t,d&&(d.textContent=`Fee rule · ${t.code}`),o&&(o.innerHTML=U(t)),g("modal-fee-detail")},h=async t=>{if(await k("Delete fee rule?",`${t.code} will be soft-deleted from the fee engine.`))try{await window.axios.delete(`/api/v1/admin/fee-rules/${t.uuid}`),b("modal-fee-detail"),v("Fee rule deleted"),await a?.reload(!1)}catch(s){f(s?.response?.data?.message||"Delete failed")}};document.getElementById("btn-open-add-fee-rule")?.addEventListener("click",()=>D()),document.getElementById("btn-fee-add-condition")?.addEventListener("click",()=>A({field:"lot_area",operator:">=",value:""})),document.getElementById("btn-fee-edit")?.addEventListener("click",()=>{i&&E(i)}),document.getElementById("btn-fee-delete")?.addEventListener("click",()=>{i&&h(i)}),p(),(async()=>{try{a=await q({table:e,exportFileName:"fee-rules",rowId:"uuid",order:[[5,"asc"]],filters:[{id:"agency",label:"Agency",options:[{value:"",label:"All"},{value:"lgu",label:"LGU"},{value:"bfp",label:"BFP"},{value:"dpwh",label:"DPWH"},{value:"cto",label:"CTO"}],match:(t,s)=>t.agency===s},{id:"basis",label:"Basis",options:[{value:"",label:"All"},{value:"fixed",label:"Fixed"},{value:"area_rate",label:"Area × rate"}],match:(t,s)=>t.basis===s},{id:"active",label:"Active",options:[{value:"",label:"All"},{value:"1",label:"Active"},{value:"0",label:"Inactive"}],match:(t,s)=>String(Number(t.is_active))===s}],columns:[{data:"code",title:"Code",responsivePriority:1,render:t=>`<span class="fw-semibold">${n(String(t??""))}</span>`},{data:"name",title:"Name",responsivePriority:1,render:t=>`<div class="fw-medium text-truncate" style="max-width:14rem">${n(String(t??""))}</div>`},{data:"agency",title:"Agency",responsivePriority:1,render:t=>P(String(t??""))},{data:"basis",title:"Basis",responsivePriority:2,render:t=>n(N(String(t??"")))},{data:"amount",title:"Amount",responsivePriority:2,className:"text-end",render:t=>n(C(t))},{data:"priority",title:"Priority",responsivePriority:3},{data:"is_active",title:"Active",responsivePriority:2,render:t=>w(!!t)}],actions:[{id:"view",label:"View rule",onClick:t=>O(t)},{id:"edit",label:"Edit",onClick:t=>E(t)},{id:"delete",label:"Delete",danger:!0,dividerBefore:!0,onClick:t=>{h(t)}}],fetchData:async()=>{const{data:t}=await window.axios.get("/api/v1/admin/fee-rules",{params:{per_page:100}});return S(t.data?.summary||null),t.data?.items||[]}})}catch(t){f(t?.response?.data?.message||"Unable to load fee rules"),S(null)}})(),l.addEventListener("submit",async t=>{t.preventDefault();const s=u?.value.trim()||"";if(F().find(r=>!r.field||r.value===""||r.value==null)){f("Each condition needs a field and value, or remove the empty row.");return}const x=G(),m=L;m&&(m.disabled=!0);try{s?(await window.axios.put(`/api/v1/admin/fee-rules/${s}`,x),v("Fee rule updated")):(await window.axios.post("/api/v1/admin/fee-rules",x),v("Fee rule created")),y(),b("modal-add-fee-rule"),await a?.reload(!1)}catch(r){const B=r?.response?.data?.errors,R=B?Object.values(B).flat()[0]:null;f(String(R||r?.response?.data?.message||(s?"Update failed":"Create failed")))}finally{m&&(m.disabled=!1)}})}export{Q as initFeeRulesPage};
