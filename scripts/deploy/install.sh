#!/bin/bash
#
# Install (or update) the pull-based deploy on the NIGMA server. Run as root
# from a directory that contains nigma-deploy.sh and the two systemd units.
#
#   sudo bash install.sh
#
# Idempotent. Does not touch the site; it only installs the script and timer.
set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

install -o root -g root -m 755 "$HERE/nigma-deploy.sh"      /usr/local/sbin/nigma-deploy
install -o root -g root -m 644 "$HERE/nigma-deploy.service" /etc/systemd/system/nigma-deploy.service
install -o root -g root -m 644 "$HERE/nigma-deploy.timer"   /etc/systemd/system/nigma-deploy.timer

cat > /etc/logrotate.d/nigma-deploy <<'EOF'
/var/log/nigma-deploy.log {
    weekly
    rotate 8
    compress
    delaycompress
    missingok
    notifempty
}
EOF

install -d -o root -g root -m 755 /var/lib/nigma-deploy
systemctl daemon-reload
systemctl enable --now nigma-deploy.timer
systemctl list-timers nigma-deploy.timer --no-pager
