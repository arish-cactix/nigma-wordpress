
# Production Environment

Version: 1.2

## Purpose

This document defines the current production environment for the NIGMA WordPress website. It serves as the operational baseline for development, troubleshooting, upgrades, disaster recovery, and future CI/CD.

> **Living document:** Update this file whenever the production environment changes.

## Production Summary

| Component | Current Production |
|-----------|--------------------|
| Website | https://www.nigma.ae |
| Environment | Production |
| Hosting | AWS EC2 (Mumbai Region) |
| Operating System | Ubuntu Linux |
| Web Server | LiteSpeed |
| PHP | 8.0.17 (LiteSpeed SAPI) |
| Database | MariaDB 10.6.7 |
| WordPress | 6.7.1 *(Update when production changes)* |
| HTTPS | Enabled |
| Multisite | No |
| Timezone | Asia/Dubai |

## Production Server Dependencies

Last verified: 2026-08-13

### AWS

| Dependency | Current Production |
|------------|--------------------|
| Region | ap-south-1 (Mumbai) |
| EC2 Instance Name | Nigma |
| EC2 Instance ID | i-00b50d13b261b98b0 |
| Private IP | 172.31.16.38 |
| Public IP | 3.7.4.103 |
| IAM Instance Profile | HomePro-SSM-Role |
| SSM Agent | amazon-ssm-agent 3.3.4793.0, installed through Snap |

The instance profile includes `AmazonSSMManagedInstanceCore`.

### Operating System

| Dependency | Current Production |
|------------|--------------------|
| OS | Ubuntu 20.04.4 LTS |
| Kernel | 5.11.0-1028-aws |
| SSH Server | OpenSSH Server 8.2p1 |
| Snap | snapd 2.54.3 |

### Web and PHP Runtime

| Dependency | Current Production |
|------------|--------------------|
| Web Server | OpenLiteSpeed 1.7.14 |
| LiteSpeed Service | `lshttpd.service` |
| PHP Runtime | lsphp80 8.0.17 |
| PHP CLI | PHP 8.0.17 |
| PHP Configuration | `/usr/local/lsws/lsphp80/etc/php/8.0/litespeed/php.ini` |
| WP-CLI | 2.6.0 |

Important PHP extensions currently available:

- bcmath
- bz2
- curl
- dom
- exif
- gd
- gmp
- imagick
- imap
- mbstring
- memcached
- mysqli
- opcache
- pdo_mysql
- redis
- soap
- sodium
- xml
- zip

### Data and Cache Services

| Dependency | Current Production |
|------------|--------------------|
| Database | MariaDB Server 10.6.7 |
| Database Service | `mariadb.service` |
| Database Listener | `127.0.0.1:3306` |
| Redis | redis-server 5.0.7 |
| Redis Service | `redis-server.service` |
| Redis Listener | `127.0.0.1:6379` |
| Memcached | memcached 1.5.22 |
| Memcached Service | `memcached.service` |
| Mail Transfer Agent | Postfix 3.4.13 |

### Network Listeners

Observed listeners relevant to the WordPress stack:

- SSH: `22/tcp`
- HTTP: `80/tcp`
- HTTPS: `443/tcp` and `443/udp`
- OpenLiteSpeed admin/runtime ports: `7080`, `8088`
- MariaDB: `127.0.0.1:3306`
- Redis: `127.0.0.1:6379`

### Application Paths

| Path | Purpose |
|------|---------|
| `/var/www/nigma` | WordPress application root |
| `/var/www/nigma/wp-content` | Version-controlled application code |
| `/usr/local/lsws` | OpenLiteSpeed installation |
| `/usr/local/lsws/lsphp80` | LiteSpeed PHP 8.0 runtime |
| `/var/lib/mysql` | MariaDB data directory |

The production Git working tree is initialized in `/var/www/nigma/wp-content` and points to:

```text
git@github.com:arish-cactix/nigma-wordpress.git
```

### WordPress Runtime

| Dependency | Current Production |
|------------|--------------------|
| WordPress Core | 6.7.1 |
| Site URL | https://www.nigma.ae |
| Home URL | https://www.nigma.ae |
| WordPress Timezone | Asia/Dubai |

Active theme:

- Avada Child Theme 1.0.0

Parent theme:

- Avada 7.11.11

Inactive bundled WordPress themes:

