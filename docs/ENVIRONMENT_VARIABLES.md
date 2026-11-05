# Environment variables

Set in `.env` (local) or the platform's environment settings (production). Never commit real values.

## Application

| Variable | Example | Notes |
|---|---|---|
| `APP_NAME` | `Campuly` | Shown everywhere through `config('app.name')` |
| `APP_ENV` | `production` | `local`, `staging`, `production` |
| `APP_KEY` | `base64:…` | `php artisan key:generate --show`; keep the same key forever (encrypted data depends on it) |
| `APP_DEBUG` | `false` | Never `true` in production |
| `APP_URL` | `https://app.campuly.com` | Exact public URL, used in links, emails, manifest |
| `APP_LOCALE` | `en` | Fallback language; Phase 1 adds `APP_DEFAULT_LOCALE=fr` |
| `APP_TIMEZONE` | `UTC` | Keep UTC; each school sets its own time zone |
| `APP_CURRENCY` | `XAF` | Default currency (ISO 4217) |
| `APP_THEME_COLOR` | `#2E2CFC` | Browser/PWA theme colour |
| `LOGO_PATH`, `LOGO_FULL_PATH`, `LOGO_FULL_DARK_PATH`, `APP_ICON_PATH`, `FAVICON_PATH` | `img/brand/…` | Override brand assets (white-label) |
| `DEMO_MODE` | `false` | `true` only on a disposable demo: resets hourly, blocks deletes |

## Database

| Variable | MySQL | PostgreSQL / Supabase | SQLite |
|---|---|---|---|
| `DB_CONNECTION` | `mysql` | `pgsql` | `sqlite` |
| `DB_HOST` | `127.0.0.1` | `aws-0-<region>.pooler.supabase.com` | — |
| `DB_PORT` | `3306` | `5432` (session pooler) | — |
| `DB_DATABASE` | `campuly` | `postgres` | absolute path to `.sqlite` file |
| `DB_USERNAME` / `DB_PASSWORD` | | `postgres.<project-ref>` / password | — |
| `DB_SSLMODE` | — | `require` | — |
| `DB_JOURNAL_MODE` | — | — | `wal` (unset: SQLite default) |
| `DB_SYNCHRONOUS` | — | — | unset; tests use `off` |
| `DB_BUSY_TIMEOUT` | — | — | milliseconds to wait for a lock, e.g. `10000` |
| `DATABASE_URL` | optional single URL overriding the above | | |

## Files

Every file the app writes goes to one of two disks (`config/filesystems.php`):

| Variable | Default | Holds |
|---|---|---|
| `FILESYSTEM_PUBLIC_DISK` | `public` | Files a browser loads directly: school logos, profile photos. `s3-public` for object storage. |
| `FILESYSTEM_PRIVATE_DISK` | `local` | Files only an authorised person downloads: notice attachments, built reports. `s3` for object storage. A notice remembers the disk its attachment went to, so changing this later keeps old attachments downloadable. |
| `FILESYSTEM_DISK` | `local` | Laravel's default disk (Livewire's temporary uploads). |

Local disks need `php artisan storage:link`. Backups use their own disk (`BACKUP_DISK`, below).

S3-compatible storage:

| Variable | Example | Notes |
|---|---|---|
| `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY` | | Server-side only; also used by SES mail |
| `AWS_DEFAULT_REGION` | `eu-west-1`; `auto` for R2 | |
| `AWS_BUCKET` | `campuly-private` | Private bucket |
| `AWS_ENDPOINT` | see table below | Omit for AWS S3 |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` | Supabase, MinIO; `false` for AWS, R2, Spaces, B2 |
| `AWS_URL` | | Public base URL of the private bucket (rarely needed) |
| `AWS_PUBLIC_BUCKET` | `campuly-public` | Unset: public files share `AWS_BUCKET` |
| `AWS_PUBLIC_URL` | `https://cdn.example.com` | URL that serves the public bucket |
| `AWS_PUBLIC_VISIBILITY` | `public` | `private` on services without object ACLs (R2, Supabase): make the bucket itself public instead |

| Service | `AWS_ENDPOINT` | Region | Path style |
|---|---|---|---|
| AWS S3 | (empty) | bucket region | `false` |
| Cloudflare R2 | `https://<account-id>.r2.cloudflarestorage.com` | `auto` | `false` |
| Supabase Storage | `https://<project-ref>.supabase.co/storage/v1/s3` | project region | `true` |
| DigitalOcean Spaces | `https://<region>.digitaloceanspaces.com` | e.g. `fra1` | `false` |
| Backblaze B2 | `https://s3.<region>.backblazeb2.com` | e.g. `eu-central-003` | `false` |
| MinIO | `http://<host>:9000` | `us-east-1` | `true` |

