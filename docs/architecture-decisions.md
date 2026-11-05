# Architecture Decisions (ADR)

Version: 1.0

## Purpose

This document records significant architectural decisions for the NIGMA WordPress project.

Each Architecture Decision Record (ADR) captures the context, the decision made, and the rationale behind it. New ADRs should be appended rather than modifying historical decisions.

---

# ADR-001 — Initialize Git Repository in `wp-content`

## Context

The production website already exists as a complete WordPress installation.

## Decision

Initialize Git inside the `wp-content` directory.

## Rationale

- Keeps the repository focused on application code.
- Avoids versioning WordPress core.
- Simplifies deployments.
- Supports local Docker development.

---

# ADR-002 — Track the Avada Parent Theme

## Context

Avada is a licensed premium theme.

## Decision

Version control both the Avada parent theme and the Avada Child Theme.

## Rationale

- Ensures reproducible local environments.
- Eliminates manual theme downloads.
- Keeps production and development aligned.

---

# ADR-003 — Track Premium Plugins

## Context

Several production plugins require commercial licences.

## Decision

Track premium plugins in the private repository.

## Rationale

- Private repository protects licensed assets.
- Simplifies onboarding.
- Ensures consistent environments.

---

# ADR-004 — Exclude Uploads

## Context

Uploads contain runtime media.

## Decision

Do not version `uploads/`.

## Rationale

- Large and continuously changing.
- Not application code.
- Can be synchronized separately or served remotely.

---

# ADR-005 — Exclude Database Dumps

## Context

The database represents runtime content.

## Decision

Do not store SQL dumps in the deployment repository.

## Rationale

- Prevents stale snapshots.
- Keeps Git history focused on code.
- Encourages migration-based changes if needed.

---

# ADR-006 — Docker is Local Only

## Context

Production runs directly on AWS EC2.

## Decision

Use Docker exclusively for local development.

## Rationale

- Mirrors production without changing production architecture.
- Simplifies onboarding.
- Keeps production lightweight.

---

# ADR-007 — Deploy Only `wp-content`

## Context

Environment-specific assets should remain on production.

## Decision

CI/CD deploys only the `wp-content` directory.

## Rationale

- Preserves uploads.
- Preserves configuration.
- Minimizes deployment risk.

---

# ADR-008 — GitHub is the Canonical Source

## Context

Production should not be treated as the development environment.

## Decision

GitHub is the canonical source of application code.

## Rationale

- Supports review.
- Enables CI/CD.
- Provides traceability.

---

# ADR-009 — Documentation is Part of the Architecture

## Context

Engineering decisions should not exist only in conversations.

## Decision

Maintain comprehensive project documentation in the repository.

## Rationale

- Supports onboarding.
- Assists AI coding agents.
- Preserves architectural knowledge.

---

# ADR-010 — Production Safety First

## Context

The website is a live business system.

## Decision

Prioritize production safety in all engineering work.

## Rationale

- Encourage backups and rollback planning.
- Minimize downtime.
- Reduce operational risk.

---

# ADR-011 — Local Docker Uses nginx and WordPress PHP-FPM

## Context

Production runs on AWS EC2 with LiteSpeed, PHP 8.0.17, MariaDB 10.6.7, and WordPress 6.7.1. Local development needs a browser-accessible WordPress environment without changing production infrastructure or committing WordPress core.

The official WordPress image copies bundled default plugins and themes into `wp-content` during first-run initialization if the whole repository `wp-content/` directory is bind-mounted.

## Decision

Use Docker Compose for local development with:

- MariaDB 10.6 for the local database.
- The official WordPress PHP 8.0 FPM image for WordPress/PHP execution.
- nginx as the local HTTP server, exposed on a configurable local port defaulting to `8080`.

Mount repository-owned `wp-content` files and subdirectories individually instead of mounting all of `wp-content/`. Use a local WordPress entrypoint wrapper to initialize WordPress core while excluding bundled default plugins and themes.

