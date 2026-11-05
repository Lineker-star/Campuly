# CAMPULY — MASTER BUILD PROMPT (v2)

**Campuly — Learn · Interact · Excel**
Bilingual (English ⇄ French) school management platform · Web + installable PWA + hybrid desktop app · Works offline, syncs with the cloud · Deployable on any platform.

> **How to use this file**
> 1. Open the Campuly source folder in your AI coding agent (Claude Code, Cursor, Codex, Windsurf…).
> 2. At the start of **every** session, paste **Part A (Context)** and **Part B (Rules)**.
> 3. Then paste **one step** from Part C (e.g. "Execute STEP 1.3"). Wait until the agent reports that every **Done when** check passes. Only then paste the next step.
> 4. Never paste a whole phase at once. Small steps = fewer bugs.
> 5. After each step, open the app yourself and follow the **Check it yourself** lines.

---

## PART A — CONTEXT (paste every session)

You are the lead engineer for **Campuly**, a school management platform for Cameroon and Africa (English- and French-speaking schools). Campuly is a fork of the open-source **Skuul V3** (MIT licence; keep `LICENSE` and `NOTICE.md`).

**Brand (already applied in the code; keep it consistent everywhere):**
- Name: **CAMPULY** (written "Campuly" in sentences; wordmark "CAMPU" navy + "LY" violet). Tagline: **Learn · Interact · Excel** / FR: **Apprendre · Interagir · Exceller**.
- Colours: Navy `#0B135A` (ink, wordmark) · Blue `#0044FB` · Indigo `#2E2CFC` (primary) · Violet `#7109FC` (accent) · Purple `#A100F8`. Brand gradient: `linear-gradient(135deg,#0044FB 0%,#2E2CFC 45%,#7109FC 80%,#A100F8 100%)`.
- Font: Plus Jakarta Sans Variable (self-hosted through npm, so it works offline).
- Assets: `public/img/brand/` (mark, full logo light/dark, icons 16–1024, maskable icons, apple-touch-icon), `public/favicon.ico`, `public/manifest.webmanifest`.
- Blade component `<x-brand-logo variant="mark|inline|full" size="sm|md|lg|xl" :tagline="true" />`; head partial `<x-partials.brand-head />`.
- Theme tokens: `resources/css/app.css` (section "CAMPULY BRAND THEME"); light + dark.
- Full rules: `docs/BRAND_GUIDELINES.md` and `docs/DESIGN_SYSTEM.md`.

**Stack (do not change without asking):** PHP 8.4, Laravel 13, Livewire 4, Blade, Alpine, Tailwind CSS 4, April UI kit, Lucide icons, Fortify/Jetstream (2FA, passkeys), Spatie Permission, DomPDF, Pest 5, Vite 6. Databases: MySQL 8 today; must also support **PostgreSQL 15+** (Supabase, Neon, RDS) and **SQLite** (desktop app).

**Codebase facts:** 129 models · 215 migrations · 68 enums · 168 Livewire components · 434 Blade views · 86 controllers · 55 policies · ~150 Action/Service classes · 193 Pest test files. Tenancy: Organization → School (campus) → Academic year → Academic period. Modules can be switched off per campus (`App\Enums\Feature`, `feature:` middleware). Roles: platform-admin, organization-admin, admin, teacher, student, parent, plus custom campus roles (accountant, librarian…). Family portal under `dashboard/portal/...`.

**Known gaps (from the audit in `docs/AUDIT_REPORT.md`):**
- Only English exists in `lang/`; 167 of 168 Livewire views have no `__()`; enum labels and ~166 dates are hard-coded English; locale is install-wide.
- No REST API (`routes/api.php` missing), no service worker, no offline mode.
- On PostgreSQL the migrations stop at 35 of 215 (`2022_05_13_133106_create_exam_records_table`: column `exam_Slot_id` has the wrong case); on SQLite they stop at 70 of 215 (`2026_08_21_120000_add_school_and_primary_to_student_records_table`: `class_groups.school_id` not found).
- Mobile money is recorded by hand only; no MTN MoMo / Orange Money; notices are email-only.

**Documents you must read before your first change:** `AGENTS.md`, `CLAUDE.md`, `.ai/rules/*`, `docs/00-INDEX.md`, then the document named in the step.

---

## PART B — RULES (paste every session)

