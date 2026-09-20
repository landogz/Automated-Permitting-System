# APICS — Automated Permitting, Inspection, and Compliance System

Phase I for the Office of the City Building Official (OCBO), City of San Fernando, Pampanga (CGSFP).

## Stack

- Laravel 12 / PHP 8.3 locally (PHP 8.4 recommended by the skill library)
- MySQL 8.4
- Tailwind CSS 4 / Vite / TypeScript
- Laravel Sanctum (API token auth) — TOR CI4/JWT mapped to this stack
- PHPUnit for tests

## Engineering standards

This project follows the **PCSPC Universal Master Project Guidelines** in
`.cursor/rules/pcspc-universal-master-guidelines.mdc` (`alwaysApply: true`), plus the
Laravel Enterprise Skill Library installed in `.claude/skills/`.

**Load `laravel-ai-coding-standards` before any code change.** It analyses the project,
routes changes to the relevant skills, and defines the output contract.

PCSPC covers: PSR-12 layered architecture, modern UI/UX (Tailwind), per-feature
Service/Repository/API modules, reuse-first components, security hardening, Sanctum
`/api/v1` APIs, and mandatory audit logging via `App\Services\Audit\AuditLogger`.

**Always check before implementing / before saying done**
(`.cursor/rules/senior-multi-role-gate.mdc`): act as Senior Frontend (20y),
Senior Graphic Artist, Senior QA Tester, Senior Bug Checker, and Senior
Cybersecurity — design, quality, bugs, and attack surface must be verified.
Always test possible devices (phones, tablets, desktops, Apple/Safari) so
designs do not break across breakpoints.

**Update the project plan on every done**
(`.cursor/rules/update-project-plan-on-done.mdc`): after each completed function/feature,
update plan todos + Progress changelog before claiming done.

**Design template (mandatory for all UI)**
(`.cursor/rules/velzon-master-design-template.mdc`): use the Velzon theme in
[`public/master/`](public/master/) as the visual source of truth for layouts,
auth, dashboards, forms, and tables. Assets via `/master/assets/...`. Do not
invent a parallel Tailwind-only look for app screens.

**Print / PDF (mandatory for official slips)**
(`.cursor/rules/government-print-format.mdc`): all Print / Save PDF outputs use a
modern Philippine LGU / OCBO government letterhead format via
`resources/js/utils/print/` or `resources/views/print/` — never bare HTML dumps.

**HTML email (mandatory for outbound mail)**
(`.cursor/rules/html-email-design.mdc`): all emails use the shared responsive
HTML shell in `resources/views/emails/` (letterhead, CTA button, mobile-safe
tables, plain-text sibling) — never bare Markdown dumps or `htmlString`-only bodies.

Non-negotiable gates before merge:

- `composer qa` passes when quality tooling is configured
- `laravel-security` review for input, auth, PII, uploads, or secrets
- `laravel-ui-accessibility` and `laravel-responsive-design` review for public UI
- Tests cover new behavior, including important negative paths

## Project conventions

- Keep controllers thin; business logic in Services; persistence in Repositories.
- Use explicit validation and allow-listed mass assignment; never pass `$request->all()` to models.
- Use FormRequest + JsonResource; API envelope `{ status, message, data|errors }`.
- Use escaped Blade output and translation helpers for user-visible strings.
- Use mobile-first layouts, semantic HTML, keyboard-accessible controls, and visible focus states.
- Use MySQL-compatible migrations with deliberate foreign keys and indexes.
- Toast for success/error notifications; SweetAlert2 for confirmations only.
- Never commit `.env` or credentials.

## Documentation

See [`docs/`](docs/) for project charter, phases, flows, admin catalog, API, security, UAT, and handover.

## Local setup

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

The local database is configured through `.env` with `DB_CONNECTION=mysql`.

## Rules source

The project-local skills were installed from:
https://github.com/zancy2222/danielzanbaltazarclaudeskills

Read the relevant `SKILL.md` in `.claude/skills/` before making changes. The AI coding
standards skill routes to the other skills and requires honest validation reporting.
