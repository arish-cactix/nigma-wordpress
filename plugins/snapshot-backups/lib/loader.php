<?php // phpcs:ignore
/**
 * Snapshot autoloader
 *
 * @package snapshot
 */

/**
 * Class name to file mapping procedure
 *
 * @param string $class_name Class name.
 */
function snapshot_resolve_class( $class_name ) {
	$matches = array();

	if ( ! preg_match( '/^WPMUDEV\\\\Snapshot4\\\\(.+)$/', $class_name, $matches ) ) {
		return false;
	}

	$class_name = $matches[1];
	$raw        = explode( '\\', strtolower( $class_name ) );

	if ( false !== strpos( $class_name, 'Traits' ) ) {
		$file = 'trait-' . array_pop( $raw ) . '.php';
	} elseif ( false !== strpos( $class_name, '_' ) ) {
			$file = 'class-' . str_replace( '_', '-', array_pop( $raw ) ) . '.php';
	} else {
		$file = 'class-' . array_pop( $raw ) . '.php';
	}

	$path = __DIR__ . '/snapshot/' . join( DIRECTORY_SEPARATOR, $raw ) . "/{$file}";

	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

spl_autoload_register( 'snapshot_resolve_class' );