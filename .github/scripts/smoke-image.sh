#!/usr/bin/env bash
set -euo pipefail
: "${APP_IMAGE:?Set APP_IMAGE}"

export APP_IMAGE
export APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
export APP_PORT=18080
export DB_PASSWORD=smoke-app-only
export MYSQL_ROOT_PASSWORD=smoke-root-only
compose=(docker compose -p travel-planner-release-smoke --env-file deploy/.env.example -f docker-compose.prod.yml)
trap '"${compose[@]}" logs --tail=80; "${compose[@]}" down -v' EXIT
"${compose[@]}" up -d --wait mysql
"${compose[@]}" run --rm app php artisan migrate --force
"${compose[@]}" up -d --wait
curl --fail --silent --show-error "http://127.0.0.1:$APP_PORT/up" > /dev/null
curl --fail --silent --show-error "http://127.0.0.1:$APP_PORT/" > /dev/null
"${compose[@]}" exec -T app sh -c \
  'test -f public/build/manifest.json && test -L public/storage && test ! -d vendor/phpunit && test ! -f .env'
"${compose[@]}" exec -T --user www-data app php -r \
  'exit(file_put_contents("storage/app/public/smoke.txt", "ok") === false ? 1 : 0);'
test "$(curl --fail --silent --show-error "http://127.0.0.1:$APP_PORT/storage/smoke.txt")" = ok
