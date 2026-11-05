#!/usr/bin/env bash
# Campuly container entrypoint: prepare caches, optionally migrate, then start Supervisor.
set -euo pipefail
cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
  exit 1
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link --force >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

php artisan optimize

exec "$@"