1. **One step at a time.** Do only the step I give you. When it is finished, stop and report.
2. **Branch per phase:** `phase-N-short-name`. Small commits with clear messages (`feat(i18n): translate students module`). Never commit to `main`.
3. **Read before you write:** open the files you will change and two sibling files; copy their conventions (Actions, Policies, Livewire patterns, enums, tests).
4. **Never break what works.** After each step run, and fix until green:
   ```bash
   php artisan test --compact
   vendor/bin/pint --dirty
   vendor/bin/phpstan analyse --memory-limit=2G
   npm run build
   ```
5. **Tests are mandatory:** every new behaviour gets a Pest test; every API endpoint gets success **and** "forbidden" tests per role.
6. **Every visible word is translatable.** Any string you add or touch goes through `__()`, `trans_choice()`, `@lang` or the JS `t()` helper, with the key in **both** `lang/en.json` and `lang/fr.json` (and PHP group files when relevant). This includes buttons, labels, placeholders, tooltips, aria-labels, toasts, validation, emails, PDFs, enum labels, empty states, error pages, the manifest, the installer and JavaScript.
7. **Brand consistency:** use theme tokens (`bg-primary`, `text-foreground`, `bg-brand-gradient`, `--chart-1..5`), never hard-coded hex values in views. Use `<x-brand-logo>` for the logo. Use `config('app.name')` instead of writing "Campuly" in code.
8. **Database portability:** every migration and query must run on MySQL 8, PostgreSQL 15+ and SQLite. No MySQL-only SQL (`FIELD()`, `IF()`, `GROUP_CONCAT`, backtick quoting, `ON UPDATE CURRENT_TIMESTAMP`, unsigned-specific tricks, raw `ENUM` alters). Use the query builder or `DB::getDriverName()` branches covered by tests.
9. **Never weaken security:** every new route, component and endpoint goes through the existing policies. No secrets in code; everything in `.env` (documented in `docs/ENVIRONMENT_VARIABLES.md`).
10. **Responsive and accessible:** every screen must work at 360, 768, 1280 and 1920 px wide, in light and dark mode, by keyboard, with WCAG 2.2 AA contrast.
11. **Ask me** (2–3 options + your recommendation) only for decisions that are mine: providers with contracts (payments, SMS), hosting accounts, paid services, legal text.
12. **Report format at the end of every step:**
    - What changed (files, in plain words)
    - Commands you ran and their result
    - "Check it yourself" — 3 to 5 clicks I can do in the browser
    - Anything left undone or risky
13. Update the relevant file in `docs/` when your change makes it out of date, and add a line to `docs/CHANGELOG_CAMPULY.md`.

---

## PART C — THE STEPS

Legend: **Do** = exact instructions · **Files** = where · **Done when** = acceptance checks · **Check it yourself** = what I verify.

### PHASE 0 — Foundation and safety net (branch `phase-0-foundation`)

**STEP 0.1 — Run it and record the baseline**
- **Do:** `composer install`, `npm ci`, `npm run build`, copy `.env.example` to `.env`, `php artisan key:generate`, create the MySQL database, `php artisan migrate`, `php artisan db:seed --class=WorldSeeder`, `php artisan storage:link`. Run the full test suite, PHPStan and the build. Write the numbers (tests passed/failed, PHPStan errors, build size, time) in `docs/BASELINE.md`.
- **Done when:** the app opens at `/install`, the suite result is recorded, and any failing test is listed with its cause.
- **Check it yourself:** open the app, see the Campuly logo on the installer.

**STEP 0.2 — Continuous integration**
- **Do:** create `.github/workflows/campuly-ci.yml` running on push and pull request with a matrix `db: [mysql, pgsql, sqlite]`: install, migrate, test, Pint, PHPStan, `npm run build`. Postgres and SQLite jobs may be marked `continue-on-error: true` until STEP 0.4 is done.
- **Done when:** the MySQL job is green on GitHub.

