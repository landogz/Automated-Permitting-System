# System Architecture

## High-level
- Public Applicant Portal (Blade + Axios SPA modules)
- OCBO Staff SPA screens
- Admin Console (master data, import/export, audit)
- Laravel 12 API `/api/v1` + Sanctum
- MySQL 8, queues, S3/local storage
- Integration adapters (Citizens Portal, CTO, BFP, DPWH) — stubs in Phase I

## Layering
Controller (thin) → Service → Repository → Eloquent Model  
FormRequest validation · JsonResource shaping · AuditLogger on mutations

## Security
RBAC (Spatie Permission), Policies, rate limits, CSRF on web, encrypted secrets, no mass-assignment wildcards.
