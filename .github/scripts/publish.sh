#!/usr/bin/env bash
set -euo pipefail
: "${APP_IMAGE_NAME:?Set APP_IMAGE_NAME}"
: "${GITHUB_SHA:?Set GITHUB_SHA}"
: "${GITHUB_REPOSITORY:?Set GITHUB_REPOSITORY}"
: "${GITHUB_RUN_ID:?Set GITHUB_RUN_ID}"
: "${ARCHIVE_URL:?Set ARCHIVE_URL}"

# Older queued runs must not move the deployment pointer backwards.
head_sha=$(git ls-remote origin refs/heads/main | cut -f1)
if [ "$head_sha" != "$GITHUB_SHA" ]; then
  echo "A newer main commit exists; skipping publication."
  exit 0
fi
docker push "$APP_IMAGE_NAME:$GITHUB_SHA"
digest=$(docker inspect --format '{{index .RepoDigests 0}}' "$APP_IMAGE_NAME:$GITHUB_SHA")
docker tag "$APP_IMAGE_NAME:$GITHUB_SHA" "$APP_IMAGE_NAME:latest"
docker push "$APP_IMAGE_NAME:latest"

bundle=$(mktemp -d)
trap 'rm -rf "$bundle"' EXIT
cp docker-compose.prod.yml "$bundle/docker-compose.yml"
cp deploy/.env.example "$bundle/.env.example"
cp docs/DEPLOYMENT.md "$bundle/README.md"
printf '%s\n' '.env' '.env.*' '!.env.example' '!image.env' > "$bundle/.gitignore"
printf 'APP_IMAGE=%s\n' "$digest" > "$bundle/image.env"
jq -n --arg sha "$GITHUB_SHA" --arg image "$digest" \
  --arg run "https://github.com/$GITHUB_REPOSITORY/actions/runs/$GITHUB_RUN_ID" \
  --arg archive "$ARCHIVE_URL" \
  '{source_sha: $sha, image: $image, workflow_run: $run, archive: $archive}' \
  > "$bundle/deployment.json"

git fetch origin deployment
git checkout -B deployment FETCH_HEAD
# Keep branch history, replacing its old source snapshot with deployment metadata.
git rm -r --ignore-unmatch . > /dev/null
cp -a "$bundle/." .
git config user.name 'github-actions[bot]'
git config user.email '41898282+github-actions[bot]@users.noreply.github.com'
git add docker-compose.yml .env.example README.md .gitignore image.env deployment.json
if git diff --cached --quiet; then
  echo "Deployment already points to this build."
  exit 0
fi
git commit -m "Deploy Travel Planner $GITHUB_SHA"
# Recheck after building/pushing; never force-push the deployment branch.
head_sha=$(git ls-remote origin refs/heads/main | cut -f1)
if [ "$head_sha" = "$GITHUB_SHA" ]; then
  git push origin HEAD:deployment
  if [ -n "${GITHUB_STEP_SUMMARY:-}" ]; then
    printf 'Published `%s`\n\n[Deployment branch](https://github.com/%s/tree/deployment) · [Application archive](%s)\n' \
      "$digest" "$GITHUB_REPOSITORY" "$ARCHIVE_URL" >> "$GITHUB_STEP_SUMMARY"
  fi
else
  echo "A newer main commit exists; deployment branch was not changed."
fi