**STEP 0.3 — Lock the brand**
- **Do:** search the whole repo (`app`, `resources`, `config`, `lang`, `routes`, `database/seeders`, `tests`) for "Skuul", "skuul.org", "yungifez". Replace user-visible occurrences with `config('app.name')` or Campuly links. Point the header "Documentation" link to `config('campuly.docs_url')` (new `config/campuly.php`, env `CAMPULY_DOCS_URL`). Add `campuly:*` aliases for every `skuul:*` artisan command (keep the old names working for the scheduler). Set `SKUUL_UPDATE_REPOSITORY` default to the Campuly fork (env `CAMPULY_UPDATE_REPOSITORY`) so the updater never pulls upstream Skuul over the brand. Keep credits in `NOTICE.md` and `LICENSE`.
- **Done when:** `grep -ri "skuul" resources app lang config routes` returns only internal identifiers (namespaces, env names kept for compatibility) and the tests pass.
- **Check it yourself:** log in, open five pages, see no "Skuul" anywhere.

**STEP 0.4 — Make the database portable (MySQL + PostgreSQL + SQLite)**
- **Do:**
  1. Fix `2022_05_13_133106_create_exam_records_table` (column name case) and every later migration that fails on PostgreSQL or SQLite. Run `php artisan migrate:fresh` on each driver until all 215 run.
  2. If fixing history becomes unsafe, create a **portable baseline**: new migrations in `database/migrations/baseline/` that create the final schema with the schema builder only, plus a `campuly:baseline` command that marks the old migrations as run on existing MySQL installs. Explain the choice in `docs/DATABASE.md`.
  3. Replace MySQL-only raw SQL found by `grep -rn "DB::raw\|selectRaw\|whereRaw\|orderByRaw\|havingRaw" app` with portable code.
  4. Make the test suite run on all three drivers (`.env.testing`, `.env.testing.pgsql`, `.env.testing.sqlite`).
- **Done when:** `migrate:fresh --seed` and the full test suite pass on MySQL, PostgreSQL and SQLite; CI matrix all green.

**STEP 0.5 — Storage, cache, queue and mail for any host**
- **Do:** make every file write go through `Storage::disk(config('filesystems.default'))` (uploads, logos, notice attachments, PDFs, backups). Document and test these combinations: local disk; S3-compatible (AWS S3, Cloudflare R2, **Supabase Storage S3 endpoint**, DigitalOcean Spaces, Backblaze B2). Queue: `database` (default for simple hosts) or `redis`. Cache and sessions: `database` or `redis`. Mail: SMTP, Mailgun, Resend, Postmark, SES. Write all variables in `docs/ENVIRONMENT_VARIABLES.md` and `.env.example` with comments.
- **Done when:** the app runs with `QUEUE_CONNECTION=database`, `CACHE_STORE=database`, `SESSION_DRIVER=database` and no Redis; uploads work against MinIO (S3-compatible) in a test.

**STEP 0.6 — Container and health**
- **Do:** finish the provided `Dockerfile` (PHP-FPM + Nginx + Supervisor running web, queue worker and scheduler), `docker-compose.prod.yml`, `.dockerignore`. The container must start with only environment variables, run `php artisan migrate --force` on boot when `RUN_MIGRATIONS=true`, and expose `/health` (already exists).
- **Done when:** `docker compose -f docker-compose.prod.yml up` gives a working app on `http://localhost:8080` with MySQL, and the same image works pointed at PostgreSQL.

### PHASE 1 — Full bilingual English ⇄ French (branch `phase-1-i18n`)

Read `docs/I18N.md` and `docs/GLOSSARY_EN_FR.md` first.

**STEP 1.1 — Locale engine**
- **Do:**
  1. Migration: `users.locale` (string 5, nullable), `schools.default_locale` (string 5, default `fr`).
  2. `config/app.php`: `supported_locales` = `['en' => 'English', 'fr' => 'Français']`; `APP_DEFAULT_LOCALE=fr`.
  3. Rewrite `app/Http/Middleware/SetApplicationLocale.php` with priority: `?lang=` → session `locale` → `auth()->user()->locale` → current school default → installation locale → `Accept-Language` → `config('app.locale')`.
  4. In the same middleware set `Carbon::setLocale()`, `Number::useLocale()` (`fr_CM` / `en_CM`).
  5. `User` implements `HasLocalePreference` so mail and notifications use the recipient's language.
- **Done when:** feature tests prove each priority level; `<html lang>` changes.

