
# Production Environment

Version: 1.2

## Purpose

This document defines the current production environment for the NIGMA WordPress website. It serves as the operational baseline for development, troubleshooting, upgrades, disaster recovery, and future CI/CD.

> **Living document:** Update this file whenever the production environment changes.

## Production Summary

| Component | Current Production |
|-----------|--------------------|
| Website | https://www.nigma.ae |
| Environment | Production |
| Hosting | AWS EC2 `t4g.medium` (Graviton, arm64), Mumbai region (`ap-south-1`) — moved from x86 `t3a.medium` on 2026-11-04 (ADR-014) |
| Operating System | Ubuntu 24.04 LTS |
| Web Server | OpenLiteSpeed 1.9.4 |
| PHP | 8.3 (LSAPI `lsphp83`), runs as `www-data`, `memory_limit` 256M |
| Database | MariaDB 10.11 |
| Object cache | Redis 7 (via LiteSpeed Cache) |
| WordPress | 7.1.4 *(Update when production changes)* |
| CDN | QUIC.cloud in front of `www.nigma.ae`; `nigma.ae` points straight at the Elastic IP |
| HTTPS | Enabled (Let's Encrypt, `certbot`, webroot challenge) |
| Multisite | No |
| Timezone | Asia/Dubai |

## Server Layout

Application Root

```
/var/www/nigma
```

Version-controlled application code

```
/var/www/nigma/wp-content
```

The git checkout is at `/var/www/nigma` with a sparse checkout limited to `wp-content/`. WordPress core is installed separately (not in git) and is verified with `wp core verify-checksums`.

Ownership model (ADR-014)

- Code (`wp-content/plugins`, `themes`, `mu-plugins`, core files, `.htaccess`, `wp-config.php`): owned by `ubuntu`, read-only to PHP.
- PHP (`www-data`) can write only `wp-content/uploads/`, `litespeed/`, `cache/` and `.litespeed_conf.dat`.
- `wp-content/object-cache.php` (LiteSpeed Redis drop-in) is an untracked copy of the plugin's `lib/object-cache.php`, kept read-only to PHP. Re-copy it after a LiteSpeed Cache update.
- Changes to LiteSpeed Cache settings that need `.htaccess` rules must be applied with WP-CLI as `ubuntu` (`wp litespeed-option set …`).

Hardening

- `disable_functions` blocks `exec`, `system`, `shell_exec`, `passthru`, `proc_open`, `popen`. Run WP-CLI with `WP_CLI_PHP_ARGS=-ddisable_functions=`.
- The vhost denies `.git`, dotfiles, `wp-config.php`, `xmlrpc.php` and PHP execution inside `uploads/`.
- The OpenLiteSpeed WebAdmin listens on `127.0.0.1` only.
- Edge rules in the vhost (reference: `scripts/deploy/vhost-edge-rules.conf`, ADR-015): `410 Gone` for spam-URL shapes, and `nigma.ae` → `https://www.nigma.ae` in one 301 hop. The vhost config is not deployed from git.

Deploys

- Pull-based via `nigma-deploy.timer` (see `docs/deployment.md`).

## Technology Stack

Infrastructure

- AWS EC2 (Graviton `t4g.medium`), Amazon EBS
- Administration through AWS Systems Manager (SSM) Session Manager / Run Command; **no inbound SSH**
- Security group allows only ports 80 and 443; IMDSv2 required
- Elastic IP `3.7.4.103`; DNS is managed at Etisalat (client-controlled)

Application

- WordPress
- Avada
- Avada Child Theme

Core Plugins

- Advanced Custom Fields Pro
- Gravity Forms
- Avada Builder
- Avada Core
- LiteSpeed Cache
- Rank Math SEO
- Defender Pro
- Snapshot Pro
- Branda Pro
- WP Migrate
- Post SMTP

## Repository Boundary

Version controlled:

- themes/
- plugins/
- mu-plugins/

Not version controlled:

- uploads/
- database dumps
- wp-config.php
- .env
- .htaccess
- logs
- cache
- backups

## Landing Pages

- **Home lift campaign page** (static HTML + Eloqua form): `/var/www/homelift/` (`index.html`, `thank-you/index.html`), served by a separate OpenLiteSpeed virtual host, `marketing`, with **PHP disabled**. Currently reachable only on the test domain `https://7898.cactixcloud.com/homelift/` (`noindex`; its own Let's Encrypt certificate `marketing-test`).
- It is **not in git** and is not touched by the deploy pipeline. A master copy should be committed (for example under a dedicated folder) before further edits.
- Going live on `marketing.nigma.ae` still needs the client to add the DNS record, a certificate, and a mapping for that hostname on both listeners.

## Operational Principles

- Production is the source of truth for runtime state.
- Development occurs locally.
- GitHub is the canonical source for application code.
- Production deployments should originate from CI/CD.
- Every production deployment should include backup, rollback, and verification.

## Software Lifecycle Policy

### WordPress Core

- Keep WordPress reasonably current.
- Do not update immediately on every release.
- Review and upgrade approximately every **3–4 months** after releases have matured.
- Always validate upgrades locally before production deployment.

### PHP

- Upgrade only after confirming compatibility with:
  - Avada
  - ACF Pro
  - Gravity Forms
  - LiteSpeed Cache
  - Rank Math SEO
  - WPMU DEV plugins
  - Custom code

### Plugins & Themes

- Review updates regularly.
- Prefer scheduled maintenance windows.
- Test locally before production.
- Batch compatible updates instead of applying every individual release immediately.

### Database

- Upgrade conservatively.
- Validate backups and rollback procedures before major version changes.

## Known Constraints

- Production uses LiteSpeed-specific caching behaviour.
- Premium plugins and themes require licence-aware maintenance.
- Uploads remain outside version control.
- `t4g` instances are burstable: monitor CPU credit balance (CloudWatch `CPUCreditBalance`).
- Some premium plugins cannot be updated from the server until their licences are valid (Gravity Forms, ACF Pro, Avada/Fusion); see `docs/security.md`.

## Future Improvements

- Docker-based local development
- GitHub Actions CI/CD
- Environment variable configuration
- Automated deployment verification
- Deployment rollback automation
- PHP upgrade planning
- WordPress lifecycle tracking

## Related Documents

- README.md
- AGENTS.md
- project-overview.md
- repository-architecture.md
- deployment.md
- architecture-decisions.md
