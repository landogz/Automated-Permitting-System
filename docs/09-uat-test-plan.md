# UAT Test Plan — APICS Phase I

## Personas
Applicant, Front desk (receiving), Evaluator, Inspector, Assessor, Compliance, Records, Admin, Building Official

## Device matrix
320, 375, 414, 768, 992, 1200, 1440, 1920 — portrait/landscape; Safari iOS/macOS; Chrome Android.

## Preconditions
- `php artisan migrate --seed`
- Local: `APICS_DEMO_QUICK_LOGIN=true` (optional quick login)
- Mail driver `log` or Mailpit for registration emails

## Automated gate (must pass before UAT sign-off)

```bash
php artisan test
# optional API smoke:
bash scripts/uat-api-smoke.sh
```

## Scripted scenarios (manual)

See detailed checklists: [`docs/uat/phase-i-scenarios.md`](uat/phase-i-scenarios.md)

### Smoke (critical path)
1. Applicant registers → pending → cannot login → admin approves → applicant logs in
2. Applicant creates draft → submits application
3. Staff classifies → routing slip → evaluation decision
4. Inspector schedules/completes inspection
5. Assessor/staff generates Order of Payment
6. Compliance issues G-03/G-04 notice (optional appeal)
7. Records creates logbook + archive; applicant receives in-app notification
8. Unauthorized role gets 403 on privileged APIs; sidebar hides unauthorized menus
9. Audit log shows mutation events after create/approve
10. Unsigned logbook print URL returns 403; signed print opens

### Negative / security
11. Wrong password → generic error (no account-state leak for declined/inactive)
12. Applicant cannot open another applicant’s application UUID
13. Evaluator cannot list/manage departments
14. Unauthenticated `/api/v1/admin/*` → 401

### Device / a11y spot checks
15. Login + Applications usable at 375px and 768px (no horizontal scroll)
16. Admin DataTables: search/filter/actions reachable on tablet; sticky Actions column
17. Keyboard: skip link, focus visible on primary CTAs

## Exit criteria
- All automated tests green
- Smoke scenarios 1–10 signed by OCBO UAT lead
- Security review doc accepted (`docs/15-security-review-phase-i.md`)
- No Sev-1/Sev-2 open defects
