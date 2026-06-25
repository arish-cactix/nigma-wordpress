<?php // phpcs:ignore
/**
 * WP CLI command class.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Cli;

/**
 * WP CLI command class
 */
abstract class Command {

	/**
	 * Returns list of available command in a class
	 *
	 * @return array [command, method]
	 */
	public static function get_commands() {
		$reflection = new \ReflectionClass( static::class );
		// @TODO: convert to dash-case
		$base_command = 'snapshot ' . strtolower( $reflection->getShortName() );

		$result  = array();
		$methods = $reflection->getMethods( \ReflectionMethod::IS_PUBLIC );
		foreach ( $methods as $method ) {
			$matches = array();
			if ( preg_match( '/^command_(.+)$/', $method->name, $matches ) ) {
				$command  = $base_command . ' ' . str_replace( '_', '-', $matches[1] );
				$result[] = array( $command, $method->name );
			}
		}

		return $result;
	}
}