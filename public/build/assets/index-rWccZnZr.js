import{e as i,u as x,b as k,t as $}from"./app-Bea-huIk.js";import{c as q}from"./create-apics-datatable-CBJrbmST.js";const w={timeZone:"Asia/Manila"};function S(t){if(!t)return'<span class="text-muted">—</span>';const r=new Date(t);if(Number.isNaN(r.getTime()))return`<span class="text-muted">${i(t)}</span>`;const n=r.toLocaleDateString("en-US",{...w,month:"short",day:"numeric",year:"numeric"}),l=r.toLocaleTimeString("en-US",{...w,hour:"numeric",minute:"2-digit",second:"2-digit",hour12:!0});return`
        <span class="apics-audit-when">
            <span class="apics-audit-when__date">${i(n)}</span>
            <span class="apics-audit-when__time">${i(l)} PHT</span>
        </span>
    `}function U(t,r){return`<span class="apics-audit-badge apics-audit-badge--${["auth","create","modify","danger"].includes(r||"")?r:"auth"}">${i(t)}</span>`}function C(t){const r=t.actor_name||"—",n=t.actor_role_label||"",l=t.actor_avatar_url||t.user?.avatar_url||null;return`
        <span class="apics-audit-actor">
            ${x(r==="—"?"User":r,l,"apics-audit-actor__avatar")}
            <span class="min-w-0">
                <span class="apics-audit-actor__name d-block text-truncate">${i(r)}</span>
                ${n?`<span class="apics-audit-actor__role d-block text-truncate">${i(n)}</span>`:""}
            </span>
        </span>
    `}function E(t){const r=t.resource;if(!r?.label)return'<span class="text-muted">—</span>';const n=i(r.label),l=i(r.type||"Record"),o=r.href?`<a href="${i(r.href)}" class="link-primary text-decoration-none">${n}</a>`:n;return`
        <span class="apics-audit-resource">
            <span class="apics-audit-resource__type">${l}</span>
            ${o}
        </span>
    `}function A(t){return`
        <span class="apics-audit-client">
            <span class="apics-audit-client__ip d-block">${i(t.ip_address||"—")}</span>
            <span class="apics-audit-client__ua d-block text-truncate" title="${i(t.user_agent||"")}">
                ${i(t.client_label||"—")}
            </span>
        </span>
    `}function f(t){try{return JSON.stringify(t??{},null,2)}catch{return String(t??"")}}function I(){const t=document.getElementById("apics-audit-console"),r=document.getElementById("audit-table");if(!t||!r)return;const n=t.querySelector("[data-audit-range]"),l=t.querySelector("[data-audit-date-from]"),o=t.querySelector("[data-audit-date-to]"),T=t.querySelectorAll("[data-audit-custom-dates]"),_=document.getElementById("audit-inspect-drawer"),u=document.querySelector("[data-audit-inspect-body]"),g=document.querySelector("[data-audit-inspect-event]");let c=null,v=[{value:"",label:"All actors"}],m=[{value:"",label:"All events"}];const y=a=>{if(!a)return;["events_24h","active_sessions","fee_overrides_24h","security_anomalies_24h"].forEach(d=>{const p=t.querySelector(`[data-audit-stat="${d}"]`);p&&(p.textContent=String(a[d]??0))});const e=t.querySelector('[data-audit-stat="security_anomalies_24h"]')?.closest(".apics-kpi-card");if(e){const d=Number(a.security_anomalies_24h??0);e.classList.toggle("apics-kpi-card--emerald",d===0),e.classList.toggle("apics-kpi-card--rose",d>0)}},b=()=>{const a=n?.value==="custom";T.forEach(s=>s.classList.toggle("d-none",!a))},L=()=>{const a=n?.value||"";return{range:a||void 0,date_from:a==="custom"&&l?.value||void 0,date_to:a==="custom"&&o?.value||void 0}},N=async a=>{if(!(!_||!u)){g&&(g.textContent=a.event),u.innerHTML=`
            <div class="text-center text-muted py-5">
                <div class="spinner-border text-primary avatar-sm" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>
        `,bootstrap.Offcanvas.getOrCreateInstance(_).show();try{const{data:s}=await window.axios.get(`/api/v1/admin/audit-logs/${a.uuid}`);if(!s?.status)throw new Error(s?.message||"Unable to load event");const e=s.data,d=f(e.old_values??{}),p=f(e.new_values??e.meta??{}),h=!!(e.old_values||e.new_values);u.innerHTML=`
                <dl class="apics-audit-meta-grid">
                    <div>
                        <dt>When (PHT)</dt>
                        <dd>${S(e.created_at)}</dd>
                    </div>
                    <div>
                        <dt>Actor</dt>
                        <dd>
                            <span class="apics-audit-actor">
                                ${x(e.actor_name||"User",e.actor_avatar_url||e.user?.avatar_url||null,"apics-audit-actor__avatar")}
                                <span class="min-w-0">
                                    <strong class="d-block">${i(e.actor_name||"—")}</strong>
                                    ${e.actor_role_label?`<div class="text-muted fs-12">${i(e.actor_role_label)}</div>`:""}
                                </span>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt>Target resource</dt>
                        <dd>${E(e)}</dd>
                    </div>
                    <div>
                        <dt>IP &amp; client</dt>
                        <dd>${A(e)}</dd>
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
                                <pre>${i(d)}</pre>
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
            `}catch(s){u.innerHTML=`<div class="alert alert-danger mb-0">${i(s?.response?.data?.message||"Unable to inspect event")}</div>`,$(s?.response?.data?.message||"Unable to inspect event")}}},P=async()=>{c&&(c.destroy(),c=null,r.innerHTML='<thead class="table-light"></thead><tbody></tbody>'),c=await q({table:r,exportFileName:"audit-logs",searchMode:"server",pageLength:25,rowId:"uuid",order:[[0,"desc"]],emptyMessage:"No audit events recorded yet.",noResultsMessage:"No events match these investigative filters.",filters:[{id:"category",label:"Event category",options:m},{id:"actor",label:"Actor",options:v},{id:"severity",label:"Severity",options:[{value:"",label:"All"},{value:"success",label:"Success"},{value:"warning",label:"Warnings"},{value:"failure",label:"Security / failed"}]}],columns:[{data:"created_at",title:"Timestamp",className:"apics-audit-col-when",responsivePriority:1,render:a=>S(a?String(a):null)},{data:"event",title:"Event",className:"apics-audit-col-event",responsivePriority:1,render:(a,s,e)=>U(e.event,e.badge_tone)},{data:"actor_name",title:"Actor",className:"apics-audit-col-actor",responsivePriority:2,render:(a,s,e)=>C(e)},{data:"resource",title:"Target resource",className:"apics-audit-col-resource",responsivePriority:2,orderable:!1,render:(a,s,e)=>E(e)},{data:"ip_address",title:"IP & client",className:"apics-audit-col-client",responsivePriority:3,render:(a,s,e)=>A(e)}],actions:[{id:"inspect",label:"View Diff",icon:"ri-code-s-slash-line",onClick:a=>{N(a)}}],fetchData:async a=>{const{data:s}=await window.axios.get("/api/v1/admin/audit-logs",{params:{per_page:100,search:a.search||void 0,category:a.filters.category||void 0,actor:a.filters.actor||void 0,severity:a.filters.severity||void 0,...L()}});if(!s?.status)throw new Error(s?.message||"Unable to load audit logs");y(s.data?.summary);const e=s.data?.filters;return e?.actors?.length&&(v=[{value:"",label:"All actors"},...e.actors.map(d=>({value:d.value,label:d.label}))]),e?.categories?.length&&(m=[{value:"",label:"All events"},...e.categories.map(d=>({value:d.value,label:d.label}))]),s.data?.items||[]}})};b(),n?.addEventListener("change",()=>{b(),c?.reload(!0)}),l?.addEventListener("change",()=>{n?.value==="custom"&&c?.reload(!0)}),o?.addEventListener("change",()=>{n?.value==="custom"&&c?.reload(!0)}),t.querySelector("[data-audit-refresh]")?.addEventListener("click",()=>{(async()=>(await c?.reload(!1),k("Audit log refreshed")))()}),(async()=>{try{const{data:a}=await window.axios.get("/api/v1/admin/audit-logs",{params:{per_page:1,range:"24h"}});y(a.data?.summary);const s=a.data?.filters;s?.actors?.length&&(v=[{value:"",label:"All actors"},...s.actors.map(e=>({value:e.value,label:e.label}))]),s?.categories?.length&&(m=[{value:"",label:"All events"},...s.categories.map(e=>({value:e.value,label:e.label}))]),await P(),t.setAttribute("aria-busy","false")}catch(a){t.setAttribute("aria-busy","false"),$(a?.response?.data?.message||"Sign in as admin to view audit logs.")}})()}export{I as initAuditPage};
