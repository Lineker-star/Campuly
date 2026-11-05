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

| Variable | Example | Notes |
|---|---|---|
| `FILESYSTEM_DISK` | `local` or `s3` | `s3` for any S3-compatible storage |
| `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY` | | Server-side only |
| `AWS_DEFAULT_REGION` | `eu-west-1` / `auto` (R2) | |
| `AWS_BUCKET` | `campuly` | Private bucket |
| `AWS_ENDPOINT` | `https://<ref>.storage.supabase.co/storage/v1/s3` | Omit for AWS S3 |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` | Supabase, MinIO, R2 |

## Queue, cache, sessions

| Variable | Simple hosting | With Redis |
|---|---|---|
| `QUEUE_CONNECTION` | `database` | `redis` |
| `CACHE_STORE` (`CACHE_DRIVER` in older config) | `database` / `file` | `redis` |
| `SESSION_DRIVER` | `database` | `redis` |
| `SESSION_LIFETIME` | `120` | |
| `SESSION_SECURE_COOKIE` | `true` (HTTPS) | |
| `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD` or `REDIS_URL` | | |

## Mail

`MAIL_MAILER` (`smtp`, `resend`, `mailgun`, `postmark`, `ses`, `log`), `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_REPLY_ADDRESS`, `MAIL_REPLY_NAME`.

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

`RUN_MIGRATIONS` (`true` on one service only), `PHP_OPCACHE_VALIDATE_TIMESTAMPS` (`0` in production).

## Planned (added by later phases)

| Phase | Variables |
|---|---|
| 1 | `APP_DEFAULT_LOCALE=fr` |
| 4 | `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` |
| 6 | `PAYMENTS_MOBILE_MONEY_DRIVER`, provider keys (`CAMPAY_*`, `NOTCHPAY_*`, `FLUTTERWAVE_*`, `MTN_MOMO_*`, `ORANGE_MONEY_*`), `SMS_DRIVER`, `SMS_*`, `WHATSAPP_*` |
| 7 | `CAMPULY_CLOUD_URL`, `CAMPULY_NODE_LAN=false` |
| 8 | `AI_PROVIDER`, `AI_API_KEY` |
