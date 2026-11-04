#!/bin/bash
#
# nigma-data — media library and database backup/restore against a PRIVATE S3 bucket (ADR-016).
#
# Installed at /usr/local/sbin/nigma-data. For hosts with the "docker" runtime.
# The media library is NOT stored in git: it is large, changes at runtime, and may contain
# visitor uploads. The bucket should be private, versioned and encrypted (see docker/prod/README.md).
#
#   nigma-data media-backup           upload new/changed media (uploads/) to $MEDIA_S3_URI (never deletes)
#   nigma-data media-restore          download the media library into uploads/ (as www-data)
#   nigma-data db-backup              dump the database and upload it (db-<date>.sql.gz and latest.sql.gz)
#   nigma-data db-restore [src]       restore a dump (file, s3:// URI, or latest) into an EMPTY database
#   nigma-data backup                 media-backup + db-backup (what the daily timer runs)
#   nigma-data status                 show configuration
#
# Authentication is the instance's IAM role (no keys on disk). The AWS CLI runs in a container
# on the host network so it can reach the instance metadata service.
#
set -uo pipefail

ENV_FILE=/etc/nigma/.env
INFRA=/opt/nigma/infra
COMPOSE_FILE="$INFRA/docker/prod/docker-compose.yml"
APP=/var/www/nigma
STATE=/var/lib/nigma
BK="$STATE/backups"
LOG=/var/log/nigma-bootstrap.log
AWSCLI_IMAGE="${AWSCLI_IMAGE:-amazon/aws-cli:latest}"
# Generated assets and tool leftovers that must not be stored or restored.
EXCLUDES=(--exclude 'fusion-styles/*' --exclude 'fusion-scripts/*' --exclude 'wp-migrate-db/*' --exclude 'snapshot-backups/*' --exclude '*.log' --exclude '.litespeed_conf.dat')

log() { printf '%s nigma-data: %s\n' "$(date -u +%FT%TZ)" "$*" | tee -a "$LOG" >&2; }
die() { log "ERROR: $*"; exit 1; }
[ "$(id -u)" = "0" ] || { echo "run as root" >&2; exit 2; }
[ -f "$ENV_FILE" ] || die "$ENV_FILE missing (environment not bootstrapped)"
set -a; . "$ENV_FILE"; set +a
dc() { CODE_UID="$(id -u "${CODE_USER:-ubuntu}" 2>/dev/null || echo 1000)" WEB_GID=33 docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"; }

# aws <uid:gid> <host-dir-to-mount-at-/data> <aws args...>
aws() {
  local ug="$1" dir="$2"; shift 2
  docker run --rm --network host -u "$ug" -e HOME=/tmp -v "$dir:/data" "$AWSCLI_IMAGE" "$@"
}
need() { [ -n "${!1:-}" ] || die "$1 is not set in $ENV_FILE"; }

media_backup() {
  need MEDIA_S3_URI
  log "syncing media library -> $MEDIA_S3_URI (additions and changes only)"
  aws 0:0 "$APP/wp-content/uploads" s3 sync /data "$MEDIA_S3_URI" "${EXCLUDES[@]}" --only-show-errors
}

media_restore() {
  need MEDIA_S3_URI
  mkdir -p "$APP/wp-content/uploads"
  log "restoring media library <- $MEDIA_S3_URI"
  aws 33:33 "$APP/wp-content/uploads" s3 sync "$MEDIA_S3_URI" /data "${EXCLUDES[@]}" --only-show-errors || die "media restore failed"
  log "media restored: $(find "$APP/wp-content/uploads" -type f | wc -l) files"
}

db_backup() {
  need DB_BACKUP_S3_URI
  mkdir -p "$BK"; chmod 700 "$BK"
  local f="$BK/db-$(date -u +%Y%m%d-%H%M%S).sql.gz"
  log "dumping database"
  # credentials come from the db container's own environment: nothing secret on this command line
  dc exec -T db sh -c 'mariadb-dump --single-transaction --quick --no-tablespaces -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' | gzip -6 > "$f" \
    || { rm -f "$f"; die "database dump failed"; }
  gzip -t "$f" || { rm -f "$f"; die "database dump is corrupt"; }
  [ "$(stat -c %s "$f")" -gt 1024 ] || { rm -f "$f"; die "database dump is suspiciously small"; }
  log "uploading $(basename "$f") ($(stat -c %s "$f") bytes)"
  aws 0:0 "$BK" s3 cp "/data/$(basename "$f")" "${DB_BACKUP_S3_URI%/}/$(basename "$f")" --only-show-errors || die "upload failed"
  aws 0:0 "$BK" s3 cp "/data/$(basename "$f")" "${DB_BACKUP_S3_URI%/}/latest.sql.gz" --only-show-errors || die "upload of latest failed"
  ls -1t "$BK"/db-*.sql.gz 2>/dev/null | tail -n +4 | xargs -r rm -f     # keep 3 local copies
}

db_restore() {
  local src="${1:-latest}" file
  mkdir -p "$BK"; chmod 700 "$BK"
  local tables
  tables="$(dc exec -T db sh -c 'mariadb -N -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE" -e "SHOW TABLES"' 2>/dev/null | wc -l)"
  [ "${tables:-0}" -eq 0 ] || [ "${FORCE:-0}" = 1 ] || die "database is not empty ($tables tables); refusing to overwrite (set FORCE=1 to override)"
  if [ -f "$src" ]; then file="$src"
  else
    need DB_BACKUP_S3_URI
    local uri="$src"; [ "$src" = latest ] && uri="${DB_BACKUP_S3_URI%/}/latest.sql.gz"
    file="$BK/restore-$(date -u +%s).sql.gz"
    log "downloading $uri"
    aws 0:0 "$BK" s3 cp "$uri" "/data/$(basename "$file")" --only-show-errors || die "download failed"
  fi
  gzip -t "$file" || die "$file is not a valid gzip file"
  log "importing into the database"
  gunzip -c "$file" | dc exec -T db sh -c 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' || die "import failed"
  log "database restored"
}

case "${1:-}" in
  media-backup)  media_backup ;;
  media-restore) media_restore ;;
  db-backup)     db_backup ;;
  db-restore)    shift; db_restore "${1:-latest}" ;;
  backup)        media_backup && db_backup ;;
  status)
    printf 'MEDIA_S3_URI      %s\nDB_BACKUP_S3_URI  %s\nBACKUP_TIMER      %s\n' "${MEDIA_S3_URI:-<unset>}" "${DB_BACKUP_S3_URI:-<unset>}" "${BACKUP_TIMER:-0}"
    systemctl list-timers nigma-backup.timer --no-pager 2>/dev/null | sed -n 1,3p ;;
  *) sed -n '2,20p' "$0"; exit 2 ;;
esac
