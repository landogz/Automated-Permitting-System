# Paperless

A Laravel application for managing paper documents and their metadata.

## Stack

- Laravel 12 / PHP 8.3 locally (PHP 8.4 recommended by the skill library)
- MySQL 8.4
- Tailwind CSS 4 / Vite
- TypeScript
- PHPUnit for tests

## Engineering standards

This project follows the Laravel Enterprise Skill Library installed in `.claude/skills/`.

**Load `laravel-ai-coding-standards` before any code change.** It analyses the project,
routes changes to the relevant skills, and defines the output contract.

Non-negotiable gates before merge:

- `composer qa` passes when quality tooling is configured
- `laravel-security` review for input, auth, PII, uploads, or secrets
- `laravel-ui-accessibility` and `laravel-responsive-design` review for public UI
- Tests cover new behavior, including important negative paths

## Project conventions

- Keep controllers thin and place business operations in focused action classes.
- Use explicit validation and allow-listed mass assignment; never pass `$request->all()` to models.
- Use Eloquent directly unless a repository provides a real persistence boundary.
- Use escaped Blade output and translation helpers for user-visible strings.
- Use mobile-first layouts, semantic HTML, keyboard-accessible controls, and visible focus states.
- Use MySQL-compatible migrations with deliberate foreign keys and indexes.
- Never commit `.env` or credentials.

## Local setup

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate
npm run dev
```

The local database is configured through `.env` with `DB_CONNECTION=mysql`.

## Rules source

The project-local skills were installed from:
https://github.com/zancy2222/danielzanbaltazarclaudeskills

Read the relevant `SKILL.md` in `.claude/skills/` before making changes. The AI coding
standards skill routes to the other skills and requires honest validation reporting.
