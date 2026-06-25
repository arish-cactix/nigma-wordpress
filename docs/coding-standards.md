# Coding Standards

Version: 1.0

## Purpose

This document defines coding standards for the NIGMA WordPress project. It is intended for developers, maintainers, Codex CLI, and other AI coding agents working on the repository.

## Core Principles

- Preserve production stability.
- Prefer small, focused changes.
- Keep code readable and maintainable.
- Prefer WordPress-native APIs.
- Avoid unnecessary plugins or dependencies.
- Preserve SEO, performance, accessibility, and security.
- Update related documentation when standards or architecture change.

## WordPress Standards

Follow WordPress development practices whenever possible.

Use WordPress APIs for:

- Options
- Metadata
- Enqueueing scripts and styles
- AJAX
- REST API
- Shortcodes
- Hooks and filters
- Escaping and sanitization
- Database access

Avoid direct database access unless there is a strong reason.

## Theme Development

Primary custom development should happen in:

```text
wp-content/themes/Avada-Child-Theme/
```

Avoid modifying:

```text
wp-content/themes/Avada/
```

The Avada parent theme is tracked for reproducibility, but it should not normally be customized.

If the parent theme must be changed, document the reason and consider creating or updating an ADR.

## Plugin Development

Prefer existing WordPress and plugin capabilities before creating new custom plugins.

If custom plugin functionality is needed:

- Keep the plugin focused.
- Avoid mixing unrelated features.
- Use namespaced or prefixed functions/classes.
- Avoid global state where possible.
- Document plugin purpose and dependencies.

## PHP Standards

- Use clear function and variable names.
- Prefer strict, defensive logic.
- Validate and sanitize input.
- Escape output.
- Avoid suppressing errors with `@`.
- Avoid editing vendor code.
- Avoid hardcoded credentials or environment-specific values.

Recommended WordPress escaping functions:

- `esc_html()`
- `esc_attr()`
- `esc_url()`
- `wp_kses_post()`

Recommended sanitization functions:

- `sanitize_text_field()`
- `sanitize_email()`
- `sanitize_key()`
- `absint()`
- `intval()`

## JavaScript Standards

- Keep JavaScript modular and readable.
- Avoid unnecessary libraries.
- Do not load scripts globally unless required.
- Use proper event handling.
- Avoid blocking render where possible.

Scripts should be enqueued through WordPress rather than hardcoded into templates.

## CSS Standards

- Prefer child theme styles.
- Avoid excessive `!important`.
- Keep selectors scoped.
- Do not override Avada globally unless necessary.
- Consider responsive behaviour and accessibility.

## Performance Standards

Every change should consider:

- Page speed
- LiteSpeed Cache behaviour
- Image loading
- Script loading
- Database queries
- Plugin overhead
- Mobile performance

Avoid adding heavy front-end assets without clear benefit.

## SEO Standards

Preserve:

- Page titles
- Meta descriptions
- Canonical URLs
- Schema markup
- Sitemap behaviour
- Redirects
- Internal links
- Image alt attributes

Coordinate with Rank Math SEO behaviour before changing SEO-sensitive code.

## Security Standards

Never commit:

- API keys
- Database credentials
- SMTP credentials
- Passwords
- `.env`
- `wp-config.php`

Security-sensitive changes should be reviewed carefully and documented.

## Accessibility Standards

When modifying front-end templates or components:

- Preserve semantic HTML.
- Use meaningful alt text.
- Maintain keyboard navigation.
- Avoid colour-only meaning.
- Preserve readable contrast.

## Documentation Requirements

When a change affects project behaviour, update documentation in the same change set.

Examples:

- Docker workflow change -> update `docs/local-development.md`
- Deployment change -> update `docs/deployment.md`
- Repository layout change -> update `docs/repository-architecture.md`
- Security change -> update `docs/security.md`
- Major decision -> update `docs/architecture-decisions.md`

## AI Agent Requirements

AI coding agents must:

- Read `AGENTS.md` first.
- Prefer safe, incremental changes.
- Avoid direct production assumptions.
- Explain impact before recommending risky changes.
- Update documentation when relevant.
- Update ADRs when architectural decisions change.

## Related Documents

- README.md
- AGENTS.md
- docs/repository-architecture.md
- docs/local-development.md
- docs/deployment.md
- docs/security.md
- docs/architecture-decisions.md
