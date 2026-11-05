# Testing and QA

## 1. Automated layers

| Layer | Tool | Where | Runs |
|---|---|---|---|
| Unit + feature | Pest 5 | `tests/Unit`, `tests/Feature` (193 files today) | Every push, MySQL + PostgreSQL + SQLite matrix |
| Static analysis | PHPStan / Larastan | `phpstan.neon` | Every push |
| Style | Pint | `pint.json` | Every push |
| Translation gates | Pest | `tests/Feature/I18n/*` | Every push (Phase 1) |
| API | Pest HTTP tests | `tests/Feature/Api/*` | Every push (Phase 3) |
| End-to-end | Playwright | `tests/e2e/*` | Nightly + before release |
| Accessibility | axe-core in Playwright, Lighthouse CI | key pages | Nightly |
| Performance | k6 or Laravel load seeder + timing | staging | Before release |

Tests use a dedicated database; never point them at real data. Do not run tests in parallel against the shared MySQL test database (upstream lock rule).

## 2. Must-have test cases

- Every policy: allowed and forbidden per role; cross-school; cross-family.
- Every Action: happy path, validation failure, transaction rollback.
- Money: XAF zero-decimal rounding, allocations, receipts, ledger balance after each operation.
- Locale: each priority level of locale resolution; dates/money in EN and FR.
- Sync: pull scoping, tombstones, push idempotency, each conflict rule.
- PWA: offline page, install manifest valid, cache cleared on logout.

## 3. Manual test scripts (run in EN and in FR, desktop and phone)

**Admin (10 min)**: sign in → switch language → create class "6e A" → add a student with a parent → invite a teacher → create a fee and invoice the class → publish a notice → open dashboard and check numbers changed.

**Teacher (8 min)**: sign in on phone → today's timetable → take attendance for one class → enter marks for one assessment → check syllabus coverage → send a class notice.

**Accountant (8 min)**: find a student by name in search → record a cash payment → print receipt → record a mobile-money payment (Phase 6) → view student balances report → export to Excel.

**Parent (5 min)**: sign in on phone → choose child → results, attendance, invoices → Pay now (sandbox, Phase 6) → read notice → switch to English/French.

**Offline (Phase 5, 15 min)**: install PWA → sign in → turn on airplane mode → take attendance, enter marks, record payment → turn off airplane mode → confirm sync indicator turns green and data appears on another device.

**Desktop (Phase 7, 30 min)**: fresh Windows PC → install → full offline school day → reconnect → compare with cloud.

## 4. Bug report template

Title · Role · Language · Device/browser · Online/offline · Steps · Expected · Actual · Screenshot · Severity (blocker/major/minor/cosmetic).

## 5. Release gate

All automated layers green, all manual scripts passed in both languages, no open blocker/major bugs, Lighthouse a11y ≥ 95 and PWA pass on key pages.
