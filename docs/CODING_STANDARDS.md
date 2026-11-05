# Coding standards and workflow

Also follow the upstream rules in `../AGENTS.md`, `../CLAUDE.md` and `../.ai/rules/` (Laravel Boost guidelines).

## Git

- `main` = deployable. Work on `phase-N-name` or `feat/<short-name>` branches.
- Conventional commits: `feat(finance): add mobile money gateway`, `fix(i18n): translate invoice PDF`, `docs:`, `test:`, `refactor:`, `chore:`.
- Pull request template: what/why, screenshots (light/dark, EN/FR, mobile), tests added, docs updated, checklist below.
- Tag releases `vX.Y.Z` (semantic versioning).

## PHP / Laravel

- PHP 8.4, strict types where files already use them; typed properties and return types.
- Business logic in `app/Actions/<Domain>/<Verb><Noun>.php`, one public `handle()`/`__invoke()`, wrapped in `DB::transaction()` when writing several tables, records audit events like its siblings.
- Controllers and Livewire components stay thin: validate, authorize, call an Action, return.
- Read models and calculations in `app/Services`.
- Enums for statuses/types; labels translated (`I18N.md`).
- Queries portable across MySQL/PostgreSQL/SQLite (`DATABASE.md`).
- No `env()` outside `config/`.
- Run Pint before committing.

## Front-end

- Blade + Livewire + Alpine; April UI components first; Tailwind utilities with **tokens only** (`DESIGN_SYSTEM.md`).
- `<x-brand-logo>` for the logo; never hard-code "Campuly" — use `config('app.name')`.
- Every string translated; every interactive element keyboard-accessible and labelled.
- Mobile-first: check 360 px before 1280 px.
- JavaScript in `resources/js/` as ES modules; no inline scripts except tiny Alpine `x-data`.

## Review checklist

- [ ] Policy check present and tested
- [ ] EN + FR strings added; no literals
- [ ] Works on MySQL, PostgreSQL, SQLite (CI)
- [ ] Responsive 360/768/1280/1920; light/dark
- [ ] Tests added; CI green
- [ ] Docs + `CHANGELOG_CAMPULY.md` updated
- [ ] No secrets, no debug output, no N+1 queries
