# AGENTS.md

# NIGMA AI Coding Agent Instructions

Version: 2.0

## Purpose

This document defines how Codex CLI and other AI coding agents must operate inside the NIGMA WordPress repository.

AI agents should treat this repository as a production-backed engineering project, not as a generic WordPress sandbox.

## Required Reading Order

Before making non-trivial changes, read the relevant project documentation in this order:

1. `README.md`
2. `AGENTS.md`
3. `docs/project-overview.md`
4. `docs/repository-architecture.md`
5. `docs/documentation-policy.md`
6. `docs/coding-standards.md`

Then read additional documents depending on the task:

| Task Type | Required Document |
|----------|-------------------|
| Production or server changes | `docs/production-environment.md` |
| Docker or local setup | `docs/local-development.md` |
| Deployment or CI/CD | `docs/deployment.md` |
| Security-sensitive changes | `docs/security.md` |
| Troubleshooting | `docs/troubleshooting.md` |
| Architectural decisions | `docs/architecture-decisions.md` |

## Project Context

Website: https://www.nigma.ae

Company: NIGMA Lifts Installation and Maintenance Co. LLC

Parent Company: Imdaad Group

NIGMA provides elevators, escalators, home lifts, cargo lifts, car lifts, automatic doors, access systems, maintenance, modernization, repairs, and automation solutions across the UAE and Middle East.

## Repository Scope

This repository is the application repository for the NIGMA WordPress website.

It is not a full WordPress installation.

Tracked application code:

```text
wp-content/themes/Avada/
wp-content/themes/Avada-Child-Theme/
wp-content/plugins/
wp-content/mu-plugins/
```

Excluded runtime/environment data:

```text
uploads/
database dumps
wp-config.php
.env
.htaccess
cache
logs
backups
```

## Production Baseline

Production runs on:

- AWS EC2
- LiteSpeed
- PHP 8.0.17
- MariaDB 10.6.7
- WordPress 6.7.1

Production does not use Docker.

Docker is for local development only.

## Core Operating Rules

AI agents must:

- Prefer safe, incremental changes.
- Never modify WordPress core.
- Prefer changes inside `wp-content/themes/Avada-Child-Theme/`.
- Avoid modifying `wp-content/themes/Avada/` unless explicitly justified.
- Avoid adding new plugins unless explicitly approved.
- Preserve SEO, performance, accessibility, and security.
- Preserve backward compatibility.
- Avoid direct production assumptions.
- Explain the impact of risky changes before suggesting them.

## Production Safety Rules

Production is live.

For production-related work:

- Diagnose before fixing.
- Prefer read-only diagnostics first.
- Give one safe next action at a time.
- Consider backups before risky operations.
- Consider rollback before implementation.
- Preserve uploads.
- Preserve database.
- Preserve `wp-config.php`.
- Preserve `.htaccess`.

## Deployment Rules

CI/CD should deploy only:

```text
wp-content/
```

CI/CD must not deploy:

```text
docker/
docs/
README.md
AGENTS.md
.env
.env.example
database dumps
uploads/
wp-config.php
.htaccess
```

Deployment logic must preserve production runtime state.

## Documentation Impact Assessment

Before completing any task, determine whether documentation must be updated.

If the task affects any of the following, update the relevant document in the same change set:

| Change | Documentation |
|--------|---------------|
| Business/project scope | `docs/project-overview.md` |
| Production environment | `docs/production-environment.md` |
| Repository layout | `docs/repository-architecture.md` |
| Local Docker workflow | `docs/local-development.md` |
| Deployment or CI/CD | `docs/deployment.md` |
| Coding conventions | `docs/coding-standards.md` |
| Security policy | `docs/security.md` |
| New troubleshooting knowledge | `docs/troubleshooting.md` |
| Documentation workflow | `docs/documentation-policy.md` |
| AI workflow | `AGENTS.md` |
| Major engineering decision | `docs/architecture-decisions.md` |

If no documentation update is required, be prepared to explain why.

## ADR Workflow

When a change affects long-term architecture, deployment strategy, Docker architecture, security posture, repository structure, database handling, production operations, or development workflow, create or update an ADR in:

```text
docs/architecture-decisions.md
```

For each ADR, capture:

- Context
- Decision
- Rationale
- Consequences

Do not create ADRs for minor implementation details.

## Code Change Workflow

For non-trivial changes:

1. Understand the task.
2. Read the relevant documentation.
3. Identify impacted files.
4. Assess production impact.
5. Assess documentation impact.
6. Assess ADR impact.
7. Make the smallest safe change.
8. Update affected documentation.
9. Verify results.
10. Summarize changes clearly.

## Troubleshooting Workflow

When troubleshooting:

1. Start with the most likely cause.
2. Provide only one safe next action at a time.
3. Wait for command output before suggesting further actions.
4. Prefer diagnostics before fixes.
5. Explain what each command does.
6. Identify whether the issue should be added to `docs/troubleshooting.md`.

## WordPress Development Rules

Use WordPress APIs for:

- Hooks and filters
- Script and style enqueueing
- Options
- Metadata
- REST endpoints
- AJAX
- Shortcodes
- Escaping
- Sanitization
- Database access

Avoid hardcoding environment-specific values.

## Theme Development Rules

Preferred customization location:

```text
wp-content/themes/Avada-Child-Theme/
```

Avoid modifying the Avada parent theme unless:

- There is no safer alternative.
- The reason is documented.
- The change is reviewed carefully.
- ADR impact is considered.

## Plugin Development Rules

Before creating or modifying plugin code:

- Prefer existing WordPress or plugin APIs.
- Keep custom logic focused.
- Avoid editing vendor/plugin code unless necessary.
- Document dependencies and side effects.

## Security Rules

Never expose or commit:

- API keys
- Database credentials
- SMTP credentials
- Passwords
- Private keys
- `.env`
- `wp-config.php`

Security-sensitive changes must be documented and reviewed.

## Performance Rules

Consider:

- LiteSpeed Cache behaviour
- Front-end asset size
- Database query cost
- Plugin overhead
- Mobile performance
- Image loading
- SEO impact

## Local Development Rules

Docker is local-only.

Local development should:

- Use Docker Compose.
- Mount repository `wp-content`.
- Use local or sanitized database snapshots.
- Avoid connecting directly to production database.
- Serve uploads remotely or sync them separately.

## Git Rules

Use feature branches for development.

Do not commit:

- uploads
- database dumps
- cache
- logs
- backups
- secrets

Commit documentation updates together with related code changes.

## Final Response Expectations

When reporting work, summarize:

- What changed.
- Why it changed.
- Files affected.
- Testing or verification performed.
- Documentation updated.
- ADR updated or not required.

## Related Documentation

- `README.md`
- `docs/project-overview.md`
- `docs/production-environment.md`
- `docs/repository-architecture.md`
- `docs/local-development.md`
- `docs/deployment.md`
- `docs/coding-standards.md`
- `docs/security.md`
- `docs/documentation-policy.md`
- `docs/architecture-decisions.md`
- `docs/troubleshooting.md`
