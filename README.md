# NIGMA WordPress Repository

> Production application repository for **NIGMA Lifts Installation and Maintenance Co. LLC**

## Introduction

This repository contains the version-controlled application code for the NIGMA WordPress website.

Unlike a traditional WordPress repository, this project intentionally versions only the application layer required for development and deployment. WordPress core, runtime configuration, uploads, and the production database are managed separately.

## About NIGMA

NIGMA is a subsidiary of Imdaad Group providing vertical transportation, automation, and access solutions across the UAE and the Middle East.

Website: https://www.nigma.ae

## Repository Purpose

This repository is the canonical source for all deployable application code.

Its goals are to:

- Version application code safely.
- Support local Docker development.
- Support future CI/CD deployments.
- Provide consistent documentation for developers and AI coding agents.

## Repository Scope

Tracked:

- wp-content/themes/Avada
- wp-content/themes/Avada-Child-Theme
- wp-content/plugins
- wp-content/mu-plugins

Not tracked:

- uploads
- database dumps
- wp-config.php
- .env
- .htaccess
- cache
- logs
- backups

## Production Summary

- Hosting: AWS EC2
- Web Server: LiteSpeed
- PHP: 8.0.17
- Database: MariaDB 10.6.7
- WordPress: 6.7.1
- Environment: Production

## Development Workflow

Production is **not** developed directly.

Workflow:

1. Clone repository locally.
2. Develop using Docker.
3. Commit to feature branches.
4. Merge into `main`.
5. CI/CD deploys only `wp-content` to production.

## Documentation

Additional documentation is located under the `docs/` directory.

Future documents include:

- Project Overview
- Production Environment
- Repository Architecture
- Local Development
- Deployment
- Coding Standards
- Security
- Troubleshooting
- Architecture Decisions

## AI Coding Agents

Project-specific instructions for Codex CLI and other coding agents are maintained in `AGENTS.md`.

## License

Internal project owned by:

**NIGMA Lifts Installation and Maintenance Co. LLC**  
**Imdaad Group**
