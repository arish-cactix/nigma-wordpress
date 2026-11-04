#!/bin/bash
#
# Install (or update) the NIGMA deploy tooling on a server. Run as root from a directory that
# contains the scripts and systemd units (the sparse "infra" checkout: /opt/nigma/infra/scripts/deploy).
#
#   sudo bash install.sh                       existing/native host: install the deployer (unchanged behaviour)
#   sudo bash install.sh --runtime docker ...  NEW host that should run the Docker stack (ADR-016)
#
# Options (docker runtime):
#   --domains a.example,www.a.example   hostnames served by this host (SITE_DOMAINS)
#   --cert-name name                    certificate lineage name (default nigma-ae)
#   --acme-staging                      use Let's Encrypt STAGING (for tests)
#   --media-s3 s3://bucket/uploads      media library location (restored on an empty host)
#   --db-s3 s3://bucket/db              database dump location (db/latest.sql.gz restored when the DB is empty)
#   --backup-timer                      install the daily media+database backup timer
#   --http-port N / --https-port N      published ports (tests only; default 80/443)
#   --now                               run the first deploy immediately (builds the environment) instead of waiting for the timer
#
# Idempotent. With no options it does not touch the site and does not change the runtime marker.
set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUNTIME=""; DOMAINS=""; CERT_NAME=""; STAGING=0; MEDIA_S3=""; DB_S3=""; BACKUP_TIMER=0; HTTP_PORT=""; HTTPS_PORT=""; NOW=0
while [ $# -gt 0 ]; do
  case "$1" in
    --runtime) RUNTIME="$2"; shift 2 ;;
    --domains) DOMAINS="$2"; shift 2 ;;
    --cert-name) CERT_NAME="$2"; shift 2 ;;
    --acme-staging) STAGING=1; shift ;;
    --media-s3) MEDIA_S3="$2"; shift 2 ;;
    --db-s3) DB_S3="$2"; shift 2 ;;
    --backup-timer) BACKUP_TIMER=1; shift ;;
    --http-port) HTTP_PORT="$2"; shift 2 ;;
    --https-port) HTTPS_PORT="$2"; shift 2 ;;
    --now) NOW=1; shift ;;
    *) echo "unknown option: $1" >&2; exit 2 ;;
  esac
done
[ -z "$RUNTIME" ] || [ "$RUNTIME" = native ] || [ "$RUNTIME" = docker ] || { echo "--runtime must be native or docker" >&2; exit 2; }

install -o root -g root -m 755 "$HERE/nigma-deploy.sh"            /usr/local/sbin/nigma-deploy
install -o root -g root -m 755 "$HERE/nigma-docker-bootstrap.sh"  /usr/local/sbin/nigma-docker-bootstrap
install -o root -g root -m 755 "$HERE/nigma-data.sh"              /usr/local/sbin/nigma-data
install -o root -g root -m 644 "$HERE/nigma-deploy.service"       /etc/systemd/system/nigma-deploy.service
install -o root -g root -m 644 "$HERE/nigma-deploy.timer"         /etc/systemd/system/nigma-deploy.timer
install -o root -g root -m 644 "$HERE/nigma-backup.service"       /etc/systemd/system/nigma-backup.service
install -o root -g root -m 644 "$HERE/nigma-backup.timer"         /etc/systemd/system/nigma-backup.timer

cat > /etc/logrotate.d/nigma-deploy <<'EOF'
/var/log/nigma-deploy.log /var/log/nigma-bootstrap.log {
    weekly
    rotate 8
    compress
    delaycompress
    missingok
    notifempty
}
EOF
install -d -o root -g root -m 755 /var/lib/nigma-deploy /etc/nigma

if [ "$RUNTIME" = "docker" ]; then
  echo docker > /etc/nigma/runtime
  # create /etc/nigma/.env from the template (secrets are generated later by the bootstrap)
  if [ ! -f /etc/nigma/.env ]; then
    TEMPLATE="$HERE/../../docker/prod/.env.example"
    [ -f "$TEMPLATE" ] || { echo "cannot find $TEMPLATE (run this from the infra checkout)" >&2; exit 1; }
    install -m 600 -o root -g root "$TEMPLATE" /etc/nigma/.env
  fi
  setkv() { # setkv KEY VALUE  (only if VALUE is non-empty)
    [ -n "$2" ] || return 0
    if grep -q "^$1=" /etc/nigma/.env; then sed -i "s#^$1=.*#$1=$2#" /etc/nigma/.env; else echo "$1=$2" >> /etc/nigma/.env; fi
  }
  setkv SITE_DOMAINS "$DOMAINS"; setkv CERT_NAME "$CERT_NAME"; setkv MEDIA_S3_URI "$MEDIA_S3"; setkv DB_BACKUP_S3_URI "$DB_S3"
  setkv HTTP_PORT "$HTTP_PORT"; setkv HTTPS_PORT "$HTTPS_PORT"
  [ "$STAGING" = 1 ] && setkv ACME_STAGING 1
  [ "$BACKUP_TIMER" = 1 ] && setkv BACKUP_TIMER 1
  echo "runtime: docker  (config: /etc/nigma/.env)"
elif [ "$RUNTIME" = "native" ]; then
  rm -f /etc/nigma/runtime
  echo "runtime: native"
else
  echo "runtime: unchanged ($(cat /etc/nigma/runtime 2>/dev/null || echo 'native, no marker'))"
fi

systemctl daemon-reload
systemctl enable --now nigma-deploy.timer
if [ -f /etc/nigma/.env ] && grep -q '^BACKUP_TIMER=1' /etc/nigma/.env; then systemctl enable --now nigma-backup.timer; fi
systemctl list-timers 'nigma-*' --no-pager

if [ "$NOW" = 1 ]; then
  echo "running the first deploy now (this builds the Docker environment if it is missing)..."
  /usr/local/sbin/nigma-deploy --force
fi
