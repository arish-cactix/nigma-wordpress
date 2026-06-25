#!/usr/bin/env bash
set -Eeuo pipefail

if [[ "${1:-}" == apache2* ]] || [ "${1:-}" = 'php-fpm' ]; then
	cd /var/www/html

	uid="$(id -u)"
	gid="$(id -g)"
	if [ "$uid" = '0' ]; then
		user='www-data'
		group='www-data'
	else
		user="$uid"
		group="$gid"
	fi

	if [ ! -e index.php ] && [ ! -e wp-includes/version.php ]; then
		echo >&2 "WordPress not found in $PWD - copying core without bundled wp-content defaults..."
		tar \
			--create \
			--file - \
			--directory /usr/src/wordpress \
			--owner "$user" \
			--group "$group" \
			--exclude './wp-content/index.php' \
			--exclude './wp-content/plugins' \
			--exclude './wp-content/themes' \
			--exclude './wp-content/mu-plugins' \
			. | tar --extract --file - --no-overwrite-dir
		echo >&2 "Complete! WordPress core has been copied to $PWD"
	fi
fi

if ! command -v wp >/dev/null 2>&1; then
	echo >&2 "Installing WP-CLI..."
	curl -sL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp
	chmod +x /usr/local/bin/wp
fi

exec docker-entrypoint.sh "$@"
