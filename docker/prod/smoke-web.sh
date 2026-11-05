#!/bin/bash
#
# Smoke test for the Docker WEB tier (OpenLiteSpeed + lsphp83 + our config).
#
# Starts only the web container (no database) against a throw-away fake docroot,
# using the same mounts as docker-compose.yml, and asserts the hardening and edge
# rules. Runs rootless with podman or with docker; needs no root and no real site.
#
#   bash docker/prod/smoke-web.sh
#
set -uo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$HERE/../.." && pwd)"
IMAGE="${WEB_IMAGE:-docker.io/litespeedtech/openlitespeed:1.9.4-lsphp83}"
HP="${SMOKE_HTTP_PORT:-18080}"; SP="${SMOKE_HTTPS_PORT:-18443}"
NAME="nigma-smoke-web-$$"
RT="$(command -v podman || command -v docker || true)"
[ -n "$RT" ] || { echo "need podman or docker"; exit 2; }
IS_PODMAN=0; [[ "$RT" == *podman ]] && IS_PODMAN=1
# On SELinux hosts (e.g. Fedora) rootless podman needs a :Z label on bind mounts; Docker on Ubuntu does not.
Z=""; [ "$IS_PODMAN" = 1 ] && Z="Z"

W="$(mktemp -d)"; pass=0; fail=0
cleanup() { $RT rm -f "$NAME" >/dev/null 2>&1; if [ "$IS_PODMAN" = 1 ]; then podman unshare rm -rf "$W" 2>/dev/null; fi; rm -rf "$W" 2>/dev/null; }
trap cleanup EXIT

ok()   { printf '  \033[32mPASS\033[0m %s\n' "$1"; pass=$((pass+1)); }
bad()  { printf '  \033[31mFAIL\033[0m %s  (%s)\n' "$1" "${2:-}"; fail=$((fail+1)); }
chk()  { if [ "$2" = "$3" ]; then ok "$1"; else bad "$1" "got '$2', want '$3'"; fi; }
code() { curl -sk -o /dev/null -w '%{http_code}' --max-time 15 "$@"; }

echo "== preparing fixtures in $W"
mkdir -p "$W"/app/wp-content/{uploads,litespeed,cache} "$W"/{conf/lsws,acme/.well-known/acme-challenge,le/live/smoke,lscache,logs}
touch "$W/app/wp-content/.litespeed_conf.dat" "$W/app/wp-content/debug.log"
echo "User-agent: *" > "$W/app/robots.txt"
cat > "$W/app/index.php" <<'PHP'
<?php
// Fake WordPress front controller: reports what PHP can see.
if (isset($_GET['probe'])) {
    $u = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown';
    $docroot_w = @file_put_contents(__DIR__ . '/should-not-write.txt', 'x') !== false;
    $uploads_w = @file_put_contents(__DIR__ . '/wp-content/uploads/ok.txt', 'x') !== false;
    header('Content-Type: application/json');
    echo json_encode([
        'php' => PHP_VERSION, 'user' => $u, 'memory_limit' => ini_get('memory_limit'),
        'disable_functions' => ini_get('disable_functions'), 'exec_exists' => function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions')))),
        'docroot_writable' => $docroot_w, 'uploads_writable' => $uploads_w,
        'extensions' => array_values(array_intersect(['imagick', 'redis', 'mysqli', 'intl', 'gd', 'curl', 'zip', 'sodium', 'soap', 'xml', 'mbstring'], get_loaded_extensions())),
    ]);
    exit;
}
echo "front-controller";
PHP
echo "challenge-ok" > "$W/acme/.well-known/acme-challenge/probe-token"

