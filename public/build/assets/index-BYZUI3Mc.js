import{k as Q,t as u,b as v,f as V,d as X}from"./app-Cwtxlj4R.js";import{n as R,r as Z,i as F,e as i,f as tt,t as et}from"./shared-CMJ6zgNa.js";const at={all:"All notifications",unread:"Unread",read:"Read"},G=40;function it(){const n=document.getElementById("apics-notif-inbox");if(!n)return;const d=n.querySelector("[data-notif-mail-list]"),T=n.querySelector("[data-notif-loader]"),L=n.querySelector("[data-notif-empty]"),A=n.querySelector("[data-notif-range]"),I=n.querySelector("[data-notif-folder-label]"),g=n.querySelector("[data-notif-detail-body]"),r=n.querySelector("[data-notif-open-related]"),m=n.querySelector("[data-notif-mark-one]"),N=n.querySelector("[data-notif-search]"),D=n.querySelector("[data-notif-pager]"),M=n.querySelector("[data-notif-page-label]"),w=n.querySelector("[data-notif-prev]"),E=n.querySelector("[data-notif-next]"),W=n.querySelector("[data-notif-admin-tools]"),$=document.querySelector("[data-notif-templates-table]"),U=document.getElementById("form-add-template");if(!d||!g)return;let x="all",j="",c=1,b=1,o=[],p=null,S=null,q=!1;const C=Q("workflow.manage");C&&W?.classList.remove("d-none");const Y=t=>{["total","unread","read"].forEach(e=>{n.querySelectorAll(`[data-notif-count="${e}"]`).forEach(a=>{a.textContent=String(t[e]??0)})})},_=()=>{document.body.classList.remove("email-detail-show"),p=null,d.querySelectorAll("li.is-active").forEach(t=>t.classList.remove("is-active")),g.innerHTML=`
            <div class="text-center text-muted py-5">
                <p class="fs-13 mb-0">Select a notification to read it.</p>
            </div>
        `,r?.classList.add("d-none"),m?.classList.add("d-none"),delete r?.dataset.url,delete m?.dataset.uuid},k=t=>{const e=F(t.data?.event),a=(t.data?.url||"").trim();g.innerHTML=`
            <div class="mt-4 mb-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="apics-notif-inbox__avatar ${e.bg} ${e.text} fs-18">
                        <i class="${e.icon}"></i>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <h5 class="fw-bold email-subject-title mb-1">${i(t.title||"Notification")}</h5>
                        <p class="text-muted fs-12 mb-0">
                            <i class="mdi mdi-clock-outline align-middle"></i>
                            ${i(tt(t.created_at))}
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
        `,r&&(r.classList.toggle("d-none",!(a&&a!=="#")),a&&a!=="#"?r.dataset.url=a:delete r.dataset.url),m&&(m.classList.toggle("d-none",!!t.is_read),m.dataset.uuid=t.uuid)},H=t=>{p=t.uuid,document.body.classList.add("email-detail-show"),d.querySelectorAll("li").forEach(e=>{e.classList.toggle("is-active",e.dataset.uuid===t.uuid)}),k(t)},P=async t=>{await window.axios.post(`/api/v1/notifications/${t}/read`),o=o.map(s=>s.uuid===t?{...s,is_read:!0,read_at:new Date().toISOString()}:s);const e=o.find(s=>s.uuid===t)||null;return d.querySelector(`li[data-uuid="${t}"]`)?.classList.remove("unread"),e},O=()=>{if(d){if(o.length===0){d.innerHTML="",L?.classList.remove("d-none");return}L?.classList.add("d-none"),d.innerHTML=o.map(t=>{const e=F(t.data?.event),a=t.is_read?"":"unread",s=t.uuid===p?"is-active":"",f=i((t.body||"").replace(/\s+/g," ").slice(0,120)),y=i(et(t.created_at));return`
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
                                – <span class="teaser">${f}${f.length>=120?"…":""}</span>
                            </a>
                            <div class="date">${y}</div>
                        </div>
                    </li>
                `}).join("")}},l=async(t=!1)=>{n.setAttribute("aria-busy","true"),T?.classList.remove("d-none");try{const{data:e}=await window.axios.get("/api/v1/notifications",{params:{per_page:G,page:c,status:x,search:j||void 0}});if(!e?.status)throw new Error(e?.message||"Unable to load notifications");o=Z(e);const a=e.data?.counts||{total:e.data?.meta?.total??o.length,unread:e.data?.unread_count??0,read:0};typeof a.read!="number"&&(a.read=Math.max(0,(a.total||0)-(a.unread||0))),Y(a),b=Number(e.data?.meta?.last_page||1);const s=Number(e.data?.meta?.current_page||c),f=Number(e.data?.meta?.total||o.length),y=Number(e.data?.meta?.per_page||G),z=f===0?0:(s-1)*y+1,J=Math.min(s*y,f);if(I&&(I.textContent=at[x]),A&&(A.textContent=f===0?"No messages in this folder":`Showing ${z}–${J} of ${f}`),D&&M&&w&&E){const h=b>1;D.classList.toggle("d-none",!h),M.textContent=`Page ${s} of ${b}`,w.disabled=s<=1,E.disabled=s>=b}if(O(),p){const h=o.find(K=>K.uuid===p);h?H(h):_()}t&&v("Inbox refreshed")}catch(e){u(e?.response?.data?.message||"Unable to load notifications"),o=[],O(),L?.classList.remove("d-none")}finally{T?.classList.add("d-none"),n.setAttribute("aria-busy","false")}},B=async()=>{if(!$||!C)return;const t=$.querySelector("tbody");if(t)try{const{data:e}=await window.axios.get("/api/v1/admin/notification-templates",{params:{per_page:100}}),a=e.data?.items||[];if(!a.length){t.innerHTML='<tr><td colspan="5" class="text-muted text-center py-4">No templates yet.</td></tr>';return}t.innerHTML=a.map(s=>`
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
                `).join("")}catch(e){u(e?.response?.data?.message||"Unable to load templates")}};n.querySelectorAll("[data-notif-filter]").forEach(t=>{t.addEventListener("click",e=>{e.preventDefault(),x=t.dataset.notifFilter||"all",c=1,n.querySelectorAll("[data-notif-filter]").forEach(s=>s.classList.remove("active")),t.classList.add("active"),l()})}),d.addEventListener("click",t=>{const e=t.target.closest("li[data-uuid]");if(!e?.dataset.uuid)return;const a=o.find(s=>s.uuid===e.dataset.uuid);a&&(H(a),a.is_read||P(a.uuid).then(s=>{s&&p===s.uuid&&k(s),l()}).catch(()=>{}))}),g.addEventListener("click",t=>{const e=t.target.closest("[data-notif-detail-open]");e?.dataset.url&&!R(e.dataset.url)&&u("Unsafe notification link blocked.")}),n.querySelector("[data-notif-close-detail]")?.addEventListener("click",()=>{_()}),r?.addEventListener("click",()=>{const t=r.dataset.url;t&&!R(t)&&u("Unsafe notification link blocked.")}),m?.addEventListener("click",()=>{const t=m.dataset.uuid;t&&(async()=>{try{const e=await P(t);v("Marked as read"),e&&k(e),await l()}catch(e){u(e?.response?.data?.message||"Unable to mark as read")}})()}),n.querySelector("[data-notif-mark-all]")?.addEventListener("click",()=>{(async()=>{try{await window.axios.post("/api/v1/notifications/read-all"),v("All notifications marked read"),_(),await l()}catch(t){u(t?.response?.data?.message||"Update failed")}})()}),n.querySelector("[data-notif-refresh]")?.addEventListener("click",()=>{l(!0)}),N?.addEventListener("input",()=>{S&&window.clearTimeout(S),S=window.setTimeout(()=>{j=(N.value||"").trim(),c=1,l()},320)}),w?.addEventListener("click",()=>{c<=1||(c-=1,l())}),E?.addEventListener("click",()=>{c>=b||(c+=1,l())}),n.querySelectorAll(".email-menu-btn").forEach(t=>{t.addEventListener("click",()=>{n.querySelector(".email-menu-sidebar")?.classList.add("menubar-show"),q=!0})}),window.addEventListener("click",t=>{const e=n.querySelector(".email-menu-sidebar");if(e?.classList.contains("menubar-show")){if(q){q=!1;return}t.target.closest(".email-menu-sidebar")||e.classList.remove("menubar-show")}}),U?.addEventListener("submit",async t=>{t.preventDefault();try{await window.axios.post("/api/v1/admin/notification-templates",{code:document.getElementById("tpl-code").value.trim(),name:document.getElementById("tpl-name").value.trim(),channel:document.getElementById("tpl-channel").value,subject:document.getElementById("tpl-subject").value.trim()||void 0,body_template:document.getElementById("tpl-body").value.trim(),is_active:!0}),v("Template created"),U.reset(),V("modal-add-template"),await B()}catch(e){u(e?.response?.data?.message||"Create failed")}}),$?.addEventListener("click",t=>{const e=t.target.closest("[data-tpl-delete]");e?.dataset.uuid&&(async()=>{if(await X("Delete template?","This soft-deletes the template."))try{await window.axios.delete(`/api/v1/admin/notification-templates/${e.dataset.uuid}`),v("Template deleted"),await B()}catch(a){u(a?.response?.data?.message||"Delete failed")}})()}),document.getElementById("modal-templates")?.addEventListener("show.bs.modal",()=>{B()}),l()}export{it as initNotificationsPage};
