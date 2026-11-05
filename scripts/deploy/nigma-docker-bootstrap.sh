#!/bin/bash
#
# nigma-docker-bootstrap — make sure the NIGMA Docker runtime exists and is healthy (ADR-016).
#
# Installed at /usr/local/sbin/nigma-docker-bootstrap. Called by nigma-deploy on hosts
# whose /etc/nigma/runtime says "docker":
#
#   environment present and healthy?  -> yes: the deployer goes straight to deploying code
#                                     -> no : this script builds/repairs it first
#
# Usage:
#   nigma-docker-bootstrap --check       exit 0 only if everything is present and healthy (changes nothing)
#   nigma-docker-bootstrap               build or repair the environment, then verify it (idempotent)
#   nigma-docker-bootstrap --status      print a status table
#   nigma-docker-bootstrap --ssl [--force]   check / obtain / renew certificates only
#   nigma-docker-bootstrap --reload-ssl      reload the web server if certbot renewed a certificate
#   nigma-docker-bootstrap --flush-redis     flush the object cache
#
# What "ready" means: Docker present; code + WordPress core + wp-config in place; web, db, redis and
# certbot containers running and healthy; configuration matches the repository; the TLS certificate
# is valid (or a placeholder while DNS does not point here yet).
#
set -uo pipefail

ETC=/etc/nigma
ENV_FILE="$ETC/.env"
CONF_DIR="$ETC/docker"
INFRA=/opt/nigma/infra
APP=/var/www/nigma
STATE=/var/lib/nigma
LOGDIR=/var/log/nigma
LOG=/var/log/nigma-bootstrap.log
COMPOSE_FILE="$INFRA/docker/prod/docker-compose.yml"
HASH_FILE="$STATE/env.hash"
SERVICES="web db redis certbot"
WEB_UID=33; WEB_GID=33   # www-data inside the containers (and on Ubuntu hosts)

log()  { printf '%s %s\n' "$(date -u +%FT%TZ)" "$*" | tee -a "$LOG" >&2; }
warn() { log "WARN: $*"; }
die()  { log "ERROR: $*"; exit 1; }

[ "$(id -u)" = "0" ] || { echo "run as root" >&2; exit 2; }
mkdir -p "$(dirname "$LOG")"; touch "$LOG"

MODE=bootstrap
case "${1:-}" in
  --check) MODE=check ;; --status) MODE=status ;; --ssl) MODE=ssl ;;
  --reload-ssl) MODE=reload ;; --flush-redis) MODE=flush ;; "") ;;
  *) echo "unknown option: $1" >&2; exit 2 ;;
esac
FORCE=0; [ "${2:-}" = "--force" ] && FORCE=1

load_env() { [ -f "$ENV_FILE" ] || return 1; set -a; . "$ENV_FILE"; set +a; CODE_USER="${CODE_USER:-ubuntu}"; return 0; }
dc() {
  CODE_UID="$(id -u "${CODE_USER:-ubuntu}" 2>/dev/null || echo 1000)" WEB_GID="$WEB_GID" \
    docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}
code_as() { sudo -u "${CODE_USER:-ubuntu}" -H "$@"; }
rand() { openssl rand -hex "${1:-24}"; }
first_domain() { echo "$SITE_DOMAINS" | tr ',' '\n' | sed 's/ //g' | head -1; }

# ------------------------------------------------------------------ checks (read-only)
docker_ok()   { command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1 && docker info >/dev/null 2>&1; }
code_ok()     { [ -d "$APP/.git" ] && [ -f "$APP/wp-settings.php" ] && [ -f "$APP/wp-config.php" ] && [ -d "$INFRA/docker/prod" ]; }
config_ok()   { [ -f "$CONF_DIR/lsws/httpd_config.conf" ] && [ -f "$CONF_DIR/lsws/vhconf.conf" ] && [ "$(cat "$HASH_FILE" 2>/dev/null)" = "$(env_hash)" ]; }