# render config exactly like the bootstrap does (see scripts/deploy/nigma-docker-bootstrap.sh)
render() {
  local cert="smoke"
  sed "s#@@CERT_NAME@@#$cert#g" "$HERE/lsws/httpd_config.conf" > "$W/conf/lsws/httpd_config.conf"
  python3 -I - "$HERE/lsws/vhconf.conf.tpl" "$ROOT/scripts/deploy/vhost-edge-rules.conf" "$cert" > "$W/conf/lsws/vhconf.conf" <<'PY'
import sys
tpl = open(sys.argv[1]).read(); rules = open(sys.argv[2]).read().rstrip("\n"); cert = sys.argv[3]
print(tpl.replace("@@EDGE_RULES@@", rules).replace("@@CERT_NAME@@", cert), end="")
PY
}
render
openssl req -x509 -nodes -newkey rsa:2048 -days 2 -subj "/CN=smoke.local" -keyout "$W/le/live/smoke/privkey.pem" -out "$W/le/live/smoke/fullchain.pem" 2>/dev/null
cp "$HERE/php/99-nigma.ini" "$W/conf/99-nigma.ini"
chmod -R a+rX "$W"

# www-data (uid 33) must own the writable places; under rootless podman that needs a user-namespace chown
# (logs/ stays root-owned: the OpenLiteSpeed master process, running as root, writes the logs)
for d in app/wp-content/uploads app/wp-content/litespeed app/wp-content/cache app/wp-content/.litespeed_conf.dat app/wp-content/debug.log lscache; do
  if [ "$IS_PODMAN" = 1 ]; then podman unshare chown -R 33:33 "$W/$d"; else sudo chown -R 33:33 "$W/$d"; fi
done

echo "== starting $IMAGE"
$RT run -d --name "$NAME" -p "$HP:80" -p "$SP:443" \
  -v "$W/conf/lsws/httpd_config.conf:/usr/local/lsws/conf/httpd_config.conf:ro${Z:+,$Z}" \
  -v "$W/conf/lsws/vhconf.conf:/usr/local/lsws/conf/vhosts/nigma/vhconf.conf:ro${Z:+,$Z}" \
  -v "$W/conf/99-nigma.ini:/usr/local/lsws/lsphp83/etc/php/8.3/mods-available/99-nigma.ini:ro${Z:+,$Z}" \
  -v "$W/app:/var/www/nigma:ro${Z:+,$Z}" \
  -v "$W/app/wp-content/uploads:/var/www/nigma/wp-content/uploads${Z:+:$Z}" \
  -v "$W/app/wp-content/litespeed:/var/www/nigma/wp-content/litespeed${Z:+:$Z}" \
  -v "$W/app/wp-content/cache:/var/www/nigma/wp-content/cache${Z:+:$Z}" \
  -v "$W/app/wp-content/.litespeed_conf.dat:/var/www/nigma/wp-content/.litespeed_conf.dat${Z:+:$Z}" \
  -v "$W/app/wp-content/debug.log:/var/www/nigma/wp-content/debug.log${Z:+:$Z}" \
  -v "$W/le:/etc/letsencrypt:ro${Z:+,$Z}" -v "$W/acme:/var/www/acme:ro${Z:+,$Z}" \
  -v "$W/lscache:/usr/local/lsws/cachedata${Z:+:$Z}" -v "$W/logs:/usr/local/lsws/logs${Z:+:$Z}" \
  "$IMAGE" >/dev/null || { echo "container failed to start"; exit 1; }

