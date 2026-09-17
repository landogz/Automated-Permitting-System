# Security Review — APICS Phase I (2026-09-17)

Review scope: Sanctum auth, registration approval, Spatie RBAC, applications/records/notifications IDOR, FormRequests, audit logging, demo login, print routes.

## Findings & remediations

| Severity | Finding | Status |
|----------|---------|--------|
| HIGH | Unauthenticated logbook print IDOR (`/admin/logbooks/{uuid}/print`) | **Fixed** — route requires `signed` temporary URL (15 min); unsigned → 403 |
| HIGH | Demo passwords rendered in login HTML | **Temporarily restored for local QA** — shown only when `APP_ENV=local` and quick-login enabled; turn off with `APICS_DEMO_QUICK_LOGIN=false` before staging/prod. `POST /api/v1/auth/demo-login` remains available |
| MEDIUM | Login messages enumerated pending/declined/inactive | **Mitigated** — generic credential failure; pending adds non-confirming guidance only |
| MEDIUM | Register unique-email 422 can confirm addresses | **Accepted residual** — throttled `5/min`; duplicate UX retained for applicants |
| MEDIUM | Sanctum tokens never expired | **Fixed** — `SANCTUM_EXPIRATION` default 10080 minutes (7 days) |
| MEDIUM | Admin Blade shells guarded only in JS | **Accepted residual (Phase I)** — all mutating/read APIs use `auth:sanctum` + permission checks; Blade pages are chrome only. Phase II: cookie SPA / server middleware |

## Controls confirmed solid

- Sanctum bearer auth + throttled login/register
- Spatie roles/permissions with negative API tests
- Registration approval gated by `users.manage`
- Application ownership checks / notification user scoping
- FormRequest authorization on writes
- AuditLogger on reviewed mutations (no secrets in meta)
- Privilege-aware sidebar (`data-nav-permissions`)

## Production checklist

- [ ] `APP_DEBUG=false`
- [ ] `APICS_DEMO_QUICK_LOGIN=false` (or unset)
- [ ] HTTPS only; HSTS
- [ ] Strong `APP_KEY`; rotate if leaked
- [ ] Review `SANCTUM_EXPIRATION` for LGU policy
- [ ] Restrict CORS to known origins (no `*` on authenticated APIs)
