# Database portability (MySQL · PostgreSQL · SQLite)

Campuly must run unchanged on:

| Engine | Where | Use |
|---|---|---|
| MySQL 8 / MariaDB 10.11+ | Any VPS, cPanel, PlanetScale-compatible, Railway, Aiven | Default for classic hosting |
| PostgreSQL 15+ | **Supabase**, Neon, AWS RDS, Render, Railway, Fly | Cloud-native hosting |
| SQLite 3.40+ | Campus node (desktop), single-school pilot, tests | Offline / embedded |

## Current status (STEP 0.4, 5 Nov 2026)

`php artisan migrate:fresh` runs all **221** migrations (215 of the app plus package migrations) on MySQL 8.4, PostgreSQL 17 and SQLite 3.

Before STEP 0.4, PostgreSQL stopped after 35 and SQLite after 70. Six migrations were fixed in place:

| Migration | Problem | Fix |
|---|---|---|
| `2022_05_13_133106_create_exam_records_table` | unique key named `exam_Slot_id` (PostgreSQL is case-sensitive) | `exam_slot_id`; the index name is unchanged |
| `2022_09_02_141457_correct_unique_key_in_subjects_table` | `dropIndex` on a unique key (PostgreSQL keeps it as a constraint) | `dropUnique`; same SQL on MySQL |
| `2026_08_21_120000_add_school_and_primary_to_student_records_table` | `UPDATE … JOIN` (MySQL only) | correlated subquery through the query builder |
| `2026_08_21_310000_rename_semesters_to_academic_periods` | foreign keys dropped by name (SQLite cannot) | dropped by column; same names on MySQL/PostgreSQL |
| `2026_08_22_073653_replace_gradebook_subject_scope_with_course_offering` | SQLite will not drop a column an index still names | drop that index first, on SQLite only (MySQL needs it for a foreign key) |
| `2026_09_28_120640_let_organizations_claim_the_same_unproved_host` | generated column written with MySQL `IF()` | `CASE WHEN`; virtual on SQLite, stored elsewhere |

### Why history was fixed and not replaced by a baseline

STEP 0.4 allowed a fresh "baseline" migration set if fixing history became unsafe. It did not: every fix produces the same schema on MySQL, and an existing MySQL install never re-runs a migration it has already run. A baseline would have meant a second copy of the schema to keep in step and a `campuly:baseline` command to mark old migrations on existing installs, so it was not built.

### Portable expressions in queries

`App\Services\Database\PortableSql` writes the few expressions the engines spell differently: `yearMonth()` (instead of `DATE_FORMAT`), `daysSince()` (instead of `DATEDIFF`), `greatest()` (SQLite has no `GREATEST`) and `jsonArrayLength()` (instead of `JSON_LENGTH`). Use it rather than writing a driver-specific function in `selectRaw()`/`orderByRaw()`.

## Running the tests on each engine

`phpunit.xml` forces Sail's MySQL settings so a test run can never reach a real database. `tests/phpunit-for-driver.php` writes `phpunit.<driver>.xml` (git-ignored) from `.env.testing`, `.env.testing.pgsql` or `.env.testing.sqlite`. A `DB_*` variable set in the shell overrides the file.

```bash
composer test:mysql     # Sail / CI MySQL (host "mysql", user "sail")
composer test:pgsql     # PostgreSQL on 127.0.0.1:5432, user sail / password
composer test:sqlite    # database/testing.sqlite (WAL, no fsync: tests only)

# another port or user, for example:
DB_PORT=5433 DB_USERNAME=campuly DB_PASSWORD= composer test:pgsql
```

CI (`.github/workflows/campuly-ci.yml`) runs the same commands in a MySQL, PostgreSQL and SQLite matrix.

