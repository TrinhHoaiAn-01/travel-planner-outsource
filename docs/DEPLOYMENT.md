# Build, publish, and deploy Travel Planner

## What happens on a release

Push or merge tested changes into `main`. The **Travel Planner CI** workflow:

1. Builds the `testing` Docker target, migrates a disposable MySQL database, and
   runs the complete PHPUnit suite against isolated in-memory SQLite databases.
2. Calls the reusable **Travel Planner CD** workflow only after tests pass and
   only for a push to `main` (or a manual CI run on `main`).
3. Builds the `production` image with compiled Vite assets and production Composer
   dependencies. Tests, local `.env` files, uploads, and development tools are excluded.
4. Starts the production Compose stack with a disposable MySQL database, checks
   `/up` and `/`, and verifies the asset manifest and writable public uploads.
5. Uploads `travel-planner-<source-sha>.tar.gz` and its SHA-256 checksum as the
   `travel-planner-<source-sha>` Actions artifact, retained for **30 days**.
6. Pushes `ghcr.io/trinhhoaian-01/travel-planner:<source-sha>` and `:latest`.
7. Adds a `github-actions[bot]` commit to `deployment`. That branch contains
   `docker-compose.yml`, `.env.example`, `image.env`, `deployment.json`, this guide,
   and `.gitignore`. Its existing history remains available; the old source
   snapshot is replaced by these deployment files. Application code lives in the
   image and archive. No force push is used.

`image.env` pins the image digest. `deployment.json` links the tested source,
workflow run, and archive. Publishing is serialized, and outdated queued runs do
not update `deployment`. Pushes to `deployment` do not start CI.

This publishes deployable packages and records a release; it does **not** connect
to a hosting server or restart an existing website. Use the server steps below.
The image targets Linux; the GitHub runner builds Linux AMD64. ARM hosts can build
locally or extend the workflow to publish multiple architectures.

## One-time GitHub setup

- Enable GitHub Actions for this repository and allow the actions used by these
  workflows. The publishing job requests `contents: write` and `packages: write`
  through its built-in `GITHUB_TOKEN`; no publishing PAT is required.
- If rules protect `deployment`, allow this automation to write to it. A denied
  branch write fails the publishing step; do not disable protection on `main`.
- In the existing GHCR package's settings, ensure this repository has **Write**
  access under **Manage Actions access**. The image source label links new
  packages to the repository, but existing package permissions may need adjustment.
- Make the package public if anonymous image pulls are desired. Public repository
  visibility does not automatically make a GHCR package public. For a private
  package, use a classic PAT with `read:packages` on the hosting machine:

  ```sh
  docker login ghcr.io -u YOUR_GITHUB_USERNAME
  ```

  Paste the PAT at the password prompt. Do not commit it.

Releases work from pushes to `main` even if the repository default branch is
still `master`. To expose the new manual **Run workflow** button, change the
repository default branch to `main` under Settings. Changing the default branch is
optional for automatic releases. The old workflow on `master` still exists there;
avoid pushing release changes to `master` until it is synchronized or retired.

## Publish future versions

Merge your normal feature PR into `main`, or, when committing directly:

```sh
git switch main
git pull --ff-only origin main
# Make and review your changes, then stage only the intended files.
git add PATH_TO_CHANGED_FILE
git commit -m "Describe the change"
git push origin main
```

Open the repository's **Actions → Travel Planner CI** run. Wait for both
`laravel-tests` and `publish / publish` to succeed. Then check:

- **Packages → travel-planner** for the source SHA tag and `latest`.
- The **deployment** branch for `Deploy Travel Planner <source-sha>`.
- The run's **Artifacts** section for the downloadable application archive.

For a failed run, inspect the first failing step, fix the cause, and push again.
Use **Re-run all jobs** to retry a transient failure on the current `main` commit.
A newer `main` commit causes an older run to skip branch publication. CI failures
must be fixed before publication; tests are never bypassed.

## First deployment with Docker (recommended)

Install Git and Docker Engine/Desktop with Compose v2, using Linux containers.
Use a separate folder for `deployment` so source checkout overrides cannot mount
local files over the published image:

```sh
git clone --branch deployment --single-branch https://github.com/TrinhHoaiAn-01/travel-planner-outsource.git travel-planner-deploy
cd travel-planner-deploy
cp .env.example .env
```

PowerShell: use `Copy-Item .env.example .env` instead of `cp` if preferred.
Edit `.env` and set:

- `APP_URL` to the externally reachable URL and `APP_PORT` to the host port (8080
  by default). Put a TLS reverse proxy in front for a public HTTPS deployment.
- `APP_KEY` to one freshly generated key. Generate it without changing any files:
  take the image reference from `image.env` and run:

  ```sh
  docker run --rm --entrypoint php IMAGE_REFERENCE artisan key:generate --show
  ```

  Replace `IMAGE_REFERENCE` with the complete `ghcr.io/...@sha256:...` value.
  Keep the same key on every subsequent update.
- `DB_PASSWORD` and `MYSQL_ROOT_PASSWORD` to separate strong passwords. Leave the
  application user as `travel_planner`; it does not use MySQL root.
- SMTP settings with `MAIL_MAILER=smtp` if password-reset emails must be delivered.
  The default `log` mailer is for local validation.

