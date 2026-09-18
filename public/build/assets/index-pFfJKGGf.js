import{k as K,b,t as p,f as Q,d as V}from"./app-B97eAumq.js";import{r as X,i as R,e as i,f as Z,t as tt}from"./shared-B2v1tA1N.js";const et={all:"All notifications",unread:"Unread",read:"Read"},F=40;function nt(){const n=document.getElementById("apics-notif-inbox");if(!n)return;const d=n.querySelector("[data-notif-mail-list]"),T=n.querySelector("[data-notif-loader]"),w=n.querySelector("[data-notif-empty]"),A=n.querySelector("[data-notif-range]"),N=n.querySelector("[data-notif-folder-label]"),g=n.querySelector("[data-notif-detail-body]"),r=n.querySelector("[data-notif-open-related]"),u=n.querySelector("[data-notif-mark-one]"),I=n.querySelector("[data-notif-search]"),D=n.querySelector("[data-notif-pager]"),M=n.querySelector("[data-notif-page-label]"),L=n.querySelector("[data-notif-prev]"),E=n.querySelector("[data-notif-next]"),G=n.querySelector("[data-notif-admin-tools]"),$=document.querySelector("[data-notif-templates-table]"),j=document.getElementById("form-add-template");if(!d||!g)return;let x="all",C="",c=1,v=1,o=[],f=null,S=null,q=!1;const U=K("workflow.manage");U&&G?.classList.remove("d-none");const W=t=>{["total","unread","read"].forEach(e=>{n.querySelectorAll(`[data-notif-count="${e}"]`).forEach(a=>{a.textContent=String(t[e]??0)})})},_=()=>{document.body.classList.remove("email-detail-show"),f=null,d.querySelectorAll("li.is-active").forEach(t=>t.classList.remove("is-active")),g.innerHTML=`
            <div class="text-center text-muted py-5">
                <p class="fs-13 mb-0">Select a notification to read it.</p>
            </div>
        `,r?.classList.add("d-none"),u?.classList.add("d-none"),delete r?.dataset.url,delete u?.dataset.uuid},k=t=>{const e=R(t.data?.event),a=(t.data?.url||"").trim();g.innerHTML=`
            <div class="mt-4 mb-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="apics-notif-inbox__avatar ${e.bg} ${e.text} fs-18">
                        <i class="${e.icon}"></i>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <h5 class="fw-bold email-subject-title mb-1">${i(t.title||"Notification")}</h5>
                        <p class="text-muted fs-12 mb-0">
                            <i class="mdi mdi-clock-outline align-middle"></i>
                            ${i(Z(t.created_at))}
                            ${t.is_read?"":' · <span class="badge bg-warning-subtle text-warning">Unread</span>'}
                        </p>
                    </div>
                </div>
            </div>
            <div class="apics-notif-inbox__detail-card mb-3">${i(t.body||"No additional details.")}</div>
            <div class="d-flex flex-wrap gap-2 mb-2">
                ${t.channel?`<span class="badge bg-secondary-subtle text-secondary">${i(t.channel)}</span>`:""}
                ${t.template_code?`<span class="badge bg-info-subtle text-info">${i(t.template_code)}</span>`:""}
                ${t.status?`<span class="badge bg-primary-subtle text-primary">${i(t.status)}</span>`:""}
            </div>
            ${a&&a!=="#"?`<button type="button" class="btn btn-primary btn-sm" data-notif-detail-open data-url="${i(a)}">
                        Open related page <i class="ri-arrow-right-line align-middle ms-1"></i>
                   </button>`:""}
        `,r&&(r.classList.toggle("d-none",!(a&&a!=="#")),a&&a!=="#"?r.dataset.url=a:delete r.dataset.url),u&&(u.classList.toggle("d-none",!!t.is_read),u.dataset.uuid=t.uuid)},H=t=>{f=t.uuid,document.body.classList.add("email-detail-show"),d.querySelectorAll("li").forEach(e=>{e.classList.toggle("is-active",e.dataset.uuid===t.uuid)}),k(t)},P=async t=>{await window.axios.post(`/api/v1/notifications/${t}/read`),o=o.map(s=>s.uuid===t?{...s,is_read:!0,read_at:new Date().toISOString()}:s);const e=o.find(s=>s.uuid===t)||null;return d.querySelector(`li[data-uuid="${t}"]`)?.classList.remove("unread"),e},O=()=>{if(d){if(o.length===0){d.innerHTML="",w?.classList.remove("d-none");return}w?.classList.add("d-none"),d.innerHTML=o.map(t=>{const e=R(t.data?.event),a=t.is_read?"":"unread",s=t.uuid===f?"is-active":"",m=i((t.body||"").replace(/\s+/g," ").slice(0,120)),y=i(tt(t.created_at));return`
                    <li class="${a} ${s}" data-uuid="${i(t.uuid)}" role="listitem">
                        <div class="col-mail col-mail-1">
                            <span class="apics-notif-inbox__avatar ${e.bg} ${e.text} me-2 fs-15">
                                <i class="${e.icon}"></i>
                            </span>
                            <a href="javascript:void(0)" class="title">
                                <span class="title-name">${i(t.title||"Notification")}</span>
                            </a>
                        </div>
                        <div class="col-mail col-mail-2">
                            <a href="javascript:void(0)" class="subject">
                                <span class="subject-title">${i(t.title||"Notification")}</span>
                                – <span class="teaser">${m}${m.length>=120?"…":""}</span>
                            </a>
                            <div class="date">${y}</div>
                        </div>
                    </li>
                `}).join("")}},l=async(t=!1)=>{n.setAttribute("aria-busy","true"),T?.classList.remove("d-none");try{const{data:e}=await window.axios.get("/api/v1/notifications",{params:{per_page:F,page:c,status:x,search:C||void 0}});if(!e?.status)throw new Error(e?.message||"Unable to load notifications");o=X(e);const a=e.data?.counts||{total:e.data?.meta?.total??o.length,unread:e.data?.unread_count??0,read:0};typeof a.read!="number"&&(a.read=Math.max(0,(a.total||0)-(a.unread||0))),W(a),v=Number(e.data?.meta?.last_page||1);const s=Number(e.data?.meta?.current_page||c),m=Number(e.data?.meta?.total||o.length),y=Number(e.data?.meta?.per_page||F),Y=m===0?0:(s-1)*y+1,z=Math.min(s*y,m);if(N&&(N.textContent=et[x]),A&&(A.textContent=m===0?"No messages in this folder":`Showing ${Y}–${z} of ${m}`),D&&M&&L&&E){const h=v>1;D.classList.toggle("d-none",!h),M.textContent=`Page ${s} of ${v}`,L.disabled=s<=1,E.disabled=s>=v}if(O(),f){const h=o.find(J=>J.uuid===f);h?H(h):_()}t&&b("Inbox refreshed")}catch(e){p(e?.response?.data?.message||"Unable to load notifications"),o=[],O(),w?.classList.remove("d-none")}finally{T?.classList.add("d-none"),n.setAttribute("aria-busy","false")}},B=async()=>{if(!$||!U)return;const t=$.querySelector("tbody");if(t)try{const{data:e}=await window.axios.get("/api/v1/admin/notification-templates",{params:{per_page:100}}),a=e.data?.items||[];if(!a.length){t.innerHTML='<tr><td colspan="5" class="text-muted text-center py-4">No templates yet.</td></tr>';return}t.innerHTML=a.map(s=>`
                    <tr data-uuid="${i(s.uuid)}">
                        <td><code class="fs-12">${i(s.code)}</code></td>
                        <td>${i(s.name)}</td>
                        <td>${i(s.channel)}</td>
                        <td>${s.is_active?'<span class="badge bg-success-subtle text-success">Yes</span>':'<span class="badge bg-secondary-subtle text-secondary">No</span>'}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-soft-danger" data-tpl-delete data-uuid="${i(s.uuid)}">
                                Delete
                            </button>
                        </td>
                    </tr>
                `).join("")}catch(e){p(e?.response?.data?.message||"Unable to load templates")}};n.querySelectorAll("[data-notif-filter]").forEach(t=>{t.addEventListener("click",e=>{e.preventDefault(),x=t.dataset.notifFilter||"all",c=1,n.querySelectorAll("[data-notif-filter]").forEach(s=>s.classList.remove("active")),t.classList.add("active"),l()})}),d.addEventListener("click",t=>{const e=t.target.closest("li[data-uuid]");if(!e?.dataset.uuid)return;const a=o.find(s=>s.uuid===e.dataset.uuid);a&&(H(a),a.is_read||P(a.uuid).then(s=>{s&&f===s.uuid&&k(s),l()}).catch(()=>{}))}),g.addEventListener("click",t=>{const e=t.target.closest("[data-notif-detail-open]");e?.dataset.url&&(window.location.href=e.dataset.url)}),n.querySelector("[data-notif-close-detail]")?.addEventListener("click",()=>{_()}),r?.addEventListener("click",()=>{const t=r.dataset.url;t&&(window.location.href=t)}),u?.addEventListener("click",()=>{const t=u.dataset.uuid;t&&(async()=>{try{const e=await P(t);b("Marked as read"),e&&k(e),await l()}catch(e){p(e?.response?.data?.message||"Unable to mark as read")}})()}),n.querySelector("[data-notif-mark-all]")?.addEventListener("click",()=>{(async()=>{try{await window.axios.post("/api/v1/notifications/read-all"),b("All notifications marked read"),_(),await l()}catch(t){p(t?.response?.data?.message||"Update failed")}})()}),n.querySelector("[data-notif-refresh]")?.addEventListener("click",()=>{l(!0)}),I?.addEventListener("input",()=>{S&&window.clearTimeout(S),S=window.setTimeout(()=>{C=(I.value||"").trim(),c=1,l()},320)}),L?.addEventListener("click",()=>{c<=1||(c-=1,l())}),E?.addEventListener("click",()=>{c>=v||(c+=1,l())}),n.querySelectorAll(".email-menu-btn").forEach(t=>{t.addEventListener("click",()=>{n.querySelector(".email-menu-sidebar")?.classList.add("menubar-show"),q=!0})}),window.addEventListener("click",t=>{const e=n.querySelector(".email-menu-sidebar");if(e?.classList.contains("menubar-show")){if(q){q=!1;return}t.target.closest(".email-menu-sidebar")||e.classList.remove("menubar-show")}}),j?.addEventListener("submit",async t=>{t.preventDefault();try{await window.axios.post("/api/v1/admin/notification-templates",{code:document.getElementById("tpl-code").value.trim(),name:document.getElementById("tpl-name").value.trim(),channel:document.getElementById("tpl-channel").value,subject:document.getElementById("tpl-subject").value.trim()||void 0,body_template:document.getElementById("tpl-body").value.trim(),is_active:!0}),b("Template created"),j.reset(),Q("modal-add-template"),await B()}catch(e){p(e?.response?.data?.message||"Create failed")}}),$?.addEventListener("click",t=>{const e=t.target.closest("[data-tpl-delete]");e?.dataset.uuid&&(async()=>{if(await V("Delete template?","This soft-deletes the template."))try{await window.axios.delete(`/api/v1/admin/notification-templates/${e.dataset.uuid}`),b("Template deleted"),await B()}catch(a){p(a?.response?.data?.message||"Delete failed")}})()}),document.getElementById("modal-templates")?.addEventListener("show.bs.modal",()=>{B()}),l()}export{nt as initNotificationsPage};
