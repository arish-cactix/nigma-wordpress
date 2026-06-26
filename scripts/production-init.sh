#!/usr/bin/env bash
#
# Run ONCE on the EC2 server to migrate from the old full-repo checkout
# inside wp-content/ to a clean sparse checkout at the WordPress root.
#
# After this runs, only wp-content/ from the repo is tracked on the server.
# No docker/, docs/, .github/, or other non-WordPress files will land here.
#
# Usage:
#   bash scripts/production-init.sh git@github.com:YOUR-ORG/nigma-wordpress.git
#

set -Eeuo pipefail

REPO_URL="${1:?Usage: $0 <repo-ssh-url>}"
WP_ROOT="/var/www/nigma"
OLD_REPO="$WP_ROOT/wp-content"

echo "=== NIGMA: Production sparse checkout setup ==="
echo "WordPress root : $WP_ROOT"
echo "Repository     : $REPO_URL"
echo ""

# ── Step 1: Remove the old full-repo git setup from wp-content/ ─────────────
if [ -d "$OLD_REPO/.git" ]; then
    echo "[1/4] Removing legacy git repo from wp-content/..."
    rm -rf "$OLD_REPO/.git"
fi

# Remove non-WordPress files that landed there from the old checkout
for artifact in .github docker docs scripts README.md AGENTS.md PROJECT.md \
                .gitignore .env.example; do
    rm -rf "$OLD_REPO/$artifact"
done

# ── Step 2: Initialise a new git repo at the WordPress root ─────────────────
echo "[2/4] Initialising git at $WP_ROOT..."
cd "$WP_ROOT"

if [ -d ".git" ]; then
    echo "  .git already exists — skipping git init."
else
    git init
    git remote add origin "$REPO_URL"
fi

# ── Step 3: Configure sparse checkout — wp-content/ only ────────────────────
echo "[3/4] Configuring sparse checkout (wp-content/ only)..."
git sparse-checkout init --cone
git sparse-checkout set wp-content

# ── Step 4: Fetch and checkout main ─────────────────────────────────────────
echo "[4/4] Fetching and checking out main..."
git fetch origin main
git checkout main

echo ""
echo "=== Done. ==="
echo "Only wp-content/ is now tracked. Deploy with:"
echo "  cd /var/www/nigma && git pull origin main"
