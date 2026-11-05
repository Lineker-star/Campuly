# Campuly changelog

Changes made in the Campuly fork. Upstream Skuul history stays in `../CHANGELOG.md`.

## [Unreleased] — Phase 0 — Foundation (branch `phase-0-foundation`)

### Added
- STEP 0.1: `docs/BASELINE.md` with the first recorded test, PHPStan and build results.
- STEP 0.2: `.github/workflows/campuly-ci.yml`, a CI matrix on MySQL, PostgreSQL and SQLite (install, build, migrate, test, Pint, PHPStan).
- STEP 0.3: `campuly:*` names for every `skuul:*` Artisan command (the old names keep working); `config/campuly.php` with `CAMPULY_DOCS_URL`; `CAMPULY_UPDATE_REPOSITORY`; `tests/Feature/CampulyBrandTest.php`.
- STEP 0.4: `App\Services\Database\PortableSql` (year-month, days-between, greatest, JSON array length for MySQL, PostgreSQL and SQLite); `App\Services\Database\SyncIdSequences` (moves PostgreSQL id sequences past seeded fixed ids); migration adding `min(boolean)`/`max(boolean)` aggregates on PostgreSQL; `.env.testing.pgsql`, `.env.testing.sqlite`, `tests/phpunit-for-driver.php` and `composer test:mysql|pgsql|sqlite`; `DB_JOURNAL_MODE`, `DB_SYNCHRONOUS`, `DB_BUSY_TIMEOUT` for SQLite; `PostgresDumper` and `SqliteDumper`, so `campuly:backup` and the restore rehearsal work on every engine; `App\Traits\StoresDatesWithoutTime` on the 32 models with date-only casts; `tests/Feature/PortableSqlTest.php`, `tests/Feature/DateOnlyCastTest.php`.
- STEP 0.5: `FILESYSTEM_PUBLIC_DISK` / `FILESYSTEM_PRIVATE_DISK` and an `s3-public` disk; packages `league/flysystem-aws-s3-v3`, `symfony/mailgun-mailer`, `symfony/postmark-mailer`, `symfony/http-client`, `resend/resend-php`; Resend mailer; `jobs`, `job_batches`, `cache` and `cache_locks` tables; `tests/Feature/StorageAndDriversTest.php` and `tests/Feature/S3CompatibleStorageTest.php` (MinIO in CI).
- STEP 0.6: `docker/php-fpm.conf` (keeps environment variables in PHP workers), `docker-compose.pgsql.yml`, `.github/scripts/wait-for-health.sh` and a CI job that builds the image and checks `/health` on MySQL and PostgreSQL.

### Changed
- STEP 0.4: all 221 migrations and `migrate:fresh --seed` run on MySQL, PostgreSQL and SQLite. Six migrations fixed in place without changing their MySQL result (see `docs/DATABASE.md`); MySQL-only `DATE_FORMAT`, `DATEDIFF`, `GREATEST` and `JSON_LENGTH` in dashboard trends and the promotions list replaced; seeders use ISO dates; a polymorphic column on `timetable_time_slot_weekday` no longer keeps a foreign key to `subjects` on SQLite (all 404 foreign keys now match across engines); tests and factories pick "the first" record by id instead of relying on row order; SQL-text assertions in tests ignore the quoting style; CI uses the per-driver PHPUnit configs.
- STEP 0.3: the header and sidebar "Documentation" links follow `CAMPULY_DOCS_URL` and are translatable; `campuly:update` installs releases from the Campuly fork (`Lineker-star/Campuly`) instead of upstream Skuul; installer and updater messages, the demo welcome notice, mail defaults, new backup file names (`campuly-<date>`) and the domain-verification record (`_campuly-verification.<host>`) use the Campuly name.
- STEP 0.6: the container starts from environment variables only, waits for the database before `RUN_MIGRATIONS`, runs artisan as `www-data`, can switch the worker and scheduler off (`RUN_WORKER`, `RUN_SCHEDULER`), builds CSS with classes from `app/`, and no longer needs Redis in `docker-compose.prod.yml`; `.dockerignore` keeps local caches, databases, tests and docs out of the image.
- STEP 0.5: every file write uses the configured public or private disk (logos, profile photos, syllabus files, notice attachments, reports, health check); school logo URLs come from the disk that holds them; a notice attachment is read from the disk it was written to; `CACHE_STORE` is read (`CACHE_DRIVER` still works); `.env.example` defaults to the database cache, session and queue with commented settings for S3-compatible storage and every mail provider; Symfony packages updated to versions without security advisories.
- The test bootstrap runs on Windows (no `posix` extension).

### Kept for compatibility
- `skuul:*` command signatures, the `SKUUL_UPDATE_REPOSITORY` fallback, the encrypted-backup header and key labels (older backups must stay readable), the `Yungifez\AprilUI` package namespace.

## [Unreleased] — 2026-11-05 — Rebrand and project documentation

### Added
- Campuly brand assets in `public/img/brand/` (full logo light/dark, mark, app icons 16–1024 px, maskable icons, apple-touch-icon, original artwork) and `public/favicon.ico`.
- `public/manifest.webmanifest` (name, colours, icons, shortcuts) and `<x-partials.brand-head />` (favicons, manifest, theme colour, app meta) in the app, guest and error layouts.
- `<x-brand-logo>` Blade component (mark, inline wordmark CAMPU**LY** with tagline, full lock-up with dark variant).
- Campuly theme in `resources/css/app.css`: brand tokens for light and dark, brand gradient, gradient primary buttons, chart colours, active-menu rail, sign-in aurora, wordmark styles.
- Plus Jakarta Sans Variable, self-hosted via `@fontsource-variable/plus-jakarta-sans` (works offline).
- `lang/fr.json` (first French strings) and brand keys in `lang/en.json`.
- Config keys `app.logo_full`, `app.logo_full_dark`, `app.app_icon`, `app.theme_color`; env-driven `DB_SSLMODE` and `APP_TIMEZONE`.
- `MASTER_PROMPT.md` v2, `NOTICE.md`, new `README.md`, `docs/` set (21 documents incl. index, plus screenshots), `Dockerfile`, `docker/`, `docker-compose.prod.yml`, `.dockerignore`.

### Changed
- Product name defaults to Campuly (`APP_NAME`, `config('app.name')` default); user-visible "Skuul" text in sign-in, installer, errors, setup checklist and installer messages now uses the app name.
- Sidebar, header, sign-in card, installer and error pages show the Campuly logo.
- Dashboard setup progress and attendance bars use the brand gradient.
- `.env.example`: `APP_NAME=Campuly`, `APP_CURRENCY=XAF`, brand logo path, Campuly mail addresses.
- `tests/Feature/InstallationTest.php` expects "Install Campuly".

### Not yet changed (planned in Phase 0)
- Internal identifiers (`skuul:*` commands, `SKUUL_UPDATE_REPOSITORY`, namespaces), updater messages, header link to skuul.org documentation.

---

## Français — [Non publié] — 05/11/2026 — Nouvelle identité et documentation

- Ajout de l'identité Campuly : logos, icônes, favicon, manifeste d'application, couleurs de marque (clair et sombre), police Plus Jakarta Sans hébergée localement.
- Le nom du produit devient Campuly dans l'interface, l'installateur, la connexion et les pages d'erreur.
- Ajout du prompt maître v2, du README, de la notice de licence, de 21 documents de projet et des fichiers Docker.
