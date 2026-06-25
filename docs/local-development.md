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
├── database/              # local-only, ignored
├── docs/
├── wp-content/
│   ├── index.php
│   ├── themes/
│   ├── plugins/
│   └── mu-plugins/
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

## Current Docker Services

The local Docker environment currently defines:

- `db` using MariaDB 10.6 with a named volume for database state.
- `wordpress` using the official WordPress PHP 8.0 FPM image.
- `web` using nginx as the local browser-accessible HTTP service.

Local HTTP access defaults to `http://localhost:8080` through `LOCAL_HTTP_PORT`.

WordPress core is stored in a Docker named volume. The repository-owned `wp-content/index.php`, `wp-content/plugins/`, `wp-content/themes/`, and `wp-content/mu-plugins/` paths are mounted individually so the official WordPress image does not copy bundled default plugins or themes into the repository.

## WordPress Core

WordPress core is **not** version controlled.

It should be provided by the Docker image or installed automatically during environment initialization.

## Database Strategy

The production database is never used directly.

Local development should use:

- a sanitized development database, or
- a manually imported production snapshot.

Database dumps are intentionally excluded from version control. Local SQL exports/imports may be kept in the root-level `database/` directory, which is ignored by Git and must not be deployed.

To import a local dump into the Docker MariaDB service, place a `.sql` or `.sql.gz` file in `database/` and run:

```bash
./docker/db/import-db.sh
```

The script imports the newest dump by default and resets the local database named by `MARIADB_DATABASE`. A specific dump can be passed as an argument.

## Media Strategy

The `uploads/` directory is excluded from Git.

Two mechanisms work together to keep the local environment fully functional without a copy of the uploads directory:

1. **URL replacement** — `import-db.sh` runs a WP-CLI search-replace after every import, rewriting all production URLs (`https://www.nigma.ae`) to the local URL (`http://localhost:8080`). This ensures WordPress generates local URLs for all media references.

2. **nginx proxy** — the local nginx configuration intercepts all `/wp-content/uploads/` requests. Local files take precedence; any file not present locally is transparently proxied from the production website (`www.nigma.ae`).

If a local file copy is needed, uploads can be synchronised manually into `wp-content/uploads/`, which is gitignored.

## Environment Configuration

Secrets should never be committed.

Examples:

- database credentials
- API keys
- SMTP credentials
- third-party service tokens

Local configuration should be supplied using environment variables.

## Development Workflow

### First-time setup on a new machine

```bash
git clone <repo-url>
cd nigma-wordpress
cp .env.example .env
# Edit .env and set real passwords
# Place a .sql or .sql.gz dump in database/
docker compose up -d
./docker/db/import-db.sh
```

Then open `http://localhost:8080`.

The import script handles URL replacement, Avada cache clearing, and deactivating production-only plugins (LiteSpeed Cache, PWA, WP Defender, Gravity Forms reCAPTCHA). It requires an internet connection on first run to download WP-CLI.

### Ongoing workflow

1. Clone repository.
2. Configure `.env`.
3. Start Docker and import database (see above).
4. Develop locally.
5. Commit changes.
6. Push feature branch.
7. Open Pull Request.
8. Merge into `main`.
9. CI/CD deploys application code.

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
