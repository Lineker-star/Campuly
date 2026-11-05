#!/usr/bin/env bash
# Wait until /health reports every check ok: database, cache, storage, and the
# queue worker and scheduler heartbeats (they need up to a minute after start).
set -uo pipefail
url="${HEALTH_URL:-http://localhost:8080/health}"
for i in $(seq 1 120); do
  body="$(curl -s "$url" || true)"
  if echo "$body" | grep -q '"status":"ok"'; then
    echo "healthy after ${i}x2s: $body"
    exit 0
  fi
  sleep 2
done
echo "not healthy: $body" >&2
exit 1
