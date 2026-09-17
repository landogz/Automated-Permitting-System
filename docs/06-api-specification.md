# API Specification (`/api/v1`)

## Envelope
Success: `{ "status": true, "message": "...", "data": {} }`  
Error: `{ "status": false, "message": "...", "errors": {} }`

## Auth
- `POST /api/v1/auth/register` — public applicant signup → `pending` (rate-limited)
- `POST /api/v1/auth/login` — only **approved** + active users
- `POST /api/v1/auth/logout` (auth:sanctum)
- `GET /api/v1/auth/me` (auth:sanctum)

## Admin
- Departments CRUD + import/export
- Form definitions CRUD
- Audit logs index/show
- **Registrations queue:** list pending, approve, decline (with reason) + emails
- **Users & Roles:** `/admin/users` + `/api/v1/admin/users` (+ `/meta`) — staff create/update, role assign, activate/deactivate (`users.manage`)

## Applications
- CRUD draft/submit for **approved** applicants
- Staff queue endpoints (subsequent)

Auth: Bearer Sanctum token. Rate-limit login and register.
