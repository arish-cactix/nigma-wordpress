# Security

Version: 1.0

## Purpose

This document defines the security principles, operational policies, and development practices for the NIGMA WordPress project. It is intended to guide developers, DevOps engineers, and AI coding agents in maintaining a secure application and deployment process.

> Security is a continuous process. This document should be reviewed and updated whenever the security posture, infrastructure, or deployment workflow changes.

## Security Principles

The project follows these guiding principles:

- Production safety before convenience.
- Least privilege.
- Defense in depth.
- Secure by default.
- Version control is not a secret store.
- Every security-sensitive change should be documented.

## Secrets Management

Never commit:

- `.env`
- `wp-config.php`
- API keys
- Database credentials
- SMTP credentials
- Private certificates
- SSH private keys
- Cloud access keys

Environment-specific secrets must be injected through environment variables or secure infrastructure mechanisms.

## Git Security

- The Git repository is private.
- Use SSH authentication.
- Protect the `main` branch.
- Prefer Pull Requests over direct pushes.
- Review security-sensitive changes before merging.

## Production Server

Production runs on AWS EC2 (Graviton, Ubuntu 24.04). See `docs/production-environment.md`.

Controls in place:

- **No inbound SSH.** The security group allows only ports 80 and 443. Administration is through AWS Systems Manager (SSM) Session Manager and Run Command.
- IMDSv2 is required on the instance.
- The OpenLiteSpeed WebAdmin console listens on `127.0.0.1` only.
- Redis and MariaDB listen on localhost only.
- PHP `disable_functions` blocks command-execution functions (`exec`, `system`, `shell_exec`, `passthru`, `proc_open`, `popen`).
- The web server denies `.git`, dotfiles, `wp-config.php` and `xmlrpc.php`, and blocks PHP execution inside `uploads/`.
- Spam and probe URL shapes are answered with `410 Gone` before PHP runs (ADR-015).

Recommendations:

- Apply operating system security updates during maintenance windows.
- Monitor disk usage, system health and the CPU credit balance (burstable instance).

## WordPress Security

- Never modify WordPress core directly.
- Remove unused plugins and themes.
- Keep active plugins reviewed regularly.
- Validate compatibility before upgrades.
- Use strong administrator credentials.
- Enable two-factor authentication where available.

## Plugin & Theme Security

Before introducing or updating plugins/themes:

- Verify source and authenticity.
- Review maintenance status.
- Test in the local environment.
- Prefer actively maintained software.

## File Permissions

Ownership model (ADR-014, supersedes ADR-013):

- Code (`plugins/`, `themes/`, `mu-plugins/`, WordPress core, `.htaccess`, `wp-config.php`): owned by `ubuntu`, group `www-data`, **read-only to PHP**.
- PHP runs as `www-data` and can write only `wp-content/uploads/`, `wp-content/litespeed/`, `wp-content/cache/` and `wp-content/.litespeed_conf.dat`.
- `wp-content/object-cache.php` (the LiteSpeed Redis drop-in) and `.htaccess` are deliberately not writable by PHP: they are executed or interpreted on every request and are where attackers hide persistence.

Consequence: plugin and theme updates are made with WP-CLI as `ubuntu` or by committing to git, not from the WordPress admin. A compromised plugin can no longer rewrite the code base.

Follow the principle of least privilege and avoid overly permissive file permissions.

## Deployment Security

Production deployments:

- Originate from GitHub `main` and are **pulled** by the server (`nigma-deploy.timer`); GitHub holds no server credentials and there is no inbound SSH.
- Health-check the site and roll back automatically on failure; the commit that is live is published at `/deploy-version.txt`.
- Preserve uploads and environment-specific configuration (only `wp-content/` is checked out).
- Log outcomes to `/var/log/nigma-deploy.log` and in the GitHub Actions run.

Anyone who can push to `main` can change production: restrict write access and enable branch protection on `main`. See `docs/deployment.md`.

## Backup & Recovery

Maintain:

- Database backups.
- File backups.
- EBS snapshots (or equivalent infrastructure backups).

Regularly verify that backups can be restored successfully.

## Monitoring & Incident Response

Monitor:

- Server availability.
- PHP and web server logs.
- Disk usage.
- Backup status.
- Security plugin alerts.

