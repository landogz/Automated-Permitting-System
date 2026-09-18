import{e as l,t as m,b as p,f,s as b,d as D}from"./app-Da3N1zM7.js";import{c as F,b as S}from"./create-apics-datatable-B4drYWec.js";function $(t){const a=Number(t??0);return Number.isNaN(a)?"₱0.00":new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP",minimumFractionDigits:2}).format(a)}function _(t){if(!t)return"—";try{return new Date(t).toLocaleString()}catch{return l(t)}}function w(t){return t.replaceAll("_"," ").replace(/\b\w/g,a=>a.toUpperCase())}function A(t){return`<span class="badge ${{lgu:"bg-primary-subtle text-primary",bfp:"bg-danger-subtle text-danger",dpwh:"bg-warning-subtle text-warning",cto:"bg-success-subtle text-success"}[t]||"bg-secondary-subtle text-secondary"}">${l(t.toUpperCase())}</span>`}function I(t){const a=(d,i)=>{document.querySelectorAll(`[data-fr-stat="${d}"]`).forEach(s=>{s.textContent=i})};if(!t){["active","inactive","lgu","bfp","dpwh","cto","total"].forEach(d=>a(d,"—"));return}a("active",String(t.active??0)),a("inactive",String(t.inactive??0)),a("lgu",String(t.lgu??0)),a("bfp",String(t.bfp??0)),a("dpwh",String(t.dpwh??0)),a("cto",String(t.cto??0)),a("total",String(t.total??0))}function L(t){const a=t||[];return a.length?`<div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 align-middle">
            <thead class="table-light"><tr><th>#</th><th>Field</th><th>Op</th><th>Value</th></tr></thead>
            <tbody>${a.map((i,s)=>`<tr>
                <td class="text-muted">${s+1}</td>
                <td><code>${l(String(i.field||""))}</code></td>
                <td>${l(String(i.operator||""))}</td>
                <td>${l(String(i.value??""))}</td>
            </tr>`).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted mb-0 fs-13">No conditions — applies to all matching applications by priority.</p>'}function U(t){return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Code</p>
                <p class="fw-semibold mb-0">${l(t.code)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Agency</p>
                <div>${A(t.agency)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Basis</p>
                <p class="mb-0 text-capitalize">${l(w(t.basis))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Active</p>
                <div>${S(!!t.is_active)}</div>
            </div>
        </div>
        <div class="border rounded p-3 mb-3 bg-light-subtle">
            <h6 class="fs-13 text-uppercase text-muted mb-2">Name</h6>
            <p class="fw-semibold mb-0">${l(t.name)}</p>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Amount</p>
                <p class="fw-semibold text-primary mb-0">${l($(t.amount))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Rate</p>
                <p class="fw-medium mb-0">${t.rate!=null&&t.rate!==""?l(String(t.rate)):"—"}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Priority</p>
                <p class="fw-medium mb-0">${l(String(t.priority))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted fs-11 text-uppercase mb-1">Updated</p>
                <p class="mb-0 small">${l(_(t.updated_at))}</p>
            </div>
        </div>
        <h6 class="fs-13 text-uppercase text-muted mb-2">Conditions (${t.condition_count??(t.conditions||[]).length})</h6>
        ${L(t.conditions)}
    `}function k(){return{code:document.getElementById("fee-code").value.trim(),name:document.getElementById("fee-name").value.trim(),agency:document.getElementById("fee-agency").value,basis:document.getElementById("fee-basis").value,amount:Number(document.getElementById("fee-amount").value||0),rate:Number(document.getElementById("fee-rate").value||0)||null,priority:Number(document.getElementById("fee-priority").value||100),is_active:document.getElementById("fee-active").value==="1"}}function M(){const t=document.getElementById("fee-rules-table"),a=document.getElementById("form-add-fee-rule");if(!t||!a)return;let d=null,i=null;const s=document.getElementById("fee-detail-body"),v=document.getElementById("modal-fee-detail-label"),o=document.getElementById("modal-add-fee-rule-label"),r=document.getElementById("btn-save-fee-rule"),c=document.getElementById("fee-uuid"),g=()=>{a.reset(),c&&(c.value=""),document.getElementById("fee-priority").value="100",document.getElementById("fee-amount").value="0",document.getElementById("fee-rate").value="0",document.getElementById("fee-active").value="1",o&&(o.textContent="Add fee rule"),r&&(r.textContent="Save rule")},C=()=>{g(),b("modal-add-fee-rule")},y=e=>{i=e,c&&(c.value=e.uuid),document.getElementById("fee-code").value=e.code,document.getElementById("fee-name").value=e.name,document.getElementById("fee-agency").value=e.agency,document.getElementById("fee-basis").value=e.basis,document.getElementById("fee-priority").value=String(e.priority??100),document.getElementById("fee-amount").value=String(e.amount??0),document.getElementById("fee-rate").value=e.rate!=null&&e.rate!==""?String(e.rate):"0",document.getElementById("fee-active").value=e.is_active?"1":"0",o&&(o.textContent=`Edit fee rule · ${e.code}`),r&&(r.textContent="Update rule"),f("modal-fee-detail"),b("modal-add-fee-rule")},P=e=>{i=e,v&&(v.textContent=`Fee rule · ${e.code}`),s&&(s.innerHTML=U(e)),b("modal-fee-detail")},x=async e=>{if(await D("Delete fee rule?",`${e.code} will be soft-deleted from the fee engine.`))try{await window.axios.delete(`/api/v1/admin/fee-rules/${e.uuid}`),f("modal-fee-detail"),p("Fee rule deleted"),await d?.reload(!1)}catch(n){m(n?.response?.data?.message||"Delete failed")}};document.getElementById("btn-open-add-fee-rule")?.addEventListener("click",()=>C()),document.getElementById("btn-fee-edit")?.addEventListener("click",()=>{i&&y(i)}),document.getElementById("btn-fee-delete")?.addEventListener("click",()=>{i&&x(i)}),(async()=>{try{d=await F({table:t,exportFileName:"fee-rules",rowId:"uuid",order:[[5,"asc"]],filters:[{id:"agency",label:"Agency",options:[{value:"",label:"All"},{value:"lgu",label:"LGU"},{value:"bfp",label:"BFP"},{value:"dpwh",label:"DPWH"},{value:"cto",label:"CTO"}],match:(e,n)=>e.agency===n},{id:"basis",label:"Basis",options:[{value:"",label:"All"},{value:"fixed",label:"Fixed"},{value:"area_rate",label:"Area × rate"}],match:(e,n)=>e.basis===n},{id:"active",label:"Active",options:[{value:"",label:"All"},{value:"1",label:"Active"},{value:"0",label:"Inactive"}],match:(e,n)=>String(Number(e.is_active))===n}],columns:[{data:"code",title:"Code",responsivePriority:1,render:e=>`<span class="fw-semibold">${l(String(e??""))}</span>`},{data:"name",title:"Name",responsivePriority:1,render:e=>`<div class="fw-medium text-truncate" style="max-width:14rem">${l(String(e??""))}</div>`},{data:"agency",title:"Agency",responsivePriority:1,render:e=>A(String(e??""))},{data:"basis",title:"Basis",responsivePriority:2,render:e=>l(w(String(e??"")))},{data:"amount",title:"Amount",responsivePriority:2,className:"text-end",render:e=>l($(e))},{data:"priority",title:"Priority",responsivePriority:3},{data:"is_active",title:"Active",responsivePriority:2,render:e=>S(!!e)}],actions:[{id:"view",label:"View rule",onClick:e=>P(e)},{id:"edit",label:"Edit",onClick:e=>y(e)},{id:"delete",label:"Delete",danger:!0,dividerBefore:!0,onClick:e=>{x(e)}}],fetchData:async()=>{const{data:e}=await window.axios.get("/api/v1/admin/fee-rules",{params:{per_page:100}});return I(e.data?.summary||null),e.data?.items||[]}})}catch(e){m(e?.response?.data?.message||"Unable to load fee rules"),I(null)}})(),a.addEventListener("submit",async e=>{e.preventDefault();const n=c?.value.trim()||"",E=k(),u=r;u&&(u.disabled=!0);try{n?(await window.axios.put(`/api/v1/admin/fee-rules/${n}`,E),p("Fee rule updated")):(await window.axios.post("/api/v1/admin/fee-rules",E),p("Fee rule created")),g(),f("modal-add-fee-rule"),await d?.reload(!1)}catch(h){const B=h?.response?.data?.errors,N=B?Object.values(B).flat()[0]:null;m(String(N||h?.response?.data?.message||(n?"Update failed":"Create failed")))}finally{u&&(u.disabled=!1)}})}export{M as initFeeRulesPage};
