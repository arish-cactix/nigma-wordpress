# Deployment

Version: 1.1

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

Every deployment has a documented rollback path. Choose the option that matches the situation.

### Option 1 — Revert commit (preferred)

Use when the site is still up but the last deploy introduced a bug.

```bash
git revert HEAD --no-edit
git push origin main
```

This creates a new commit that undoes the last change and triggers the normal deploy pipeline. The rollback is permanent and traceable in git history.

To revert multiple commits:

```bash
git revert HEAD~3..HEAD --no-edit   # reverts last 3 commits
git push origin main
```

### Option 2 — Emergency rollback workflow

Use when the site is down and you need to restore immediately without waiting for a local commit.

1. Go to **GitHub → Actions → Rollback Production**
2. Click **Run workflow**
3. Paste the target commit SHA (find it in the Deploy to Production history)
4. Click **Run workflow**

The workflow SSHes to the server, resets `wp-content` to that commit, clears the cache, and runs a health check.

**Important:** This is a temporary fix. The next push to `main` will re-introduce the rolled-back code. Always follow up with a revert commit (Option 1) to make the rollback permanent.

### Finding the right commit SHA

```bash
git log --oneline -10
```

Or browse the commit history in GitHub → Code → Commits.

### After any rollback

- Verify the homepage loads correctly
- Check critical forms and login
- Confirm the LiteSpeed cache is serving fresh content
- Open a follow-up issue or PR to address the root cause

## Emergency Changes

Emergency production fixes should be rare.

When necessary:

1. Apply the minimal safe fix.
2. Commit the same change back into Git immediately.
3. Open a follow-up review if needed.

## Current Pipeline

Implemented:

- GitHub Actions deployment on push to `main`
- Sparse checkout — only `wp-content/` lands on the server
- File ownership hardening — `ubuntu` owns code, `www-data` owns `uploads/` and `litespeed/`
- LiteSpeed cache cleared on every deploy
- Post-deploy health check (HTTP 200)
- Manual rollback workflow (`workflow_dispatch`)

## Future Improvements

Planned enhancements:

- Deployment notifications (Slack or email)
- Zero-downtime deployment
- Staging environment

## Related Documents

- README.md
- AGENTS.md
- repository-architecture.md
- local-development.md
- production-environment.md
- architecture-decisions.md