The SQLite connection reads `DB_JOURNAL_MODE`, `DB_SYNCHRONOUS` and `DB_BUSY_TIMEOUT` (unset keeps SQLite's defaults). The campus node should use `DB_JOURNAL_MODE=wal`.

## Rules for every migration and query

1. Use the schema builder only. No raw `ALTER TABLE` unless wrapped in `match (DB::getDriverName())` with a tested branch per driver.
2. Column names: `snake_case`, lowercase only (PostgreSQL folds case and quoting makes it brittle).
3. Enums: store as `string` columns validated by PHP enums — never native `ENUM` types.
4. JSON: `json` columns; query with Laravel JSON helpers (`whereJsonContains`), not `JSON_EXTRACT`.
5. Booleans: `boolean`; never compare with `'1'` strings.
6. Dates: `timestamp`/`date`; timezone handled in PHP (`Africa/Douala` default, per-school setting).
7. Money: integer minor units (`bigInteger`) via `brick/money`; XAF has 0 decimals.
8. No MySQL-only functions: `FIELD()`, `IF()`, `IFNULL()` (use `COALESCE`), `GROUP_CONCAT` (collect in PHP or driver branch), `DATE_FORMAT` (format in PHP), backticks.
9. Case-insensitive search: `whereLike()`/`orWhereLike()` (Laravel 11+) instead of `LIKE` on PostgreSQL.
10. Every migration must have a working `down()` or be explicitly marked irreversible in a comment.
11. Dropping columns on SQLite: use Laravel's built-in table rebuild (supported since Laravel 11); test it.
12. Sync tables (Phase 3): `uuid` (ULID) unique, `updated_at` indexed, `deleted_at`, `version`.
13. Never rely on row order without `orderBy`: PostgreSQL returns rows in storage order, and an updated row moves. Write `Model::query()->oldest('id')->first()`, not `Model::first()`.
14. A model with a `date` / `immutable_date` cast uses `App\Traits\StoresDatesWithoutTime`, so the column holds `Y-m-d` on SQLite too (`tests/Feature/DateOnlyCastTest.php` enforces it).
15. `min()`/`max()` of a boolean (used by `ofMany(['is_primary' => 'max'])`) work on PostgreSQL only because migration `2026_11_05_063731_add_boolean_min_max_aggregates_for_postgresql` defines them. Keep that migration.
16. Seeders that write fixed ids call `App\Services\Database\SyncIdSequences` before the next insert into that table.
17. Match SQL text in tests without quotes (`str_replace(['`', '"'], '', $sql)`): MySQL quotes with backticks, PostgreSQL and SQLite with double quotes.

## Supabase specifics

- In the Supabase dashboard open **Connect** and copy the connection details.
- Campuly is a long-running PHP server, so use the **Session pooler** (host `aws-0-<region>.pooler.supabase.com`, port `5432`, user `postgres.<project-ref>`) for both the app and migrations. The direct host `db.<project-ref>.supabase.co` is IPv6-only unless you buy the IPv4 add-on, so many hosts cannot reach it.
- For very high concurrency use the **Transaction pooler** (port `6543`) for the app. It does not support prepared statements, so set `PDO::ATTR_EMULATE_PREPARES => true` in the `pgsql` connection options, and keep running migrations through the session pooler.
- Always `DB_SSLMODE=require`.
- Do not enable Supabase Row Level Security on Campuly tables accessed by Laravel (Laravel connects as the owner role; policies live in Laravel). If you expose tables to Supabase's REST API, enable RLS and deny by default.
- Backups: Supabase daily backups + Campuly's own encrypted backups (`campuly:backup`) to a separate bucket.

## Backups across engines

`campuly:backup` and `campuly:rehearse-restore` pick the dumper of the connection's driver (`App\Services\Backup\DatabaseDumperRegistry`):

| Engine | Dumper | Needs |
|---|---|---|
| MySQL | `MysqlDumper` | `mysqldump` and `mysql` on the PATH; password in a temporary options file |
| PostgreSQL / Supabase | `PostgresDumper` | `pg_dump` and `psql` (same major version as the server or newer); password in `PGPASSWORD`, never in the arguments; dumps without owner or grants so they restore into any role |
| SQLite | `SqliteDumper` | nothing: written in PHP, SQL text read in one transaction |

Every dump is SQL text, compressed and encrypted the same way, and the restore rehearsal checks it for the core tables whatever the quoting (`users`, `public.users`, `"users"`).
