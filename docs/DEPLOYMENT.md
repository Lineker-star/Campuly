# Deploying Campuly

Campuly is a Laravel (PHP 8.4) application. Every deployment has four parts:

| Part | Choices |
|---|---|
| **App runtime** (runs PHP) | Docker container, or a classic PHP host |
| **Database** | MySQL 8 / MariaDB, PostgreSQL 15+ (Supabase, Neon…), SQLite (single-school pilot) |
| **File storage** | Local disk (persistent volume) or S3-compatible bucket |
| **Background work** | Queue worker + scheduler (cron every minute) |

> PostgreSQL and SQLite become fully supported when **STEP 0.4** of `MASTER_PROMPT.md` is done (today the migrations need fixing on those engines; see `DATABASE.md`). Until then use MySQL in production.

Supabase, Neon and similar services provide the **database and storage**; they do not run PHP. Vercel and Netlify cannot run the Campuly app server (use them only for a marketing site).

---

## 0. Common production settings

```env
APP_NAME=Campuly
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.yourdomain.com
APP_KEY=base64:...            # php artisan key:generate --show (once, keep it safe)
APP_CURRENCY=XAF
APP_TIMEZONE=UTC             # keep UTC; each school sets its own time zone in School settings
LOG_CHANNEL=stderr            # containers; use "daily" on classic hosts
SESSION_SECURE_COOKIE=true
BACKUP_KEY=...                # required: backups refuse to run without it
```

Release commands (every deploy):

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build          # or build in CI and ship public/build
php artisan migrate --force
php artisan storage:link
php artisan optimize             # config, routes, views, events cache
php artisan queue:restart
```

Processes:
- **Web:** PHP-FPM + Nginx (or `php artisan serve` only for testing).
- **Worker:** `php artisan queue:work --tries=3 --max-time=3600`
- **Scheduler:** `* * * * * php artisan schedule:run` (or `php artisan schedule:work` as a process)

First run: open `https://<your-domain>/install` and complete the installer.

---

## 1. Docker on any VPS (Hostinger VPS, Contabo, DigitalOcean, Hetzner, OVH) — recommended

Files provided: `Dockerfile`, `docker-compose.prod.yml` (app + MySQL), `docker-compose.pgsql.yml` (swap MySQL for PostgreSQL), `docker/` (Nginx, PHP-FPM, Supervisor, entrypoint).

The image runs Nginx + PHP-FPM, the queue worker and the scheduler under Supervisor, listens on port **8080** and answers `GET /health`. It is configured **by environment variables only** (no `.env` file inside the image):

| Variable | Default in the image | Purpose |
|---|---|---|
| `APP_KEY` | — (required) | The container refuses to start without it |
| `RUN_MIGRATIONS` | `false` (`true` in the compose file) | Run `php artisan migrate --force` at start, waiting up to `DB_WAIT_SECONDS` (60) for the database |
| `RUN_WORKER` / `RUN_SCHEDULER` | `true` | Set `false` when the platform runs `php artisan queue:work` / `schedule:work` as separate services from the same image |
| `PHP_FPM_MAX_CHILDREN` | `10` | PHP workers; about 60 MB of RAM each |
| `APP_ENV` / `APP_DEBUG` / `LOG_CHANNEL` | `production` / `false` / `stderr` | Logs go to `docker logs` |

```bash
# on the server (Ubuntu 24.04), once
curl -fsSL https://get.docker.com | sh
git clone <your-campuly-repo> campuly && cd campuly
cp .env.example .env    # set APP_KEY, APP_URL, DB_PASSWORD, DB_ROOT_PASSWORD, mail settings
# a key: docker run --rm php:8.4-cli php -r 'echo "base64:".base64_encode(random_bytes(32)), PHP_EOL;'
docker compose -f docker-compose.prod.yml up -d --build      # migrations run at start
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=WorldSeeder --force
# open http://<server>:8080 and finish the installer
```

PostgreSQL instead of MySQL, same image: `docker compose -f docker-compose.prod.yml -f docker-compose.pgsql.yml up -d --build`. A hosted database (Supabase, Neon, RDS): set `DB_*` in `.env` (`DB_HOST_IN_COMPOSE` to its host) and start only the app: `docker compose -f docker-compose.prod.yml up -d --no-deps app`. Redis is optional (`--profile redis`).

The CI job "Container" builds this image and checks that `/health` reports every check OK on MySQL and then on PostgreSQL.

Put Caddy or Nginx Proxy Manager in front for HTTPS, or use Cloudflare Tunnel. Update: `git pull && docker compose -f docker-compose.prod.yml up -d --build`.

## 2. Laravel Cloud / Laravel Forge / Ploi

- **Laravel Cloud:** connect the Git repo, choose PHP 8.4, add a MySQL or Postgres database from the dashboard, add a worker (`queue:work`) and enable the scheduler; set env vars; build command `npm ci && npm run build`; deploy command `php artisan migrate --force`.
- **Forge / Ploi:** create a server (any cloud), a site with PHP 8.4, connect the repo, enable "Quick deploy", add a daemon `php artisan queue:work --tries=3`, enable the scheduler, issue Let's Encrypt SSL. Deploy script = release commands above.