`tests/Feature/S3CompatibleStorageTest.php` round-trips files through a real S3-compatible server when `S3_TEST_ENDPOINT`, `S3_TEST_KEY` and `S3_TEST_SECRET` are set (CI starts MinIO for it).

## Queue, cache, sessions

| Variable | Simple hosting | With Redis |
|---|---|---|
| `QUEUE_CONNECTION` | `database` (run `php artisan queue:work --tries=3`) | `redis` |
| `CACHE_STORE` (`CACHE_DRIVER` still read) | `database` / `file` | `redis` |
| `SESSION_DRIVER` | `database` | `redis` |
| `SESSION_LIFETIME` | `120` | |
| `SESSION_SECURE_COOKIE` | `true` (HTTPS) | |
| `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD` or `REDIS_URL` | | |

## Mail

Common to all: `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_REPLY_ADDRESS`, `MAIL_REPLY_NAME`.

| `MAIL_MAILER` | Also set | Notes |
|---|---|---|
| `smtp` | `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` | Any provider's SMTP relay |
| `mailgun` | `MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAILGUN_ENDPOINT` (`api.eu.mailgun.net` for EU) | |
| `resend` | `RESEND_KEY` | |
| `postmark` | `POSTMARK_TOKEN` | |
| `ses` | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION` | Verified sender domain required |
| `log` | | Writes mail to the log (development) |

## Backups and monitoring (existing)

`BACKUP_DISK`, `BACKUP_FILES_DISK`, `BACKUP_PATH`, `BACKUP_KEY` (required), `BACKUP_REQUIRE_ENCRYPTION`, `BACKUP_KEEP_DAYS`, `BACKUP_KEEP_MONTHS`, `BACKUP_MAX_AGE_HOURS`, `BACKUP_REHEARSAL_*`, `MONITORING_SLOW_QUERY_MS`, `MONITORING_SLOW_REQUEST_QUERY_MS`, `RELEASE_RPO_HOURS`, `RELEASE_RTO_MINUTES`, `RELEASE_RETENTION_POLICY_VERSION`, `RELEASE_RETENTION_POLICY_APPROVED`.

## Accounts and documents (existing)

`CHECK_EMAIL_DOMAINS`, `ACCOUNT_INVITATION_EXPIRY_HOURS`, `DOCUMENT_RENDERER` (empty, `browser`, `dompdf`), `BROWSER_RENDERER_URL`, `BROWSER_RENDERER_TOKEN`, `BROWSER_RENDERER_TIMEOUT`.

## Campuly

| Variable | Default | Purpose |
|---|---|---|
| `CAMPULY_DOCS_URL` | `https://github.com/Lineker-star/Campuly/tree/main/docs` | Where the header and sidebar "Documentation" links lead. |
| `CAMPULY_UPDATE_REPOSITORY` | `Lineker-star/Campuly` | GitHub repository whose releases `campuly:update` installs. `SKUUL_UPDATE_REPOSITORY` is still read when this is unset, for older installs. |

## Container

`RUN_MIGRATIONS` (`true` on one service only), `DB_WAIT_SECONDS` (60), `RUN_WORKER` and `RUN_SCHEDULER` (`true`; `false` when the platform runs them separately), `PHP_FPM_MAX_CHILDREN` (10), `PHP_OPCACHE_VALIDATE_TIMESTAMPS` (`0` in production). Compose only: `APP_PORT` (8080), `DB_HOST_IN_COMPOSE` (`db`), `DB_ROOT_PASSWORD`. See docs/DEPLOYMENT.md section 1.

## Planned (added by later phases)

| Phase | Variables |
|---|---|
| 1 | `APP_DEFAULT_LOCALE=fr` |
| 4 | `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` |
| 6 | `PAYMENTS_MOBILE_MONEY_DRIVER`, provider keys (`CAMPAY_*`, `NOTCHPAY_*`, `FLUTTERWAVE_*`, `MTN_MOMO_*`, `ORANGE_MONEY_*`), `SMS_DRIVER`, `SMS_*`, `WHATSAPP_*` |
| 7 | `CAMPULY_CLOUD_URL`, `CAMPULY_NODE_LAN=false` |
| 8 | `AI_PROVIDER`, `AI_API_KEY` |
