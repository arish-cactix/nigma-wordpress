<?php // phpcs:ignore
/**
 * WP CLI entry point.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4;

use WPMUDEV\Snapshot4\Helper\Singleton;
use WP_CLI;

/**
 * WP CLI class
 */
class Cli extends Singleton {

	/**
	 * Command class instances
	 *
	 * @var array
	 */
	protected static $command_instances = array();

	/**
	 * Inits WP CLI commands
	 */
	public function init() {
		$command_classes = self::get_command_classes();
		$this->load_commands( $command_classes );
	}

	/**
	 * Returns list of command classes
	 *
	 * @return array
	 */
	private static function get_command_classes() {
		$dir = __DIR__ . '/cli/command';

		$classes = array();
		foreach ( scandir( $dir ) as $file ) {
			$matches = array();
			if ( preg_match( '/^class\-(.+?)\.php/', $file, $matches ) ) {
				$class     = __NAMESPACE__ . '\\Cli\\Command\\' . ucfirst( $matches[1] );
				$classes[] = $class;
			}
		}

		return $classes;
	}

	/**
	 * Register commands to WP-CLI
	 *
	 * @param array $command_classes Classes with commands.
	 */
	public function load_commands( array $command_classes ) {
		foreach ( $command_classes as $class ) {
			$commands = $class::get_commands();
			foreach ( $commands as $item ) {
				list( $command, $method ) = $item;

				$func = function ( $args, $assoc_args ) use ( $class, $method ) {
					call_user_func( array( $this, 'on_command' ), $class, $method, $args, $assoc_args );
				};
				WP_CLI::add_command( $command, $func );
			}
		}
	}

	/**
	 * Instantiate command class
	 *
	 * @param stiring $class_name Command class name.
	 * @return \WPMUDEV\Snapshot4\Cli\Command
	 */
	protected static function get_command_class_instance( $class_name ) {
		if ( ! isset( self::$command_instances[ $class_name ] ) ) {
			self::$command_instances[ $class_name ] = new $class_name();
		}
		return self::$command_instances[ $class_name ];
	}

	/**
	 * Callback function for WP-CLI
	 *
	 * @param string $class_name Command class.
	 * @param string $method Command class method.
	 * @param array  $args Command args.
	 * @param array  $assoc_args Command keys.
	 * @return mixed
	 */
	public function on_command( $class_name, $method, $args, $assoc_args ) {
		$instance = self::get_command_class_instance( $class_name );
		return $instance->$method( $args, $assoc_args );
	}
}