**STEP 1.2 — Language switcher**
- **Do:** route `POST /locale` (`LocaleController@update`, validated `en|fr`, saves to user + session + 1-year cookie, redirects back). Blade component `<x-language-switcher />` (segmented control "EN | FR", `aria-pressed`, keyboard focus) placed in: header (desktop), user menu (mobile), sign-in, forgot password, invitation, installer, error pages, family portal. Shortcut `Alt+L`. Command-palette item "Switch language / Changer de langue". Before reload, save unsaved Livewire form data to `sessionStorage` and restore it.
- **Done when:** switching takes < 1 s, keeps the same page, never logs the user out; test covers guest and signed-in.
- **Check it yourself:** on the login page click FR, then sign in: everything you have translated so far is French.

**STEP 1.3 — Formatting helpers**
- **Do:** in `app/helpers.php` add `local_date($date, 'short|medium|long|time|datetime')`, `money($amount, ?$currency)`, `number_local($n, $decimals = 0)`, `percent_local($n)`. French: `5 nov. 2026`, `125 000 FCFA`, `12,5 %`. English: `Nov 5, 2026`, `FCFA 125,000`, `12.5%`. XAF/XOF have 0 decimals. Replace every hard-coded `->format(` in views (`grep -rn "->format('" resources/views`) and every money display.
- **Done when:** unit tests for both locales; no `->format('` left in views except machine formats (ISO for inputs).

**STEP 1.4 — Translate everything, module by module**
- **Do:** one commit per module, in this order:
  1. Layouts, sidebar menu (`app/Livewire/Layouts/Menu.php` labels), header, breadcrumbs, command palette
  2. Auth (login, 2FA, passkeys, password reset, invitations), installer, error pages
  3. Dashboard (cards, trends, setup checklist)
  4. People: students, teachers, admins, parents, invitations, imports
  5. Academic structure: years, periods, classes, sections, cohorts, subjects, course offerings, promotions, graduations
  6. Gradebook, exams, grading scales, report cards, transcripts, rankings
  7. Attendance, timetables, syllabi, lesson notes
  8. Finance: fees, invoices, payments, receipts, expenses, deposits, budgets, financial reports
  9. Family portal (all 13 areas)
  10. Care: incidents, support plans, health records; staff operations
  11. Boarding, library, facilities, programmes, graduation plans, calendar, notices
  12. Settings, organizations, roles and permissions, features page
  13. Emails, notifications, PDFs, console messages shown to users
  For each module: wrap every literal; convert enum `label()`, `description()`, `group()` to `__('enums.<enum>.<case>')` (PHP group file `lang/{en,fr}/enums.php`); add French validation attribute names in `lang/fr/validation.php`; use `trans_choice` for counts.
- **Done when (per module):** the module's screens show zero English words in French mode (verified by the Playwright check from STEP 1.6) and tests pass.

**STEP 1.5 — Bilingual school vocabulary and documents**
- **Do:** the "school language" presets (`EditSchoolLanguage`, `SchoolOperatingProfile`) store each label as `{"en": "...", "fr": "..."}`. Add presets: *Cameroon – Francophone* (6e, 5e, 4e, 3e, 2nde, 1ère, Tle; trimestre; séquence; matière; frais de scolarité; professeur principal) and *Cameroon – Anglophone* (Form 1–5, Lower Sixth, Upper Sixth; term; sequence; subject; school fees; class master/mistress). PDFs (report cards, transcripts, receipts, invoices, timetables) render in the recipient's language; report cards get a "Bilingual (EN/FR)" print option.
- **Done when:** a French-preset school shows "Élèves de 6e" and an English-preset school shows "Form 1 students" in both interface languages correctly.

**STEP 1.6 — Translation quality gates**
- **Do:** (a) Pest test `tests/Feature/I18n/TranslationKeysTest.php`: `en.json` and `fr.json` have identical keys, no empty values; same for every PHP group file. (b) Pest test that scans Blade/Livewire views for text nodes not wrapped in translation (allow-list in `tests/Fixtures/i18n-allowlist.php`). (c) Playwright test `tests/e2e/i18n.spec.ts`: log in as each demo role, visit 40 key URLs in French, fail on any word from `tests/e2e/english-sentinels.json` ("Save", "Cancel", "Students", "Dashboard"…).
- **Done when:** all three gates pass in CI.
- **Check it yourself:** switch to French, click every menu item as admin, teacher, accountant, parent, student. Report any English word you find.

### PHASE 2 — Campuly design system and full UI/UX upgrade (branch `phase-2-ui`)

