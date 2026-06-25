# Documentation Policy

Version: 1.0

## Purpose

This document defines how project documentation is created, maintained, and evolved throughout the lifecycle of the NIGMA WordPress project.

Documentation is considered a core project asset and must evolve together with the source code.

## Guiding Principles

The documentation should:

- Explain both **what** and **why**.
- Reflect the current state of the project.
- Support developers, operators, and AI coding agents.
- Be updated as part of the same change set whenever applicable.
- Remain concise, accurate, and cross-referenced.

Documentation is not optional; it is part of the deliverable.

## Living Documentation

All project documentation is considered **living documentation**.

Whenever the project changes, evaluate whether documentation requires updating.

Never knowingly leave documentation inconsistent with the codebase.

## Documentation Workflow

Every significant change should follow this workflow:

1. Identify the proposed change.
2. Determine whether it affects documentation.
3. Update all relevant document(s).
4. Determine whether an Architecture Decision Record (ADR) is required.
5. Update or create the ADR if necessary.
6. Verify cross-references between documents.
7. Commit code and documentation together.

## Documentation Matrix

| Change | Documentation to Update |
|---------|-------------------------|
| Business scope | `docs/project-overview.md` |
| Production infrastructure | `docs/production-environment.md` |
| Repository structure | `docs/repository-architecture.md` |
| Local Docker workflow | `docs/local-development.md` |
| Deployment or CI/CD | `docs/deployment.md` |
| Coding conventions | `docs/coding-standards.md` |
| Security policy | `docs/security.md` |
| New troubleshooting knowledge | `docs/troubleshooting.md` |
| Architectural decision | `docs/architecture-decisions.md` |
| AI workflow | `AGENTS.md` |
| Repository overview | `README.md` |

## Architecture Decision Records (ADR)

Create or update an ADR when a change:

- Alters project architecture.
- Changes deployment strategy.
- Introduces a long-term engineering policy.
- Modifies repository structure.
- Changes Docker architecture.
- Introduces a significant security decision.
- Changes development workflow.

Minor implementation details do not require ADRs.

## AI Agent Responsibilities

AI coding agents should:

1. Read `README.md`.
2. Read `AGENTS.md`.
3. Consult relevant documents before making changes.
4. Determine documentation impact before completing a task.
5. Update all affected documents.
6. Create or update ADRs when appropriate.
7. Keep documentation and code synchronized.

## Human Review

Documentation changes should be reviewed with the same care as source code.

Reviewers should verify:

- Accuracy.
- Cross-references.
- Consistency.
- Grammar and clarity.
- Architectural alignment.

## Review Cadence

Review documents when:

- Major features are completed.
- Infrastructure changes.
- Production configuration changes.
- Development workflow changes.
- New architectural decisions are made.

Additionally, perform a periodic documentation review at least every six months.

## Future Improvements

Potential enhancements include:

- Documentation quality checklist.
- Automated link validation.
- CI checks for documentation updates.
- ADR templates.
- Documentation version history.

## Related Documents

- README.md
- AGENTS.md
- docs/project-overview.md
- docs/architecture-decisions.md
- docs/deployment.md
- docs/coding-standards.md