# Hash of everything that defines the environment; a change means containers must be re-created.
env_hash() {
  {
    cat "$COMPOSE_FILE" "$INFRA/docker/prod/lsws/httpd_config.conf" "$INFRA/docker/prod/lsws/vhconf.conf.tpl" \
        "$INFRA/docker/prod/php/99-nigma.ini" "$INFRA/scripts/deploy/vhost-edge-rules.conf" 2>/dev/null
    grep -E '^(SITE_DOMAINS|CERT_NAME|TZ|DB_NAME|DB_USER|DB_BUFFER_POOL|HTTP_PORT|HTTPS_PORT|CODE_USER|ACME_STAGING)=' "$ENV_FILE" 2>/dev/null
  } | sha256sum | cut -d' ' -f1
}

container_state() {   # prints: running+healthy | running | missing | unhealthy | stopped
  local id; id="$(dc ps -q "$1" 2>/dev/null | head -1)"
  [ -n "$id" ] || { echo missing; return; }
  local run health
  run="$(docker inspect -f '{{.State.Running}}' "$id" 2>/dev/null)"
  health="$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}none{{end}}' "$id" 2>/dev/null)"
  [ "$run" = "true" ] || { echo stopped; return; }
  case "$health" in healthy) echo "running+healthy" ;; starting|none) echo running ;; *) echo unhealthy ;; esac
}
services_ok() { local s; for s in $SERVICES; do [ "$(container_state "$s")" = "running+healthy" ] || return 1; done; return 0; }

public_ip() {
  local t ip
  t="$(curl -s -m 3 -X PUT http://169.254.169.254/latest/api/token -H 'X-aws-ec2-metadata-token-ttl-seconds: 60' 2>/dev/null)"
  ip="$(curl -s -m 3 -H "X-aws-ec2-metadata-token: $t" http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null)"
  [ -n "$ip" ] || ip="$(curl -s -m 5 https://checkip.amazonaws.com 2>/dev/null | tr -d '[:space:]')"
  echo "$ip"
}
any_domain_points_here() {
  local ip d r; ip="$(public_ip)"; [ -n "$ip" ] || return 1
  for d in $(echo "$SITE_DOMAINS" | tr ',' ' '); do
    r="$(getent ahostsv4 "$d" 2>/dev/null | awk 'NR==1{print $1}')"; [ "$r" = "$ip" ] && return 0
  done; return 1
}

CERT_DIR() { echo "$STATE/letsencrypt/live/$CERT_NAME"; }
# ok | expiring | placeholder-ready | placeholder-pending | missing
ssl_state() {
  local d; d="$(CERT_DIR)"
  [ -f "$d/fullchain.pem" ] || { echo missing; return; }
  if [ -f "$d/.placeholder" ]; then
    if any_domain_points_here; then echo placeholder-ready; else echo placeholder-pending; fi; return
  fi
  openssl x509 -checkend $((30*86400)) -noout -in "$d/fullchain.pem" >/dev/null 2>&1 && echo ok || echo expiring
}
# ok and "placeholder, DNS not here yet" are healthy. If a certificate is wanted but the last attempt was
# under an hour ago we are in back-off: treat that as healthy so a failing issuance does not retrigger repairs.
ssl_ok() {
  case "$(ssl_state)" in
    ok|placeholder-pending) return 0 ;;
    *) local last; last="$(cat "$STATE/ssl.last_attempt" 2>/dev/null || echo 0)"; [ $(( $(date +%s) - last )) -lt 3600 ] ;;
  esac
}

full_check() { docker_ok && load_env && code_ok && config_ok && services_ok && ssl_ok; }

print_status() {
  load_env || { echo "no $ENV_FILE yet (environment not bootstrapped)"; return 1; }
  printf '%-18s %s\n' "docker" "$(docker_ok && echo ok || echo MISSING)"
  printf '%-18s %s\n' "code + core" "$(code_ok && echo ok || echo MISSING)"
  printf '%-18s %s\n' "config in sync" "$(config_ok && echo ok || echo OUT-OF-SYNC)"
  local s; for s in $SERVICES; do printf '%-18s %s\n' "container $s" "$(container_state "$s")"; done
  printf '%-18s %s\n' "ssl ($CERT_NAME)" "$(ssl_state)"
  printf '%-18s %s\n' "runtime marker" "$(cat "$ETC/runtime" 2>/dev/null)"
}

# ------------------------------------------------------------------ small actions
reload_ssl() {
  load_env || return 0
  if [ -f "$STATE/letsencrypt/.renewed" ]; then
    log "certificate renewed; reloading the web server"
    dc exec -T web /usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1 && rm -f "$STATE/letsencrypt/.renewed"
  fi
}
flush_redis() { load_env || return 0; dc exec -T redis redis-cli flushdb >/dev/null 2>&1; }

