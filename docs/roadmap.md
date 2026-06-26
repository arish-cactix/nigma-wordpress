# Project Roadmap

Version: 1.2

## Purpose

This roadmap outlines the planned evolution of the NIGMA WordPress engineering project. It provides a phased view of completed work, current priorities, and future enhancements.

> This is a living document. Update it as milestones are completed, priorities change, or new initiatives are approved.

## Project Vision

Build a well-documented, production-safe, AI-assisted WordPress engineering platform with:

- Reproducible local development
- Git-based workflows
- Automated CI/CD
- Strong documentation
- Secure deployment practices
- High maintainability

## Phase 1 – Engineering Foundation ✅

Status: **Completed**

Achievements:

- Git repository initialized
- GitHub repository established
- Version control strategy defined
- `.gitignore` created
- Premium themes tracked
- Premium plugins tracked
- Deployment boundary defined (`wp-content`)
- Engineering documentation created
- AI agent workflow established
- Documentation governance established
- Architecture Decision Record (ADR) process established

## Phase 2 – Local Development

Status: **Completed** ✅

Achievements:

- Repository reorganized with `wp-content/` structure
- Docker Compose environment (MariaDB + WordPress PHP-FPM + nginx)
- Environment variable configuration via `.env`
- Local database import workflow with two-pass URL search-replace
- Media proxy: nginx proxies missing uploads from production
- Production-only plugin deactivation on import

## Phase 3 – Continuous Integration & Deployment

Status: **Completed** ✅

Achievements:

- GitHub Actions deploy workflow (push to `main` → SSH → EC2)
- Sparse checkout on EC2 — only `wp-content/` tracked, WordPress core untouched
- LiteSpeed cache cleared on every deploy (rm -rf + recreate)
- File ownership enforced: `ubuntu` owns code, `www-data` writes only to `uploads/` and `litespeed/`
- Post-deploy health check: HTTP 200 verified after every deploy
- Content Security Policy enforced via mu-plugin (covers GTM, GA, Google Fonts, YouTube, reCAPTCHA, Vimeo)
- X-Frame-Options, HSTS, Referrer-Policy via WP Defender Pro

## Phase 4 – Quality Engineering

Status: **Completed** ✅

Achievements:

- PHP_CodeSniffer with WordPress Coding Standards on every push and PR
- Scans `mu-plugins/security-headers-csp.php` and `themes/Avada-Child-Theme`
- Excludes third-party mu-plugins and template files (header.php, footer.php, templates/)
- PHPStan static analysis at level 5 with WordPress 6.x stubs
- Deployment notifications via email (Gmail SMTP) on every deploy and rollback
- Manual rollback workflow (`workflow_dispatch`) with health check
- Uptime monitoring via UptimeRobot (external, continuous)

## Phase 5 – Operations & Monitoring

Status: **In Progress**

Achieved:

- Uptime monitoring (UptimeRobot)
- Deploy and rollback email notifications
- Post-deploy health check in CI
- PHP error logging to `wp-content/debug.log` via mu-plugin (silent, no visitor exposure)
- Log rotation via logrotate: weekly, 4 weeks retained, compressed
- Security review cadence documented: monthly, quarterly, and annual checklists

Remaining:

- **Backup automation** *(pending)* — configure EC2 Data Lifecycle Manager: monthly AMI, retain 3 minimum. Restore procedure: launch new EC2 instance from EBS snapshot. WP Snapshot Backups plugin is secondary and will be removed once DLM is active.
- **Log management** ✅ — PHP error logging active via `mu-plugins/error-logging.php`, writes silently to `wp-content/debug.log`. Log rotation installed on deploy: weekly, 4 weeks retained, compressed. View logs: `tail -n 100 /var/www/nigma/wp-content/debug.log`.
- **Security review cadence** ✅ — monthly, quarterly, and annual checklists documented in `docs/security-review.md`
- Performance benchmarking

## Phase 6 – Future Enhancements

Potential future initiatives:

- Blue/Green deployments
- Zero-downtime deployments
- Canary releases
- Automated dependency updates
- Infrastructure as Code
- Enhanced observability

## Guiding Principles

Every phase should:

- Protect production stability.
- Preserve documentation quality.
- Follow Git workflows.
- Update ADRs when architecture changes.
- Update documentation alongside code.
- Prioritize maintainability over short-term convenience.

## Current Priority

Phase 5 – Operations & Monitoring:

1. Backup automation (EC2 Data Lifecycle Manager).
2. Security review cadence.
3. Performance benchmarking.

## Success Criteria

The project will be considered mature when:

- Local development is reproducible.
- Deployments are automated.
- Documentation remains current.
- AI coding agents can contribute consistently.
- Production changes are traceable and reversible.
- New developers can onboard with minimal manual setup.

## Related Documents

- README.md
- AGENTS.md
- docs/documentation-policy.md
- docs/repository-architecture.md
- docs/local-development.md
- docs/deployment.md
- docs/architecture-decisions.md
