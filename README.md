<p align="center">
  <img src="public/img/brand/campuly-logo.png" alt="Campuly — Learn · Interact · Excel" width="420">
</p>

# Campuly

**Learn · Interact · Excel** — *Apprendre · Interagir · Exceller*

Campuly is a bilingual (English ⇄ French) school management platform for primary, secondary and higher-education institutions in Cameroon and across Africa. It manages organizations and campuses, students and families, academic calendars, attendance, gradebooks and report cards, timetables, syllabi, fees and accounting, boarding, library, student care and staff operations, with a dedicated portal for parents and learners.

Target editions:

| Edition | What it is | Status |
|---|---|---|
| Web | Responsive web app (desktop, tablet, phone) | Running (Skuul V3 base, Campuly brand applied) |
| PWA | Installable from the browser on Windows, macOS, Linux, Android, iOS; offline core screens | Planned — Phases 4–5 |
| Desktop client | Native installer (.msi/.exe/.dmg) wrapping the PWA | Planned — Phase 7 |
| Campus node | Full app on a local machine (SQLite) that syncs with the cloud and serves the school LAN | Planned — Phase 7 |

The build plan is in **[MASTER_PROMPT.md](MASTER_PROMPT.md)**. All project documents are listed in **[docs/00-INDEX.md](docs/00-INDEX.md)**.

## Quick start (local)

Requirements: PHP 8.4.1+, Composer 2, Node.js 22 or 24, MySQL 8 (PostgreSQL and SQLite support is being completed in Phase 0), optional Redis.

```bash
composer install --no-interaction --prefer-dist
npm ci
npm run build
cp .env.example .env            # Windows: copy .env.example .env
php artisan key:generate
# edit .env: DB_HOST=127.0.0.1, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan migrate
php artisan db:seed --class=WorldSeeder
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000/install, create the administrator, organization and first campus, then complete the setup checklist. Keep `php artisan queue:work --tries=3` and `php artisan schedule:work` running for notices, reminders and backups.

**Demo data (disposable database only):** set `DEMO_MODE=true`, run `php artisan config:clear` and `php artisan skuul:refresh-demo`. All demo accounts use the password `password` (see `docs/AUDIT_REPORT.md` for the list).

## Deploy anywhere

Docker image, VPS, Laravel Cloud/Forge, Render, Railway, Fly.io, Sevalla, cPanel; database on MySQL, PostgreSQL (Supabase, Neon) or SQLite; files on local disk or any S3-compatible storage (including Supabase Storage). Step-by-step guides: **[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)**.

## Brand

Logo, colours, typography and usage rules: **[docs/BRAND_GUIDELINES.md](docs/BRAND_GUIDELINES.md)**. Assets live in `public/img/brand/`.

## Development checks

```bash
php artisan test --compact
vendor/bin/pint --dirty
vendor/bin/phpstan analyse --memory-limit=2G
composer audit && npm audit --audit-level=high
npm run build
```

Contribution rules: [docs/CODING_STANDARDS.md](docs/CODING_STANDARDS.md) and [AGENTS.md](AGENTS.md).

## Licence and credits

Campuly modifications are © 2026 Megaj Technologies and are distributed under the MIT licence. It is built on [Skuul](https://github.com/yungifez/skuul) (MIT), © 2022 Ebuka Ifezue and contributors; see [NOTICE.md](NOTICE.md) and [LICENSE](LICENSE).
