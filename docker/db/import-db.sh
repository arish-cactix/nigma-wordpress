#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DATABASE_DIR="$ROOT_DIR/database"
ENV_FILE="$ROOT_DIR/.env"

if [ -f "$ENV_FILE" ]; then
	set -a
	# shellcheck disable=SC1090
	. "$ENV_FILE"
	set +a
fi

COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-nigma-wordpress}"
MARIADB_DATABASE="${MARIADB_DATABASE:-nigma_local}"
MARIADB_ROOT_PASSWORD="${MARIADB_ROOT_PASSWORD:-}"

if [ -z "$MARIADB_ROOT_PASSWORD" ]; then
	echo "MARIADB_ROOT_PASSWORD is not set. Create .env from .env.example before importing." >&2
	exit 1
fi

dump_path="${1:-}"

if [ -z "$dump_path" ]; then
	if [ ! -d "$DATABASE_DIR" ]; then
		echo "Database directory not found: $DATABASE_DIR" >&2
		exit 1
	fi

	dump_path="$(
		find "$DATABASE_DIR" -maxdepth 1 -type f \( -name '*.sql' -o -name '*.sql.gz' \) -printf '%T@ %p\n' \
			| sort -nr \
			| sed -n '1s/^[^ ]* //p'
	)"
fi

if [ -z "$dump_path" ]; then
	echo "No .sql or .sql.gz dump found in $DATABASE_DIR" >&2
	exit 1
fi

if [ ! -f "$dump_path" ]; then
	echo "Dump file not found: $dump_path" >&2
	exit 1
fi

case "$dump_path" in
	*.sql|*.sql.gz) ;;
	*)
		echo "Unsupported dump type: $dump_path" >&2
		echo "Expected a .sql or .sql.gz file." >&2
		exit 1
		;;
esac

echo "Starting MariaDB service..."
docker compose --project-directory "$ROOT_DIR" up -d db

echo "Waiting for MariaDB to accept connections..."
for attempt in $(seq 1 60); do
	if docker compose --project-directory "$ROOT_DIR" exec -T db mariadb-admin ping -uroot -p"$MARIADB_ROOT_PASSWORD" --silent >/dev/null 2>&1; then
		break
	fi

	if [ "$attempt" -eq 60 ]; then
		echo "MariaDB did not become ready in time." >&2
		exit 1
	fi

	sleep 2
done

echo "Resetting local database '$MARIADB_DATABASE'..."
docker compose --project-directory "$ROOT_DIR" exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" <<SQL
DROP DATABASE IF EXISTS \`$MARIADB_DATABASE\`;
CREATE DATABASE \`$MARIADB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL

echo "Importing $dump_path into '$MARIADB_DATABASE'..."
if [[ "$dump_path" == *.gz ]]; then
	gunzip -c "$dump_path" | docker compose --project-directory "$ROOT_DIR" exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"
else
	docker compose --project-directory "$ROOT_DIR" exec -T db mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE" < "$dump_path"
fi

echo "Import complete."

local_url="${WORDPRESS_HOME:-http://localhost:8080}"
production_url="https://www.nigma.ae"

echo "Replacing production URLs ($production_url → $local_url)..."
# First pass: full URL with protocol
docker compose --project-directory "$ROOT_DIR" exec -T wordpress \
	php -d memory_limit=512M /usr/local/bin/wp search-replace \
	"$production_url" "$local_url" \
	--all-tables --quiet --allow-root
# Second pass: bare domain (catches protocol-relative and non-https variants)
production_domain="www.nigma.ae"
local_domain="localhost:8080"
docker compose --project-directory "$ROOT_DIR" exec -T wordpress \
	php -d memory_limit=512M /usr/local/bin/wp search-replace \
	"$production_domain" "$local_domain" \
	--all-tables --quiet --allow-root

echo "URL replacement complete."

echo "Clearing Avada compiled CSS/JS cache..."
docker compose --project-directory "$ROOT_DIR" exec -T wordpress \
	php -d memory_limit=512M /usr/local/bin/wp option delete \
	fusion_dynamic_css_ids fusion_dynamic_css_time --allow-root 2>/dev/null || true
docker compose --project-directory "$ROOT_DIR" exec -T wordpress \
	bash -c "rm -f /var/www/html/wp-content/uploads/fusion-styles/*.css \
	              /var/www/html/wp-content/uploads/fusion-scripts/*.js 2>/dev/null; true"

echo "Deactivating production-only plugins..."
docker compose --project-directory "$ROOT_DIR" exec -T wordpress \
	php -d memory_limit=512M /usr/local/bin/wp plugin deactivate \
	litespeed-cache pwa wp-defender gravityformsrecaptcha \
	--allow-root --quiet

echo "Local environment ready."
