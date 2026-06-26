# Security Review Cadence

Version: 1.0

## Purpose

A scheduled, actionable checklist for reviewing the security posture of the NIGMA WordPress production environment. Run each review on schedule and note any findings.

---

## Monthly Review

Run on the first working day of each month.

### WordPress

- [ ] Apply any pending WordPress core updates
- [ ] Apply any pending plugin updates — validate compatibility first
- [ ] Apply any pending theme updates
- [ ] Check for any inactive plugins or themes — remove if not needed
- [ ] Review WordPress admin users — remove or demote any unused accounts

### Logs

- [ ] Review `wp-content/debug.log` for recurring PHP errors or warnings
- [ ] Review WP Defender scan results — investigate any flagged files
- [ ] Check UptimeRobot incident history — any downtime to investigate?

### Backups

- [ ] Confirm the most recent AMI backup exists and is dated correctly (AWS EC2 console → AMIs)
- [ ] Confirm at least 3 AMIs are retained

---

## Quarterly Review

Run every three months (January, April, July, October).

### Access Control

- [ ] Audit SSH keys on the production server — remove any that are no longer needed
- [ ] Audit GitHub repository collaborators — remove any stale access
- [ ] Audit AWS IAM users and roles — verify least privilege
- [ ] Change any shared credentials if personnel have changed

### Server & Infrastructure

- [ ] Check PHP version (`php -v` on server) — is a newer patch available?
- [ ] Check SSL certificate expiry — should be auto-renewed but verify
- [ ] Check LiteSpeed version — apply any available security patches
- [ ] Review EC2 security group rules — are any ports open that should not be?

### Content Security Policy

- [ ] Review `wp-content/mu-plugins/security-headers-csp.php`
- [ ] Are all allowed domains still in active use?
- [ ] Have any new third-party scripts been added that require a CSP update?

### GitHub Actions

- [ ] Review Dependabot or manually check pinned Action versions (`actions/checkout`, `dawidd6/action-send-mail`, etc.) for known CVEs
- [ ] Verify that all required secrets are still valid (rotate if expired or suspect)

---

## Annual Review

Run once per year. Expand the quarterly review with the following.

### Full Access Audit

- [ ] Full review of all WordPress user accounts — roles and passwords
- [ ] Full review of all SSH keys across all environments
- [ ] Full review of GitHub team and repository permissions
- [ ] Full review of AWS account access

### Backup Restoration Test

- [ ] Perform a test restore from the most recent AMI to verify the procedure works end-to-end
- [ ] Document the restore outcome and any issues encountered

### Security Documentation

- [ ] Review and update `docs/security.md` — are principles still current?
- [ ] Review and update this document — are checklists still accurate?
- [ ] Add any new ADRs for security architecture changes made during the year

---

## How to Log a Finding

If a review uncovers an issue:

1. Note the finding in a GitHub issue with the label `security`.
2. Assess severity (Critical / High / Medium / Low).
3. Resolve before the next deploy if Critical or High.
4. Document the fix in the relevant commit or ADR if it changes architecture.

---

## Related Documents

- docs/security.md
- docs/deployment.md
- docs/architecture-decisions.md