Read `docs/BRAND_GUIDELINES.md` and `docs/DESIGN_SYSTEM.md` first.

**STEP 2.1 — Component gallery**
- **Do:** route `/dev/ui` (local and staging only) showing every component in light and dark, EN and FR: buttons (primary gradient, secondary, outline, ghost, destructive, icon, loading), inputs, selects, combobox, date picker, checkbox, radio, switch, file upload with preview, tables, cards, stat tiles, badges/status pills, tabs, modal, drawer, toast, empty state, skeletons, pagination, charts, avatar, breadcrumb, stepper.
- **Done when:** every component uses tokens only and passes axe-core with zero serious issues.

**STEP 2.2 — App shell**
- **Do:**
  - Sidebar: brand block on top (`<x-brand-logo :tagline="true">`), sections with icons, live counters (pending portal requests, overdue invoices, waitlist), collapse to icons, remembered state, active item with gradient rail (already styled).
  - Header: school switcher, period switcher, global search, language switcher, theme toggle, notifications bell with unread count, sync indicator (placeholder until Phase 5), profile menu.
  - Mobile (< 768 px): bottom navigation with 4 role-specific tabs + "More" sheet; sticky page actions; header collapses on scroll.
  - Page header: title, breadcrumbs, primary action, secondary actions in a menu; consistent everywhere.
  - Page transitions with `wire:navigate` and a top progress bar in brand gradient.
- **Done when:** shell reviewed at 360/768/1280/1920 px, light/dark, EN/FR; keyboard-only navigation works.

**STEP 2.3 — Global search and command palette**
- **Do:** Ctrl/Cmd+K palette searching students (name, admission no.), staff, parents, invoices (number), classes, pages and actions ("Record a payment", "Take attendance", "New notice"). Server-side search endpoint scoped by policies, debounce 200 ms, recent items, keyboard navigation, translated.
- **Done when:** finds a student by partial name in < 300 ms on 10 000 students (seeded).

**STEP 2.4 — Role dashboards**
- **Do:** replace the single dashboard with role views (`resources/views/dashboards/{admin,teacher,accountant,librarian,parent,student}.blade.php`):
  - Admin/Director: enrolment by class, attendance today vs 4-week trend, fees collected vs expected, overdue invoices, events, setup health, recent activity.
  - Teacher: today's timetable, registers to take, mark sheets due, syllabus coverage, my classes.
  - Accountant: collections today/week/month by channel (cash, MTN MoMo, Orange Money, bank), top debtors, aging buckets, cash to deposit.
  - Librarian: loans due, overdue, holds ready.
  - Parent/Student: per child — attendance %, latest results, balance with "Pay now", today's timetable, notices.
  Use one chart library (Apache ECharts recommended) wrapped in `<x-chart>` reading `--chart-1..5`, the theme and the locale.
- **Done when:** each role sees only its dashboard; charts readable in dark mode; numbers covered by tests.

**STEP 2.5 — Lists and forms everywhere**
- **Do:** one reusable data-table pattern: search, filters drawer, column chooser, sort, bulk select + bulk actions, export CSV/Excel/PDF, saved views, pagination; on mobile rows become cards. Forms: two-column on desktop, one on mobile, inline validation, autosave drafts for long forms (student registration, mark sheets), sticky Save bar, unsaved-changes warning. Empty states with illustration + action. Confirmation dialogs naming the object. Toasts with Undo for reversible actions. Skeletons while loading.
- **Done when:** students, teachers, invoices, payments and notices lists use the new pattern; Lighthouse Accessibility ≥ 95 on them.

**STEP 2.6 — Polish pass**
- **Do:** motion (150–250 ms, respects reduced motion), focus rings in brand indigo, print styles for every printable page with the Campuly or school logo, favicon badge for unread notifications, branded PDF templates (header gradient line, logo, footer "Generated by Campuly").
- **Done when:** screenshots of 20 key screens (light/dark, EN/FR, desktop/mobile) saved in `docs/screenshots/`.

### PHASE 3 — REST API and sync protocol (branch `phase-3-api`)

Read `docs/API.md` and `docs/OFFLINE_SYNC.md` first.

