# Troubleshooting Guide

Version: 1.0

## Purpose

This document serves as the operational knowledge base for troubleshooting the NIGMA WordPress project.

Unlike the other documents, this guide is expected to grow continuously as new issues are encountered and resolved.

> Every significant issue that is diagnosed and resolved should be documented here.

## Troubleshooting Philosophy

Follow these principles:

- Diagnose before fixing.
- Make one safe change at a time.
- Prefer read-only diagnostics first.
- Preserve production stability.
- Record both the root cause and the resolution.

## Standard Troubleshooting Workflow

1. Define the problem.
2. Collect evidence.
3. Review logs and diagnostics.
4. Identify the root cause.
5. Apply the smallest safe fix.
6. Verify the result.
7. Update this document if the issue is new.

## Production Safety

When troubleshooting production:

- Take backups before risky changes.
- Avoid direct edits where possible.
- Prefer Git-based fixes.
- Consider rollback before implementing changes.
- Verify application health after resolution.

## Common Categories

### Git

Examples:

- Merge conflicts
- Branch divergence
- Remote authentication
- SSH key issues

### Docker

Examples:

- Container startup failures
- Volume mount issues
- Networking problems
- Database connectivity

### WordPress

Examples:

- White screen of death
- Plugin conflicts
- Theme issues
- Update failures
- Scheduled task problems

### LiteSpeed

Examples:

- Cache not clearing
- Incorrect cached content
- Rewrite rule issues

### PHP

Examples:

- Fatal errors
- Memory exhaustion
- Version compatibility
- Missing extensions

### Database

Examples:

- Connection failures
- Import/export issues
- Corruption recovery
- Slow queries

### Deployment

Examples:

- Failed deployment
- Missing files
- Rollback procedures
- CI/CD failures

### Permissions

Examples:

- File ownership
- Directory permissions
- SSH access
- Git permission problems

### Security

Examples:

- Login lockouts
- Malware detection
- Security plugin alerts
- Certificate issues

## Troubleshooting Entry Template

For each new issue, record:

### Title

### Symptoms

### Environment

### Root Cause

### Resolution

### Prevention

### Related ADR (if applicable)

### Related Documentation

## Continuous Improvement

After resolving an issue, consider whether updates are needed for:

- docs/architecture-decisions.md
- docs/documentation-policy.md
- docs/security.md
- docs/deployment.md
- docs/local-development.md
- AGENTS.md

## Related Documents

- README.md
- AGENTS.md
- docs/documentation-policy.md
- docs/security.md
- docs/deployment.md
- docs/local-development.md
- docs/architecture-decisions.md