case "$MODE" in
  check)  full_check; exit $? ;;
  status) print_status; exit $? ;;
  reload) reload_ssl; exit 0 ;;
  flush)  flush_redis; exit 0 ;;
esac

# ================================================================== build / repair
fix_permissions() {
  local W="$APP/wp-content"
  mkdir -p "$W/uploads" "$W/litespeed" "$W/cache" "$W/upgrade"
  [ -f "$W/.litespeed_conf.dat" ] || : > "$W/.litespeed_conf.dat"
  [ -f "$W/debug.log" ] || : > "$W/debug.log"
  chown -R "$WEB_UID:$WEB_GID" "$W/uploads" "$W/litespeed" "$W/cache"
  chown "${CODE_USER}:$WEB_GID" "$W/upgrade"
  chown "$WEB_UID:$WEB_GID" "$W/.litespeed_conf.dat"; chmod 664 "$W/.litespeed_conf.dat"
  chown "$WEB_UID:${CODE_USER}" "$W/debug.log" 2>/dev/null || chown "$WEB_UID:$WEB_GID" "$W/debug.log"; chmod 664 "$W/debug.log"
}

ensure_docker() {
  if ! command -v docker >/dev/null 2>&1 || ! docker compose version >/dev/null 2>&1; then
    log "installing Docker Engine and the compose plugin (Ubuntu packages)"
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -qq || die "apt-get update failed"
    apt-get install -y -qq docker.io docker-compose-v2 git curl ca-certificates openssl python3 sudo >/dev/null || die "could not install docker"
  fi
  # Bounded container logs: an unbounded log once filled a server disk.
  if [ ! -f /etc/docker/daemon.json ]; then
    mkdir -p /etc/docker
    printf '{\n  "log-driver": "json-file",\n  "log-opts": { "max-size": "10m", "max-file": "3" },\n  "live-restore": true\n}\n' > /etc/docker/daemon.json
    systemctl restart docker >/dev/null 2>&1 || true
  fi
  systemctl enable --now docker >/dev/null 2>&1 || true
  for i in $(seq 1 20); do docker info >/dev/null 2>&1 && break; sleep 2; done
  docker info >/dev/null 2>&1 || die "docker daemon is not running"
  # NB: nothing here mounts or exposes the Docker socket to a container.
}

ensure_env_file() {
  mkdir -p "$ETC"; chmod 755 "$ETC"
  if [ ! -f "$ENV_FILE" ]; then
    [ -f "$INFRA/docker/prod/.env.example" ] || die "$INFRA/docker/prod/.env.example missing (clone the repo to $INFRA first)"
    log "creating $ENV_FILE from .env.example"
    install -m 600 -o root -g root "$INFRA/docker/prod/.env.example" "$ENV_FILE"
  fi
  chmod 600 "$ENV_FILE"
  load_env
  local changed=0
  if [ -z "${DB_PASSWORD:-}" ];      then sed -i "s#^DB_PASSWORD=.*#DB_PASSWORD=$(rand 24)#" "$ENV_FILE"; changed=1; fi
  if [ -z "${DB_ROOT_PASSWORD:-}" ]; then sed -i "s#^DB_ROOT_PASSWORD=.*#DB_ROOT_PASSWORD=$(rand 24)#" "$ENV_FILE"; changed=1; fi
  [ "$changed" = 1 ] && { log "generated database passwords (stored only in $ENV_FILE)"; load_env; }
  [ -n "${SITE_DOMAINS:-}" ] && [ -n "${CERT_NAME:-}" ] || die "SITE_DOMAINS / CERT_NAME missing in $ENV_FILE"
}

ensure_dirs() {
  mkdir -p "$STATE"/{db,letsencrypt,lscache,backups} "$STATE/acme/.well-known/acme-challenge" "$LOGDIR/lsws" "$CONF_DIR/lsws"
  chown "$WEB_UID:$WEB_GID" "$STATE/lscache"
  chmod 755 "$STATE" "$STATE/acme" "$STATE/acme/.well-known" "$STATE/acme/.well-known/acme-challenge"
  chmod 700 "$STATE/backups"
}

