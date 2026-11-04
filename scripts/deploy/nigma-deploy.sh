#!/bin/bash
#
# nigma-deploy — pull-based deploy for the NIGMA WordPress server.
#
# Installed at /usr/local/sbin/nigma-deploy and run as root by
# nigma-deploy.timer (every minute). It needs no inbound port and no
# credentials: the repository is public, so it is fetched over HTTPS.
#
# What it does when origin/main has a new commit:
#   1. fast-forward /var/www/nigma to it (as the code owner, not root)
#   2. fix ownership for the hardened layout (code read-only to PHP)
#   3. clear the page, asset and object caches
#   4. health-check the site; on failure roll back to the previous commit
#   5. publish the deployed commit at /deploy-version.txt so CI can verify it
#
# Runtime (ADR-014 / ADR-016): /etc/nigma/runtime selects how the site is hosted.
#   (no file) or "native"  OpenLiteSpeed installed directly on the host (the live server). Unchanged behaviour.
#   "docker"               the Docker stack in docker/prod. Before deploying code, every run asks
#                          "is the Docker environment present and healthy?"
#                            yes -> go straight to deploying the code
#                            no  -> build/repair the environment first (nigma-docker-bootstrap), then deploy
#
# Usage:
#   nigma-deploy                 normal run (what the timer calls)
#   nigma-deploy --force         redeploy even if already up to date
#   nigma-deploy --pin <sha>     deploy <sha> and pause auto-deploys (emergency rollback)
#   nigma-deploy --unpin         resume auto-deploys of origin/main
#   nigma-deploy --status        show current, pinned and last-failed commits
#
set -uo pipefail

APP=/var/www/nigma
BRANCH=main
CODE_USER=ubuntu
WEB_USER=www-data
WEB_GROUP=www-data
SITE_HOST=www.nigma.ae
STATE=/var/lib/nigma-deploy
LOG=/var/log/nigma-deploy.log
LOCK=/run/nigma-deploy.lock
CACHE_DIR=/usr/local/lsws/cachedata/nigma
VERSION_FILE="$APP/deploy-version.txt"
HTTPS_PORT=443

# No marker file means "native": hosts installed before the Docker runtime existed behave exactly as before.
RUNTIME=native
[ -f /etc/nigma/runtime ] && RUNTIME="$(tr -d '[:space:]' < /etc/nigma/runtime)"
if [ "$RUNTIME" = "docker" ]; then
  CACHE_DIR=/var/lib/nigma/lscache
  if [ -f /etc/nigma/.env ]; then
    _domains="$(grep -E '^SITE_DOMAINS=' /etc/nigma/.env | cut -d= -f2- | tr ',' '\n' | sed 's/ //g')"
    SITE_HOST="$(printf '%s\n' "$_domains" | grep -m1 '^www\.' || printf '%s\n' "$_domains" | head -1)"
    _p="$(grep -E '^HTTPS_PORT=' /etc/nigma/.env | cut -d= -f2-)"; [ -n "$_p" ] && HTTPS_PORT="$_p"
    _b="$(grep -E '^GIT_BRANCH=' /etc/nigma/.env | cut -d= -f2-)"; [ -n "$_b" ] && BRANCH="$_b"   # docker hosts follow GIT_BRANCH
  fi
fi

log() { printf '%s %s\n' "$(date -u +%FT%TZ)" "$*" | tee -a "$LOG" >&2; logger -t nigma-deploy -- "$*" 2>/dev/null || true; }
git_as() { sudo -u "$CODE_USER" -H git -C "$APP" "$@"; }