## 3. Render / Railway / Fly.io / Koyeb / Google Cloud Run (Docker based)

All use the same `Dockerfile`.
- **Render:** New → Web Service → Docker; add a Background Worker with command `php artisan queue:work --tries=3` and a Cron Job `php artisan schedule:run` every minute; database: Render PostgreSQL or external (Supabase/MySQL). Mount a disk at `/var/www/html/storage/app` or use S3.
- **Railway:** New project → Deploy from repo (Dockerfile detected) → add MySQL or PostgreSQL plugin → reference its variables in `DB_*`; add a second service from the same repo with start command `php artisan queue:work` and a cron service for the scheduler.
- **Fly.io:** `fly launch` (uses Dockerfile) → `fly volumes create campuly_storage` → processes in `fly.toml`: `app`, `worker = "php artisan queue:work"`, `scheduler = "php artisan schedule:work"`.
- **Cloud Run / Koyeb:** stateless containers → **use S3 storage** and an external database; run the scheduler with Cloud Scheduler hitting a protected route or a separate always-on worker.

Set `RUN_MIGRATIONS=true` on exactly one service so migrations run once at boot.

## 4. Sevalla

Create an Application from the repo (Dockerfile), add a database (MySQL or PostgreSQL) in the same region, add processes: `web` (default), `worker` (`php artisan queue:work`), `cron` (`php artisan schedule:work`). Add a persistent disk or object storage.

## 5. cPanel / shared hosting (no Docker)

Requirements: PHP 8.4 with `intl`, `gd`, `zip`, `bcmath`, `pdo_mysql`; SSH access; cron jobs.
1. Build locally: `composer install --no-dev -o`, `npm run build`. Upload the project **outside** `public_html` (e.g. `~/campuly`).
2. Point the domain's document root to `~/campuly/public` (or symlink `public_html` → `~/campuly/public`).
3. Create the MySQL database in cPanel; fill `.env`.
4. SSH: `php artisan key:generate`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`.
5. Cron (every minute): `cd ~/campuly && php artisan schedule:run >> /dev/null 2>&1`.
6. Queue without a daemon: set `QUEUE_CONNECTION=database` and add a cron every minute `php artisan queue:work --stop-when-empty --max-time=55`.

## 6. Supabase as database and storage

1. Create a Supabase project in the region closest to your users (e.g. `eu-west` for Central Africa latency).
2. **Database:** Connect → **Session pooler**:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=aws-0-<region>.pooler.supabase.com
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres.<project-ref>
   DB_PASSWORD=<database password>
   DB_SSLMODE=require
   ```
   (Transaction pooler on 6543 only with emulated prepares — see `DATABASE.md`.)
3. **Storage:** Storage → create a private bucket `campuly` → Storage → S3 Configuration → enable S3, create an access key:
   ```env
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=<access key id>
   AWS_SECRET_ACCESS_KEY=<secret>
   AWS_DEFAULT_REGION=<project region>
   AWS_BUCKET=campuly
   AWS_ENDPOINT=https://<project-ref>.storage.supabase.co/storage/v1/s3
   AWS_USE_PATH_STYLE_ENDPOINT=true
   ```
   S3 keys bypass Supabase RLS: keep them server-side only.
4. Run the app on any runtime above (Render, Railway, Fly, VPS, Laravel Cloud) with these variables.
5. Keep Laravel authentication; do not use Supabase Auth for Campuly users.

## 7. Other managed databases and storage

| Service | Variables |
|---|---|
| Neon (Postgres) | `DB_CONNECTION=pgsql`, pooled host `...-pooler.<region>.aws.neon.tech`, `DB_SSLMODE=require` |
| Aiven / DigitalOcean / RDS MySQL | `DB_CONNECTION=mysql`, `MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt` |
| Cloudflare R2 | `AWS_ENDPOINT=https://<account>.r2.cloudflarestorage.com`, `AWS_DEFAULT_REGION=auto`, path style true |
| DigitalOcean Spaces | `AWS_ENDPOINT=https://<region>.digitaloceanspaces.com` |
| Backblaze B2 | `AWS_ENDPOINT=https://s3.<region>.backblazeb2.com` |
| Redis (optional) | Upstash/Railway: `REDIS_URL=rediss://...`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis` |
| Mail | `MAIL_MAILER=resend|mailgun|postmark|ses|smtp` + provider keys |

## 8. Multi-school SaaS (later)

Wildcard DNS `*.campuly.com` → app; custom school domains are already supported by `ResolveDomainContext` (`SchoolDomain` model). Use a CDN in front of `/build` and `/img`.

## 9. After every deployment — smoke test

1. `/health` returns OK.
2. Sign in as admin; dashboard loads with the Campuly brand.
3. Upload a school logo (tests storage).
4. Publish a notice (tests queue + mail).
5. `php artisan skuul:check-backup` passes the next morning.
