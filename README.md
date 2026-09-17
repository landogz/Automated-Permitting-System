# APICS — Automated Permitting, Inspection, and Compliance System

Phase I for the **Office of the City Building Official**, City of San Fernando, Pampanga (CGSFP).

## Quick start

```bash
composer install
npm install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve --port=8001
```

Open http://127.0.0.1:8001

### Demo accounts (seeded)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@csfp.local | Admin@12345 |
| Applicant | applicant@csfp.local | Applicant@123 |

## Stack

Laravel 12 · Sanctum · Spatie Permission · MySQL · Vite · TypeScript · Tailwind CSS 4

TOR CodeIgniter 4 / JWT is mapped to Laravel 12 / Sanctum (see `docs/00-project-charter.md`).

## Documentation

Full project docs: [`docs/README.md`](docs/README.md)

## API

Base path: `/api/v1`  
Envelope: `{ status, message, data|errors }`

## Ownership

Source code and data belong to CGSFP per TOR.