fix_permissions() {
  # Code: owned by the deploy user, readable (not writable) by PHP.
  find "$APP/wp-content" \
    -path "$APP/wp-content/uploads" -prune -o \
    -path "$APP/wp-content/litespeed" -prune -o \
    -path "$APP/wp-content/cache" -prune -o \
    -exec chown "$CODE_USER:$WEB_GROUP" {} + 2>/dev/null
  # The only places PHP may write.
  mkdir -p "$APP/wp-content/uploads" "$APP/wp-content/litespeed" "$APP/wp-content/cache" "$APP/wp-content/upgrade"
  chown -R "$WEB_USER:$WEB_GROUP" "$APP/wp-content/uploads" "$APP/wp-content/litespeed" "$APP/wp-content/cache"
  chown "$CODE_USER:$WEB_GROUP" "$APP/wp-content/upgrade"
  [ -f "$APP/wp-content/.litespeed_conf.dat" ] || : > "$APP/wp-content/.litespeed_conf.dat"
  chown "$WEB_USER:$WEB_GROUP" "$APP/wp-content/.litespeed_conf.dat"; chmod 664 "$APP/wp-content/.litespeed_conf.dat"
  [ -f "$APP/wp-content/debug.log" ] || : > "$APP/wp-content/debug.log"
  chown "$WEB_USER:$CODE_USER" "$APP/wp-content/debug.log"; chmod 664 "$APP/wp-content/debug.log"
}

clear_caches() {
  [ -d "$CACHE_DIR" ] && find "$CACHE_DIR" -mindepth 1 -delete 2>/dev/null
  find "$APP/wp-content/litespeed" -mindepth 1 -delete 2>/dev/null
  flush_object_cache
  return 0
}

flush_object_cache() {
  if [ "$RUNTIME" = "docker" ]; then nigma-docker-bootstrap --flush-redis >/dev/null 2>&1
  else command -v redis-cli >/dev/null && redis-cli flushdb >/dev/null 2>&1
  fi
  return 0
}

# Docker runtime only: "environment present and healthy? yes -> deploy code; no -> build it first".
# Returns non-zero (and deploys nothing) if the environment cannot be made healthy.
ensure_runtime() {
  [ "$RUNTIME" = "docker" ] || return 0
  nigma-docker-bootstrap --reload-ssl >/dev/null 2>&1          # pick up a renewed certificate
  if nigma-docker-bootstrap --check >/dev/null 2>&1; then return 0; fi
  local now last; now=$(date +%s); last=$(cat "$STATE/bootstrap.last" 2>/dev/null || echo 0)
  if [ "${1:-}" != "now" ] && [ $((now - last)) -lt 600 ]; then
    log "Docker environment is not healthy; last repair attempt was under 10 minutes ago - not deploying code yet"
    return 1
  fi
  echo "$now" > "$STATE/bootstrap.last"
  log "Docker environment missing or unhealthy -> bootstrapping it before deploying code"
  if nigma-docker-bootstrap >>"$LOG" 2>&1; then log "Docker environment is ready"; return 0; fi
  log "ERROR: Docker bootstrap failed (see /var/log/nigma-bootstrap.log); code NOT deployed"
  return 1
}

health_check() {
  local ok=0 attempt code1 code2
  for attempt in 1 2 3 4 5 6; do
    sleep 3
    code1=$(curl -sk -o /dev/null -w '%{http_code}' --max-time 25 --resolve "$SITE_HOST:$HTTPS_PORT:127.0.0.1" "https://$SITE_HOST:$HTTPS_PORT/")
    code2=$(curl -sk -o /dev/null -w '%{http_code}' --max-time 25 --resolve "$SITE_HOST:$HTTPS_PORT:127.0.0.1" "https://$SITE_HOST:$HTTPS_PORT/?s=healthcheck")
    if [ "$code1" = "200" ] && [ "$code2" = "200" ]; then ok=1; break; fi
    log "health check attempt $attempt: / -> $code1, /?s= -> $code2"
  done
  [ "$ok" = "1" ]
}

publish_version() {
  local sha tmp
  sha=$(git_as rev-parse HEAD)
  tmp="$VERSION_FILE.tmp"
  printf '%s\n' "$sha" > "$tmp" && chown root:root "$tmp" && chmod 644 "$tmp" && mv -f "$tmp" "$VERSION_FILE"
  printf '%s\n' "$sha" > "$STATE/current"
}

