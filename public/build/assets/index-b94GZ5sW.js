import{b as u,t as m,e as i,k as f,u as g}from"./app-DRefgvz5.js";import{s as b}from"./status-badge-CaU8VUam.js";function r(e,t){document.querySelectorAll(`[data-dash="${e}"]`).forEach(a=>{a.textContent=String(t)})}function d(e){if(!e)return"—";const t=new Date(e);return Number.isNaN(t.getTime())?"—":t.toLocaleString(void 0,{month:"short",day:"numeric",hour:"2-digit",minute:"2-digit"})}function h(e){return e.replace(/\./g," · ").replace(/_/g," ").replace(/\b\w/g,t=>t.toUpperCase())}function _(e){return{amber:"bg-warning",blue:"bg-info",indigo:"bg-primary",emerald:"bg-success",rose:"bg-danger",slate:"bg-secondary",primary:"bg-primary",warning:"bg-warning",info:"bg-info",success:"bg-success",danger:"bg-danger"}[e]||"bg-primary"}function v(e){const t=document.querySelector("[data-dash-pipeline]");if(!t)return;const a=Math.max(1,...e.map(n=>n.count)),s=e.reduce((n,o)=>n+o.count,0);if(r("pipeline_total",`${s} in pipeline view`),!e.length){t.innerHTML='<p class="text-muted mb-0 fs-13">No pipeline data.</p>';return}t.innerHTML=e.map(n=>{const o=Math.round(n.count/a*100);return`
            <div class="apics-dashboard__pipe-row">
                <div class="apics-dashboard__pipe-meta">
                    <span class="apics-dashboard__pipe-label">${i(n.label)}</span>
                    <span class="apics-dashboard__pipe-count font-monospace">${n.count}</span>
                </div>
                <div class="progress apics-dashboard__pipe-bar" role="progressbar" aria-valuenow="${n.count}" aria-valuemin="0" aria-valuemax="${a}" aria-label="${i(n.label)}">
                    <div class="progress-bar ${_(n.tone)}" style="width:${o}%"></div>
                </div>
            </div>`}).join("")}function y(e){const t=document.querySelector("[data-dash-attention]");if(!t)return;const a=e.filter(s=>!s.permission||f(s.permission));if(!a.length){t.innerHTML='<div class="list-group-item text-muted fs-13 py-4 text-center">No queues available for your role.</div>';return}t.innerHTML=a.map(s=>{const n=s.count>0;return`
            <a href="${i(s.path)}" class="list-group-item list-group-item-action apics-dashboard__attention-item ${n?"is-hot":""}">
                <span class="apics-dashboard__attention-body">
                    <span class="fw-semibold d-block">${i(s.label)}</span>
                    <span class="text-muted fs-12">${i(s.hint)}</span>
                </span>
                <span class="badge ${n?"bg-danger":"bg-secondary-subtle text-secondary"} rounded-pill font-monospace">${s.count}</span>
                <i class="ri-arrow-right-s-line text-muted" aria-hidden="true"></i>
            </a>`}).join("")}function x(e){const t=document.querySelector("[data-dash-recent-apps]");if(t){if(!e.length){t.innerHTML='<tr><td colspan="4" class="text-muted text-center py-4 fs-13">No applications yet.</td></tr>';return}t.innerHTML=e.map(a=>`
        <tr>
            <td>
                <div class="fw-semibold font-monospace fs-13">${i(a.application_no||"—")}</div>
                <div class="text-muted fs-11 d-md-none text-truncate" style="max-width:10rem">${i(a.project_title||"")}</div>
            </td>
            <td class="d-none d-md-table-cell">
                <div class="text-truncate" style="max-width:16rem">${i(a.project_title||"—")}</div>
                <div class="text-muted fs-11">${i(a.applicant_name||"")}</div>
            </td>
            <td>${b(a.status||"draft")}</td>
            <td class="d-none d-lg-table-cell text-muted fs-12 text-nowrap">${i(d(a.updated_at))}</td>
        </tr>`).join("")}}function $(e){const t=document.querySelector("[data-dash-activity]");if(t){if(!e.length){t.innerHTML='<li class="text-muted fs-13 text-center py-3">No recent audit events.</li>';return}t.innerHTML=e.map(a=>{const s=Object.values(a.meta||{}).filter(n=>n!=null&&String(n).trim()!=="").slice(0,2).map(n=>i(String(n)));return`
            <li class="apics-dashboard__activity-item">
                ${g(a.actor_name||"System",a.actor_avatar_url,"apics-user-avatar apics-user-avatar--sm")}
                <span class="min-w-0 flex-grow-1">
                    <span class="d-block fw-semibold fs-13 text-truncate">${i(h(a.event))}</span>
                    <span class="d-block text-muted fs-11">
                        ${i(a.actor_name||"System")}
                        ${s.length?" · "+s.join(" · "):""}
                    </span>
                </span>
                <span class="text-muted fs-11 text-nowrap ms-2">${i(d(a.created_at))}</span>
            </li>`}).join("")}}function S(e){const t=e.applications||{};r("evaluation_queue",e.evaluation_queue??0),r("inspections_scheduled",e.inspections?.scheduled??0),r("for_payment",t.for_payment??0),r("for_releasing",t.for_releasing??0),r("apps_total",t.total??0),r("released",t.released??0),r("compliance_open",e.compliance?.open_notices??0),r("pending_reg",e.pending_registrations??0),r("logbooks",e.records?.logbook_entries??0),r("unread_notif",e.notifications?.unread??0);const a={"stat-apps-total":t.total??0,"stat-under-eval":t.under_evaluation??0,"stat-inspections":e.inspections?.scheduled??0,"stat-payment":t.for_payment??0,"stat-released":t.released??0,"stat-pending-reg":e.pending_registrations??0,"stat-logbooks":e.records?.logbook_entries??0,"stat-unread-notif":e.notifications?.unread??0};Object.entries(a).forEach(([o,p])=>{const c=document.getElementById(o);c&&(c.textContent=String(p))});const s=document.querySelector("[data-dash-generated]");s&&(s.textContent=d(e.meta?.generated_at)||new Date().toLocaleString());const n=document.querySelector("[data-dash-recent-col]");if(n){const o=!!e.meta?.can_view_audit;n.classList.toggle("col-xl-7",o),n.classList.toggle("col-xl-12",!o)}v(e.pipeline||[]),y(e.attention||[]),x(e.recent_applications||[]),$(e.recent_activity||[])}async function l(e=!1){const t=document.getElementById("apics-admin-dashboard");if(t){t.setAttribute("aria-busy","true"),t.classList.add("is-loading");try{const{data:a}=await window.axios.get("/api/v1/staff/dashboard-stats");if(!a?.status)throw new Error(a?.message||"Unable to load dashboard");S(a.data||{}),e&&u("Dashboard refreshed")}catch(a){m(a?.response?.data?.message||"Unable to load operational stats")}finally{t.setAttribute("aria-busy","false"),t.classList.remove("is-loading")}}}function E(){(document.getElementById("apics-admin-dashboard")||document.getElementById("ops-stats"))&&(document.getElementById("btn-dashboard-refresh")?.addEventListener("click",()=>{l(!0)}),l(!1))}export{E as initDashboardStats};
