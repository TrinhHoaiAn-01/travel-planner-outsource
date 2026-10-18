#!/usr/bin/env bash
set -euo pipefail
: "${APP_IMAGE:?Set APP_IMAGE}"
: "${GITHUB_SHA:?Set GITHUB_SHA}"

stage=$(mktemp -d)
container=$(docker create "$APP_IMAGE")
trap 'docker rm "$container" >/dev/null; rm -rf "$stage"' EXIT
docker cp "$container:/var/www/html/." "$stage/"
cp docs/DEPLOYMENT.md "$stage/DEPLOYMENT.md"
# Make the public upload link portable to a non-Docker installation.
rm "$stage/public/storage"
ln -s ../storage/app/public "$stage/public/storage"
printf '%s\n' "$GITHUB_SHA" > "$stage/REVISION"
test -f "$stage/vendor/autoload.php"
test -f "$stage/public/build/manifest.json"
test ! -e "$stage/.env"
test ! -d "$stage/vendor/phpunit"
mkdir -p dist
archive="travel-planner-$GITHUB_SHA.tar.gz"
tar -czf "dist/$archive" -C "$stage" .
(cd dist && sha256sum "$archive" > "$archive.sha256")