**STEP 3.1 — API foundation:** create `routes/api.php` (prefix `/api/v1`), Sanctum personal access tokens named per device, abilities per role, rate limit 120/min per token, `Accept-Language` respected, errors as RFC 9457 `application/problem+json` with translated `title`/`detail`, API Resources with envelope `{ data, meta, links }`. **Done when:** `/api/v1/me` works with a token and returns 401/403 correctly.

**STEP 3.2 — Resource endpoints** (controllers stay thin, reuse Actions + Policies): auth (login, logout, me, tokens), schools, academic years/periods, classes, sections, subjects, students, staff, parents, timetable, attendance registers (read/write), gradebook mark sheets (read/write), fee invoices, payments (read/record), notices, calendar, library loans, portal endpoints (per child), reports (list/download). **Done when:** each endpoint has success + forbidden tests per role.

**STEP 3.3 — Sync-ready schema:** for every syncable table add `uuid` (ULID, unique, backfilled), index on `updated_at`, `deleted_at` (soft delete = tombstone), `version` (unsigned int, +1 on every update via a model trait `Syncable`). Clients may create records with their own ULID. **Done when:** migrations run on MySQL, PostgreSQL, SQLite; trait tested.

**STEP 3.4 — Sync endpoints:** `GET /api/v1/sync/pull?since=<cursor>&scopes=attendance,marks,…` (changes + tombstones the user may see, 500 per page, opaque cursor); `POST /api/v1/sync/push` (batch of `{client_mutation_id, entity, uuid, op, base_version, payload, device_id, occurred_at}`, idempotent through a `sync_mutations` table, each mutation executed through the normal Action class; response per item `applied | conflict | rejected` + server copy). **Done when:** tests prove idempotency, conflicts, scoping and tombstones.

**STEP 3.5 — API documentation:** generate OpenAPI 3.1 (Scramble) at `/docs/api` (admins only in production), examples in EN and FR. **Done when:** the spec validates and lists every endpoint.

### PHASE 4 — Installable PWA (branch `phase-4-pwa`)

Read `docs/PWA_DESKTOP.md` first. The manifest and icons already exist in `public/`.

**STEP 4.1 — Localised manifest:** serve `/manifest.webmanifest` from a controller so `name`, `short_name`, `description`, shortcut names and `lang` follow the user's language; keep icons from `public/img/brand/`. **Done when:** Chrome DevTools → Application → Manifest shows no errors in both languages.

**STEP 4.2 — Service worker:** add `vite-plugin-pwa` (Workbox, `injectManifest`). Precache the app shell (built CSS/JS, fonts, brand icons, offline page). Runtime: static assets cache-first; `/api/v1` GETs network-first with 3 s timeout then cache; HTML pages network-first with offline fallback; cache names include the user id and are cleared on logout. Bilingual offline page `resources/views/offline.blade.php` with the Campuly logo. Update toast: "A new version of Campuly is available — Reload".
**Done when:** Lighthouse PWA checks pass; reload in airplane mode shows the offline page.

**STEP 4.3 — Install experience:** "Install Campuly" button in the user menu and on the dashboard (captures `beforeinstallprompt`), iOS instruction sheet ("Share → Add to Home Screen"), installed-mode detection to hide the button. **Done when:** installs on Windows (Edge/Chrome) and Android with the Campuly icon and opens in its own window.

**STEP 4.4 — Push notifications:** Web Push (VAPID keys in `.env`), opt-in per user and per type (notices, absences, payments, results), messages in the user's language. **Done when:** a test notice reaches an installed PWA.

### PHASE 5 — Offline-first data and sync engine (branch `phase-5-offline`)

**STEP 5.1 — Local store:** `resources/js/offline/db.js` with Dexie (IndexedDB): tables mirror sync scopes the user may see (school, period, classes, sections, students, timetable, registers, mark sheets, invoices, payments, notices, calendar, library). Encrypt sensitive payloads with WebCrypto AES-GCM; key derived at login, kept in memory; wipe on logout or after 30 days without sync.

**STEP 5.2 — Outbox and sync worker:** every offline write becomes a mutation in an outbox table; worker runs on start, on `online`, every 2 min, and via Background Sync; exponential back-off; header indicator (green online · amber syncing · grey offline · red conflicts) and a "Pending changes (n)" drawer.

