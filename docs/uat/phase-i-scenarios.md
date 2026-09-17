# Phase I UAT scenarios (checklist)

Mark each row Pass / Fail / Blocked. Attach evidence (screenshot or API response id).

| ID | Persona | Steps | Expected | Result |
|----|---------|-------|----------|--------|
| UAT-01 | Applicant | Register new email | 201 pending; emails logged; login blocked | |
| UAT-02 | Admin | Approve registration | Status approved; applicant can login | |
| UAT-03 | Admin | Decline with reason | Status declined; login blocked | |
| UAT-04 | Applicant | Create draft + submit | Status submitted; appears in staff queue | |
| UAT-05 | Receiving/Eval | Classify application | Classification recorded | |
| UAT-06 | Staff | Generate routing slip | Slip created with offices | |
| UAT-07 | Evaluator | Record evaluation decision | Decision saved; time tracking note | |
| UAT-08 | Inspector | Schedule + complete inspection | Status/notes saved | |
| UAT-09 | Assessor | Generate OoP | G-02 amounts; CTO/BFP/DPWH stubs present | |
| UAT-10 | Compliance | Issue notice | G-03/G-04 visible; appeal path if used | |
| UAT-11 | Records | Logbook + archive + notify | Print signed URL works; applicant inbox updates | |
| UAT-12 | Evaluator | Open Departments API/UI | 403 / menu hidden | |
| UAT-13 | Guest | Open unsigned print URL | 403 | |
| UAT-14 | Admin | Import departments CSV dry-run | Validation report; no commit until confirm | |
| UAT-15 | ICT | Review `/admin/audit` after mutations | Events present without secrets | |

## Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| OCBO UAT Lead | | | |
| ICT Security | | | |
| Vendor (APICS) | | | |
