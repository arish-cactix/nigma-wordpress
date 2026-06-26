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

Production runs on AWS EC2.

Recommendations:

- Use SSH key authentication only.
- Disable password authentication where practical.
- Limit SSH access to authorized administrators.
- Apply operating system security updates during maintenance windows.
- Monitor disk usage and system health.

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

Recommended ownership:

- Owner: `ubuntu`
- Group: `www-data`

Follow the principle of least privilege and avoid overly permissive file permissions.

## Deployment Security

Production deployments should:

- Originate from GitHub.
- Be performed by CI/CD where possible.
- Include backup verification.
- Preserve uploads and environment-specific configuration.
- Log deployment outcomes.

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
