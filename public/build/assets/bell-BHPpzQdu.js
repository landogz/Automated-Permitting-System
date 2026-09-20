import{i as N,k as q,t as v,b as k}from"./app-Bea-huIk.js";import{n as M,i as $,t as x,e,r as H}from"./shared-CMJ6zgNa.js";function A(){const n=document.querySelector("[data-apics-notif-bell]");if(!n||!N())return;n.classList.remove("d-none");const f=n.querySelector("[data-notif-badge]"),g=n.querySelector("[data-notif-new-count]"),u=n.querySelector("[data-notif-list]"),d=n.querySelector("[data-notif-detail]"),L=n.querySelector("[data-notif-mark-all]"),w=n.querySelector("[data-notif-view-all]"),m=n.querySelector(".dropdown-menu");if(!u||!m)return;w&&q("applications.manage")&&w.classList.remove("d-none");let c=[];const y=t=>{const a=Math.max(0,t);!f||!g||(f.classList.toggle("d-none",a<=0),f.innerHTML=`${a>99?"99+":String(a)}<span class="visually-hidden">unread notifications</span>`,g.textContent=`${a} New`)},h=()=>{d&&(d.classList.add("d-none"),d.innerHTML="",u.classList.remove("d-none"))},b=t=>{if(!d)return;const a=$(t.data?.event),s=(t.data?.url||"").trim(),o=t.created_at?new Date(t.created_at).toLocaleString(void 0,{dateStyle:"medium",timeStyle:"short"}):x(t.created_at);u.classList.add("d-none"),d.classList.remove("d-none"),d.innerHTML=`
            <div class="apics-notif-detail p-3">
                <button type="button" class="btn btn-sm btn-ghost-secondary mb-2 px-1" data-notif-back>
                    <i class="ri-arrow-left-line align-middle"></i> Back to list
                </button>
                <div class="d-flex gap-3 mb-3">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title ${a.bg} ${a.text} rounded-circle fs-18">
                            <i class="${a.icon}"></i>
                        </span>
                    </div>
                    <div class="min-w-0">
                        <h6 class="mb-1 fs-14 fw-semibold">${e(t.title||"Notification")}</h6>
                        <p class="mb-0 fs-11 text-muted text-uppercase">
                            <i class="mdi mdi-clock-outline"></i> ${e(o)}
                        </p>
                    </div>
                </div>
                <div class="apics-notif-detail__body fs-13 text-body mb-3">${e(t.body||"No additional details.")}</div>
                <div class="d-grid gap-2">
                    ${s&&s!=="#"?`<button type="button" class="btn btn-primary btn-sm" data-notif-open data-url="${e(s)}">
                                Open related page
                           </button>`:""}
                    ${t.is_read?"":`<button type="button" class="btn btn-soft-secondary btn-sm" data-notif-mark-one data-uuid="${e(t.uuid)}">
                                Mark as read
                           </button>`}
                </div>
            </div>
        `},S=(t,a)=>{if(c=t,y(a),h(),t.length===0){u.innerHTML='<div class="text-center text-muted py-4 px-3 fs-13">No notifications yet.</div>';return}u.innerHTML=t.map(s=>{const o=s.data?.event,l=$(o),i=s.is_read?"":"is-unread",r=e((s.body||"").replace(/\s+/g," ").slice(0,140));return`
                    <button type="button"
                        class="apics-notif-item ${i}"
                        data-notif-item
                        data-uuid="${e(s.uuid)}"
                        aria-label="View notification: ${e(s.title||"Notification")}">
                        <span class="avatar-xs me-3 flex-shrink-0">
                            <span class="avatar-title ${l.bg} ${l.text} rounded-circle fs-16">
                                <i class="${l.icon}"></i>
                            </span>
                        </span>
                        <span class="apics-notif-item__content text-start min-w-0">
                            <span class="apics-notif-item__title">${e(s.title||"Notification")}</span>
                            <span class="apics-notif-item__preview">${r}${r.length>=140?"…":""}</span>
                            <span class="apics-notif-item__meta">
                                <i class="mdi mdi-clock-outline"></i> ${e(x(s.created_at))}
                                <span class="apics-notif-item__hint">Tap for details</span>
                            </span>
                        </span>
                        <i class="ri-arrow-right-s-line apics-notif-item__chevron text-muted"></i>
                    </button>
                `}).join("")},_=async t=>{await window.axios.post(`/api/v1/notifications/${t}/read`),c=c.map(a=>a.uuid===t?{...a,is_read:!0,read_at:new Date().toISOString()}:a),y(c.filter(a=>!a.is_read).length)},p=async()=>{try{const{data:t}=await window.axios.get("/api/v1/notifications",{params:{per_page:12},skipLoading:!0});if(!t?.status)return;const a=H(t),s=Number(t.data?.unread_count??t.data?.counts?.unread??a.filter(o=>!o.is_read).length);S(a,s)}catch{}};m.addEventListener("click",t=>{const a=t.target;if(a.closest("[data-notif-back]")){t.preventDefault(),t.stopPropagation(),h();return}const s=a.closest("[data-notif-open]");if(s){t.preventDefault(),t.stopPropagation();const i=s.dataset.url||"";i&&i!=="#"&&!M(i)&&v("Unsafe notification link blocked.");return}const o=a.closest("[data-notif-mark-one]");if(o?.dataset.uuid){t.preventDefault(),t.stopPropagation(),(async()=>{try{await _(o.dataset.uuid),k("Marked as read");const i=c.find(r=>r.uuid===o.dataset.uuid);i&&b({...i,is_read:!0})}catch(i){v(i?.response?.data?.message||"Unable to mark as read")}})();return}const l=a.closest("[data-notif-item]");if(l?.dataset.uuid){t.preventDefault(),t.stopPropagation();const i=c.find(r=>r.uuid===l.dataset.uuid);if(!i)return;b(i),i.is_read||_(i.uuid).then(()=>{const r=c.find(D=>D.uuid===i.uuid);r&&!d?.classList.contains("d-none")&&b(r)}).catch(()=>{})}}),L?.addEventListener("click",t=>{t.preventDefault(),t.stopPropagation(),(async()=>{try{await window.axios.post("/api/v1/notifications/read-all"),k("All notifications marked read"),await p()}catch(a){v(a?.response?.data?.message||"Unable to mark notifications read")}})()}),n.addEventListener("show.bs.dropdown",()=>{p()}),p(),window.setInterval(()=>{!n.classList.contains("show")&&!m.classList.contains("show")&&p()},6e4)}export{A as initNotificationBell};
