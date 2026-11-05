# Campuly architecture

## 1. Today (Skuul V3 base)

Server-rendered Laravel monolith.

```
Browser ──HTTP──> routes/web.php ──> middleware ──> Controller ──> Blade page
                                                     └─ Livewire components (AJAX)
                                                          └─ Actions (app/Actions/*) ──> Models ──> MySQL
                                                          └─ Policies (app/Policies/*)
Queue (Redis/database): BuildReport, SendNoticeEmails, heartbeat
Scheduler: 16 tasks (notices, calendar advance, library holds, backups, restore rehearsal, reminders…)
```

**Layers**
- `app/Http/Controllers` — thin; return pages.
- `app/Livewire` — forms, tables, boards (168 components).
- `app/Actions/<Domain>` — one class per business operation, run inside DB transactions, record audit events.
- `app/Services/<Domain>` — calculations and read models (dashboards, finance, calendar, grades).
- `app/Policies` — authorization for every model; Spatie roles/permissions + campus roles.
- `app/Enums` — statuses, types, features (68).
- `app/Reports` — financial and academic reports; `app/Jobs/BuildReport` renders them.
- `resources/views` — Blade + April UI components.

**Tenancy and context middleware:** `ResolveDomainContext` (custom domains), `SetActiveSchool`, `SetActiveAcademicPeriod`, `RequireActiveSchool`, `EnsureAcademicYearIsSet`, `EnsureAcademicPeriodIsSet`, `feature:<name>`, `EnsureAccountIsActive`, `EnsurePasswordChange`, `PreventGraduatedStudent`, `SetApplicationLocale`.

## 2. Target (end of Phase 7)

```
                         ┌───────────────────────────────────────────┐
                         │ Cloud: Laravel 13 (Docker or PHP host)     │
                         │  Web UI (Livewire)  ·  /api/v1 + /sync     │
                         │  Actions · Policies · Queue · Scheduler    │
                         │  DB: MySQL | PostgreSQL (Supabase/Neon)    │
                         │  Files: local | S3 (Supabase Storage, R2…) │
                         └───────────────▲───────────────────────────┘
               sync pull/push over HTTPS │ (same protocol for every client)
        ┌───────────────────┬────────────┴───────────┬──────────────────────┐
        │ Browser / PWA     │ Desktop client (Tauri) │ Campus node           │
        │ IndexedDB+outbox  │ PWA in native shell    │ NativePHP + SQLite    │
        │ phones, laptops   │ .msi, printing, tray   │ all modules offline   │
        └───────────────────┴────────────────────────┴──────┬───────────────┘
                                                          LAN browsers
```

**Principles**
1. One source of business rules: the Action classes. Web, API and sync all call them.
2. One authorization layer: policies; API abilities only narrow them.
3. One sync protocol (see `OFFLINE_SYNC.md`) for PWA, desktop client and campus node.
4. Database-agnostic code (see `DATABASE.md`).
5. Everything translatable (see `I18N.md`).

## 3. Integrations (Phase 6+)

| Contract | Drivers |
|---|---|
| `OnlinePaymentGateway` (exists) | Stripe (exists), MoMo/Orange aggregator, direct MTN MoMo, Orange Money |
| `MessageChannel` (new) | log, SMS HTTP, Twilio, Africa's Talking, WhatsApp Cloud API |
| `DocumentRenderer` (exists) | built-in, DomPDF, headless browser |
| `DatabaseDumper` (exists) | MySQL; add PostgreSQL and SQLite in Phase 0 |

## 4. Folder additions planned

```
desktop/client/      Tauri project (Phase 7)
desktop/node/        NativePHP config (Phase 7)
resources/js/offline/ Dexie store, outbox, sync worker (Phase 5)
resources/js/i18n.js  JS translator (Phase 1)
routes/api.php        API v1 (Phase 3)
docker/               nginx, php, supervisor configs
tests/e2e/            Playwright tests
```
