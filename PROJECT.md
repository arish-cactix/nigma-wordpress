# PROJECT.md

# NIGMA Engineering Dashboard

Version: 1.3

> This document provides a high-level snapshot of the project's current state. Unlike the roadmap, which focuses on long-term planning, this dashboard reflects the current engineering focus and is expected to change frequently.

---

## Project Status

**Status:** Active Development

Current Phase:

**Phase 5 – Operations & Monitoring**

Project Health:

- Repository established
- Documentation foundation completed
- Docker local development environment implemented
- CI/CD pipeline live (GitHub Actions → EC2)
- Content Security Policy enforced on production
- File ownership hardened on production server
- PHP_CodeSniffer + WordPress Coding Standards passing on every push
- PHPStan static analysis (level 5) passing on every push
- Deployment notifications, health check, and rollback workflow active
- Uptime monitoring active (UptimeRobot)

---

## Completed Phases

### Phase 2 – Local Development ✅

- Repository reorganized with `wp-content/` structure
- Docker Compose environment (MariaDB + WordPress PHP-FPM + nginx)
- Environment variable configuration via `.env`
- Local database import workflow (`docker/db/import-db.sh`)
- Media proxy strategy (nginx proxies missing uploads from production)
- Two-pass URL search-replace on import
- Production-only plugin deactivation on import

### Phase 3 – CI/CD ✅

- GitHub Actions deploy workflow (`.github/workflows/deploy.yml`)
- Sparse checkout on EC2 — only `wp-content/` tracked, WordPress core untouched
- Automated deployment: push to `main` → SSH → git pull → cache purge
- LiteSpeed cache cleared on every deploy
- File ownership enforced: `ubuntu` owns code, `www-data` writes only to `uploads/` and `litespeed/`
- Post-deploy health check: HTTP 200 verified after every deploy

### Security Hardening (Phase 3 addition) ✅

- `Content-Security-Policy` enforced via mu-plugin (`wp-content/mu-plugins/security-headers-csp.php`)
- Policy covers: GTM, GA, Google Fonts, YouTube, reCAPTCHA, Vimeo
- X-Frame-Options, HSTS, Referrer-Policy, Permissions-Policy via WP Defender Pro
- File permissions: `ubuntu:ubuntu` on all code, `www-data:www-data` on `uploads/` and `litespeed/` only

---

## Current Objective

Phase 5 – Operations & Monitoring: establish operational practices for a stable, observable production environment.

Primary goals:

- Log management
- Security review cadence
- Backup verification workflow
- Performance benchmarking

---

## Immediate Tasks

Priority order:

1. ~~Reorganize repository structure~~
2. ~~Docker development environment~~
3. ~~Environment variable configuration~~
4. ~~Local database workflow~~
5. ~~Media URL override~~
6. ~~Validate local development workflow~~
7. ~~GitHub Actions CI/CD~~
8. ~~Post-deploy health check~~
9. ~~Content Security Policy~~
10. ~~Document rollback strategy~~
11. ~~PHP_CodeSniffer + WordPress Coding Standards~~
12. ~~Monitoring / uptime alerting~~
13. ~~Static analysis (PHPStan)~~

---

## Current Architecture

Production

- AWS EC2
- LiteSpeed
- PHP 8.0.17
- MariaDB 10.6.7
- WordPress 6.7.1

Development

- GitHub repository (`arish-cactix/nigma-wordpress`)
- Local Docker (MariaDB 10.6 + WordPress PHP 8.0 FPM + nginx)
- Git-based workflow
- AI-assisted development

Deployment

- GitHub Actions on push to `main`
- Sparse checkout at `/var/www/nigma/` (wp-content only)
- EC2 deploy user: `ubuntu`
- WordPress root: `/var/www/nigma/`

---

## Working Principles

Every change should:

- Preserve production stability
- Follow Git workflows
- Be documented
- Consider security
- Update ADRs when required
- Update documentation when affected

---

## Active Documentation

Repository:

- README.md
- AGENTS.md
- PROJECT.md

Project Documentation:

- project-overview.md
- production-environment.md
- repository-architecture.md
- local-development.md
- deployment.md
- coding-standards.md
- security.md
- documentation-policy.md
- architecture-decisions.md
- troubleshooting.md
- roadmap.md

---

## Engineering Workflow

For every significant task:

1. Understand the requirement
2. Review relevant documentation
3. Assess architecture impact
4. Assess documentation impact
5. Assess ADR impact
6. Implement safely
7. Update documentation
8. Verify results
9. Commit related changes together

---

## Next Milestones

- Backup automation — EC2 Data Lifecycle Manager (monthly AMI, retain 3)
- Log management
- Security review cadence
- Performance benchmarking
- Phase out WP Snapshot Backups plugin (after DLM is active)

---

## Key References

Start here:

1. README.md
2. AGENTS.md
3. docs/documentation-policy.md

Then read the remaining documentation based on the task being performed.