**STEP 5.3 — Offline screens** (Alpine views over IndexedDB; online they keep using Livewire): attendance register (daily + per lesson), mark entry, student/staff/parent directory and profiles (read), timetable (read), notices and calendar (read), record payment + provisional receipt (`<DEVICE>-<YEAR>-<#####>`, replaced by the official number after sync), library check-out/in, boarding roll call, family portal (results, attendance, timetable, balance). Any other screen shows a translated "Available online only" panel.

**STEP 5.4 — Conflict rules** (implement exactly what `docs/OFFLINE_SYNC.md` says): attendance last-writer-wins per (student, date, lesson) with full history; marks field-level with a side-by-side resolution dialog; payments append-only with duplicate detection (student, amount, channel, reference, date); structure data read-only offline.

**Done when (whole phase):** Playwright scenario: go offline → teacher takes 3 registers and enters 20 marks → accountant records 5 payments and prints receipts → go online → everything appears on a second browser within 2 minutes → a deliberate mark conflict shows the dialog.

### PHASE 6 — Cameroon payments and communication (branch `phase-6-payments-messaging`)

**STEP 6.1 — Mobile money gateway:** implement `App\Contracts\OnlinePaymentGateway` (already used by Stripe) for an aggregator covering **MTN MoMo and Orange Money** (ask me: CamPay, NotchPay, Flutterwave, or direct MTN MoMo Open API `mtncameroon` + Orange Money Web Pay). Parent portal "Pay now" → request-to-pay → webhook (signature verified) + polling fallback → payment recorded through existing finance Actions → ledger → bilingual receipt by SMS/email. Sandbox credentials in `.env`.