# deploy <target-sha> <mode: ff|hard>
deploy() {
  local target="$1" mode="$2" prev
  prev=$(git_as rev-parse HEAD)
  log "deploying ${prev:0:7} -> ${target:0:7} ($mode)"

  if [ "$mode" = "hard" ]; then git_as reset --hard "$target" >>"$LOG" 2>&1; else git_as merge --ff-only "$target" >>"$LOG" 2>&1; fi
  if [ $? -ne 0 ]; then
    log "ERROR: could not move the working tree to ${target:0:7}; nothing changed. Resolve manually."
    echo "$target" > "$STATE/last_failed"; return 1
  fi

  fix_permissions; clear_caches
  if health_check; then
    publish_version
    echo "$target" > "$STATE/last_ok"; rm -f "$STATE/last_failed"
    log "deployed ${target:0:7} OK"
    return 0
  fi

  log "ERROR: health check failed after deploying ${target:0:7}; rolling back to ${prev:0:7}"
  git_as reset --hard "$prev" >>"$LOG" 2>&1
  fix_permissions; clear_caches
  if health_check; then log "rolled back to ${prev:0:7}; site healthy"; else log "CRITICAL: site still unhealthy after rollback to ${prev:0:7}"; fi
  publish_version
  echo "$target" > "$STATE/last_failed"
  return 1
}

fetch_remote() { timeout 120 sudo -u "$CODE_USER" -H git -C "$APP" fetch --quiet origin "$BRANCH" 2>>"$LOG"; }

mkdir -p "$STATE"; touch "$LOG"
exec 9>"$LOCK"
flock -n 9 || { echo "another nigma-deploy is running" >&2; exit 0; }

case "${1:-}" in
  --status)
    echo "runtime:     $RUNTIME"
    [ "$RUNTIME" = "docker" ] && { echo "environment: $(nigma-docker-bootstrap --check >/dev/null 2>&1 && echo healthy || echo NOT-HEALTHY)"; }
    echo "current:     $(git_as rev-parse --short HEAD)"
    echo "live file:   $(head -1 "$VERSION_FILE" 2>/dev/null | cut -c1-7)"
    echo "pinned:      $(cat "$STATE/pinned" 2>/dev/null | cut -c1-7 || true)"
    echo "last failed: $(cat "$STATE/last_failed" 2>/dev/null | cut -c1-7 || true)"
    exit 0 ;;
  --unpin)
    rm -f "$STATE/pinned"; log "unpinned; auto-deploys resume"; exit 0 ;;
  --pin)
    sha="${2:-}"; [ -n "$sha" ] || { echo "usage: nigma-deploy --pin <sha>" >&2; exit 2; }
    ensure_runtime now || exit 1
    fetch_remote
    full=$(git_as rev-parse --verify "$sha^{commit}" 2>/dev/null) || { echo "unknown commit $sha" >&2; exit 2; }
    deploy "$full" hard && echo "$full" > "$STATE/pinned" && log "pinned to ${full:0:7}; auto-deploys paused (run: nigma-deploy --unpin)"
    exit $? ;;
  --force)
    ensure_runtime now || exit 1
    fetch_remote; deploy "$(git_as rev-parse "origin/$BRANCH")" hard; exit $? ;;
  "") ;;
  *) echo "unknown option: $1" >&2; exit 2 ;;
esac

# ---- normal (timer) run ----
ensure_runtime || exit 1            # docker runtime: environment present? skip : build it first
[ -f "$STATE/pinned" ] && exit 0
fetch_remote || { log "fetch failed (network?); will retry"; exit 0; }

cur=$(git_as rev-parse HEAD); new=$(git_as rev-parse "origin/$BRANCH")
if [ "$cur" = "$new" ]; then
  [ "$(head -1 "$VERSION_FILE" 2>/dev/null)" = "$cur" ] || publish_version
  exit 0
fi
# do not retry a commit that already failed and was rolled back
[ "$(cat "$STATE/last_failed" 2>/dev/null)" = "$new" ] && exit 0
if ! git_as merge-base --is-ancestor "$cur" "$new"; then
  log "ERROR: server is not an ancestor of origin/$BRANCH (local commits or force-push); refusing to auto-deploy"
  exit 1
fi
deploy "$new" ff
