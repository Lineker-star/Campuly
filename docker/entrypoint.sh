#!/usr/bin/env bash
# Campuly container entrypoint.
# The container is configured by environment variables only; no .env file is needed.
# 1. check the required settings, 2. prepare writable folders, 3. wait for the
# database and migrate when RUN_MIGRATIONS=true, 4. cache config/routes/views,
# 5. hand over to Supervisor (web, queue worker, scheduler).
set -euo pipefail
cd /var/www/html

# Run artisan as the web user so every file it writes stays writable for PHP-FPM.
artisan() { su-exec www-data php artisan "$@"; }

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is not set. Generate one with: docker run --rm campuly/app php artisan key:generate --show" >&2
  exit 1
fi

mkdir -p storage/app/public storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

# A SQLite database lives in a file; create it on first start.
if [ "${DB_CONNECTION:-}" = "sqlite" ] && [ -n "${DB_DATABASE:-}" ] && [ ! -f "${DB_DATABASE}" ]; then
  mkdir -p "$(dirname "${DB_DATABASE}")"
  touch "${DB_DATABASE}"
  chown www-data:www-data "$(dirname "${DB_DATABASE}")" "${DB_DATABASE}"
fi

chown -R www-data:www-data storage bootstrap/cache

artisan storage:link --force >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  # The database container may still be starting.
  wait_seconds="${DB_WAIT_SECONDS:-60}"
  for attempt in $(seq 1 "$wait_seconds"); do
    if artisan migrate --force --no-interaction; then
      break
    fi
    if [ "$attempt" = "$wait_seconds" ]; then
      echo "The database did not accept migrations within ${wait_seconds}s." >&2
      exit 1
    fi
    echo "Waiting for the database ($attempt/${wait_seconds})..." >&2
    sleep 1
  done
fi

artisan optimize --no-interaction

exec "$@"
