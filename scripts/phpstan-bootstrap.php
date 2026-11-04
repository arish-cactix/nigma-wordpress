<?php
/**
 * PHPStan bootstrap: constants WordPress defines at runtime that the
 * wordpress-stubs package does not declare. Used for static analysis only.
 *
 * @package NIGMA
 */

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/var/www/html/wp-content' );
}