## Rationale

- Keeps Docker local-only.
- Keeps WordPress core out of Git.
- Preserves `wp-content/` as the application and deployment boundary.
- Avoids repository pollution from bundled WordPress defaults.
- Uses a simple, reliable local browser-accessible architecture.

## Consequences

- Local nginx does not exactly match production LiteSpeed behavior.
- LiteSpeed-specific cache behavior still requires separate validation.
- The Docker setup is suitable for local development, not production deployment.

---

---

# ADR-012 — Leadership Page Synced from Imdaad's Contentful CMS

## Context

`/group/leadership/` mirrors the leadership team shown on imdaad.ae's own leadership page, since Imdaad Group is NIGMA's parent company. Both pages were maintained by hand as separate WordPress content, which repeatedly caused the two pages to drift out of sync (wrong bios, stale job titles, wrong ordering).

Imdaad already treats a shared Contentful space (`j7ml8jri83gb`, entry `3UnytZ6wA9RBT8ytpQsvOr`) as the source of truth for this content, and a read-only sync from that same entry was already built for homepro.ae's equivalent leadership page.

## Decision

Add `wp-content/mu-plugins/imdaad-leadership-sync.php`: a read-only Contentful Content Delivery API integration that rebuilds WordPress page 509 (`/group/leadership/`) from the shared Imdaad Contentful entry. Sync is manual and on-demand only, via:

- A "Revalidate from Imdaad" button in the page 509 editor (AJAX, requires `edit_post` capability).
- A `wp imdaad-leadership sync` WP-CLI command.

Nothing runs automatically (no cron). Contentful credentials are read from `wp-config.php` constants or WP options — never hardcoded in this version-controlled file. Headshots are sideloaded into the Media Library and reused on re-sync (matched by Contentful asset ID). The previous page content is always backed up to postmeta before being overwritten, and a sync that resolves no people aborts without touching the live page.

## Rationale

- Removes the manual, error-prone duplication between imdaad.ae and nigma.ae leadership content.
- Read-only Contentful access — this integration cannot write back to Imdaad's CMS.
- Manual revalidation (no cron) keeps content changes deliberate and reviewable, consistent with Production Safety First (ADR-010).
- Mirrors the pattern already proven on homepro.ae, rather than inventing a new integration approach.

## Consequences

- The Fusion Builder (Avada) shortcode markup generated by the sync is a close approximation of the page's existing layout, not a byte-for-byte copy of the original hand-built shortcode attributes — cosmetic drift is possible and should be checked visually after each sync.
- Contentful becomes a soft dependency for this one page: if Imdaad restructures the "Leadership Page" entry's content model, the sync will need updating.
- Editors must use "Revalidate from Imdaad" (or the WP-CLI command) instead of hand-editing page 509 directly; direct edits will be overwritten by the next sync.

---

# ADR-013 — Uploads and LiteSpeed Cache Ownership Matches the Actual PHP Runtime User

> **Status: Superseded by ADR-014.** The gap described here was closed on the new server by running PHP as `www-data`.

## Context

Documentation (PROJECT.md, docs/deployment.md, docs/security.md) described the intended file-ownership model as: `ubuntu` owns the codebase, `www-data` owns and writes `wp-content/uploads/` and `wp-content/litespeed/` — a deliberate split so the web-facing runtime user couldn't modify application code.

Investigating a failed image sideload in the Imdaad Leadership Sync (ADR-012), we found this was never actually true on production: OpenLiteSpeed's PHP workers (`lsphp`) run as `ubuntu`, not `www-data`. `ubuntu` is not a member of the `www-data` group. Every deploy's `sudo chown -R www-data:www-data .../uploads` (in `.github/workflows/deploy.yml`) was therefore reasserting ownership the running PHP process couldn't write to, so any in-app file write into `uploads/` (media uploads, this sync's headshot sideloads, etc.) failed with "The uploaded file could not be moved."

