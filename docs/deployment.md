# Deployment

Version: 1.0

## Purpose

This document defines the deployment strategy for the NIGMA WordPress project. It establishes how application code moves from a developer workstation to the production environment safely and consistently.

> Production deployments should always originate from Git and, in the future, be automated through CI/CD.

## Deployment Philosophy

The production server is a runtime environment, not a development environment.

Principles:

- GitHub is the canonical source of application code.
- Development is performed locally.
- Changes are reviewed before reaching production.
- Deploy only the application layer (`wp-content`).
- Every deployment should have a rollback strategy.

## Branching Strategy

Recommended branches:

- `main` – production-ready code
- `feature/<name>` – new features
- `fix/<name>` – bug fixes
- `hotfix/<name>` – urgent production fixes
- `release/<version>` – optional release preparation

## Development Workflow

1. Clone the repository.
2. Create a feature branch.
3. Develop locally using Docker.
4. Test locally.
5. Commit changes.
6. Push the branch.
7. Open a Pull Request.
8. Review and merge into `main`.
9. CI/CD deploys the updated `wp-content`.

## Deployment Scope

Deploy:

- `wp-content/themes`
- `wp-content/plugins`
- `wp-content/mu-plugins`
- `wp-content/index.php`

Do **not** deploy:

- `uploads`
- `database`
- `wp-config.php`
- `.env`
- `.htaccess`
- `docker/`
- `docs/`
- `README.md`
- `AGENTS.md`

## CI/CD Vision

Target pipeline:

1. Trigger on merge to `main`.
2. Validate repository.
3. Create or verify backup.
4. Synchronize `wp-content` to production.
5. Preserve excluded files and directories.
6. Purge LiteSpeed cache if required.
7. Perform post-deployment verification.
8. Report deployment status.

## Production Safety Checklist

Before deployment:

- Verify changes have been reviewed.
- Confirm backups are available.
- Validate local testing.
- Confirm target branch is `main`.

After deployment:

- Verify homepage.
- Verify critical forms.
- Verify login.
- Verify sitemap and SEO behaviour.
- Check server logs for errors.

## Rollback Strategy

Every deployment should have a documented rollback path.

Preferred options:

- Restore previous Git revision.
- Restore from backup if required.
- Verify application health after rollback.

## Emergency Changes

Emergency production fixes should be rare.

When necessary:

1. Apply the minimal safe fix.
2. Commit the same change back into Git immediately.
3. Open a follow-up review if needed.

## Future Improvements

Planned enhancements:

- GitHub Actions deployment.
- Zero-downtime deployment.
- Automated rollback.
- Deployment notifications.
- Smoke testing after deployment.

## Related Documents

- README.md
- AGENTS.md
- repository-architecture.md
- local-development.md
- production-environment.md
- architecture-decisions.md
