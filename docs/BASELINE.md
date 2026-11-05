# Baseline (STEP 0.1)

The state of the code as received, recorded before any Phase 0 change. Measured on 5 November 2026 at commit `0ce5e24` (plus the Windows test-bootstrap fix `d5b6cbb`, which changes nothing on Linux).

## Machine

| Item | Value |
|---|---|
| OS | Windows 11 Pro (no Docker / Sail) |
| PHP | 8.4.27 CLI, OPcache on for CLI; `gd`, `intl`, `exif`, `sodium`, `pdo_sqlite`, `pdo_pgsql` enabled |
| Database | MySQL 8.4.3 (Laragon), `127.0.0.1:3306` |
| Node / npm | 22.23 / npm 11 |
| Composer | 2.11.1 |

## Setup

| Command | Result | Time |
|---|---|---|
| `composer install` | OK | 443 s |
| `npm ci` | OK (esbuild post-install script blocked by npm, build still works) | 11 s |
| `npm run build` | OK, `public/build` = **584 KB** (JS 391 KB / 125 KB gzip, CSS 121 KB / 21 KB gzip, 4 font files) | 19 s |
| `php artisan key:generate` | OK | — |
| `php artisan migrate` | **221 / 221** migrations (215 app + package) | 177 s |
| `php artisan db:seed --class=WorldSeeder` | 250 countries, 5 000 states (cities are switched off in `config/world.php`) | 10 s |
| `php artisan storage:link` | OK | — |
| `/install` | HTTP 200, "Install Campuly" with the Campuly logo | — |

## Quality gates

| Gate | Result | Time |
|---|---|---|
| PHPStan (level 5, `phpstan-baseline.neon` with 14 entries) | **0 errors** | 175 s |
| Test suite (Pest, MySQL) | **2 576 passed, 26 failed, 3 skipped** (9 594 assertions, 2 605 tests) | 7 980 s |

The suite is slow on Windows: the 36 `Demo*` tests each seed the whole demo school. A first run without CLI OPcache was stopped after 2 h 35 min at test 955.

## Failing tests and their cause

| Tests | Count | Cause | Status |
|---|---|---|---|
| `BackupTest` (14 tests) | 14 | `mysqldump` is not on the PATH of this Windows machine | Environment only: all pass with Laragon's MySQL `bin` on the PATH |
| `UpdateApplicationTest` (7 tests) | 7 | `Process::fake()` patterns were written as `*'describe'*`; Windows quotes arguments with `"`, so no fake matched. One test also expected the old "Skuul" name | Fixed: patterns no longer depend on the quote character; messages use `config('app.name')` (STEP 0.3) |
| `SchoolTest::the application logo has a deployable default` | 1 | The rebrand changed the default logo to `img/brand/campuly-mark.png`; the test still expected the Skuul file | Fixed: test expects the Campuly file and that it exists |
| `ThemeToggleTest::the application favicon has a deployable default` | 1 | The rebrand changed the default favicon to `favicon.ico` | Fixed the same way |
| `LinkButtonContrastTest::the application repaints link buttons with a readable colour` | 1 | The rebrand paints links with `--brand-link` instead of `--primary-foreground` | Fixed: test expects `--brand-link`; contrast checked at 8.9–9.5 : 1 (light) and 6.9–7.2 : 1 (dark) |
| `ReleaseConfigurationTest::readme retains the breaking upgrade warning…` | 1 | The new README dropped the Skuul V2 upgrade warning | Fixed: "Upgrading an existing Skuul install" section restored |
| `ReleaseConfigurationTest::cached configuration is refused before the test application boots` | 1 | Windows only: Laravel treats `APP_CONFIG_CACHE` as absolute only when it starts with `/` or `\`, so `C:\…\tmp` is read as a relative path | Environment only; passes on Linux / CI |

After these fixes the affected files pass on this machine except the last row.

## Other databases (before STEP 0.4)

| Engine | `migrate:fresh` |
|---|---|
| PostgreSQL 17 | stops at 35 / 221 (`exam_Slot_id` case) |
| SQLite 3 | stops at 70 / 221 (`UPDATE … JOIN` on `class_groups.school_id`) |