ensure_checkouts() {
  # infra checkout: compose + config templates + deploy scripts (sparse)
  if [ ! -d "$INFRA/.git" ]; then
    log "cloning infrastructure files to $INFRA"
    mkdir -p "$(dirname "$INFRA")"
    git clone -q --filter=blob:none --no-checkout "$GIT_REPO" "$INFRA" || die "git clone (infra) failed"
    git -C "$INFRA" sparse-checkout init --no-cone && git -C "$INFRA" sparse-checkout set '/docker/prod/' '/scripts/deploy/' && git -C "$INFRA" checkout -q "$GIT_BRANCH" || die "infra checkout failed"
  else
    git -C "$INFRA" fetch -q origin "$GIT_BRANCH" 2>>"$LOG" && git -C "$INFRA" merge -q --ff-only "origin/$GIT_BRANCH" >>"$LOG" 2>&1 || warn "could not update $INFRA (continuing with what is there)"
  fi
  # site checkout: wp-content only, owned by the deploy user
  if [ ! -d "$APP/.git" ]; then
    log "cloning wp-content to $APP"
    mkdir -p "$APP"; chown "$CODE_USER:$WEB_GID" "$APP"
    code_as git clone -q --filter=blob:none --no-checkout "$GIT_REPO" "$APP" || die "git clone (site) failed"
    code_as git -C "$APP" sparse-checkout init --no-cone && code_as git -C "$APP" sparse-checkout set '/wp-content/' && code_as git -C "$APP" checkout -q "$GIT_BRANCH" || die "site checkout failed"
  fi
}

wpcli() { dc --profile tools run --rm -T --no-deps wpcli "$@"; }

ensure_core() {
  if [ ! -f "$APP/wp-settings.php" ]; then
    log "installing WordPress $WP_VERSION core"
    wpcli core download --version="$WP_VERSION" --skip-content --path=/var/www/nigma >>"$LOG" 2>&1 || die "wp core download failed"
  fi
  wpcli core verify-checksums --path=/var/www/nigma >>"$LOG" 2>&1 || die "WordPress core does not match the official checksums"
}

ensure_wpconfig() {
  [ -f "$APP/wp-config.php" ] && return 0
  log "writing wp-config.php (new salts; database credentials from $ENV_FILE)"
  salt() { openssl rand -base64 48 | tr -d '\n'; }
  ( umask 077
  cat > "$APP/wp-config.php" <<EOF
<?php
define( 'DB_NAME', '${DB_NAME}' );
define( 'DB_USER', '${DB_USER}' );
define( 'DB_PASSWORD', '${DB_PASSWORD}' );
define( 'DB_HOST', 'db' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
define( 'AUTH_KEY',         '$(salt)' );
define( 'SECURE_AUTH_KEY',  '$(salt)' );
define( 'LOGGED_IN_KEY',    '$(salt)' );
define( 'NONCE_KEY',        '$(salt)' );
define( 'AUTH_SALT',        '$(salt)' );
define( 'SECURE_AUTH_SALT', '$(salt)' );
define( 'LOGGED_IN_SALT',   '$(salt)' );
define( 'NONCE_SALT',       '$(salt)' );
define( 'WP_CACHE_KEY_SALT', '$(salt)' );
\$table_prefix = '${TABLE_PREFIX:-ngm_}';
define( 'WP_DEBUG', false );
define( 'WP_CACHE', true );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'DISALLOW_FILE_EDIT', true );

/* That's all, stop editing! Happy publishing. */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
EOF
  )
  chown "$CODE_USER:$WEB_GID" "$APP/wp-config.php"; chmod 640 "$APP/wp-config.php"
}

render_config() {
  mkdir -p "$CONF_DIR/lsws"
  sed "s#@@CERT_NAME@@#$CERT_NAME#g" "$INFRA/docker/prod/lsws/httpd_config.conf" > "$CONF_DIR/lsws/httpd_config.conf"
  python3 - "$INFRA/docker/prod/lsws/vhconf.conf.tpl" "$INFRA/scripts/deploy/vhost-edge-rules.conf" "$CERT_NAME" > "$CONF_DIR/lsws/vhconf.conf" <<'PY'
import sys
tpl = open(sys.argv[1]).read(); rules = open(sys.argv[2]).read().rstrip("\n"); cert = sys.argv[3]
print(tpl.replace("@@EDGE_RULES@@", rules).replace("@@CERT_NAME@@", cert), end="")
PY
  chmod 644 "$CONF_DIR/lsws/"*.conf
}

