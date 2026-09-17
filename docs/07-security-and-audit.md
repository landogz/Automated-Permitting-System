# Security and Audit

## Controls
- Sanctum tokens; password rules (min 8, mixed, numbers, symbols)
- Spatie roles/permissions + Policies
- FormRequest on all writes
- Escaped Blade `{{ }}`
- Upload MIME/size allow-lists
- IDOR: ownership/role checks
- No `$guarded = []`
- APP_DEBUG=false in production

## Audit
Every successful mutation logs via `App\Services\Audit\AuditLogger` with dotted event names (e.g. `department.created`). Never log passwords/tokens/OTP. Admin module: `/admin/audit` (UI) + API.
