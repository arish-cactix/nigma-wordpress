# Server Dependencies

Version: 1.0

## Purpose

This document records the server-level dependencies installed on the NIGMA production EC2 instance.

It is intentionally limited to infrastructure, operating system, runtime, service, package, network, and filesystem dependencies inside the server instance.

It does not document application-level dependencies such as WordPress core, WordPress themes, WordPress plugins, Composer packages, npm packages, or database content.

Last verified: 2026-08-13

## AWS Instance

| Dependency | Current Production |
|------------|--------------------|
| AWS Region | ap-south-1 (Mumbai) |
| EC2 Instance Name | Nigma |
| EC2 Instance ID | i-00b50d13b261b98b0 |
| Private IP | 172.31.16.38 |
| Public IP | 3.7.4.103 |
| IAM Instance Profile | HomePro-SSM-Role |
| Required SSM Policy | AmazonSSMManagedInstanceCore |

## Operating System

| Dependency | Current Production |
|------------|--------------------|
| OS | Ubuntu 20.04.4 LTS |
| Kernel | 5.11.0-1028-aws |
| Architecture | x86_64 |

## Core Server Services

| Service | Purpose | Current State |
|---------|---------|---------------|
| `lshttpd.service` | OpenLiteSpeed web server | running |
| `mariadb.service` | MariaDB database server | running |
| `redis-server.service` | Redis object/cache service | running |
| `memcached.service` | Memcached cache service | running |
| `postfix@-.service` | Postfix mail transfer agent | running |
| `ssh.service` | OpenSSH server | running |
| `cron.service` | System scheduled task runner | running |
| `certbot.timer` | Let's Encrypt certificate renewal timer | enabled |
| `snap.amazon-ssm-agent.amazon-ssm-agent.service` | AWS Systems Manager agent | running |
| `snapd.service` | Snap package manager daemon | running |
| `rsyslog.service` | System logging | running |
| `systemd-timesyncd.service` | System clock synchronization | running |
| `systemd-resolved.service` | DNS resolution | running |

## Installed Server Packages

| Package | Version |
|---------|---------|
| certbot | 0.40.0-1ubuntu0.1 |
| openlitespeed | 1.7.14-2+focal |
| lsphp80 | 8.0.17-1+focal |
| lsphp80-common | 8.0.17-1+focal |
| lsphp80-curl | 8.0.17-1+focal |
| lsphp80-imagick | 3.7.0-1+focal |
| lsphp80-memcached | 3.1.5-1+focal |
| lsphp80-mysql | 8.0.17-1+focal |
| lsphp80-redis | 5.3.7-1+focal |
| mariadb-server | 1:10.6.7+maria~focal |
| memcached | 1.5.22-2ubuntu0.2 |
| openssh-server | 1:8.2p1-4ubuntu0.4 |
| postfix | 3.4.13-0ubuntu1.2 |
| redis-server | 5:5.0.7-2ubuntu0.1 |
| snapd | 2.54.3+20.04.1ubuntu0.2 |
| python3-certbot | 0.40.0-1ubuntu0.1 |

## Snap Packages

| Package | Version | Revision | Notes |
|---------|---------|----------|-------|
| amazon-ssm-agent | 3.3.4793.0 | 13349 | classic confinement |

The SSM agent is managed by Snap. Use the Snap systemd unit when restarting it:

```text
sudo systemctl restart snap.amazon-ssm-agent.amazon-ssm-agent.service
```

## Web and PHP Runtime

| Dependency | Current Production |
|------------|--------------------|
| Web Server | OpenLiteSpeed 1.7.14 |
| Web Server Path | `/usr/local/lsws` |
| PHP Runtime | LiteSpeed PHP 8.0.17 |
| PHP Runtime Path | `/usr/local/lsws/lsphp80` |
| PHP Configuration | `/usr/local/lsws/lsphp80/etc/php/8.0/litespeed/php.ini` |
| PHP CLI Binary | `/usr/local/lsws/lsphp80/bin/php` |

## SSL and Certificates

| Dependency | Current Production |
|------------|--------------------|
| Certificate Authority | Let's Encrypt |
| Certificate Tooling | Certbot 0.40.0 |
| Certificate Name | `nigma.ae` |
| Covered Domains | `nigma.ae`, `www.nigma.ae` |
| Certificate Directory | `/etc/letsencrypt/live/nigma.ae` |
| Renewal Timer | `certbot.timer` |
| Renewal Service | `certbot.service` |
| Web Server Reload Hook | `systemctl restart lsws` |
| Verified Expiry | 2026-10-26 03:47:05 UTC |

Let's Encrypt account, archive, live certificate, renewal, and renewal-hook directories are present under `/etc/letsencrypt`.

Private key contents are not stored in this repository.

## PHP Extensions

Server PHP extensions currently available:

- bcmath
- bz2
- calendar
- ctype
- curl
- dom
- enchant
- exif
- fileinfo
- filter
- ftp
- gd
- gettext
- gmp
- iconv
- igbinary
- imagick
- imap
- json
- libxml
- mbstring
- memcached
- msgpack
- mysqli
- mysqlnd
- openssl
- pdo_mysql
- redis
- soap
- sockets
- sodium
- xml
- xmlreader
- xmlwriter
- xsl
- zip
- zlib

## Data and Cache Services

| Dependency | Current Production |
|------------|--------------------|
| MariaDB | 10.6.7 |
| MariaDB Data Directory | `/var/lib/mysql` |
| Redis | 5.0.7 |
| Memcached | 1.5.22 |
| Postfix | 3.4.13 |

## Network Listeners

Observed listeners relevant to the server stack:

| Listener | Purpose |
|----------|---------|
| `22/tcp` | SSH |
| `80/tcp` | HTTP |
| `443/tcp` | HTTPS |
| `443/udp` | HTTP/3 or QUIC-capable HTTPS listener |
| `7080/tcp` and `7080/udp` | OpenLiteSpeed admin/runtime listener |
| `8088/tcp` | OpenLiteSpeed runtime listener |
| `127.0.0.1:3306/tcp` | MariaDB local listener |
| `127.0.0.1:6379/tcp` | Redis local listener |

## Filesystem Paths

| Path | Purpose |
|------|---------|
| `/var/www/nigma` | Production web root |
| `/usr/local/lsws` | OpenLiteSpeed installation |
| `/usr/local/lsws/lsphp80` | LiteSpeed PHP 8.0 runtime |
| `/var/lib/mysql` | MariaDB data directory |
| `/var/log` | System logs |

## Scheduled Task Metadata

The `ubuntu` user did not have a user crontab at the time of verification.

System cron files were present for OS and package maintenance:

- `/etc/cron.d/certbot`
- `/etc/cron.d/e2scrub_all`
- `/etc/cron.d/php`
- `/etc/cron.d/popularity-contest`
- `/etc/cron.daily/apport`
- `/etc/cron.daily/apt-compat`
- `/etc/cron.daily/bsdmainutils`
- `/etc/cron.daily/dpkg`
- `/etc/cron.daily/logrotate`
- `/etc/cron.daily/man-db`
- `/etc/cron.daily/popularity-contest`
- `/etc/cron.daily/update-notifier-common`
- `/etc/cron.weekly/man-db`
- `/etc/cron.weekly/update-notifier-common`

Cron command contents were not copied into this repository because scheduled task definitions can contain sensitive arguments.
