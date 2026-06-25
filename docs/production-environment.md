
# Production Environment

Version: 1.1

## Purpose

This document defines the current production environment for the NIGMA WordPress website. It serves as the operational baseline for development, troubleshooting, upgrades, disaster recovery, and future CI/CD.

> **Living document:** Update this file whenever the production environment changes.

## Production Summary

| Component | Current Production |
|-----------|--------------------|
| Website | https://www.nigma.ae |
| Environment | Production |
| Hosting | AWS EC2 (Mumbai Region) |
| Operating System | Ubuntu Linux |
| Web Server | LiteSpeed |
| PHP | 8.0.17 (LiteSpeed SAPI) |
| Database | MariaDB 10.6.7 |
| WordPress | 6.7.1 *(Update when production changes)* |
| HTTPS | Enabled |
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

Git is intentionally initialized inside `wp-content`.

## Technology Stack

Infrastructure

- AWS EC2
- Amazon EBS
- SSH administration

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
