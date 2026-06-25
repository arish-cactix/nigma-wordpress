# Local Development

Version: 1.0

## Purpose

This document defines the local development environment for the NIGMA WordPress project. It serves as the blueprint for Docker, development workflows, and future automation.

> Production never uses Docker. Docker is a local development tool only.

## Objectives

The local environment should:

- Mirror production behaviour where practical.
- Be reproducible on any developer workstation.
- Keep production configuration isolated.
- Allow safe experimentation without affecting production.
- Support future CI/CD workflows.

## Development Principles

- Never develop directly on production.
- GitHub is the canonical source for application code.
- Docker exists only for local development.
- WordPress core should be supplied by Docker, not committed to Git.
- The repository's `wp-content/` directory is mounted into the WordPress container.

## Target Repository Structure

```text
nigma-wordpress/
├── docker/
├── docs/
├── wp-content/
├── docker-compose.yml
├── .env.example
├── README.md
└── AGENTS.md
```

## Docker Design Goals

The Docker environment should provide:

- WordPress
- PHP
- MariaDB
- phpMyAdmin (optional)
- Mail testing (optional)

Additional developer tools may be added if they improve productivity without complicating the setup.

## WordPress Core

WordPress core is **not** version controlled.

It should be provided by the Docker image or installed automatically during environment initialization.

## Database Strategy

The production database is never used directly.

Local development should use:

- a sanitized development database, or
- a manually imported production snapshot.

Database dumps are intentionally excluded from version control.

## Media Strategy

The `uploads/` directory is excluded from Git.

Preferred approaches:

1. Proxy media requests to the production website.
2. Synchronize uploads manually when required.

The local environment should remain functional even when uploads are unavailable.

## Environment Configuration

Secrets should never be committed.

Examples:

- database credentials
- API keys
- SMTP credentials
- third-party service tokens

Local configuration should be supplied using environment variables.

## Development Workflow

Recommended workflow:

1. Clone repository.
2. Configure `.env`.
3. Start Docker.
4. Import development database.
5. Develop locally.
6. Commit changes.
7. Push feature branch.
8. Open Pull Request.
9. Merge into `main`.
10. CI/CD deploys application code.

## Git Policy

Commit only application code and documentation.

Never commit:

- uploads
- cache
- logs
- database dumps
- secrets

## Future Improvements

- One-command environment bootstrap.
- Automated WordPress installation.
- Local HTTPS support.
- Development mail catcher.
- Xdebug support.
- PHPUnit integration.
- Playwright or Cypress end-to-end testing.

## Related Documents

- README.md
- AGENTS.md
- repository-architecture.md
- deployment.md
- production-environment.md
