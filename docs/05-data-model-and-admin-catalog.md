# Data Model & Admin Catalog

## Principle
All business reference data is Admin-editable with CSV/XLSX import (dry-run → commit) and export. Mutations are audited.

## Admin-editable entities
| Domain | Entities |
|--------|----------|
| Org | departments, signatories, inspector pools |
| Access | users, roles, permissions, **registration approval queue** |
| Reference | barangays, occupancy types, building types, project natures |
| Forms | form_definitions, field schemas, revisions, print templates |
| Workflow | statuses, transitions, routing templates, SLA, classifiers |
| Fees | fee_schedules, agency splits |
| Documents | checklists, MIME/size rules |
| Notices | G-03/G-04 templates, numbering series |
| Notifications | templates, triggers |
| Integrations | adapter endpoints, encrypted keys, stub toggles |
| System | holidays, branding, feature flags |

## Core transactional entities
- `permit_applications` (uuid, application_no, status, classification, payload JSON)
- `application_documents`
- `form_definitions` / `form_submissions`
- `audit_logs`
