import{e as $,b as k,t as S,d as C,f as U,s as N}from"./app-DpvXLM-T.js";import{c as H,b as Y}from"./create-apics-datatable-DPPhyJg9.js";const F=[{value:"text",label:"Text"},{value:"number",label:"Number"},{value:"email",label:"Email"},{value:"tel",label:"Phone"},{value:"date",label:"Date"},{value:"textarea",label:"Long text"},{value:"select",label:"Dropdown"},{value:"location",label:"Map location"}];function L(s){return s.trim().toLowerCase().replace(/[^a-z0-9]+/g,"_").replace(/^_+|_+$/g,"").slice(0,60)}function _(){return{sections:[{title:"Application details",fields:[{name:"owner_name",label:"Owner full name",type:"text",required:!0}]}]}}function B(s){if(!s||typeof s!="object")return _();const n=s;return Array.isArray(n.sections)&&n.sections.length?{sections:n.sections.map((o,e)=>{const m=o&&typeof o=="object"?o:{},r=Array.isArray(m.fields)?m.fields:[];return{title:String(m.title||`Section ${e+1}`),fields:r.map(u=>P(u)).filter(u=>!!u)}})}:Array.isArray(n.fields)&&n.fields.length?{sections:[{title:"Application details",fields:n.fields.map(o=>P(o)).filter(o=>!!o)}]}:_()}function P(s){if(!s||typeof s!="object")return null;const n=s,o=String(n.name||"").trim(),e=String(n.label||"").trim();if(!o||!e)return null;const m=String(n.type||"text"),r=Array.isArray(n.options)?n.options.map(u=>String(u)).filter(Boolean):String(n.options||"").split(",").map(u=>u.trim()).filter(Boolean);return{name:o,label:e,type:F.some(u=>u.value===m)?m:"text",required:!!n.required,options:m==="select"?r:void 0,step:n.step!=null?String(n.step):m==="number"?"0.01":void 0}}function E(s){return s.sections.reduce((n,o)=>n+o.fields.length,0)}function K(s,n,o){let e=B(n);const m=o?.templates||[],r=()=>{const p=`${e.sections.length} section(s) · ${E(e)} field(s)`,l=m.length?`<div class="d-flex flex-wrap gap-2 mb-3" data-templates>
                <span class="text-muted fs-12 align-self-center me-1">Load template:</span>
                ${m.map((t,i)=>`<button type="button" class="btn btn-sm btn-soft-info" data-apply-template="${i}">
                                <i class="ri-file-copy-2-line align-bottom me-1"></i>${$(t.code)}
                            </button>`).join("")}
            </div>`:"";s.innerHTML=`
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <h6 class="mb-0">Form fields builder</h6>
                    <p class="text-muted fs-12 mb-0" data-schema-summary>${$(p)}</p>
                </div>
                <button type="button" class="btn btn-sm btn-soft-primary" data-add-section>
                    <i class="ri-add-line align-bottom me-1"></i> Add section
                </button>
            </div>
            ${l}
            <div data-sections class="d-flex flex-column gap-3">
                ${e.sections.map((t,i)=>V(t,i,e.sections.length)).join("")}
            </div>
        `,u(s)},u=p=>{p.querySelectorAll("[data-apply-template]").forEach(l=>{l.addEventListener("click",()=>{(async()=>{const t=Number(l.dataset.applyTemplate),i=m[t];i&&(E(e)>0&&o?.confirmReplace&&!await o.confirmReplace()||(e=B(i.schema),o?.onApplyTemplate?.(i),r()))})()})}),p.querySelector("[data-add-section]")?.addEventListener("click",()=>{e.sections.push({title:`Section ${e.sections.length+1}`,fields:[]}),r()}),p.querySelectorAll("[data-section]").forEach(l=>{const t=Number(l.dataset.sectionIndex);l.querySelector("[data-section-title]")?.addEventListener("input",i=>{e.sections[t].title=i.target.value,h(p)}),l.querySelector("[data-section-up]")?.addEventListener("click",()=>{if(t<=0)return;const i=e.sections;[i[t-1],i[t]]=[i[t],i[t-1]],r()}),l.querySelector("[data-section-down]")?.addEventListener("click",()=>{if(t>=e.sections.length-1)return;const i=e.sections;[i[t+1],i[t]]=[i[t],i[t+1]],r()}),l.querySelector("[data-remove-section]")?.addEventListener("click",()=>{e.sections.length<=1||(e.sections.splice(t,1),r())}),l.querySelector("[data-add-field]")?.addEventListener("click",()=>{const i=l.querySelector("[data-new-label]"),d=l.querySelector("[data-new-type]"),c=l.querySelector("[data-new-required]"),f=l.querySelector("[data-new-options]"),v=i?.value.trim()||"";if(!v){i?.focus();return}const y=d?.value||"text",I=L(v)||`field_${e.sections[t].fields.length+1}`;let A=I,a=2;const b=new Set(e.sections.flatMap(w=>w.fields.map(x=>x.name)));for(;b.has(A);)A=`${I}_${a++}`;const g=(f?.value||"").split(",").map(w=>w.trim()).filter(Boolean);e.sections[t].fields.push({name:A,label:v,type:y,required:!!c?.checked,options:y==="select"?g:void 0,step:y==="number"?"0.01":void 0}),r()}),l.querySelectorAll("[data-field-row]").forEach(i=>{const d=Number(i.dataset.fieldIndex);i.querySelector("[data-field-label]")?.addEventListener("input",c=>{const f=c.target.value;e.sections[t].fields[d].label=f;const v=i.querySelector("[data-field-name]");if(v&&!v.dataset.locked){const y=L(f);y&&(v.value=y,e.sections[t].fields[d].name=y)}}),i.querySelector("[data-field-name]")?.addEventListener("input",c=>{const f=c.target;f.dataset.locked="1",e.sections[t].fields[d].name=L(f.value)||f.value}),i.querySelector("[data-field-type]")?.addEventListener("change",c=>{const f=c.target.value;e.sections[t].fields[d].type=f,f!=="select"&&delete e.sections[t].fields[d].options,f==="number"&&(e.sections[t].fields[d].step="0.01"),r()}),i.querySelector("[data-field-required]")?.addEventListener("change",c=>{e.sections[t].fields[d].required=c.target.checked}),i.querySelector("[data-field-options]")?.addEventListener("input",c=>{const f=c.target.value.split(",").map(v=>v.trim()).filter(Boolean);e.sections[t].fields[d].options=f}),i.querySelector("[data-remove-field]")?.addEventListener("click",()=>{e.sections[t].fields.splice(d,1),r()}),i.querySelector("[data-move-up]")?.addEventListener("click",()=>{if(d<=0)return;const c=e.sections[t].fields;[c[d-1],c[d]]=[c[d],c[d-1]],r()}),i.querySelector("[data-move-down]")?.addEventListener("click",()=>{const c=e.sections[t].fields;d>=c.length-1||([c[d+1],c[d]]=[c[d],c[d+1]],r())})})})},h=p=>{const l=p.querySelector("[data-schema-summary]");l&&(l.textContent=`${e.sections.length} section(s) · ${E(e)} field(s)`)};return r(),{getSchema:()=>B(e),setSchema:p=>{e=B(p),r()},validate:()=>{if(!e.sections.length)return"Add at least one section.";for(const l of e.sections){if(!l.title.trim())return"Every section needs a title.";for(const t of l.fields){if(!t.name.trim()||!t.label.trim())return"Every field needs a name and label.";if(!/^[a-z][a-z0-9_]*$/.test(t.name))return`Invalid field key “${t.name}”. Use lowercase letters, numbers, underscores.`;if(t.type==="select"&&(!t.options||t.options.length===0))return`Dropdown “${t.label}” needs at least one option.`}}const p=e.sections.flatMap(l=>l.fields.map(t=>t.name));return new Set(p).size!==p.length?"Field keys must be unique across the whole form.":null}}}function V(s,n,o){const e=s.fields.map((r,u)=>G(r,n,u,s.fields.length)).join(""),m=o>1;return`<div class="border rounded p-3 bg-light-subtle" data-section data-section-index="${n}">
        <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
            <div class="flex-grow-1">
                <label class="form-label mb-1">Section title</label>
                <input type="text" class="form-control" data-section-title value="${$(s.title)}" maxlength="120">
            </div>
            <div class="btn-group" role="group" aria-label="Section order">
                <button type="button" class="btn btn-soft-secondary" data-section-up ${n===0?"disabled":""} aria-label="Move section up" title="Move section up">
                    <i class="ri-arrow-up-s-line"></i>
                </button>
                <button type="button" class="btn btn-soft-secondary" data-section-down ${n>=o-1?"disabled":""} aria-label="Move section down" title="Move section down">
                    <i class="ri-arrow-down-s-line"></i>
                </button>
                <button type="button" class="btn btn-soft-danger" data-remove-section ${m?"":"disabled"} aria-label="Remove section" title="${m?"Remove section":"At least one section required"}">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-3">
                <thead>
                    <tr>
                        <th style="min-width:8rem">Key</th>
                        <th style="min-width:10rem">Label</th>
                        <th style="min-width:7rem">Type</th>
                        <th>Required</th>
                        <th style="min-width:10rem">Options</th>
                        <th class="text-end" style="width:7rem">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${e||'<tr><td colspan="6" class="text-muted text-center py-3">No fields yet — add one below.</td></tr>'}
                </tbody>
            </table>
        </div>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">New field label</label>
                <input type="text" class="form-control form-control-sm" data-new-label placeholder="e.g. Floor area (sqm)">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Type</label>
                <select class="form-select form-select-sm" data-new-type>
                    ${F.map(r=>`<option value="${r.value}">${r.label}</option>`).join("")}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Options (dropdown)</label>
                <input type="text" class="form-control form-control-sm" data-new-options placeholder="a, b, c">
            </div>
            <div class="col-md-1">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" data-new-required id="new-req-${n}">
                    <label class="form-check-label" for="new-req-${n}">Req.</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-primary w-100" data-add-field>
                    <i class="ri-add-line align-bottom"></i> Add field
                </button>
            </div>
        </div>
    </div>`}function G(s,n,o,e){const m=F.map(h=>`<option value="${h.value}" ${s.type===h.value?"selected":""}>${h.label}</option>`).join(""),r=(s.options||[]).join(", "),u=s.type==="select"?"":"disabled";return`<tr data-field-row data-field-index="${o}">
        <td><input type="text" class="form-control form-control-sm font-monospace" data-field-name value="${$(s.name)}" maxlength="60"></td>
        <td><input type="text" class="form-control form-control-sm" data-field-label value="${$(s.label)}" maxlength="255"></td>
        <td><select class="form-select form-select-sm" data-field-type>${m}</select></td>
        <td class="text-center"><input class="form-check-input" type="checkbox" data-field-required ${s.required?"checked":""}></td>
        <td><input type="text" class="form-control form-control-sm" data-field-options value="${$(r)}" placeholder="opt1, opt2" ${u}></td>
        <td class="text-end text-nowrap">
            <button type="button" class="btn btn-sm btn-soft-secondary" data-move-up ${o===0?"disabled":""} aria-label="Move up"><i class="ri-arrow-up-s-line"></i></button>
            <button type="button" class="btn btn-sm btn-soft-secondary" data-move-down ${o>=e-1?"disabled":""} aria-label="Move down"><i class="ri-arrow-down-s-line"></i></button>
            <button type="button" class="btn btn-sm btn-soft-danger" data-remove-field aria-label="Remove field"><i class="ri-close-line"></i></button>
        </td>
    </tr>`}function J(s){return s.split(/[\n,]+/).map(n=>L(n)).filter(Boolean)}function z(s){return Array.isArray(s)?s.map(n=>String(n)).filter(Boolean).join(`
`):""}function X(){const s=document.getElementById("forms-table"),n=document.getElementById("form-builder"),o=document.getElementById("form-schema-editor");if(!s||!n||!o)return;let e=null,m=null,r=[],u=null;const h=document.getElementById("modal-form-builder-label"),p=document.getElementById("form-uuid"),l=document.getElementById("form-code"),t=document.getElementById("form-title"),i=document.getElementById("form-revision"),d=document.getElementById("form-effective-date"),c=document.getElementById("form-active"),f=document.getElementById("form-attachments"),v=()=>(u||(u=K(o,_(),{templates:r,confirmReplace:()=>C("Replace current fields?","Loading a template will overwrite the sections and fields in this editor. Unsaved changes will be lost."),onApplyTemplate:a=>{!m&&!l.value.trim()&&(l.value=a.code),t.value.trim()||(t.value=a.title),a.required_attachments?.length&&(f.value=z(a.required_attachments)),k(`Loaded ${a.code} field template`)}})),u),y=a=>{m=a?.uuid||null,p.value=m||"",l.value=a?.code||"",t.value=a?.title||"",i.value=a?.revision||"01",d.value=a?.effective_date||"",c.checked=a?!!a.is_active:!0,f.value=z(a?.required_attachments||[]),v().setSchema(a?.schema||_()),l.disabled=!!a,h&&(h.textContent=a?`Edit form · ${a.code}`:"Add form definition")},I=()=>{y(),N("modal-form-builder")},A=async a=>{try{const{data:b}=await window.axios.get(`/api/v1/admin/form-definitions/${a.uuid}`),g=b.data||a;y(g),N("modal-form-builder")}catch(b){S(b?.response?.data?.message||"Unable to load form")}};document.getElementById("btn-add-form")?.addEventListener("click",()=>I()),(async()=>{try{r=(await window.axios.get("/api/v1/admin/form-definitions/templates")).data?.data?.items||[]}catch{r=[]}v();try{e=await H({table:s,exportFileName:"form-definitions",rowId:"uuid",filters:[{id:"active",label:"Active",options:[{value:"",label:"All"},{value:"1",label:"Active"},{value:"0",label:"Inactive"}],match:(a,b)=>String(Number(a.is_active))===b}],columns:[{data:"code",title:"Code",responsivePriority:1,render:a=>`<span class="fw-medium">${$(String(a??""))}</span>`},{data:"title",title:"Title",responsivePriority:1},{data:"revision",title:"Rev",responsivePriority:3},{data:"schema",title:"Fields",orderable:!1,responsivePriority:2,render:(a,b,g)=>{const w=B(g.schema);return`<span class="badge bg-primary-subtle text-primary">${E(w)} fields</span>
                                <span class="text-muted small ms-1">${w.sections.length} sec</span>`}},{data:"is_active",title:"Active",responsivePriority:2,render:a=>Y(!!a)}],actions:[{id:"edit",label:"Edit fields",onClick:a=>{A(a)}},{id:"toggle",label:"Toggle active",onClick:async a=>{try{await window.axios.put(`/api/v1/admin/form-definitions/${a.uuid}`,{is_active:!a.is_active}),k(a.is_active?"Form deactivated":"Form activated"),await e?.reload(!1)}catch(b){S(b?.response?.data?.message||"Update failed")}}},{id:"delete",label:"Delete",danger:!0,dividerBefore:!0,onClick:async a=>{if(await C("Delete form definition?",`${a.code} will be soft-deleted. Existing applications keep their saved payload.`))try{await window.axios.delete(`/api/v1/admin/form-definitions/${a.uuid}`),k("Form deleted"),await e?.reload(!1)}catch(b){S(b?.response?.data?.message||"Delete failed")}}}],fetchData:async()=>{const{data:a}=await window.axios.get("/api/v1/admin/form-definitions",{params:{per_page:100}});return a.data?.items||[]}})}catch(a){S(a?.response?.data?.message||"Sign in as admin to manage forms.")}})(),n.addEventListener("submit",async a=>{a.preventDefault();const b=l.value.trim(),g=t.value.trim(),w=i.value.trim()||"01",x=document.getElementById("btn-save-form"),R=v();if(!b||!g){S("Code and title are required.");return}const j=R.validate();if(j){S(j);return}const M=R.getSchema();if(E(M)===0){S("Add at least one field before saving.");return}x&&(x.disabled=!0);const D={code:b,title:g,revision:w,effective_date:d.value||null,schema:M,required_attachments:J(f.value),is_active:c.checked};try{if(m){const{data:q}=await window.axios.put(`/api/v1/admin/form-definitions/${m}`,D);k(q.message||"Form updated")}else{const{data:q}=await window.axios.post("/api/v1/admin/form-definitions",D);k(q.message||"Form created")}U("modal-form-builder"),y(),await e?.reload(!1)}catch(q){const T=q?.response?.data?.errors,O=T?Object.values(T).flat()[0]:null;S(String(O||q?.response?.data?.message||"Save failed"))}finally{x&&(x.disabled=!1)}})}export{X as initFormsPage};
