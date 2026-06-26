# Project Roadmap

Version: 1.1

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

Status: **Planned**

Objectives:

- PHP_CodeSniffer
- WordPress Coding Standards
- PHPUnit
- Static analysis
- Automated code formatting
- End-to-end testing
- Performance benchmarking

## Phase 5 – Operations & Monitoring

Status: **Planned**

Objectives:

- Monitoring dashboards
- Backup verification
- Log management
- Security reviews
- Performance optimization
- Capacity planning
- Maintenance automation

## Phase 6 – Future Enhancements

Potential future initiatives:

- Staging environment
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

Phase 4 – Quality Engineering:

1. Document rollback strategy.
2. PHP_CodeSniffer with WordPress Coding Standards.
3. Static analysis.
4. Uptime monitoring and alerting.

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