**STEP 6.2 — SMS and WhatsApp:** `App\Contracts\MessageChannel` with drivers (log, generic HTTP SMS, Twilio, Africa's Talking, WhatsApp Cloud API — ask me which). Use for notices, absence alerts, fee reminders, results release. Templates in EN/FR; per-user channel preferences; delivery log.

**STEP 6.3 — In-app messaging:** conversations between parents and teachers/administration, attachments, read receipts, admin moderation, policies, notifications, mobile-friendly chat UI.

**Done when:** sandbox payment end-to-end; SMS via fake driver in tests; messaging scoped correctly.

### PHASE 7 — Hybrid desktop app (branch `phase-7-desktop`)

**STEP 7.1 — Desktop client (lightweight):** Tauri 2 project in `desktop/client/` wrapping the PWA build, with first-run screen "Server address" (default `https://app.campuly.com`), Windows `.msi` + `.exe`, macOS `.dmg`, Linux `.AppImage`, auto-update, tray icon, native notifications, silent receipt printing. Icons from `public/img/brand/`.

**STEP 7.2 — Campus node (full offline):** NativePHP Desktop (v2.x; confirm Laravel 13 support for the version you install) in `desktop/node/` running the whole Laravel app on a local SQLite database (requires STEP 0.4). First-run wizard (bilingual): sign in to the cloud → choose school → full download via `/api/v1/sync/pull` → ready. Background sync job every 2 minutes using the Phase 3 protocol. Optional LAN mode: serve on `http://<computer-name>:8000` for other computers when the internet is down. Encrypted local backups.

**STEP 7.3 — Signing and release:** code-sign Windows builds (certificate or Azure Trusted Signing), notarise macOS, GitHub Actions workflow building installers on tag `v*`.

**Done when:** fresh Windows PC → install → sign in once → unplug network → full school day (attendance, marks, payments, notices, printing) → reconnect → everything on the cloud with no loss.

### PHASE 8 — New features (branch `phase-8-features`, one sub-branch per feature)

Each feature: bilingual, responsive, policies, tests, docs, available offline where marked (O).

1. **Cameroon report cards (O for viewing):** francophone (séquences 1–6, trimestres, coefficients, moyennes /20, rang, mentions, appréciations, conseil de classe, bulletin officiel) and anglophone (Form 1–5, Sixth Form, GCE O/A-Level grades) templates.
2. **Homework and assignments (O):** post, submit (files/photos, camera on mobile), grade into gradebook, due-date reminders.
3. **Online quizzes / CBT:** question bank, randomised timed tests, auto-marking, anti-cheat basics, results to gradebook.
4. **E-learning library:** lessons, videos (the play icon in the logo), PDFs per subject/class; offline download (O).
5. **ID cards and QR:** printable student/staff cards; QR gate attendance (O) and library loans (O).
6. **Public admissions portal:** online application, document upload, entrance exam scheduling, admission letters, payment of application fee.
7. **HR and payroll lite:** contracts, salary scales, monthly payslips, CNPS/IRPP deductions configurable.
8. **Transport:** routes, stops, vehicles, pupils per route, transport fees.
9. **Analytics and early warning:** risk list (attendance + grades + balance), class comparisons, exports.
10. **AI assistant (feature flag):** draft report-card comments in EN/FR from marks, summarise a term, in-app "How do I…?" help. Ask me which AI provider.
11. **Multi-school SaaS mode:** each school on its own subdomain (`school.campuly.com`) or custom domain (already partly supported), plan limits, billing for Campuly subscriptions.
12. **Audit and data rights:** activity log viewer, per-student data export, retention settings.

### PHASE 9 — Hardening, performance, launch (branch `phase-9-release`)

**STEP 9.1 — Security:** OWASP Top 10 review, CSP and security headers, rate limits on auth and API, 2FA required for admins and accountants, encrypted backups (`BACKUP_KEY`), dependency audit (`composer audit`, `npm audit`). Write findings in `docs/SECURITY_GUIDE.md`.
**STEP 9.2 — Performance:** remove N+1 queries (the query detector is installed), indexes for search and sync cursors, cache dashboard aggregates, lazy-load heavy Livewire parts, image optimisation. Target: < 300 ms server time on main pages with 10 000 students across 5 campuses (seed with a load-test seeder).
**STEP 9.3 — Documentation:** bilingual user guides (admin, teacher, accountant, parent/student) in `docs/user-guides/` and in-app help links; update `docs/DEPLOYMENT.md` with what you actually deployed.
**STEP 9.4 — Release:** follow `docs/RELEASE_CHECKLIST.md`: backup/restore rehearsal, `php artisan skuul:release-readiness --check-backups` green (alias it as `campuly:release-readiness` in STEP 0.3), tag `v1.0.0`, changelog EN/FR, installers published.

---

## PART D — DEPLOYMENT TARGETS (the app must deploy on all of these)

Campuly is a PHP (Laravel) application. It needs **a host that runs PHP 8.4 (or a Docker container)** plus **a database**. Supabase, Neon and similar services provide the database and file storage; the PHP part runs on any of the hosts below. Full steps per platform are in `docs/DEPLOYMENT.md`.

| Need | Options that must work |
|---|---|
| App runtime | Docker on any VPS (Hostinger, Contabo, DigitalOcean, Hetzner, OVH), Laravel Cloud, Laravel Forge / Ploi, Render, Railway, Fly.io, Koyeb, Google Cloud Run, Sevalla, cPanel shared hosting (with PHP 8.4, cron, no Docker) |
| Database | MySQL 8 / MariaDB 10.11+ (any host), **PostgreSQL 15+ (Supabase, Neon, RDS, Railway, Render)**, SQLite (desktop / single-school pilot) |
| Files | Local disk, or S3-compatible: **Supabase Storage**, AWS S3, Cloudflare R2, DigitalOcean Spaces, Backblaze B2 |
| Queue / cache / sessions | `database` (no extra service) or Redis (Upstash, Railway, self-hosted) |
| Mail | SMTP, Resend, Mailgun, Postmark, Amazon SES |
| Not suitable for the app server | Vercel / Netlify static hosting (they can host the marketing site or docs only) |

**Supabase in one line:** use Supabase for **PostgreSQL** (`DB_CONNECTION=pgsql`, Session pooler host `aws-0-<region>.pooler.supabase.com` port 5432, `DB_SSLMODE=require`; Transaction pooler 6543 only with emulated prepares) and **Storage** (S3 endpoint `https://<project-ref>.storage.supabase.co/storage/v1/s3`, path-style), while the Laravel app runs on Render/Railway/Fly/VPS/Laravel Cloud. Keep Laravel's own authentication; do not mix in Supabase Auth.

---

## PART E — FIRST MESSAGE TO SEND YOUR AGENT

> "Read PART A and PART B of MASTER_PROMPT.md, then read AGENTS.md, CLAUDE.md, .ai/rules, docs/00-INDEX.md and docs/AUDIT_REPORT.md. Summarise in 15 lines what Campuly is and how you will work. Then execute **STEP 0.1** only, and stop with the report format from Rule 12."