Because PHP already runs as `ubuntu`, which already owns and can write every other file in `wp-content/` (themes, plugins, mu-plugins), the intended `www-data` isolation was already ineffective before this fix: a compromised upload could reach the codebase via the same `ubuntu`-owned files regardless of `uploads/`'s ownership.

## Decision

Make `uploads/` ownership match reality instead of fighting it: `ubuntu:ubuntu`, same as the rest of `wp-content/` (`litespeed/` was already `ubuntu:ubuntu`). Removed the `www-data` chown step from `deploy.yml`.

Did not attempt to reconfigure OpenLiteSpeed to run PHP as `www-data` (which would have restored the originally-intended isolation) — that requires the LiteSpeed WebAdmin console or root-level vhost config access, is a larger change with more moving parts, and wasn't undertaken as part of resolving the immediate upload failure.

## Rationale

- Fixes real media/upload failures on production immediately.
- Removes a chown step that was silently fighting the actual runtime configuration on every deploy.
- Introduces no new risk: `ubuntu` already had full write access to the codebase via the PHP runtime, so this doesn't grant anything an attacker couldn't already reach.

## Consequences

- There is currently no OS-level containment between the WordPress application's runtime and its own codebase. This should be treated as a known gap, not a resolved one — see docs/security.md.
- A future, more thorough fix would reconfigure OpenLiteSpeed's PHP execution context to run as a dedicated low-privilege user (e.g. `www-data`) separate from the `ubuntu` deploy/code-owning user, restoring real isolation. Tracked as a Phase 6 candidate, not scheduled.

# ADR-014 — Hardened Graviton Host, PHP as `www-data`, and Pull-Based Deploys

## Context

On 2026-11-04 the production server was found to be compromised: malicious files had been planted in WordPress core and plugin directories, extra administrator accounts existed, and a persistent background process re-created the files when they were removed. The server ran an end-of-life operating system (Ubuntu 20.04), PHP 8.0 (end of life), WordPress core and several licence-gated premium plugins that had fallen behind, and a burstable `t3a.medium` instance that had exhausted its CPU credits for an extended period.

ADR-013 had documented a known gap: PHP ran as the code-owning `ubuntu` user, so any code execution inside WordPress could modify the code base and persist. That gap is what made the compromise durable.

The previous deploy mechanism (GitHub Actions connecting over SSH to the server) also depended on an inbound SSH rule and on private keys that had lived on the compromised host.

## Decision

1. **Rebuild on a new host instead of cleaning in place:** AWS Graviton `t4g.medium` (arm64), Ubuntu 24.04 LTS, OpenLiteSpeed 1.9.4, PHP 8.3, MariaDB 10.11, Redis object cache. Code comes from git plus freshly installed, checksum-verified WordPress core; only the audited database and the uploads folder were carried over. The Elastic IP was moved to the new host; the old host was stopped and kept for rollback and forensics.
2. **Run PHP as `www-data`** with the code read-only to it (supersedes ADR-013). PHP may write only `uploads/`, `litespeed/`, `cache/` and `.litespeed_conf.dat`. Dangerous PHP functions are disabled, `.git`, dotfiles, `wp-config.php` and `xmlrpc.php` are denied, and PHP execution is blocked inside `uploads/`.
3. **No inbound SSH.** Administration is through AWS Systems Manager. The OpenLiteSpeed WebAdmin listens on localhost only. IMDSv2 is required.
4. **Pull-based deploys.** The server runs `nigma-deploy.timer`, which fetches `origin/main` over HTTPS (the repository is public), deploys, health-checks and rolls back automatically on failure, then publishes the deployed commit at `/deploy-version.txt`. GitHub Actions verifies that file instead of connecting to the server. The previous SSH key pair and write-capable deploy key were revoked.

## Rationale

