# NIGMA production Docker stack

For **new or rebuilt servers** (ADR-016). The live server runs OpenLiteSpeed natively (ADR-014) and does not use this stack; a host chooses its runtime with the file `/etc/nigma/runtime` (`docker`, or absent / `native`).

## What runs

| Container | Image | Role | Published |
|---|---|---|---|
| `web` | `litespeedtech/openlitespeed:1.9.4-lsphp83` | OpenLiteSpeed + PHP 8.3 (same versions as the native server), PHP as `www-data`, code mounted **read-only** | 80, 443 |
| `db` | `mariadb:10.11` | database, data in `/var/lib/nigma/db` | — |
| `redis` | `redis:7.2-alpine` | object cache (LiteSpeed Cache) | — |
| `certbot` | `certbot/certbot` | issues/renews Let's Encrypt certificates (HTTP-01) | — |
| `wpcli` | `wordpress:cli-php8.3` | one-off WP-CLI, only on demand (`--profile tools`) | — |

No container is given the Docker socket. Only `web` is reachable from the internet.

## How a deploy works on a Docker host

`nigma-deploy` (run every minute by `nigma-deploy.timer`) does this first, before looking at git:

```
Docker environment present and healthy?   (nigma-docker-bootstrap --check)
   yes ─► skip straight to deploying the code
   no  ─► nigma-docker-bootstrap  (build / repair the environment) ─► verify ─► then deploy the code
```

"Present and healthy" means all of: Docker installed and running; code, WordPress core and `wp-config.php` in place; the rendered config matches the repository; `web`, `db`, `redis`, `certbot` running and healthy; the TLS certificate valid (or a placeholder while DNS does not point at the host yet).
If the environment cannot be made healthy, **no code is deployed** and the failure is logged. Repairs are retried at most every 10 minutes.

`nigma-docker-bootstrap` is idempotent. In order it: installs Docker (Ubuntu packages) and bounded container logs; creates `/etc/nigma/.env` and generates the database passwords; clones the sparse checkouts; installs and verifies WordPress core; writes `wp-config.php`; renders the server config; creates a placeholder certificate; starts the containers and waits for health; restores media and the database from S3 if they are empty; fixes LiteSpeed's Redis host; obtains the real certificate; and verifies everything.

### SSL

1. A self-signed **placeholder** certificate lets the web server start immediately.
2. When at least one name in `SITE_DOMAINS` resolves to this host's public IP, certbot requests the real certificate over HTTP-01 (webroot) — at most once an hour if it keeps failing. Until then the status is `placeholder-pending` and the host serves HTTPS with the placeholder.
3. The `certbot` container renews every 12 hours and touches a flag; the deployer notices it and reloads the web server (no Docker socket needed).
4. Use `ACME_STAGING=1` while testing to avoid Let's Encrypt rate limits.

`www.nigma.ae` is also behind the QUIC.cloud CDN; the apex points straight at the host, so the apex is the name that proves "DNS points here".

## Setting up a new host

Requirements: Ubuntu 24.04 (amd64 or arm64), outbound internet, ports 80/443 open, and an instance role with the S3 permissions below. No SSH is needed (use SSM).

```bash
# as root on the new host
apt-get update && apt-get install -y git
git clone --filter=blob:none --no-checkout https://github.com/arish-cactix/nigma-wordpress.git /opt/nigma/infra
cd /opt/nigma/infra && git sparse-checkout init --no-cone && git sparse-checkout set /docker/prod/ /scripts/deploy/ && git checkout main

bash scripts/deploy/install.sh --runtime docker \
  --domains nigma.ae,www.nigma.ae --cert-name nigma-ae \
  --media-s3 s3://YOUR-BUCKET/uploads --db-s3 s3://YOUR-BUCKET/db --backup-timer \
  --now
```

`--now` runs the first deploy immediately (which builds the environment). Without it the timer does the same within a minute. Add `--acme-staging` for a test host.

Check progress and health:

```bash
nigma-deploy --status                 # runtime, environment healthy?, commit
nigma-docker-bootstrap --status       # docker / code / config / each container / ssl
tail -f /var/log/nigma-bootstrap.log /var/log/nigma-deploy.log
```

## Media library and database (private S3 bucket)

The media library is **not** kept in git: it is 400+ MB, changes whenever an editor uploads, and can contain visitor-uploaded files. Instead it lives in a private S3 bucket:

- Create the bucket with **Block Public Access on, versioning on, default encryption on**, and a lifecycle rule that expires old non-current versions (for example after 90 days).
- Give the host's instance role the policy in `iam-policy.example.json` (list + get/put only; deliberately no delete).
- `nigma-data media-backup` / `db-backup` (or `backup`) upload; the daily timer (`--backup-timer`) runs `backup`.
- On a host with an empty `uploads/` or an empty database, the bootstrap restores them (`MEDIA_S3_URI`, `DB_BACKUP_S3_URI` in `/etc/nigma/.env`).
- Generated assets (`fusion-styles/`, `fusion-scripts/`), Migrate DB exports and Snapshot archives are excluded.

Manual: `nigma-data media-restore`, `nigma-data db-restore [file|s3-uri|latest]` (refuses a non-empty database unless `FORCE=1`).

## Files

| File | Purpose |
|---|---|
| `docker-compose.yml` | The stack |
| `.env.example` | Non-secret settings and documentation of every variable (copied to `/etc/nigma/.env`) |
| `lsws/httpd_config.conf` | OpenLiteSpeed server config (derived from the image default) |
| `lsws/vhconf.conf.tpl` | Virtual host template; rendered with the shared edge rules (`scripts/deploy/vhost-edge-rules.conf`) |
| `php/99-nigma.ini` | PHP hardening (memory, `disable_functions`, …) |
| `iam-policy.example.json` | Minimum S3 permissions for the instance role |
| `smoke-web.sh` | Web-tier smoke test (see below) |

Host paths: `/etc/nigma` (config, secrets), `/opt/nigma/infra` (this repo's `docker/prod` + `scripts/deploy`), `/var/www/nigma` (site checkout + core), `/var/lib/nigma` (database, certificates, page cache, backups), `/var/log/nigma` (web server logs).

## Testing

`bash docker/prod/smoke-web.sh` starts only the web container (podman or docker, rootless) against a fake site and asserts: PHP 8.3 as `www-data`, `exec()` disabled, code read-only to PHP, uploads writable, blocked paths (403), spam shapes (410), real URLs (200), the apex → `www` redirect, the ACME challenge path, and TLS. It does not need a database or root.

The full stack (containers, certbot, S3) needs a real Docker host; use a throw-away instance with `--acme-staging`.

## Limits and caveats

- `latest`/floating tags (`certbot`, `amazon/aws-cli`) and tag-only pins mean image content can change; pin digests if you need bit-for-bit rebuilds.
- Updating plugins from the WordPress admin does not work (code is read-only); update with WP-CLI (`docker compose --profile tools run --rm wpcli …`) and commit to git, as in `docs/runbook.md`.
- PHP extensions `imap`, `apcu` and `ldap` (installed on the native server) are not in the image. Nothing known depends on them, but verify with a real restore before relying on a rebuild.
- The Docker stack and the native server are separate hosts; do not run both on one machine (both want ports 80/443).
