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

## Future ADRs

Examples:

- Environment variable strategy.
- CI/CD implementation.
- Testing framework.
- PHP upgrades.
- Major WordPress upgrades.
- Infrastructure changes.
