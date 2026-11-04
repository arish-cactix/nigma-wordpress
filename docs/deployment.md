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
- `deploy-version.txt` (written by the server, not by git)
- `docker/`
- `scripts/` (installed on the server separately)
- `docs/`
- `README.md`
- `AGENTS.md`

## How Deploys Work (pull-based)

> Step-by-step procedures (deploy, verify, roll back, update plugins, certificates, troubleshooting) are in [runbook.md](runbook.md).

Production pulls its own code; GitHub does not push to it.

1. A change is merged to `main`.
2. `nigma-deploy.timer` on the server runs `/usr/local/sbin/nigma-deploy` every minute. It fetches `origin/main` over HTTPS (the repository is public, so no credentials are involved) and fast-forwards `/var/www/nigma` when there is a new commit.
3. It fixes file ownership (code is read-only to PHP; only `uploads/`, `litespeed/`, `cache/` and `.litespeed_conf.dat` are writable by `www-data`), clears the LiteSpeed page cache, the `wp-content/litespeed` asset cache and the Redis object cache, then health-checks `/` and a search page.
4. If the health check fails, it **rolls back automatically** to the previous commit and will not retry the failed commit until a newer one arrives.
5. On success it publishes the deployed commit at `https://www.nigma.ae/deploy-version.txt`.
6. The **Deploy to Production** workflow waits until that file shows the pushed commit (or a newer one containing it), runs an HTTP health check and sends the notification email. A red run therefore means production is *not* on the new commit.

The server needs no inbound SSH port and GitHub holds no server credentials. Anyone who can push to `main` can change production, as before, so protect `main` accordingly.

Source files live in `scripts/deploy/` (`nigma-deploy.sh`, `nigma-deploy.service`, `nigma-deploy.timer`, `install.sh`). The server does not check out `scripts/`, so a change to them takes effect only after it is re-installed on the server (run `install.sh` as root, via SSM Session Manager or Run Command).

Useful commands on the server (via SSM, as root):

```bash
nigma-deploy --status           # current / live / pinned / last-failed commit
tail -f /var/log/nigma-deploy.log
systemctl list-timers nigma-deploy.timer
```

## Docker runtime (new or rebuilt hosts)

A host chooses its runtime with `/etc/nigma/runtime`: absent or `native` is the live server (OpenLiteSpeed installed directly; behaviour unchanged), `docker` is the stack in `docker/prod` (ADR-016). On a `docker` host every deploy run first checks that the environment exists and is healthy:

```
environment present and healthy?  yes -> deploy the code
                                  no  -> build / repair the environment (all containers + SSL), verify, then deploy the code
```

If the environment cannot be made healthy, no code is deployed. Setup, SSL, and the S3-backed media library are described in `docker/prod/README.md` and `docs/runbook.md`.

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

### Option 2 — Rollback workflow

Use when you want to restore an earlier `wp-content` state without writing the commit yourself.

1. Go to **GitHub → Actions → Rollback Production**
2. Click **Run workflow**
3. Paste the target commit SHA (find it in the Deploy to Production history)
4. Click **Run workflow**

The workflow commits the `wp-content` tree of that commit on top of `main` ("Rollback: restore wp-content to …"). The normal pull-based deploy then applies it, so the rollback is **permanent and visible in git history** — there is no temporary state to clean up afterwards.

### Option 3 — Emergency rollback on the server

Use when GitHub is unavailable or the deploy pipeline itself is broken. Run on the server via SSM Session Manager, as root:

```bash
nigma-deploy --pin <good-sha>   # deploys that commit and pauses auto-deploys
nigma-deploy --unpin            # later: resume deploying origin/main
```

While pinned, pushes to `main` are **not** deployed. Always unpin once the underlying problem is fixed on `main`.

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

- Pull-based deploy: the server fetches `origin/main` every minute (see ADR-014)
- Sparse checkout — only `wp-content/` lands on the server
- File ownership hardening — code owned by `ubuntu` and read-only to PHP; PHP runs as `www-data` and can write only `uploads/`, `litespeed/`, `cache/` and `.litespeed_conf.dat` (ADR-014, supersedes ADR-013)
- LiteSpeed page cache, asset cache and Redis object cache cleared on every deploy
- Server-side health check with automatic rollback, and `/deploy-version.txt` verification
- Post-deploy health check and notification email from GitHub Actions
- Rollback workflow (`workflow_dispatch`) that commits the restored tree to `main`; emergency `nigma-deploy --pin` on the server

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
- runbook.md
- architecture-decisions.md
