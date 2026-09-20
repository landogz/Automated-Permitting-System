import{t as f,b as R,f as y,e as a,d as V,s as z}from"./app-Bea-huIk.js";import{c as O,o as Q}from"./create-apics-datatable-CBJrbmST.js";import{o as I}from"./index-v2WZi9AM.js";import{s as J,a as K,b as W,t as X}from"./ops-completed-BWf4a4bb.js";import{G as N,z as Y,E as Z}from"./site-map-viewer-f2cablRX.js";import{i as ee}from"./application-select-CKlpq9eL.js";function u(e){const t=Number(e??0);return Number.isNaN(t)?"₱0.00":new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP",minimumFractionDigits:2}).format(t)}function _(e){if(!e)return"—";try{return new Date(e).toLocaleString()}catch{return a(e)}}function te(e){return Z(e,{relative:!1,padHour:!0})}function v(e){return e.replaceAll("_"," ").replace(/\b\w/g,t=>t.toUpperCase())}function H(e,t,s){const r=e.toLowerCase(),l={lgu:"bg-primary-subtle text-primary",bfp:"bg-danger-subtle text-danger",dpwh:"bg-warning-subtle text-warning",cto:"bg-success-subtle text-success"}[r]||"bg-secondary-subtle text-secondary",d=t!=null?`${e.toUpperCase()} share: ${u(t)}${s?` — ${s}`:""}`:`${e.toUpperCase()} fee agency`;return`<span class="badge ${l} apics-agency-tip" title="${a(d)}" data-bs-toggle="tooltip">${a(e.toUpperCase())}</span>`}function M(e){return Y(e)}function L(e){Q({formCode:"G-02",formTitle:"Order of Payment",documentNo:e.oop_no,subtitle:e.application?.project_title||void 0,meta:[{label:"Application No.",value:e.application?.application_no||"—"},{label:"Status",value:v(e.status)},{label:"Project",value:e.application?.project_title||"—"},{label:"Location",value:e.application?.project_location||"—"},{label:"Issued",value:_(e.issued_at)},{label:"Assessed by",value:e.assessed_by?.name||"—"}],columns:[{key:"agency",label:"Agency"},{key:"description",label:"Description"},{key:"amount",label:"Amount",align:"right"}],rows:(e.lines||[]).map(t=>({agency:String(t.agency||"").toUpperCase(),description:t.description||"—",amount:u(t.amount)})),totalLabel:"Total amount due",totalValue:u(e.total_amount),signatures:[{role:"Assessed by",name:e.assessed_by?.name},{role:"Reviewed by"},{role:"City Building Official"}],windowTitle:`G-02 ${e.oop_no}`})}function x(e){const t=(s,r)=>{document.querySelectorAll(`[data-oop-stat="${s}"]`).forEach(c=>{c.textContent=r})};if(!e){t("issued","—"),t("paid_stub","—"),t("cancelled","—"),t("total","—"),t("issued_amount","₱0.00"),t("paid_amount","₱0.00");return}t("issued",String(e.issued??0)),t("paid_stub",String(e.paid_stub??0)),t("cancelled",String(e.cancelled??0)),t("total",String(e.total??0)),t("issued_amount",u(e.issued_amount)),t("paid_amount",u(e.paid_amount))}function j(e){return e.length?`<div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">#</th>
                    <th>Agency</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th>Stub ref</th>
                </tr>
            </thead>
            <tbody>${e.map(s=>`<tr>
                <td class="text-muted">${a(String(s.line_order??""))}</td>
                <td>${H(String(s.agency||""),s.amount,s.description)}</td>
                <td>${a(s.description||"—")}</td>
                <td class="text-end">${N(s.amount)}</td>
                <td class="small text-muted">${a(s.external_stub_reference||"—")}</td>
            </tr>`).join("")}</tbody>
        </table>
    </div>`:'<p class="text-muted mb-0">No fee lines on this order.</p>'}function ae(e){const t=e.application||{},s=e.lines||[];return`
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">OoP number</p>
                <p class="fw-semibold mb-0">${a(e.oop_no)}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Status</p>
                <div>${M(e.status)}</div>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Total due</p>
                <p class="fw-semibold fs-18 mb-0 text-primary">${a(u(e.total_amount))}</p>
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="text-muted text-uppercase fw-medium fs-11 mb-1">Assessed by</p>
                <p class="mb-0">${a(e.assessed_by?.name||"—")}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Application</h6>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">Number</p>
                            <p class="fw-medium mb-0">${a(t.application_no||"—")}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">App status</p>
                            <p class="mb-0 text-capitalize">${a(v(String(t.status||"—")))}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted fs-11 text-uppercase mb-1">Project</p>
                            <p class="fw-medium mb-0">${a(t.project_title||"—")}</p>
                            <p class="text-muted small mb-0">${a(t.project_location||"")}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Owner</p>
                            <p class="mb-0">${a(String(t.owner_name||"—"))}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Classification</p>
                            <p class="mb-0 text-capitalize">${a(t.classification?v(String(t.classification)):"—")}</p>
                        </div>
                        <div class="col-sm-4">
                            <p class="text-muted fs-11 text-uppercase mb-1">Occupancy</p>
                            <p class="mb-0">${a(String(t.occupancy||"—"))}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">Lot area</p>
                            <p class="mb-0">${t.lot_area!=null&&t.lot_area!==""?`${a(String(t.lot_area))} sqm`:"—"}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted fs-11 text-uppercase mb-1">Floor area</p>
                            <p class="mb-0">${t.floor_area!=null&&t.floor_area!==""?`${a(String(t.floor_area))} sqm`:"—"}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <h6 class="fs-13 text-uppercase text-muted mb-3">Payment trail</h6>
                    <p class="mb-2"><span class="text-muted">Issued:</span> ${a(_(e.issued_at))}</p>
                    <p class="mb-2"><span class="text-muted">Paid:</span> ${a(_(e.paid_at))}</p>
                    <p class="mb-2"><span class="text-muted">CTO stub:</span> <code class="fs-12">${a(e.cto_stub_reference||"—")}</code></p>
                    <p class="mb-2"><span class="text-muted">Payment ref:</span> <code class="fs-12">${a(e.payment_reference||"—")}</code></p>
                    ${e.override_reason?`<div class="alert alert-warning border-0 mb-0 mt-2 py-2 fs-13"><strong>Override:</strong> ${a(e.override_reason)}</div>`:'<p class="text-muted mb-0 fs-13">No total override applied.</p>'}
                </div>
            </div>
        </div>

        <h6 class="fs-13 text-uppercase text-muted mb-2">Fee line items (${s.length})</h6>
        ${j(s)}
    `}function se(e){const t=e.lines||[];return t.length?`
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <div>
                <p class="fw-medium mb-0">${a(e.application?.application_no||"Application")}</p>
                <p class="text-muted small mb-0">${a(e.application?.project_title||"")}</p>
            </div>
            <div class="text-md-end">
                <p class="text-muted fs-11 text-uppercase mb-0">Computed total</p>
                <p class="fw-semibold text-primary fs-16 mb-0">${a(u(e.total))}</p>
            </div>
        </div>
        <p class="text-muted fs-12 mb-2">
            Basis · Lot ${e.lot_area!=null?a(String(e.lot_area)):"—"} sqm ·
            Floor ${e.floor_area!=null?a(String(e.floor_area)):"—"} sqm ·
            ${e.classification?a(v(e.classification)):"Unclassified"}
        </p>
        ${j(t.map((s,r)=>({...s,line_order:r+1})))}
    `:'<div class="text-warning mb-0 fs-13"><i class="ri-error-warning-line me-1"></i>No active fee rules matched this application. Update Fee Rules before issuing.</div>'}function T(){return[{data:"oop_no",title:"OoP No.",responsivePriority:1,render:e=>`<span class="fw-semibold">${a(String(e??""))}</span>`},{data:"application",title:"Application",responsivePriority:1,render:(e,t,s)=>`<div class="apics-app-no">${a(s.application?.application_no||"—")}</div>
                 <div class="text-muted small text-truncate" style="max-width:14rem">${a(s.application?.project_title||"")}</div>
                 <div class="text-muted small">${s.application?.lot_area!=null?`Lot ${a(String(s.application.lot_area))} sqm`:""}</div>`},{data:"status",title:"Status",responsivePriority:1,render:e=>M(String(e??""))},{data:"total_amount",title:"Total",responsivePriority:1,className:"text-end",render:e=>N(e,{total:!0})},{data:"lines",title:"Agencies",responsivePriority:2,orderable:!1,render:(e,t,s)=>{const r=s.lines||[];if(!r.length)return"—";const c=new Map;return r.forEach(l=>{const d=String(l.agency||"").toLowerCase(),m=c.get(d);if(!m){c.set(d,{...l});return}c.set(d,{...m,amount:Number(m.amount||0)+Number(l.amount||0),description:`${m.description}; ${l.description}`})}),Array.from(c.values()).map(l=>H(String(l.agency||""),l.amount,l.description)).join(" ")}},{data:"issued_at",title:"Issued",responsivePriority:3,render:e=>te(e)},{data:"assessed_by",title:"Assessor",responsivePriority:4,render:(e,t,s)=>a(s.assessed_by?.name||"—")}]}function ce(){const e=document.getElementById("oop-table"),t=document.getElementById("oop-completed-table"),s=document.getElementById("form-generate-oop");if(!e||!s)return;const r=ee(s);let c=null,l=null,d=null;const m=async()=>{await Promise.all([c?.reload(!1),l?.reload(!1)])},p=document.getElementById("oop-fee-preview"),h=document.getElementById("oop-detail-body"),$=document.getElementById("modal-oop-detail-label"),w=document.getElementById("btn-oop-view-app"),P=document.getElementById("btn-oop-mark-paid"),D=document.getElementById("btn-oop-print"),F=o=>{d=o,$&&($.textContent=`Order of Payment · ${o.oop_no}`),h&&(h.innerHTML=ae(o)),w?.classList.toggle("d-none",!o.application?.uuid),P?.classList.toggle("d-none",o.status!=="issued"),z("modal-oop-detail")},S=async o=>{if(await V("Mark this OoP as paid?",`${o.oop_no} will be recorded as paid (CTO stub). Application moves to For Releasing — Released only after G-01 logbook.`))try{const{data:i}=await window.axios.post(`/api/v1/staff/orders-of-payment/${o.uuid}/mark-paid`);y("modal-oop-detail"),X("Payment recorded",i.data?.next_step),await m()}catch(i){f(i?.response?.data?.message||"Update failed")}},U=async o=>{if(!p||!o){p&&(p.innerHTML='<p class="text-muted fs-13 mb-0">Select an application to preview the fee assessment.</p>');return}p.innerHTML='<div class="text-muted fs-13"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Computing fees…</div>';try{const{data:i}=await window.axios.get(`/api/v1/staff/applications/${o}/orders-of-payment/preview`);p.innerHTML=se(i.data||{})}catch(i){p.innerHTML=`<p class="text-danger fs-13 mb-0">${a(i?.response?.data?.message||"Unable to preview fees")}</p>`}};w?.addEventListener("click",()=>{const o=d?.application?.uuid;o&&(y("modal-oop-detail"),I(o))}),P?.addEventListener("click",()=>{d&&S(d)}),D?.addEventListener("click",()=>{if(!d){f("No order selected to print");return}L(d)});const B=document.getElementById("oop-app-uuid");let g="";const A=()=>{const o=r.get("oop-app-uuid")?.getValue()||B?.value.trim()||"";o!==g&&(g=o,U(o))};B?.addEventListener("change",A),document.getElementById("modal-generate-oop")?.addEventListener("shown.bs.modal",()=>{A()});const E=o=>{const i=[{id:"view-order",label:"View Order",primary:!0,onClick:n=>F(n)},{id:"print",label:"Print G-02",onClick:n=>L(n)},{id:"view-app",label:"View application details",visible:n=>!!n.application?.uuid,onClick:n=>{n.application?.uuid&&I(n.application.uuid)}}];return o&&i.push({id:"mark-paid",label:"Mark paid (CTO stub)",dividerBefore:!0,visible:n=>n.status==="issued",onClick:n=>{S(n)}}),i};(async()=>{try{c=await O({table:e,exportFileName:"orders-of-payment-active",rowId:"uuid",searchMode:"server",order:[[0,"desc"]],columns:T(),actions:E(!0),fetchData:async({search:o})=>{const{data:i}=await window.axios.get("/api/v1/staff/orders-of-payment",{params:{search:o||void 0,bucket:"active",per_page:100}});x(i.data?.summary||null);const n=i.data?.items||[];return J("oop-queue",i.data?.meta?.total??n.length),n}}),t&&(l=await O({table:t,exportFileName:"orders-of-payment-completed",rowId:"uuid",searchMode:"server",order:[[0,"desc"]],columns:T(),actions:E(!1),fetchData:async({search:o})=>{const{data:i}=await window.axios.get("/api/v1/staff/orders-of-payment",{params:{search:o||void 0,bucket:"completed",per_page:100}});x(i.data?.summary||null);const n=i.data?.items||[];return K("oop-queue",i.data?.meta?.total??n.length),n}})),W("oop-queue",()=>l?.raw,()=>c?.raw)}catch(o){f(o?.response?.data?.message||"Unable to load orders"),x(null)}})(),s.addEventListener("submit",async o=>{o.preventDefault();const i=r.get("oop-app-uuid")?.getValue()||document.getElementById("oop-app-uuid").value.trim();if(!i){f("Please select an application");return}const n=document.getElementById("oop-override-total").value,q=document.getElementById("oop-override-reason").value.trim(),b=document.getElementById("btn-issue-oop");b&&(b.disabled=!0);try{await window.axios.post(`/api/v1/staff/applications/${i}/orders-of-payment`,{override_total:n!==""?Number(n):void 0,override_reason:q||void 0}),R("Order of payment issued"),s.reset(),r.get("oop-app-uuid")?.clear(),g="",p&&(p.innerHTML='<p class="text-muted fs-13 mb-0">Select an application to preview the fee assessment.</p>'),y("modal-generate-oop"),await m()}catch(C){const k=C?.response?.data?.errors,G=k?Object.values(k).flat()[0]:null;f(String(G||C?.response?.data?.message||"Generate failed"))}finally{b&&(b.disabled=!1)}})}export{ce as initOrdersOfPaymentPage};