- A rebuild removes any persistence that an in-place cleanup could miss, and moves the server off unsupported software at the same time.
- Running PHP as an unprivileged user restores the isolation ADR-013 identified as missing: a compromised plugin can no longer rewrite the code base or its own persistence.
- Pull-based deploys remove the inbound port and every long-lived credential from the pipeline, and a failed deploy no longer leaves production in a half-deployed state.
- Graviton gives equal or better performance per cost, but burstable instances still need monitoring; this is tracked as a constraint.

## Consequences

- Updating plugins or themes from the WordPress admin no longer works (code is read-only to PHP). Updates are made with WP-CLI as `ubuntu` or by committing to git, which is the intended workflow.
- LiteSpeed Cache settings that need `.htaccess` changes must be applied with WP-CLI as `ubuntu`; the admin UI will show a "please add these codes" notice instead.
- The deploy script and units live in `scripts/deploy/` but are **installed on the server separately**; changing them requires re-running `scripts/deploy/install.sh` there.
- Anyone who can push to `main` can change production. Branch protection on `main` is recommended.
- Premium plugins with expired or missing licences (Gravity Forms, ACF Pro, Avada and Fusion) could not be updated and remain a risk until renewed.
- The old host is retained temporarily and should be terminated after the soak period.

# ADR-015 — Spam-URL Filtering and Canonical Host Handled at the Origin (No Cloudflare)

## Context

After the compromise (ADR-014) bots kept requesting thousands of spam "doorway" URLs and fake sitemaps that the site used to serve. Each request ran the full WordPress and Avada stack just to return a 404, which drove CPU use on a burstable instance. Separately, `nigma.ae` (apex) points straight at the server and relied on WordPress for the redirect to `www.nigma.ae`, which costs a PHP request per hit.

The client does not want Cloudflare on the `nigma.ae` domain. `www.nigma.ae` is already fronted by the QUIC.cloud CDN; the apex is not.

## Decision

Handle both at the origin with OpenLiteSpeed rewrite rules in the vhost (reference copy: `scripts/deploy/vhost-edge-rules.conf`):

- Return `410 Gone` for spam URL shapes (random alphanumeric token paths, long numeric paths, numbered sitemaps, `?sitemapNNN.xml`, and root-level `.php` files that do not exist) before PHP runs.
- Redirect `nigma.ae` to `https://www.nigma.ae` in one 301 hop, preserving path and query, and excluding `/.well-known/acme-challenge/` so certificate renewal keeps working.

## Rationale

- No dependency on a CDN or DNS change the client has declined; works for both hosts and for direct hits on the origin IP.
- 410 (rather than 404) tells search engines the pages are permanently gone, which speeds up de-indexing of the spam.
- The patterns were tested against every real URL on the site (posts, pages, terms, sitemaps, static paths) with zero matches before going live.

## Consequences

- The rules live in the server vhost config, which is **not deployed from git**. Changing them means editing the file on the server and restarting OpenLiteSpeed; keep the reference copy in sync.
- A future real URL that looks like a spam token (a path segment of 15+ lowercase letters and digits containing 4+ digits, after at least one other segment) would be answered with 410. Re-run the false-positive test (all real URLs) whenever new URL structures are introduced.
- Renewal of certificates for the apex depends on the ACME path exemption; verify it after changing the redirect rule.

# ADR-016 — Docker Runtime for New or Rebuilt Servers, Self-Building Environment, Media in Private S3

## Context

ADR-014 rebuilt production on a hardened native host, after a compromise showed how slowly a hand-built server is recreated. The project owner asked for the deployment to be able to stand up its own environment: if the required Docker environment is not present, build it first (all services running, including SSL), then deploy the code; if it is present, go straight to the code. The media library should also be recoverable on a fresh host.

Constraints: the live server is native OpenLiteSpeed and is serving customers; `AGENTS.md` and ADR-014 say production does not use Docker and that uploads are not committed; the repository is public; the media library is 400+ MB, grows with every editor upload, and Gravity Forms can store visitor-submitted files in the same folder.

