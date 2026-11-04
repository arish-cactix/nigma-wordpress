<?php
/**
 * PHP error logging configuration.
 *
 * Writes PHP errors silently to debug.log without exposing them to visitors.
 *
 * @package NIGMA
 */

/*
 * Intentional runtime ini_set() calls: production runs with WP_DEBUG off, and
 * WP_DEBUG_LOG only takes effect when WP_DEBUG is on, so it cannot be used to
 * keep error logging enabled here. Display of errors stays disabled elsewhere.
 */
// phpcs:disable WordPress.PHP.IniSet -- Intentional; see the comment above.
ini_set( 'log_errors', '1' );
ini_set( 'error_log', WP_CONTENT_DIR . '/debug.log' );
// phpcs:enable WordPress.PHP.IniSet
