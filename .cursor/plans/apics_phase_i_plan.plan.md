---
name: APICS Phase I Plan (pointer)
overview: "Canonical living plan is apics_phase_i_plan_5d06a183.plan.md — keep that file updated; this stub mirrors its todo statuses for quick reference."
todos:
  - id: docs-suite
    content: Create docs/ suite (charter, phases, flows, admin catalog, API, security, UAT, deploy, manuals, KT, roadmap, SLA) and update README
    status: completed
  - id: foundation
    content: "Foundation: Sanctum auth, RBAC, AuditLogger, API envelope, Admin shell, reusable UI, import/export framework"
    status: completed
  - id: forms-intake
    content: Dynamic Forms Engine + Online Application/Intake + uploads + Citizens Portal adapter stub + tracking
    status: completed
  - id: registration-approval
    content: "Applicant self-registration; Admin approve/decline queue; emails on submitted/approved/declined; block login until approved"
    status: completed
  - id: classifier-eval
    content: Permit Classifier + Evaluation/Routing slips + time tracking + Admin workflow config
    status: completed
  - id: inspection-fees-compliance
    content: Inspection scheduling/notes + Order of Payment/fee engine + CTO/BFP/DPWH stubs + G-03/G-04 compliance
    status: completed
  - id: records-notif
    content: Records/Archiving logbooks + Notifications + dashboards + PDF/print polish
    status: completed
  - id: qa-uat-deploy
    content: "QA gate: automated tests + security review + UAT scripts (done). Remaining: staging, training/KT, production go-live + warranty process"
    status: pending
  - id: plan-sync-rule
    content: Add alwaysApply Cursor rule — after every completed function/feature, update this project plan (todos, status, changelog) before saying done
    status: completed
isProject: false
---

# APICS Phase I — Living Plan (pointer)

**Canonical source of truth:** [`apics_phase_i_plan_5d06a183.plan.md`](./apics_phase_i_plan_5d06a183.plan.md)

Do not maintain a separate changelog here. Update the canonical plan’s frontmatter `todos` and **Progress changelog** on every done.

## Status snapshot (synced from canonical)

| Todo | Status |
|------|--------|
| docs-suite | completed |
| foundation | completed |
| forms-intake | completed |
| registration-approval | completed |
| plan-sync-rule | completed |
| classifier-eval | completed |
| inspection-fees-compliance | completed |
| records-notif | completed |
| qa-uat-deploy | pending — QA gate done; staging/KT/prod remain (NEXT) |

Admin dashboard progress reads the canonical markdown via `ProjectPlanService`.