echo "== waiting for the web server"
up=0; for i in $(seq 1 40); do [ "$(code "https://127.0.0.1:$SP/")" != "000" ] && { up=1; break; }; sleep 2; done
[ "$up" = 1 ] && ok "web server answers on HTTPS" || { bad "web server did not start"; $RT logs "$NAME" 2>&1 | tail -15; echo; echo "RESULT: $pass passed, $fail failed"; exit 1; }
H=127.0.0.1; B="https://$H:$SP"

echo "== rendered config is well-formed (a real-host test found rules duplicated into a comment)"
chk "no unresolved @@MARKER@@ in the rendered configs" "$(grep -c '@@[A-Z_]*@@' "$W/conf/lsws/httpd_config.conf" "$W/conf/lsws/vhconf.conf" | awk -F: '{s+=$2} END {print s+0}')" "0"
chk "edge rules appear exactly once in the vhost config" "$(grep -c 'Spam doorway / probe URLs' "$W/conf/lsws/vhconf.conf")" "1"
errs="$(grep -E '\[(ERROR|CRIT)\]' "$W/logs/error.log" 2>/dev/null | grep -v 'RCS' | sed -E 's/^[0-9-]+ [0-9:.]+ //' | cut -c1-120)"
chk "OpenLiteSpeed logged no configuration errors at startup" "$(printf '%s' "$errs" | grep -c .)" "0"
[ -z "$errs" ] || { echo "$errs" | head -5 | sed 's/^/      /'; }

echo "== PHP runtime and hardening"
J=$(curl -sk --max-time 15 "$B/?probe=1")
jf() { python3 -I -c "import sys,json; d=json.load(sys.stdin); v=d['$1']; print(','.join(map(str,v)) if isinstance(v,list) else v)" <<<"$J" 2>/dev/null; }
case "$(jf php)" in 8.3.*) ok "PHP 8.3 ($(jf php))";; *) bad "PHP version" "$(jf php)";; esac
chk "PHP runs as www-data" "$(jf user)" "www-data"
chk "memory_limit is 256M" "$(jf memory_limit)" "256M"
chk "exec() is disabled" "$(jf exec_exists)" "False"
chk "docroot is read-only to PHP" "$(jf docroot_writable)" "False"
chk "uploads/ is writable by PHP" "$(jf uploads_writable)" "True"
exts="$(jf extensions)"; for e in imagick redis mysqli intl gd curl zip sodium soap; do [[ ",$exts," == *",$e,"* ]] && ok "extension $e loaded" || bad "extension $e missing"; done

echo "== blocked paths (expect 403)"
for p in /.git/config /.env /wp-config.php /xmlrpc.php /wp-content/uploads/shell.php /wp-content/uploads/x.php7; do chk "$p" "$(code "$B$p")" "403"; done

echo "== spam shapes (expect 410)"
for p in /xylan/dresden/013240525101 /vicunna/fratricidal/l0i2f3e0l2i6k8e9n5e6s9s4 /aborsement/t0e2a5p0o3y446023/sitemap.xml /sitemap-child-2431.xml "/?sitemap415.xml" /goods.php /wp-log1n.php; do chk "$p" "$(code "$B$p")" "410"; done

echo "== legitimate URLs (expect 200)"
for p in / /about-us/ /contact-us/ /services/home-lifts/ /sitemap_index.xml "/?s=lift" /robots.txt /wp-json/wp/v2/posts; do chk "$p" "$(code "$B$p")" "200"; done

echo "== apex host redirect"
r=$(curl -sk -o /dev/null -w '%{http_code} %{redirect_url}' -H 'Host: nigma.ae' "$B/about-us/?x=1")
chk "nigma.ae/about-us/?x=1 -> www in one hop" "$r" "301 https://www.nigma.ae/about-us/?x=1"
chk "spam on the apex gets 410 (no redirect)" "$(code -H 'Host: nigma.ae' "$B/xylan/dresden/013240525101")" "410"

echo "== certificate challenge path"
chk "ACME token is served from the acme webroot" "$(curl -sk -H 'Host: nigma.ae' --max-time 15 "http://$H:$HP/.well-known/acme-challenge/probe-token")" "challenge-ok"
chk "ACME path is not redirected on the apex" "$(code -H 'Host: nigma.ae' "http://$H:$HP/.well-known/acme-challenge/probe-token")" "200"

echo "== TLS"
subj=$(echo | openssl s_client -connect "$H:$SP" -servername smoke.local 2>/dev/null | openssl x509 -noout -subject 2>/dev/null)
case "$subj" in *smoke.local*) ok "serves the mounted certificate";; *) bad "certificate" "$subj";; esac

echo; echo "RESULT: $pass passed, $fail failed"
[ "$fail" = 0 ]