# A self-signed stand-in so the web server can start before a real certificate exists.
ensure_placeholder_cert() {
  local d; d="$(CERT_DIR)"; [ -f "$d/fullchain.pem" ] && return 0
  log "creating a placeholder certificate (real one is requested once DNS points here)"
  mkdir -p "$d"
  local san="" x; for x in $(echo "$SITE_DOMAINS" | tr ',' ' '); do san="${san:+$san,}DNS:$x"; done
  openssl req -x509 -nodes -newkey rsa:2048 -days 30 -subj "/CN=$(first_domain)" -addext "subjectAltName=$san" \
    -keyout "$d/privkey.pem" -out "$d/fullchain.pem" 2>/dev/null || die "placeholder certificate failed"
  chmod 600 "$d/privkey.pem"; touch "$d/.placeholder"
}

compose_up() {
  log "starting containers (web, db, redis, certbot)"
  dc up -d --remove-orphans --wait --wait-timeout 300 >>"$LOG" 2>&1 || { dc ps >>"$LOG" 2>&1; die "containers did not become healthy (see $LOG)"; }
  echo "$(env_hash)" > "$HASH_FILE"
}

ensure_data() {
  local data=/usr/local/sbin/nigma-data
  [ -x "$data" ] || { warn "$data not installed; skipping media/database restore"; return 0; }
  if [ -n "${MEDIA_S3_URI:-}" ] && [ -z "$(find "$APP/wp-content/uploads" -type f ! -name 'index.php' ! -name 'index.html' ! -name '.htaccess' 2>/dev/null | head -1)" ]; then
    log "media library is empty; restoring from $MEDIA_S3_URI"
    "$data" media-restore >>"$LOG" 2>&1 || warn "media restore failed (see $LOG)"
  fi
  local tables
  tables="$(dc exec -T db sh -c 'mariadb -N -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE" -e "SHOW TABLES"' 2>/dev/null | wc -l)"
  if [ "${tables:-0}" -eq 0 ]; then
    if [ -n "${DB_BACKUP_S3_URI:-}" ]; then
      log "database is empty; restoring the latest dump from $DB_BACKUP_S3_URI"
      "$data" db-restore >>"$LOG" 2>&1 || warn "database restore failed (see $LOG)"
    else
      warn "database is EMPTY and DB_BACKUP_S3_URI is not set: restore a dump with 'nigma-data db-restore <file>' or install WordPress"
    fi
  fi
}

# After a restore from the native server the LiteSpeed object-cache host points at 127.0.0.1; here Redis is "redis".
post_restore_fixes() {
  wpcli core is-installed --path=/var/www/nigma >/dev/null 2>&1 || return 0
  if dc --profile tools run --rm -T wpcli plugin is-active litespeed-cache --path=/var/www/nigma >/dev/null 2>&1; then
    for kv in "object-host redis" "object-port 6379" "object-kind 1"; do set -- $kv; wpcli litespeed-option set "$1" "$2" --path=/var/www/nigma >>"$LOG" 2>&1; done
    local lib="$APP/wp-content/plugins/litespeed-cache/lib/object-cache.php"
    [ -f "$lib" ] && install -o "$CODE_USER" -g "$WEB_GID" -m 644 "$lib" "$APP/wp-content/object-cache.php"
    flush_redis
  fi
}

