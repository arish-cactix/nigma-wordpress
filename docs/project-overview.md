# Project Overview

Version: 1.0

## Purpose

This document provides the business and technical context for the NIGMA WordPress project. It is intended for developers, DevOps engineers, AI coding agents, and future maintainers.

## Business Overview

**NIGMA Lifts Installation and Maintenance Co. LLC** is a subsidiary of **Imdaad Group**.

Website: https://www.nigma.ae

NIGMA designs, supplies, installs, modernizes, repairs, and maintains:

- Passenger Elevators
- Home Elevators
- Cargo Lifts
- Car Lifts
- Escalators
- Travelators
- Dumbwaiters
- Automatic Doors
- Sliding & Swing Doors
- Motorized Gates
- Roller Shutters
- Garage Doors
- Access Control Systems
- Parking Barriers
- Paid Parking Systems
- Bollards
- Lift Interiors & Cladding

The website is both a marketing platform and a lead-generation system.

## Project Objectives

The engineering goals are to:

- Maintain a stable production website.
- Enable safe local development with Docker.
- Adopt Git-based workflows.
- Introduce CI/CD for production deployments.
- Improve security, maintainability, and documentation.

## Technical Scope

This repository manages the **application layer** only.

Included:

- wp-content/themes
- wp-content/plugins
- wp-content/mu-plugins

Excluded:

- WordPress core
- uploads
- database dumps
- server configuration
- secrets

## Production Architecture

- Hosting: AWS EC2
- Web Server: LiteSpeed
- PHP: 8.0.17
- Database: MariaDB 10.6.7
- WordPress: 6.7.1

Production is considered the source of truth for runtime state.

## Local Development Vision

Local development will use Docker Compose.

Goals:

- Reproduce production behaviour.
- Keep local configuration isolated.
- Mount the repository's wp-content into the container.
- Never require edits directly on production.

## Repository Philosophy

This repository is designed around:

- Production safety
- Clear documentation
- Infrastructure as code
- Reproducible local development
- Minimal manual deployment

## Future Direction

Planned improvements include:

- Docker-based development
- GitHub Actions CI/CD
- Environment-based configuration
- Automated deployment
- Rollback support
- Testing and quality gates

## Related Documentation

- README.md
- AGENTS.md
- production-environment.md
- repository-architecture.md
- local-development.md
- deployment.md
- architecture-decisions.md
