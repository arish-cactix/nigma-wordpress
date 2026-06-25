# PROJECT.md

# NIGMA Engineering Dashboard

Version: 1.0

> This document provides a high-level snapshot of the project's current state. Unlike the roadmap, which focuses on long-term planning, this dashboard reflects the current engineering focus and is expected to change frequently.

---

## Project Status

**Status:** Active Development

Current Phase:

**Phase 2 – Local Development Implementation**

Project Health:

- Repository established
- Documentation foundation completed
- Ready to begin implementation

---

## Current Objective

Build a reproducible local development environment that closely mirrors production while keeping production infrastructure isolated.

Primary goals:

- Repository reorganization
- Docker development environment
- Environment variable configuration
- Local database workflow
- Media handling strategy

---

## Immediate Tasks

Priority order:

1. Reorganize repository structure
2. Move `wp-content` into the repository root
3. Create `docker/`
4. Create `.github/`
5. Create `docker-compose.yml`
6. Create `.env.example`
7. Configure environment-based `wp-config.php`
8. Implement media URL override
9. Validate local development workflow

---

## Current Architecture

Production

- AWS EC2
- LiteSpeed
- PHP 8.0.17
- MariaDB 10.6.7
- WordPress 6.7.1

Development

- GitHub repository
- Local Docker (planned)
- Git-based workflow
- AI-assisted development

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

## Current Milestone

Complete the local development environment.

Success criteria:

- A developer can clone the repository.
- Configure a local `.env`.
- Start Docker.
- Import a development database.
- Access the application locally.
- Begin development immediately.

---

## Next Milestones

After local development:

- GitHub Actions
- Automated deployment
- Rollback support
- Testing
- Monitoring
- Performance optimization

---

## Key References

Start here:

1. README.md
2. AGENTS.md
3. docs/documentation-policy.md

Then read the remaining documentation based on the task being performed.