# Obtain / renew the real certificate when at least one domain points at this host.
ensure_ssl() {
  local st d args; st="$(ssl_state)"; d="$(CERT_DIR)"
  case "$st" in
    ok) return 0 ;;
    placeholder-pending) log "SSL pending: no domain in SITE_DOMAINS resolves to this host's public IP yet (placeholder certificate in use)"; return 0 ;;
    missing|placeholder-ready|expiring) ;;
  esac
  local last; last="$(cat "$STATE/ssl.last_attempt" 2>/dev/null || echo 0)"
  if [ "$FORCE" != 1 ] && [ $(( $(date +%s) - last )) -lt 3600 ]; then log "SSL: last attempt was under an hour ago; waiting (use --ssl --force to retry now)"; return 0; fi
  date +%s > "$STATE/ssl.last_attempt"
  log "requesting a certificate for: $SITE_DOMAINS (staging=${ACME_STAGING:-0})"
  args=(certonly --webroot -w /var/www/acme --cert-name "$CERT_NAME" --non-interactive --agree-tos --keep-until-expiring)
  for x in $(echo "$SITE_DOMAINS" | tr ',' ' '); do args+=(-d "$x"); done
  [ "${ACME_STAGING:-0}" = "1" ] && args+=(--staging)
  if [ -n "${ACME_EMAIL:-}" ]; then args+=(--email "$ACME_EMAIL"); else args+=(--register-unsafely-without-email); fi
  # a placeholder lineage must be removed or certbot refuses to create the real one
  if [ -f "$d/.placeholder" ]; then rm -rf "$d" "$STATE/letsencrypt/archive/$CERT_NAME" "$STATE/letsencrypt/renewal/$CERT_NAME.conf"; fi
  # The renewal sidecar holds certbot's lock while it runs: wait for it instead of failing.
  local out ok=1 try
  for try in 1 2 3 4 5 6; do
    if out="$(dc run --rm -T --no-deps --entrypoint certbot certbot "${args[@]}" 2>&1)"; then ok=0; echo "$out" >>"$LOG"; break; fi
    echo "$out" >>"$LOG"
    if grep -q 'Another instance of Certbot is already running' <<<"$out"; then log "certbot is busy (renewal sidecar); retry $try/6 in 20s"; sleep 20; continue; fi
    break
  done
  if [ "$ok" = 0 ]; then
    log "certificate issued for $CERT_NAME"
    dc exec -T web /usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1
  else
    warn "certificate request failed (see $LOG). Restoring the placeholder so the site keeps serving HTTPS."
    ensure_placeholder_cert
    dc exec -T web /usr/local/lsws/bin/lswsctrl restart >/dev/null 2>&1
    return 1
  fi
}

verify_all() {
  local s bad=0
  print_status | tee -a "$LOG" >&2
  for s in $SERVICES; do [ "$(container_state "$s")" = "running+healthy" ] || { warn "container $s is not running+healthy"; bad=1; }; done
  # HTTPS answers on the published port (certificate verification is skipped: a placeholder may be active)
  local h; h="$(first_domain)"
  local c; c="$(curl -sk -o /dev/null -w '%{http_code}' --max-time 20 --resolve "$h:${HTTPS_PORT:-443}:127.0.0.1" "https://$h:${HTTPS_PORT:-443}/")"
  log "HTTPS check on $h: HTTP $c"
  [ "$c" != "000" ] || { warn "HTTPS did not answer"; bad=1; }
  return $bad
}

if [ "$MODE" = "ssl" ]; then load_env || die "no $ENV_FILE"; ensure_ssl; exit $?; fi

# ---------- bootstrap ----------
log "=== nigma-docker-bootstrap: building / repairing the Docker environment ==="
ensure_docker
# .env.example (the template for the env file) lives in the infra checkout, so make sure that exists first.
if [ ! -d "$INFRA/.git" ]; then
  GIT_REPO="${GIT_REPO:-https://github.com/arish-cactix/nigma-wordpress.git}"; GIT_BRANCH="${GIT_BRANCH:-main}"
  log "cloning infrastructure files to $INFRA"
  mkdir -p "$(dirname "$INFRA")"
  git clone -q --filter=blob:none --no-checkout "$GIT_REPO" "$INFRA" \
    && git -C "$INFRA" sparse-checkout init --no-cone \
    && git -C "$INFRA" sparse-checkout set '/docker/prod/' '/scripts/deploy/' \
    && git -C "$INFRA" checkout -q "$GIT_BRANCH" || die "cannot clone the repository to $INFRA"
fi
ensure_env_file
ensure_dirs
ensure_checkouts
fix_permissions
render_config
ensure_placeholder_cert
ensure_core
ensure_wpconfig
fix_permissions
compose_up
ensure_data
post_restore_fixes
ensure_ssl || warn "SSL is not ready yet; the site is served with the placeholder certificate"
if verify_all; then log "=== environment is ready ==="; exit 0; else die "environment verification failed"; fi
