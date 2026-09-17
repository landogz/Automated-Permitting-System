# Project Charter — APICS Phase I

## Project name
Automated Permitting, Inspection, and Compliance System (APICS) Phase I

## Client
City Government of San Fernando, Pampanga (CGSFP)  
Office of the City Building Official (OCBO)

## Purpose
Digitize permit processing, inspection coordination, certification, and archival in compliance with LGU QMS, P.D. 1096 (National Building Code), RA 11032 (Ease of Doing Business), and JMC 2018-01.

## Success criteria
- Online application/intake with unified QMS forms
- Classifier, evaluation/routing, inspection, basic Order of Payment, compliance notices, records/archiving, notifications
- Admin can CRUD/import/export all master data
- Full audit trail of user events
- API-first (`/api/v1`) for mobile and future integrations
- Source code, docs, manuals, KT, training, 6-month warranty

## Out of scope (Phase I)
- Live CTO payment posting (stub only — Phase II)
- Deep live Citizens Portal/BFP/DPWH (stubs with contracts)

## Ownership
Source code, databases, data, and IP belong to CGSFP. Data sovereignty retained by CGSFP.

## Stack mapping (TOR → implementation)
| TOR | Implementation |
|-----|----------------|
| PHP 8.4+ | PHP 8.3+ / 8.4 recommended |
| CodeIgniter 4 | Laravel 12 |
| JWT | Laravel Sanctum tokens |
| MySQL | MySQL 8.x |
| Webpack/Vite | Vite 7 |
| AWS S3 | Laravel filesystem S3 disk (local fallback) |
| API-capable | `/api/v1` JSON APIs |
| Audit trail | `AuditLogger` + `audit_logs` |
