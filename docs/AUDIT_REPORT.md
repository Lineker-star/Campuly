# Audit report — Skuul V3 base as received (Campuly fork)

Audited 4–5 November 2026 from `skuul-main.zip` (V3 `main` branch, Laravel 13 / Livewire 4). Everything below was verified by running the code, not only reading it.

## 1. Verified run

- Installed with PHP 8.4.27, MySQL 8.0, Node 22, Redis; `composer install`, `npm ci`, `npm run build`.
- All **215 migrations** ran on MySQL; `WorldSeeder` and the official demo school (`skuul:refresh-demo`, `DEMO_MODE=true`) loaded.
- Logged in as platform admin, teacher and parent; 16 key screens returned HTTP 200 and rendered.
- After rebranding: `php artisan view:cache` compiles all 434 views; login, dashboard, students, finance render in light, dark and mobile.

**Database portability test (5 Nov 2026):**

| Engine | Result | First failure |
|---|---|---|
| MySQL 8.0 | 215 / 215 migrations | — |
| PostgreSQL 16 (Supabase/Neon engine) | stops after 35 | `2022_05_13_133106_create_exam_records_table` — column `exam_Slot_id` (wrong case) |
| SQLite 3 (desktop engine) | stops after 70 | `2026_08_21_120000_add_school_and_primary_to_student_records_table` — `class_groups.school_id` not found |

## 2. Size of the codebase

129 models · 215 migrations · 68 enums · 168 Livewire components · 434 Blade views · 86 controllers · 55 policies · ~150 Action/Service classes · 13 report classes · 17 console commands · 16 scheduled tasks · 193 Pest test files.

## 3. Features present

| Module | Content |
|---|---|
| Organizations and campuses | Multi-organization, multi-campus; org dashboard, members, custom domains, billing groups, calendar templates, shared boarding residences, campus moves, record sharing between schools |
| Setup | Browser installer; 10-step school setup checklist; operating profile; "school language" label presets |
| Academic structure | Years (wizard, publish, roll forward), periods with status, classes, sections, cohorts, instructional model, automatic calendar advance |
| People | Students, teachers, admins, parents, profiles, print, invitations (72 h), imports of students and staff |
| Admissions | Waitlist with offer / accept / decline |
| Enrolment lifecycle | Promotions, graduations, leavers' access ended automatically, status history, transfer packages |
| Teaching | Subjects, course offerings, rosters, teaching assignments, bulk create, roll forward |
| Assessment | Assessment templates, grade categories/items, mark sheets, grading scales, approvals, result snapshots, report cards, transcripts, rankings, exams |
| Attendance | Daily and per-lesson registers with change history |
| Timetables | Weekly timetables, slots, custom items, substitutions/cover, print, personal week |
| Syllabus | Syllabi, topics, coverage tracking and report, lesson notes, shared library, reminders |
| Finance | Fee categories, fees, invoices and batches, payments and allocations, receipts, student accounts, expenses, cash deposits, budgets, financial periods, double-entry ledger; channels cash, bank, cheque, card, mobile money (manual), Stripe |
| Financial reports | Balance sheet, income statement, trial balance, general ledger, cash & bank, budget variance, income by fee type, student balances, aging, expenses, class lists |
| Student care | Incidents and safeguarding; support plans; health records (separate access) |
| Staff operations | Profiles, credentials, availability, leave with approvals |
| Calendar and notices | Events with audiences; notices with attachments, scheduling, email preferences |
| Boarding | Houses, rooms, beds, supervision, roll calls, nights away |
| Library | Titles, copies, lending desk, reservations with automatic holds, rules |
| Facilities, programmes, graduation plans | Bookings; clubs and participation; requirements, exemptions, progress |
| Family portal | Per child: results, attendance, timetable, calendar, notices, invoices, documents, requests, library, boarding, graduation, programmes, syllabi |
| Operations | Health endpoint, encrypted backups, restore rehearsal, backup checks, queue heartbeat, log viewer, updater, demo mode, release-readiness gates, audit events |
| Experience | Light/dark/system theme, collapsible sidebar, Ctrl+K palette, fullscreen, skip link, responsive |

## 4. Gaps against the Campuly vision

| Gap | Evidence | Phase that fixes it |
|---|---|---|
| French not implemented | `lang/` had English only; 167/168 Livewire views without `__()`; enum labels in PHP; ~166 hard-coded `->format()` dates | Phase 1 |
| Language install-wide | `SetApplicationLocale` reads `installations.locale` | Phase 1 |
| PostgreSQL/SQLite unsupported | See portability table | Phase 0 (STEP 0.4) |
| No API | Only `routes/web.php`, `routes/console.php` | Phase 3 |
| No PWA/offline | No service worker (manifest now added by the rebrand) | Phases 4–5 |
| Cameroon payments | Mobile money manual only | Phase 6 |
| Messaging | Email only | Phase 6 |
| LMS layer | No homework, quizzes, e-learning | Phase 8 |
| Visual identity | Upstream sage theme | Done (rebrand), refined in Phase 2 |

## 5. Rebrand already applied (5 Nov 2026)

- Name `Campuly` via `APP_NAME` and `config('app.name')` defaults; user-visible "Skuul" strings in views, installer, errors and messages replaced.
- Assets in `public/img/brand/`; `public/favicon.ico`; `public/manifest.webmanifest`; `<x-partials.brand-head />` in app, guest and error layouts.
- `<x-brand-logo>` component in sidebar, header, sign-in card, installer, error pages.
- Brand theme tokens (light + dark), gradient primary buttons, gradient progress and attendance bars, chart colours, Plus Jakarta Sans (self-hosted).
- First translation keys in `lang/en.json` and new `lang/fr.json` (tagline, sign-in, installer, maintenance).
- `APP_CURRENCY=XAF` in `.env.example`.

Left on purpose for Phase 0: internal names (`skuul:*` commands, `SKUUL_UPDATE_REPOSITORY`, namespaces), upstream updater messages, documentation links to skuul.org.

## 6. Demo accounts (`DEMO_MODE=true` only, password `password`)

| Role | Email |
|---|---|
| Platform admin | megan.carter@staff.riversideusd.example |
| School admin | karen.whitfield@staff.riversideusd.example |
| Accountant | james.patel@staff.riversideusd.example |
| Librarian | emily.rodriguez@staff.riversideusd.example |
| Teacher | angela.torres@staff.riversideusd.example |
| Parent | abigail.vasquez@family.riversideusd.example |
| Student | abigail.sanders@student.riversideusd.example |
