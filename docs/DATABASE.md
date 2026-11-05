# Database portability (MySQL · PostgreSQL · SQLite)

Campuly must run unchanged on:

| Engine | Where | Use |
|---|---|---|
| MySQL 8 / MariaDB 10.11+ | Any VPS, cPanel, PlanetScale-compatible, Railway, Aiven | Default for classic hosting |
| PostgreSQL 15+ | **Supabase**, Neon, AWS RDS, Render, Railway, Fly | Cloud-native hosting |
| SQLite 3.40+ | Campus node (desktop), single-school pilot, tests | Offline / embedded |

## Current status (tested 5 Nov 2026)

MySQL 215/215 migrations. PostgreSQL stops after 35 (`2022_05_13_133106_create_exam_records_table`: `exam_Slot_id` case). SQLite stops after 70 (`2026_08_21_120000_add_school_and_primary_to_student_records_table`: references `class_groups.school_id`). Fixing this is **STEP 0.4** of the master prompt.

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

## Supabase specifics

- In the Supabase dashboard open **Connect** and copy the connection details.
- Campuly is a long-running PHP server, so use the **Session pooler** (host `aws-0-<region>.pooler.supabase.com`, port `5432`, user `postgres.<project-ref>`) for both the app and migrations. The direct host `db.<project-ref>.supabase.co` is IPv6-only unless you buy the IPv4 add-on, so many hosts cannot reach it.
- For very high concurrency use the **Transaction pooler** (port `6543`) for the app. It does not support prepared statements, so set `PDO::ATTR_EMULATE_PREPARES => true` in the `pgsql` connection options, and keep running migrations through the session pooler.
- Always `DB_SSLMODE=require`.
- Do not enable Supabase Row Level Security on Campuly tables accessed by Laravel (Laravel connects as the owner role; policies live in Laravel). If you expose tables to Supabase's REST API, enable RLS and deny by default.
- Backups: Supabase daily backups + Campuly's own encrypted backups (`skuul:backup`) to a separate bucket.

## Backups across engines

`app/Contracts/DatabaseDumper.php` has a MySQL dumper. Phase 0 adds `PostgresDumper` (`pg_dump`) and `SqliteDumper` (file copy with `VACUUM INTO`), selected by driver.
