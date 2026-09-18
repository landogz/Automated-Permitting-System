import{e as i,b as P,t as $}from"./app-Dl4Yi89w.js";import{c as q}from"./create-apics-datatable-D2pZGRop.js";const w={timeZone:"Asia/Manila"};function S(t){if(!t)return'<span class="text-muted">—</span>';const n=new Date(t);if(Number.isNaN(n.getTime()))return`<span class="text-muted">${i(t)}</span>`;const d=n.toLocaleDateString("en-US",{...w,month:"short",day:"numeric",year:"numeric"}),l=n.toLocaleTimeString("en-US",{...w,hour:"numeric",minute:"2-digit",second:"2-digit",hour12:!0});return`
        <span class="apics-audit-when">
            <span class="apics-audit-when__date">${i(d)}</span>
            <span class="apics-audit-when__time">${i(l)} PHT</span>
        </span>
    `}function C(t,n){return`<span class="apics-audit-badge apics-audit-badge--${["auth","create","modify","danger"].includes(n||"")?n:"auth"}">${i(t)}</span>`}function k(t){const n=t.actor_name||"—",d=t.actor_role_label||"",l=n.split(/\s+/).filter(Boolean).slice(0,2).map(o=>o[0]?.toUpperCase()||"").join("")||"?";return`
        <span class="apics-audit-actor">
            <span class="apics-audit-actor__avatar" aria-hidden="true">${i(l)}</span>
            <span class="min-w-0">
                <span class="apics-audit-actor__name d-block text-truncate">${i(n)}</span>
                ${d?`<span class="apics-audit-actor__role d-block text-truncate">${i(d)}</span>`:""}
            </span>
        </span>
    `}function E(t){const n=t.resource;if(!n?.label)return'<span class="text-muted">—</span>';const d=i(n.label),l=i(n.type||"Record"),o=n.href?`<a href="${i(n.href)}" class="link-primary text-decoration-none">${d}</a>`:d;return`
        <span class="apics-audit-resource">
            <span class="apics-audit-resource__type">${l}</span>
            ${o}
        </span>
    `}function x(t){return`
        <span class="apics-audit-client">
            <span class="apics-audit-client__ip d-block">${i(t.ip_address||"—")}</span>
            <span class="apics-audit-client__ua d-block text-truncate" title="${i(t.user_agent||"")}">
                ${i(t.client_label||"—")}
            </span>
        </span>
    `}function f(t){try{return JSON.stringify(t??{},null,2)}catch{return String(t??"")}}function M(){const t=document.getElementById("apics-audit-console"),n=document.getElementById("audit-table");if(!t||!n)return;const d=t.querySelector("[data-audit-range]"),l=t.querySelector("[data-audit-date-from]"),o=t.querySelector("[data-audit-date-to]"),A=t.querySelectorAll("[data-audit-custom-dates]"),g=document.getElementById("audit-inspect-drawer"),u=document.querySelector("[data-audit-inspect-body]"),_=document.querySelector("[data-audit-inspect-event]");let c=null,v=[{value:"",label:"All actors"}],m=[{value:"",label:"All events"}];const y=a=>{if(!a)return;["events_24h","active_sessions","fee_overrides_24h","security_anomalies_24h"].forEach(r=>{const p=t.querySelector(`[data-audit-stat="${r}"]`);p&&(p.textContent=String(a[r]??0))});const e=t.querySelector('[data-audit-stat="security_anomalies_24h"]')?.closest(".apics-kpi-card");if(e){const r=Number(a.security_anomalies_24h??0);e.classList.toggle("apics-kpi-card--emerald",r===0),e.classList.toggle("apics-kpi-card--rose",r>0)}},b=()=>{const a=d?.value==="custom";A.forEach(s=>s.classList.toggle("d-none",!a))},T=()=>{const a=d?.value||"";return{range:a||void 0,date_from:a==="custom"&&l?.value||void 0,date_to:a==="custom"&&o?.value||void 0}},L=async a=>{if(!(!g||!u)){_&&(_.textContent=a.event),u.innerHTML=`
            <div class="text-center text-muted py-5">
                <div class="spinner-border text-primary avatar-sm" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>
        `,bootstrap.Offcanvas.getOrCreateInstance(g).show();try{const{data:s}=await window.axios.get(`/api/v1/admin/audit-logs/${a.uuid}`);if(!s?.status)throw new Error(s?.message||"Unable to load event");const e=s.data,r=f(e.old_values??{}),p=f(e.new_values??e.meta??{}),h=!!(e.old_values||e.new_values);u.innerHTML=`
                <dl class="apics-audit-meta-grid">
                    <div>
                        <dt>When (PHT)</dt>
                        <dd>${S(e.created_at)}</dd>
                    </div>
                    <div>
                        <dt>Actor</dt>
                        <dd>
                            <strong>${i(e.actor_name||"—")}</strong>
                            ${e.actor_role_label?`<div class="text-muted fs-12">${i(e.actor_role_label)}</div>`:""}
                        </dd>
                    </div>
                    <div>
                        <dt>Target resource</dt>
                        <dd>${E(e)}</dd>
                    </div>
                    <div>
                        <dt>IP &amp; client</dt>
                        <dd>${x(e)}</dd>
                    </div>
                    <div>
                        <dt>Request URL</dt>
                        <dd><code class="fs-12">${i(e.request_url||"—")}</code></dd>
                    </div>
                    <div>
                        <dt>Session ID</dt>
                        <dd><code class="fs-12">${i(e.session_id||"—")}</code></dd>
                    </div>
                    <div>
                        <dt>User agent</dt>
                        <dd class="fs-12">${i(e.user_agent||"—")}</dd>
                    </div>
                    <div>
                        <dt>Severity</dt>
                        <dd><span class="badge bg-secondary-subtle text-secondary text-uppercase">${i(e.severity||"success")}</span></dd>
                    </div>
                </dl>

                <h6 class="fs-13 fw-semibold mb-2">${h?"Payload delta":"Event payload"}</h6>
                <div class="apics-audit-diff">
                    ${h?`
                            <div class="apics-audit-diff__pane apics-audit-diff__pane--old">
                                <div class="apics-audit-diff__pane-title">Old values</div>
                                <pre>${i(r)}</pre>
                            </div>
                            <div class="apics-audit-diff__pane apics-audit-diff__pane--new">
                                <div class="apics-audit-diff__pane-title">New values</div>
                                <pre>${i(p)}</pre>
                            </div>
                        `:`
                            <div class="apics-audit-diff__pane apics-audit-diff__pane--new" style="grid-column: 1 / -1;">
                                <div class="apics-audit-diff__pane-title">Meta</div>
                                <pre>${i(f(e.meta??{}))}</pre>
                            </div>
                        `}
                </div>
            `}catch(s){u.innerHTML=`<div class="alert alert-danger mb-0">${i(s?.response?.data?.message||"Unable to inspect event")}</div>`,$(s?.response?.data?.message||"Unable to inspect event")}}},N=async()=>{c&&(c.destroy(),c=null,n.innerHTML='<thead class="table-light"></thead><tbody></tbody>'),c=await q({table:n,exportFileName:"audit-logs",searchMode:"server",pageLength:25,rowId:"uuid",order:[[0,"desc"]],emptyMessage:"No audit events recorded yet.",noResultsMessage:"No events match these investigative filters.",filters:[{id:"category",label:"Event category",options:m},{id:"actor",label:"Actor",options:v},{id:"severity",label:"Severity",options:[{value:"",label:"All"},{value:"success",label:"Success"},{value:"warning",label:"Warnings"},{value:"failure",label:"Security / failed"}]}],columns:[{data:"created_at",title:"Timestamp",className:"apics-audit-col-when",responsivePriority:1,render:a=>S(a?String(a):null)},{data:"event",title:"Event",className:"apics-audit-col-event",responsivePriority:1,render:(a,s,e)=>C(e.event,e.badge_tone)},{data:"actor_name",title:"Actor",className:"apics-audit-col-actor",responsivePriority:2,render:(a,s,e)=>k(e)},{data:"resource",title:"Target resource",className:"apics-audit-col-resource",responsivePriority:2,orderable:!1,render:(a,s,e)=>E(e)},{data:"ip_address",title:"IP & client",className:"apics-audit-col-client",responsivePriority:3,render:(a,s,e)=>x(e)}],actions:[{id:"inspect",label:"View Diff",icon:"ri-code-s-slash-line",onClick:a=>{L(a)}}],fetchData:async a=>{const{data:s}=await window.axios.get("/api/v1/admin/audit-logs",{params:{per_page:100,search:a.search||void 0,category:a.filters.category||void 0,actor:a.filters.actor||void 0,severity:a.filters.severity||void 0,...T()}});if(!s?.status)throw new Error(s?.message||"Unable to load audit logs");y(s.data?.summary);const e=s.data?.filters;return e?.actors?.length&&(v=[{value:"",label:"All actors"},...e.actors.map(r=>({value:r.value,label:r.label}))]),e?.categories?.length&&(m=[{value:"",label:"All events"},...e.categories.map(r=>({value:r.value,label:r.label}))]),s.data?.items||[]}})};b(),d?.addEventListener("change",()=>{b(),c?.reload(!0)}),l?.addEventListener("change",()=>{d?.value==="custom"&&c?.reload(!0)}),o?.addEventListener("change",()=>{d?.value==="custom"&&c?.reload(!0)}),t.querySelector("[data-audit-refresh]")?.addEventListener("click",()=>{(async()=>(await c?.reload(!1),P("Audit log refreshed")))()}),(async()=>{try{const{data:a}=await window.axios.get("/api/v1/admin/audit-logs",{params:{per_page:1,range:"24h"}});y(a.data?.summary);const s=a.data?.filters;s?.actors?.length&&(v=[{value:"",label:"All actors"},...s.actors.map(e=>({value:e.value,label:e.label}))]),s?.categories?.length&&(m=[{value:"",label:"All events"},...s.categories.map(e=>({value:e.value,label:e.label}))]),await N(),t.setAttribute("aria-busy","false")}catch(a){t.setAttribute("aria-busy","false"),$(a?.response?.data?.message||"Sign in as admin to view audit logs.")}})()}export{M as initAuditPage};
