<?php
/**
 * PHP error logging configuration.
 *
 * Writes PHP errors silently to debug.log without exposing them to visitors.
 *
 * @package NIGMA
 */

ini_set( 'log_errors', '1' );
ini_set( 'error_log', WP_CONTENT_DIR . '/debug.log' );