- Twenty Nineteen 3.0
- Twenty Twenty 2.8
- Twenty Twenty-One 2.4
- Twenty Twenty-Two 1.9
- Twenty Twenty-Three 1.6
- Twenty Twenty-Four 1.3
- Twenty Twenty-Five 1.0

### WordPress Plugins

Active plugins:

| Plugin | Version |
|--------|---------|
| Advanced Custom Fields Pro | 6.3.11 |
| Avada Builder | 3.11.11 |
| Avada Core | 5.11.11 |
| Branda Pro / Ultimate Branding | 3.4.22 |
| Defender Pro | 4.12.0 |
| Gravity Forms | 2.9.0 |
| Gravity Forms reCAPTCHA | 1.6.0 |
| Health Check & Troubleshooting | 1.7.1 |
| LiteSpeed Cache | 6.5.3 |
| Post SMTP | 2.9.13 |
| Rank Math SEO | 1.0.234 |
| Smart Phone Field for Gravity Forms | 2.1.4 |
| Snapshot Pro | 4.30.0 |
| WP Migrate DB Pro | 2.6.12 |
| WPMU DEV Updates | 4.11.28 |

Inactive plugins:

| Plugin | Version |
|--------|---------|
| PWA | 0.8.2 |
| The Hub Client | 2.2.2 |

Must-use plugins and drop-ins:

| Component | Type | Version |
|-----------|------|---------|
| Health Check Troubleshooting Mode | Must-use plugin | 1.9.2 |
| Security Headers CSP | Must-use plugin | 1.0 |
| WP Migrate DB Pro Compatibility | Must-use plugin | 1.3 |
| maintenance.php | Drop-in | n/a |
| object-cache.php | Drop-in | n/a |

### Scheduled Tasks

The `ubuntu` user did not have a user crontab at the time of verification.

System cron files exist for standard OS/package maintenance, including `certbot`, `php`, `logrotate`, `apt`, `dpkg`, `man-db`, and update-notifier tasks. Cron command contents were not copied into this repository because scheduled task definitions can contain sensitive arguments.

## Server Layout

Application Root

```
/var/www/nigma
```

Version-controlled application code

```
/var/www/nigma/wp-content
```

Git is intentionally initialized inside `wp-content`.

## Technology Stack

Infrastructure

- AWS EC2
- Amazon EBS
- SSH administration

Application

- WordPress
- Avada
- Avada Child Theme

Core Plugins

- Advanced Custom Fields Pro
- Gravity Forms
- Avada Builder
- Avada Core
- LiteSpeed Cache
- Rank Math SEO
- Defender Pro
- Snapshot Pro
- Branda Pro
- WP Migrate
- Post SMTP

## Repository Boundary

Version controlled:

- themes/
- plugins/
- mu-plugins/

Not version controlled:

- uploads/
- database dumps
- wp-config.php
- .env
- .htaccess
- logs
- cache
- backups

## Operational Principles

- Production is the source of truth for runtime state.
- Development occurs locally.
- GitHub is the canonical source for application code.
- Production deployments should originate from CI/CD.
- Every production deployment should include backup, rollback, and verification.

## Software Lifecycle Policy

### WordPress Core

- Keep WordPress reasonably current.
- Do not update immediately on every release.
- Review and upgrade approximately every **3–4 months** after releases have matured.
- Always validate upgrades locally before production deployment.

### PHP

- Upgrade only after confirming compatibility with:
  - Avada
  - ACF Pro
  - Gravity Forms
  - LiteSpeed Cache
  - Rank Math SEO
  - WPMU DEV plugins
  - Custom code

### Plugins & Themes

- Review updates regularly.
- Prefer scheduled maintenance windows.
- Test locally before production.
- Batch compatible updates instead of applying every individual release immediately.

### Database

- Upgrade conservatively.
- Validate backups and rollback procedures before major version changes.

## Known Constraints

- Production uses LiteSpeed-specific caching behaviour.
- Premium plugins and themes require licence-aware maintenance.
- Uploads remain outside version control.

## Future Improvements

- Docker-based local development
- GitHub Actions CI/CD
- Environment variable configuration
- Automated deployment verification
- Deployment rollback automation
- PHP upgrade planning
- WordPress lifecycle tracking

## Related Documents

- README.md
- AGENTS.md
- project-overview.md
- repository-architecture.md
- deployment.md
- architecture-decisions.md
