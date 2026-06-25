# Repository Architecture

Version: 1.0

## Purpose

This document explains the architecture of the NIGMA WordPress repository and the reasoning behind its structure. It complements `README.md` by documenting **why** the repository is organized this way.

## Repository Philosophy

This repository is the **application repository**, not a complete WordPress installation.

The objective is to version only the code that belongs to the application while keeping environment-specific assets and runtime state outside Git.

## Target Repository Structure

```text
nigma-wordpress/
├── .github/
│   └── workflows/
├── docker/
├── docs/
├── wp-content/
│   ├── index.php
│   ├── themes/
│   ├── plugins/
│   └── mu-plugins/
├── README.md
├── AGENTS.md
├── docker-compose.yml
├── .env.example
└── .gitignore
```

## Why Only `wp-content`

The application-specific code lives inside `wp-content`.

Version controlled:

- Themes
- Plugins
- MU Plugins

Environment-specific components are intentionally excluded:

- WordPress core
- Uploads
- Database
- Server configuration
- Secrets

## Key Architectural Decisions

### Git Repository

The production repository was initialized inside `wp-content`.

The local repository will evolve into the structure shown above while preserving `wp-content` as the deployable application directory.

### Themes

Track:

- Avada
- Avada Child Theme

Reason:

- Avada is a licensed premium theme.
- The child theme contains project customizations.
- Keeping both under version control ensures reproducible local environments.

### Plugins

Track all project plugins, including premium plugins.

Reason:

- The repository is private.
- Local development should closely mirror production.
- New developers should not need to manually obtain premium plugins.

### Uploads

Do not version `uploads/`.

Reason:

- Large and continuously changing.
- Runtime content rather than application code.
- Can be served from production or synchronized separately for local development.

### Database

Do not commit database dumps.

Reason:

- The database is runtime state.
- CI/CD deploys code, not content.
- Full SQL dumps do not belong in normal application history.

Migration scripts may be versioned in the future if a migration strategy is adopted.

### Environment Configuration

Keep environment-specific configuration outside Git.

Examples:

- wp-config.php
- .env
- .htaccess
- API keys
- SMTP credentials

## Deployment Model

The intended deployment flow is:

```text
Developer
    ↓
Feature Branch
    ↓
Pull Request
    ↓
main
    ↓
GitHub Actions
    ↓
Production EC2
```

Only `wp-content/` is deployed to production.

Documentation, Docker configuration, and CI/CD definitions remain in the repository but are never copied to the production web root.

## Local Development

Docker exists only for local development.

The repository should mount `wp-content/` into the WordPress container while WordPress core is supplied by the container image.

## Documentation Strategy

Repository documentation is organized as follows:

- README.md
- AGENTS.md
- docs/project-overview.md
- docs/production-environment.md
- docs/repository-architecture.md
- docs/local-development.md
- docs/deployment.md
- docs/coding-standards.md
- docs/security.md
- docs/troubleshooting.md
- docs/architecture-decisions.md

This structure is intentional and should remain stable as the project evolves.

## Related Documents

- README.md
- AGENTS.md
- project-overview.md
- production-environment.md