## Decision

1. **Two runtimes, chosen per host** by `/etc/nigma/runtime`. No marker (or `native`) means the live server, whose behaviour is unchanged. `docker` means the stack in `docker/prod`. The deployer never starts Docker on a host that is not marked `docker`, so it cannot collide with the live OpenLiteSpeed on ports 80/443.
2. **Conditional environment bootstrap in the deployer.** On a `docker` host every deploy run first asks "is the environment present and healthy?" (`nigma-docker-bootstrap --check`: Docker, code, core, config in sync, `web`/`db`/`redis`/`certbot` healthy, certificate valid). Yes: deploy the code. No: run `nigma-docker-bootstrap` (idempotent: install Docker, secrets, checkouts, verified core, config, containers, restore data, SSL), verify, then deploy. If the environment cannot be made healthy, no code is deployed.
3. **Same stack and hardening as native:** OpenLiteSpeed 1.9.4 + PHP 8.3 (the official `litespeedtech/openlitespeed` image), MariaDB 10.11, Redis 7, PHP as `www-data`, code mounted read-only, `disable_functions`, the same edge rules (one shared file), only ports 80/443 published, no Docker socket in any container.
4. **SSL:** a placeholder certificate lets the server start; a `certbot` sidecar obtains the real certificate over HTTP-01 once a domain resolves to the host, renews it every 12 hours, and the deployer reloads the web server when it renews.
5. **Media and database live in a private, versioned, encrypted S3 bucket, not in git.** The instance role grants list/get/put only. The bootstrap restores an empty `uploads/` and an empty database from it; a daily timer backs both up. Generated assets are excluded.

## Rationale

- A self-building environment turns "rebuild the server" from a manual procedure into one command, which is what a recovery after an incident needs.
- Keeping the live server native avoids a risky migration of a working site; Docker is adopted where it is new, and can be extended to production later by a separate decision.
- Using the official image avoids maintaining our own PHP build; the repository's existing local Docker setup (nginx + PHP 8.0) is deliberately separate, because LiteSpeed Cache and the edge rules require OpenLiteSpeed.
- S3 rather than git for media: the repository is public (every upload would become public, including any future form attachments), `AGENTS.md` forbids committing uploads, 400+ MB of binaries bloat a repo and make every clone slow, and editor uploads would need an automated commit job. A private Git LFS repository was considered and rejected for storage/bandwidth cost and the same commit-job problem. S3 with versioning also provides the media backup that did not exist.

## Consequences

- Two runtimes must be maintained and documented; the deploy script and `docs/runbook.md` cover both.
- The Docker host runs a root Docker daemon: keep the OS patched, and never mount the Docker socket into a container.
- Images are referenced by tag, so a rebuild can pull newer image content; pin digests if bit-for-bit rebuilds matter.
- A new host needs an instance role with the S3 policy (`docker/prod/iam-policy.example.json`) and a DNS record pointing at it before the real certificate is issued.
- The web tier has an automated smoke test (`docker/prod/smoke-web.sh`, 42 assertions). The full stack was exercised on a throw-away arm64 host on 2026-11-04 (build from nothing, the environment-present conditional, drift and failure repair, SSL via Let's Encrypt staging, backup, and a full disaster restore from S3) and again on 2026-11-05 on arm64 and amd64 (placeholder state, a real certificate and its renewal, a production-sized restore, and the native path of the deployer). What remains untested (notably the real database and plugins inside the container) is listed in `docker/prod/README.md`. Repeat the tests after significant changes.
- Backups to S3 are only as good as their last successful run; verify with a periodic restore.
- Migrating the live server to Docker is not decided here.

---

## Future ADRs

Examples:

- Environment variable strategy.
- CI/CD implementation.
- Testing framework.
- PHP upgrades.
- Major WordPress upgrades.
- Infrastructure changes.