If a security incident occurs:

1. Contain the issue.
2. Preserve relevant logs.
3. Assess impact.
4. Restore from trusted backups if necessary.
5. Document lessons learned.
6. Update documentation and ADRs if the security posture changes.

## Incident Record — November 2026

> Summary only. This repository is public, so indicators that would help an attacker are deliberately omitted.

**What happened.** On 2026-11-04 the previous production server was found to be compromised: hundreds of spam "doorway" pages and obfuscated backdoor files were present in the web root, in WordPress core directories and inside plugin directories; several unrecognised administrator accounts existed; and a persistent background process re-created removed files. Some files carried timestamps as early as 2024, so the start date is unknown.

**Contributing factors.**

- End-of-life operating system (Ubuntu 20.04) and PHP 8.0.
- PHP ran as the code-owning user (ADR-013), so code execution inside WordPress could modify the code base.
- Licence-gated premium plugins and theme (Gravity Forms, ACF Pro, Avada/Fusion) had expired or missing licences and were not updated.
- The security plugin (Defender) had been removed from the server.
- No automated backups existed; the CPU-credit-exhausted burstable instance masked the symptoms as a performance problem.

**Root cause: not determined.** The entry point was not identified. Evidence points to the web application (plugin vulnerability or stolen WordPress credentials), not SSH.

**Response.**

1. Preserved evidence (disk image, database export, quarantined files) and removed the persistence.
2. Cleaned in place first (clean core, rogue accounts removed, planted files quarantined, secrets rotated).
3. Rebuilt on a new hardened host and cut over the Elastic IP (ADR-014): fresh checksum-verified core, code from git, audited database and uploads.
4. Rotated WordPress salts, database password, GitHub deploy keys and the compromised TLS certificate (revoked).
5. Moved to pull-based deploys, spam-URL filtering at the edge (ADR-015), and updated plugins/core where licences allowed.

**Open items.**

- Renew the Gravity Forms, ACF Pro and Avada/Fusion licences and apply their updates; reactivate the WPMU DEV dashboard plugin for Defender/Snapshot/Branda updates.
- Rotate remaining third-party credentials (SMTP, reCAPTCHA, API keys) and enable two-factor authentication for all administrators.
- Enable branch protection on `main`.
- Automated EBS backups and a CloudWatch alarm on CPU credit balance are **not configured** (deferred by the project owner).
- Review the old server's logs to try to identify the entry point before its disk is deleted.

### Compromise check (run periodically and after any suspicious activity)

```bash
# On the server, as ubuntu, from /var/www/nigma (WP_CLI_PHP_ARGS=-ddisable_functions=)
wp core verify-checksums                     # core files match wordpress.org
wp plugin verify-checksums --all             # wordpress.org plugins match
wp user list --role=administrator            # only expected accounts
git status --porcelain --untracked-files=all -- wp-content | grep -i '\.ph'   # unexpected PHP not in git
find wp-content/uploads -type f \( -iname '*.php*' -o -iname '*.phtml' -o -iname '*.phar' \) ! -name index.php   # PHP in uploads
find . -path ./.git -prune -o -type f \( -iname '*.php[0-9]' -o -iname '*.pht' -o -iname '*.phar' \) -print        # PHP variants
ps -eo pid,user,args | grep -E 'php (/|-)'  # stray long-running PHP processes
nigma-deploy --status                         # live commit == origin/main, nothing pinned
```

Things that should never appear: new top-level directories in the web root, nested duplicated folder names, PHP-looking content in image/archive files, or a server commit that differs from `origin/main`.

## Update Policy

Review periodically:

- WordPress core
- PHP
- MariaDB
- Themes
- Plugins
- Operating system packages

Follow the project's maintenance cadence and validate changes locally before production deployment.

## AI Agent Responsibilities

AI coding agents must:

- Never expose secrets.
- Never recommend committing credentials.
- Explain the impact of security-sensitive changes.
- Update `docs/security.md` when security policies change.
- Update `docs/architecture-decisions.md` if a security-related architectural decision is introduced.

## Related Documents

- AGENTS.md
- docs/production-environment.md
- docs/deployment.md
- docs/coding-standards.md
- docs/architecture-decisions.md
- docs/security-review.md