Then pull the exact released image, start the database, run migrations once, and
start the application:

```sh
docker compose --env-file image.env --env-file .env -f docker-compose.yml pull
docker compose --env-file image.env --env-file .env -f docker-compose.yml up -d --wait mysql
docker compose --env-file image.env --env-file .env -f docker-compose.yml run --rm app php artisan migrate --force
docker compose --env-file image.env --env-file .env -f docker-compose.yml up -d --wait
docker compose --env-file image.env --env-file .env -f docker-compose.yml ps
```

Visit the configured `APP_URL` and `/up`. Database data and uploaded files persist
in named Docker volumes. The app uses synchronous queues, so no separate worker
is required. Container startup creates writable storage directories and caches
configuration/views. Database migrations run explicitly, not on each restart.

Do not run `down -v` against a deployment you want to keep: that deletes its
database and upload volumes. Existing installations with the old root credentials
need a planned database/user migration; changing MySQL environment variables does
not change users/passwords inside an already initialized volume. Back up and
restore your existing database/uploads before moving to this deployment layout.

## Update an existing Docker deployment

Back up the database and upload volume first. Retain `.env` and the same folder
(Compose project name `travel-planner` keeps the volumes stable):

```sh
git pull --ff-only origin deployment
docker compose --env-file image.env --env-file .env -f docker-compose.yml pull
docker compose --env-file image.env --env-file .env -f docker-compose.yml run --rm app php artisan migrate --force
docker compose --env-file image.env --env-file .env -f docker-compose.yml up -d --wait
```

For schema changes that are incompatible with the running app, schedule maintenance
before migrations. Never run demo seeders automatically on a real database.

## Install the application archive without Docker

Download and unzip the Actions artifact from the successful run. It contains the
`.tar.gz` application and checksum file. Artifacts require a GitHub login and expire
after 30 days; download and store a copy if you need a permanent release archive.

```sh
sha256sum -c travel-planner-SOURCE_SHA.tar.gz.sha256
mkdir travel-planner-release
tar -xzf travel-planner-SOURCE_SHA.tar.gz -C travel-planner-release
cd travel-planner-release
cp .env.example .env
```

PowerShell can inspect the checksum with `Get-FileHash -Algorithm SHA256` and
extract with `tar`. Configure `.env` for production (`APP_ENV=production`,
`APP_DEBUG=false`, your stable `APP_KEY`, URL, MySQL, mail, and queue settings).
Set `QUEUE_CONNECTION=sync` unless you operate a queue worker.

The archive includes `vendor/` and `public/build/`; Composer and npm do not need to
run on the server. Use PHP **8.3+** with the extensions required by Composer
(including PDO MySQL, mbstring, GD, ZIP, DOM/XML, and fileinfo) and a MySQL server.
The package was built and tested on PHP 8.3. Verify the target PHP environment:

```sh
php vendor/composer/platform_check.php
php artisan key:generate --show
php artisan migrate --force
php artisan config:cache
php artisan view:cache
```

Generate a key only for a new installation; copy it into `.env`. Give the web
server write access to `storage/` and `bootstrap/cache/`, serve **only `public/`**
as the document root, and enable Laravel's Apache rewrite rules or equivalent
Nginx routing. The archive includes a relative `public/storage` symlink; if your
extractor does not preserve it, recreate it with `php artisan storage:link` after
removing any broken link. Preserve your existing `.env`, database, and uploads
when replacing application files on later updates.

## Build locally

With Docker running, these builds do not read your local `.env`:

```sh
docker build --target testing -t travel-planner:test .
docker compose -f docker-compose.ci.yml up -d --build --wait
docker compose -f docker-compose.ci.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.ci.yml exec -T app php -d memory_limit=512M artisan test
docker compose -f docker-compose.ci.yml down -v
docker build --target production -t travel-planner:local .
```

The `down -v` above applies only to the disposable CI stack. To use the same
smoke test and packaging scripts locally, run in Bash on Linux/WSL with Docker
available, set `APP_IMAGE=travel-planner:local`, and set `GITHUB_SHA` to
`git rev-parse HEAD`. Run `.github/scripts/smoke-image.sh` and
`.github/scripts/package.sh`; the archive is written to `dist/`.

## Rollback and troubleshooting

For an application rollback, recover `image.env` from a previous `deployment`
commit, then run the `pull` and `up -d --wait` commands above. Alternatively set
`APP_IMAGE` in your local `.env` to the previous digest (the second `--env-file`
overrides the first). Remove that override before normal updates. Application
rollback does not undo database migrations; restore a compatible backup or follow
the migration-specific rollback plan.

Use `docker compose --env-file image.env --env-file .env -f docker-compose.yml logs --tail=100 app mysql`
to inspect startup errors. A GHCR 403 usually means package access is missing; a
branch push rejection usually means `deployment` protection blocks the bot. If
tests fail with missing SQLite tables, the affected database-using test class
must initialize its own schema (normally with `RefreshDatabase`).

References: [GitHub package publishing](https://docs.github.com/en/packages/managing-github-packages-using-github-actions-workflows/publishing-and-installing-a-package-with-github-actions),
[GHCR authentication and visibility](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry),
[Compose environment files](https://docs.docker.com/compose/how-tos/environment-variables/set-environment-variables/).
